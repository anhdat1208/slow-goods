<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrepareSePayWebhook
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isSePayWebhook($request)) {
            return $next($request);
        }

        // SePay does not send Accept: application/json. Without this, Laravel
        // turns ValidationException into a 302 HTML redirect.
        $request->headers->set('Accept', 'application/json');

        $this->normalizeTransferContent($request);
        $this->bridgeHmacToApiKey($request);

        return $next($request);
    }

    private function isSePayWebhook(Request $request): bool
    {
        return $request->is('api/sepay/webhook') || $request->is('sepay/webhook');
    }

    private function normalizeTransferContent(Request $request): void
    {
        foreach (['content', 'description'] as $field) {
            $value = $request->input($field);
            if (! is_string($value) || $value === '') {
                continue;
            }

            // Banks often strip "-" from transfer content: "SG-ABC" → "SG ABC".
            $normalized = preg_replace('/\bSG\s+([A-Z0-9]+)\b/i', 'SG-$1', $value);
            if (is_string($normalized) && $normalized !== $value) {
                $request->merge([$field => $normalized]);
            }
        }
    }

    private function bridgeHmacToApiKey(Request $request): void
    {
        $apiKey = (string) config('sepay.webhook_token', '');
        if ($apiKey === '') {
            return;
        }

        $authorization = (string) $request->header('Authorization', '');
        if (str_contains($authorization, 'Apikey ')) {
            return;
        }

        $signature = (string) $request->header('X-SePay-Signature', '');
        $timestamp = (string) $request->header('X-SePay-Timestamp', '');
        if ($signature === '' || $timestamp === '') {
            return;
        }

        $secret = (string) (config('sepay.webhook_secret') ?: $apiKey);
        if (! $this->validHmac($request, $signature, $timestamp, $secret)) {
            return;
        }

        // laravel-sepay only understands Authorization: Apikey …
        $request->headers->set('Authorization', 'Apikey '.$apiKey);
    }

    private function validHmac(Request $request, string $signature, string $timestamp, string $secret): bool
    {
        if (! ctype_digit($timestamp)) {
            return false;
        }

        $ts = (int) $timestamp;
        if (abs(time() - $ts) > 300) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
