<?php

namespace App\Services\Clients\Concerns;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

trait CallsRemoteApi
{
    protected function http(string $token, int $timeout = 15): PendingRequest
    {
        return Http::withToken($token)
            ->acceptJson()
            ->timeout($timeout);
    }
}
