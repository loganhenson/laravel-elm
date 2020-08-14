<?php

namespace Tightenco\Elm;

use Closure;
use Symfony\Component\HttpFoundation\RedirectResponse as Redirect;

class Middleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! $request->header('X-Laravel-Elm')) {
            return $response;
        }

        if (
            $response instanceof Redirect
            && $response->getStatusCode() === 302
            && in_array($request->method(), ['PUT', 'PATCH', 'DELETE'])
        ) {
            $response->setStatusCode(303);
        }

        return $response;
    }
}
