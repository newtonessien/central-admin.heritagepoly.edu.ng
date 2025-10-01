<?php

namespace App\Services\Clients\Concerns;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

trait CallsRemoteApi
{
    /**
     * Build a base HTTP client with token + JSON headers.
     */
    protected function http(?string $token, int $timeout = 30): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout($timeout);
    }

    /**
     * Helper to safely unwrap {"data": [...]} JSON responses.
     */
    protected function unwrap(?array $json): array
    {
        if (is_array($json) && array_key_exists('data', $json) && is_array($json['data'])) {
            return $json['data'];
        }
        return $json ?? [];
    }

    /**
     * Small logging wrapper for debugging API calls.
     */
    protected function logResponse(string $service, string $endpoint, $response): void
    {
        Log::debug("{$service} API call", [
            'endpoint' => $endpoint,
            'status'   => $response->status(),
            'body'     => $response->body(),
        ]);
    }
}
