<?php

namespace Tightenco\Elm;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;

class Middleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (! $request->header('X-Laravel-Elm')) {
            return $response;
        }

        if ($response instanceof Redirect && $response->getStatusCode() === 302 && $request->session()->has('errors')) {
            return new JsonResponse([
                'errors' => session()
                    ->get('errors')
                    ->getBag('default')
                    ->getMessages(),
            ], 200, [
                'Vary' => 'Accept',
                'X-Laravel-Elm' => 'true',
                'X-Laravel-Elm-Errors' => 'true',
            ]);
        }

        // Required to attach correct method put/patch/delete to a redirect.
        if ($response instanceof Redirect && $response->getStatusCode() === 302 && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])) {
            $response->setStatusCode(303);
        }

        // Handle user attempting to logout of an expired session.
        if ($response->getStatusCode() === 419) {
            return redirect('/');
        }

        return $response;
    }
}
