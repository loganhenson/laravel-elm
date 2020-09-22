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
              state: null,
              subscribed: [],
              // API.
              api: {
                subscribe: function (port, callback) {
                  if (port in current.app.ports) {
                    current.subscribed.push(port)
                    current.app.ports[port].subscribe(callback)
                  }
                },
                send: function (port, value) {
                  if (port in current.app.ports) {
                    current.app.ports[port].send(value)
                  }
                }
              },
              register: function (module, callback) {
                window.addEventListener('elm-ready', () => {
                  if (current.page.startsWith(module) || module === '*') {
                    callback(this.api)
                  }
                })
              }
            }

            window.LaravelElm = current

            // Utilities.
            function get(obj, path, fallback = null) {
              if (obj === null || typeof obj !== 'object') {
                return fallback
              }

              let segments = path.split('.')

              for (let segment of segments) {
                if (segment in obj) {
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
              <?php if (config('app.debug')): ?>
              sendToDevtools()
              <?php endif ?>
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

              current.subscribed = []

              window.dispatchEvent(new CustomEvent('elm-ready'))
            }

            function updateHistoryAndUrl(url, page, props) {
              current.url = url
              if (url === window.location.pathname + window.location.search) {
                window.history.replaceState({ url: url, page: page, props: props }, '', url)
              } else {
                window.history.pushState({ url: url, page: page, props: props }, '', url)
              }
            }

            <?php if (config('app.debug')): ?>
            function sendToDevtools() {
              window.postMessage({
                type: 'laravel-elm-devtools',
                data: {
                  props: current.props,
                  state: current.state,
                  url: current.url,
                  page: current.page,
                  ports: current.subscribed,
                }
              }, '*')
            }

            window.addEventListener('laravel-elm-devtools-connect', () => {
              sendToDevtools()
            })

            window.addEventListener('laravel-elm-hot-reload-props-only', () => {
              console.warn('Page state definition has changed, hot-reloading props only')
              delete (window.Elm)
              let script = document.createElement('script')
              script.async = false
              script.src = '/js/elm.js'
              document.head.appendChild(script)
              script.addEventListener('load', function () {
                setNewPage(current.url, current.page, current.props)
                script.remove()
              })
            })

            window.addEventListener('laravel-elm-hot-reload', async (event) => {
              delete Elm
              eval(event.detail)
            })
            <?php endif ?>

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

              <?php if (config('app.debug')): ?>
              sendToDevtools()
              <?php endif ?>
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

            function encrypted_csrf_token_from_cookie() {
              const cookie_name = 'XSRF-TOKEN'
              const match = document.cookie.match(new RegExp(`(^|;\\s*)(${cookie_name})=([^;]*)`))
              return (match ? decodeURIComponent(match[3]) : null)
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

              const xsrf = encrypted_csrf_token_from_cookie()
              if (xsrf) {
                headers['X-XSRF-Token'] = xsrf
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

              // Handle non-laravel-elm redirects
              if (!result.headers.has('x-laravel-elm') && result.redirected) {
                window.location = result.url
                return
              }

              // Handle dd() responses (200, but start with a script tag)
              <?php if (config('app.debug')): ?>
              if (!result.headers.has('x-laravel-elm')) {
                const response = await result.clone().text()
                if (response.indexOf('<script>') === 0) {
                  showModal(response)
                  return
                }
              }
              <?php endif ?>

              // Assumed to be a json response at this point.
              try {
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
              } catch (e) {
                console.warn(e)
              }
            }

            LaravelElm.register('*', (page) => {
              <?php if (config('app.debug')): ?>
              page.subscribe('sendStateToDevtools', (state) => {
                current.state = state
                sendToDevtools()
              })
              <?php endif ?>

              page.subscribe('sendScroll', setViewports)

              page.subscribe('get', url => {
                visit(url)
              })

              page.subscribe('post', ({ url, data }) => {
                visit(url, { method: 'POST', data })
              })

              page.subscribe('patch', ({ url, data }) => {
                data._method = 'PATCH'
                visit(url, { method: 'POST', data })
              })

              page.subscribe('delete', url => {
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
                  // @TODO: Prompt user to reload instead.
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
