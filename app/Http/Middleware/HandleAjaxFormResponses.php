<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\View\ViewErrorBag;
use Symfony\Component\HttpFoundation\Response;

class HandleAjaxFormResponses
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $request->ajax() || $request->isMethod('GET') || ! $response instanceof RedirectResponse) {
            return $response;
        }

        $errors = $this->errorsFromSession($response);

        if ($errors !== []) {
            $request->session()->forget('errors');

            return new JsonResponse([
                'message' => collect($errors)->flatten()->first() ?: 'Please check the highlighted fields.',
                'errors' => $errors,
            ], 422);
        }

        $message = session('success')
            ?? session('status')
            ?? session('error')
            ?? 'Saved successfully.';
        $request->session()->forget(['success', 'status', 'error']);

        return new JsonResponse([
            'message' => $message,
            'redirect' => $response->getTargetUrl(),
        ]);
    }

    /** @return array<string, array<int, string>> */
    private function errorsFromSession(RedirectResponse $response): array
    {
        $errors = $response->getSession()?->get('errors');

        if ($errors instanceof ViewErrorBag) {
            return $errors->getBag('default')->getMessages();
        }

        if ($errors instanceof MessageBag) {
            return $errors->getMessages();
        }

        return [];
    }
}
