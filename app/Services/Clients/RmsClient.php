<?php

namespace App\Services\Clients;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class RmsClient
{
    protected string $baseUrl;

    protected string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim(
            config('services.rms.url'),
            '/'
        );

        $this->token = config('services.rms.token');
    }

    protected function httpClient(): PendingRequest
    {
        return Http::withToken($this->token)
            ->acceptJson()
            ->baseUrl($this->baseUrl);
    }

    /**
     * Get a student's academic results from RMS.
     */
    public function getStudentResults(
        string $matricNo,
        array $filters = []
    ): array {
        return $this->httpClient()
            ->get(
                '/student-results',
                array_filter([
                    'matric_no' => $matricNo,
                    ...$filters,
                ], fn ($value) => $value !== null && $value !== '')
            )
            ->throw()
            ->json() ?? [];
    }
}
