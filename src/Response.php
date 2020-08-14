<?php

namespace Tightenco\Elm;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response as ResponseFactory;

class Response implements Responsable
{
    protected $page;
    protected $flags;
    protected $viewData = [];

    public function __construct(string $page, array $flags)
    {
        $this->page = $page;
        $this->flags = $flags;
    }

    public function toResponse($request)
    {
        $props = array_map(function ($prop) {
            return $prop instanceof Closure ? App::call($prop) : $prop;
        }, $this->flags);

        if ($request->header('X-Laravel-Elm')) {
            return new JsonResponse([
                'page' => $this->page,
                'flags' => $props,
                'url' => $request->getRequestUri(),
            ], 200, [
                'Vary' => 'Accept',
                'X-Laravel-Elm' => 'true',
            ]);
        }

        return ResponseFactory::view('app', $this->viewData + ['page' => $this->make($props)]);
    }

    public function make(array $props)
    {
        ob_start(); ?>

        <script>
            (() => {
                let current = {
                    element: null,
                    app: null,
                    url: null,
                    page: null,
                };

                function sendNewProps(flags) {
                    current.app.ports.receiveNewProps.send(flags);
                }

                function setNewPage(url, page, flags) {
                    current.page = page;
                    if (current.element) {
                        current.element.remove();
                    }
                    current.element = createAppElement()

                    current.app = Elm[page].Main.init({
                        node: current.element,
                        flags: flags,
                    })

                    window.dispatchEvent(new CustomEvent('elm-ready'))

                    current.url = url;
                    if (url === window.location.pathname + window.location.search) {
                        window.history.replaceState({ url: url, page: page, flags: flags }, '', url)
                    } else {
                        window.history.pushState({ url: url, page: page, flags: flags }, '', url)
                    }
                }

                function setPage(url, page, flags) {
                    if (current.page === page) {
                        sendNewProps(flags)
                        console.log('new props set: ', flags)
                    } else {
                        setNewPage(url, page, flags)
                        console.log('new page set: ', page, flags)
                    }
                }

                function createAppElement() {
                    let element = document.createElement('div')
                    element.id = 'app'
                    document.body.appendChild(element)
                    return element;
                }

                async function visit(url, { method = 'get', data = {} } = {}) {
                    let result
                    if (method === 'get') {
                        result = await fetch(url, {
                            headers: {
                                'X-Laravel-Elm': true,
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        })
                    } else {
                        const formData = new FormData()
                        Object.keys(data).forEach(key => formData.append(key, data[key]))
                        result = await fetch(url, {
                            method,
                            headers: {
                                'X-Laravel-Elm': true,
                                Accept: 'text/html, application/xhtml+xml',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: formData,
                        })
                    }

                    const jsonResult = await result.json()
                    setPage(jsonResult.url, jsonResult.page, jsonResult.flags)
                }

                window.addEventListener('elm-ready', () => {
                    current.app.ports.get.subscribe(url => {
                        visit(url)
                    })

                    current.app.ports.post.subscribe(({ url, data }) => {
                        visit(url, { method: 'POST', data })
                    })

                    current.app.ports.delete.subscribe(url => {
                        visit(url, { method: 'DELETE' })
                    })
                })

                window.addEventListener('popstate', async (e) => {
                    if (e.state) {
                        await setPage(e.state.url, e.state.page, e.state.flags)
                    }
                })

                window.addEventListener('load', () => {
                    setPage(window.location.pathname, "<?= $this->page ?>", <?= json_encode($props) ?>)
                })
            })();

        </script>

        <?php return ob_get_clean();
    }
}
