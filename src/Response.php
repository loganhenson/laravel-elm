<?php

namespace Tightenco\Elm;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Response as ResponseFactory;

class Response implements Responsable
{
    protected ?string $version;
    protected bool $hasSW;
    protected string $page;
    protected array $props;
    protected array $viewData = [];

    public function __construct(string $page, array $props)
    {
        $manifestPath = public_path('mix-manifest.json');

        $this->version = file_exists($manifestPath) ? md5_file($manifestPath) : null;
        $this->hasSW = file_exists(public_path('sw.js'));
        $this->page = $page;
        $this->props = $props;
    }

    public function toResponse($request)
    {
        $props = array_map(function ($prop) {
            return $prop instanceof Closure ? App::call($prop) : $prop;
        }, $this->props);

        if ($request->header('X-Laravel-Elm')) {
            return new JsonResponse([
                'page' => $this->page,
                'props' => $props,
                'url' => $request->getRequestUri(),
                'version' => $this->version,
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
            // State.
            let current = {
              version: <?= $this->version ? "'{$this->version}'" : 'null' ?>,
              element: null,
              app: null,
              url: null,
              page: null,
              modal: null,
              props: null,
            }

            // Utilities.
            function get(obj, path, fallback = null) {
              let segments = path.split('.')

              for (let segment of segments) {
                if (obj.hasOwnProperty(segment)) {
                  obj = obj[segment]
                } else {
                  return fallback
                }
              }

              return obj
            }

            function debounce(callback, wait = 250) {
              let timer
              return (...args) => {
                clearTimeout(timer)
                timer = setTimeout(() => callback(...args), wait)
              }
            }

            // Core.
            const setViewports = debounce(({ key, x, y }) => {
              current.props.viewports[key] = { x, y }
              updateHistoryAndUrl(current.url, current.page, current.props)
            }, 100)

            function sendNewProps(props) {
              current.props = props
              current.app.ports.receiveNewProps.send(props)
            }

            function setNewPage(url, page, props) {
              current.page = page
              if (current.element) {
                current.element.remove()
              }
              current.element = createAppElement()

              let app = get(Elm, page)
              if (!app) {
                console.warn('No Elm page found named: ' + page)
                return
              }

              current.app = app.Main.init({
                node: current.element,
                flags: props,
              })
              window.current = current

              window.dispatchEvent(new CustomEvent('elm-ready'))
            }

            function updateHistoryAndUrl(url, page, props) {
              current.url = url
              if (url === window.location.pathname + window.location.search) {
                window.history.replaceState({ url: url, page: page, props: props }, '', url)
              } else {
                window.history.pushState({ url: url, page: page, props: props }, '', url)
              }

              <?php if (config('app.debug')): ?>
              window.postMessage({
                type: 'laravel-elm',
                data: {
                  props: current.props,
                  url: current.url,
                  page: current.page,
                }
              }, '*')
              <?php endif ?>
            }

            function setPage(url, page, props) {
              current.props = {
                ...props,
                viewports: window.history.state ? get(window.history.state, 'props.viewports', {}) : {}
              }

              if (current.page === page) {
                sendNewProps(current.props)
              } else {
                setNewPage(url, page, current.props)
              }

              updateHistoryAndUrl(url, page, current.props)
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

            function buildFormData(formData, data, parentKey) {
              if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File) && !(data instanceof Blob)) {
                Object.keys(data).forEach(key => {
                  buildFormData(formData, data[key], parentKey ? `${parentKey}[${key}]` : key)
                })
              } else {
                const value = data == null ? '' : data

                formData.append(parentKey, value)
              }
            }

            function jsonToFormData(data) {
              const formData = new FormData()

              buildFormData(formData, data)

              return formData
            }

            function startLoading() {
              current.props.loading = true
              window.dispatchEvent(new CustomEvent('elm-loading', { detail: true }))
            }

            function stopLoading() {
              current.props.loading = false
              window.dispatchEvent(new CustomEvent('elm-loading', { detail: false }))
            }

            async function visit(url, { method = 'get', data = {} } = {}) {
              // Sent Request
              startLoading()
              sendNewProps(current.props)

              let result
              const headers = {
                'X-Laravel-Elm': true,
                Accept: 'text/html, application/xhtml+xml',
                'X-Requested-With': 'XMLHttpRequest',
              }

              if (method === 'get') {
                result = await fetch(url, { headers })
              } else {
                result = await fetch(url, {
                  method,
                  headers,
                  body: jsonToFormData(data),
                })
              }

              stopLoading()

              // Handle server errors (non laravel-elm responses)
              if (!result.headers.has('x-laravel-elm') && result.status === 500) {
                showModal(await result.text())
                return
              }

              // Handle non-laravel-elm responses
              if (!result.headers.has('x-laravel-elm') && result.redirected) {
                window.location = result.url
              }

              // Assumed to be a json response at this point.
              const jsonResult = await result.json()

              if (current.version !== jsonResult.version) {
                window.dispatchEvent(new CustomEvent('elm-update-found'))
              }

              // Handle flashed errors without a full page revisit (optimization).
              if (result.headers.has('x-laravel-elm-errors')) {
                sendNewProps({ ...current.props, errors: jsonResult.errors })
                return
              }

              setPage(jsonResult.url, jsonResult.page, jsonResult.props)
            }

            window.addEventListener('elm-ready', () => {
              current.app.ports.sendScroll.subscribe(setViewports)

              current.app.ports.get.subscribe(url => {
                visit(url)
              })

              current.app.ports.post.subscribe(({ url, data }) => {
                visit(url, { method: 'POST', data })
              })

              current.app.ports.patch.subscribe(({ url, data }) => {
                data._method = 'PATCH'
                visit(url, { method: 'POST', data })
              })

              current.app.ports.delete.subscribe(url => {
                visit(url, { method: 'DELETE' })
              })
            })

            window.addEventListener('popstate', async (e) => {
              if (e.state) {
                await setPage(e.state.url, e.state.page, e.state.props)
              }
            })

            window.addEventListener('load', () => {
              setPage(window.location.pathname + window.location.search, "<?= $this->page ?>", <?= json_encode($props) ?>)
            })
          })()
        </script>

        <?php if ($this->hasSW && $this->version): ?>
        <script>
          // Register service worker, if supported, after the load event (to deprioritize it after lazy imports).
          if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
              navigator.serviceWorker.register('/sw.js').then(function (registration) {
                window.addEventListener('elm-update-found', function () {
                  registration.update()
                })

                registration.onupdatefound = function () {
                  window.location.reload()
                }
              })
            })
          }
        </script>
        <?php endif ?>

        <?php return ob_get_clean();
    }
}
