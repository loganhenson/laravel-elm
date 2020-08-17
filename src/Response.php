<?php

namespace Tightenco\Elm;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response as ResponseFactory;

class Response implements Responsable
{
    protected string $page;
    protected array $flags;
    protected array $viewData = [];

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

        return ResponseFactory::view('app', $this->viewData + ['elm' => $this->make($props)]);
    }

    /**
     * Bind the given array of variables to the elm program,
     * render the script include,
     * and return the html.
     */
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
              modal: null,
            }

            function sendNewProps(flags) {
              current.app.ports.receiveNewProps.send(flags)
            }

            function setNewPage(url, page, flags) {
              current.page = page
              if (current.element) {
                current.element.remove()
              }
              current.element = createAppElement()

              if (!Elm.hasOwnProperty(page)) {
                console.log('No Elm page found named: ' + page)
                return
              }

              current.app = Elm[page].Main.init({
                node: current.element,
                flags: flags,
              })

              window.dispatchEvent(new CustomEvent('elm-ready'))

              current.url = url
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
              return element
            }

            function escapeHandler(event) {
              if (event.keyCode === 27) {
                hideModal()
              }
            }

            function hideModal() {
              current.modal.outerHTML = ''
              current.modal = null
              document.body.style.overflow = 'visible'
              document.removeEventListener('keydown', escapeHandler)
            }

            function showModal(html) {
              let page = document.createElement('html')
              page.innerHTML = html
              page.querySelectorAll('a').forEach(a => a.setAttribute('target', '_top'))

              current.modal = document.createElement('div')
              current.modal.style.position = 'fixed'
              current.modal.style.left = 0
              current.modal.style.top = 0
              current.modal.style.bottom = 0
              current.modal.style.right = 0
              current.modal.style.width = '100vw'
              current.modal.style.height = '100vh'
              current.modal.style.padding = '50px'
              current.modal.style.boxSizing = 'border-box'
              current.modal.style.backgroundColor = 'rgba(0, 0, 0, .6)'
              current.modal.style.zIndex = 200000
              current.modal.addEventListener('click', hideModal)

              let iframe = document.createElement('iframe')
              iframe.style.backgroundColor = 'white'
              iframe.style.borderRadius = '5px'
              iframe.style.width = '100%'
              iframe.style.height = '100%'
              current.modal.appendChild(iframe)

              document.body.prepend(current.modal)
              document.body.style.overflow = 'hidden'
              iframe.contentWindow.document.open()
              iframe.contentWindow.document.write(page.outerHTML)
              iframe.contentWindow.document.close()

              document.addEventListener('keydown', escapeHandler)
            }

            async function visit(url, { method = 'get', data = {} } = {}) {
              let result
              const headers = {
                'X-Laravel-Elm': true,
                Accept: 'text/html, application/xhtml+xml',
                'X-Requested-With': 'XMLHttpRequest',
              }

              if (method === 'get') {
                result = await fetch(url, { headers })
              } else {
                const formData = new FormData()
                Object.keys(data).forEach(key => formData.append(key, data[key]))
                result = await fetch(url, {
                  method,
                  headers,
                  body: formData,
                })
              }

              // Handle server errors (non laravel-elm responses)
              if (!result.headers.has('x-laravel-elm')) {
                showModal(await result.text())
                return
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
          })()
        </script>

        <?php return ob_get_clean();
    }
}
