<?php

namespace LoganHenson\Elm;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
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
                'errors' => $this->defaultErrorMessages($request->session()->get('errors')),
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

    /**
     * The "default" bag's messages, whichever shape the session holds them in.
     *
     * With `'serialization' => 'json'` (the Laravel 13 skeleton default) the session
     * is saved - converting the ViewErrorBag to a plain array - before this global
     * middleware sees the response.
     */
    protected function defaultErrorMessages(mixed $errors): array|object
    {
        $messages = match (true) {
            $errors instanceof ViewErrorBag => $errors->getBag('default')->getMessages(),
            $errors instanceof MessageBag => $errors->getMessages(),
            is_array($errors) => $errors['default']['messages'] ?? [],
            default => [],
        };

        // Keep encoding as a JSON object (Elm decodes a Dict), even when empty.
        return $messages === [] ? (object) [] : $messages;
    }
}
