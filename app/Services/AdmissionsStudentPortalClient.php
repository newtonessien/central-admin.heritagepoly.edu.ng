<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class AdmissionsStudentPortalClient
{
    protected string $base;
    protected string $token;

    public function __construct()
    {
        $this->base  = rtrim(config('services.student_portal.url'), '/');
        $this->token = (string) config('services.student_portal.token');


          $this->base  = rtrim(config('services.student_portal.url'), '/');
    $this->token = (string) config('services.student_portal.token');

    // 🔎 Debug log
    Log::info('StudentPortalClient constructed', [
        'base'  => $this->base,
        'token' => substr($this->token, 0, 6) . '...'  // don’t log full secret
    ]);

    }


    protected function http()
    {
        return Http::acceptJson()
            ->asJson()
            ->withToken($this->token)
            ->timeout(30);
    }

    public function migrateBasic(array $payload): array
{
    $url = $this->base . '/students/migrate-basic';

    Log::info('SP migrate-basic →', [
        'url'     => $url,
        'payload' => $payload,
    ]);

    try {
        $r = $this->http()->post($url, $payload);
        $r->throw(); // throws RequestException if not 2xx

        $body = $r->json() ?? $r->body();

        Log::info('SP migrate-basic ←', [
            'status' => $r->status(),
            'body'   => $body,
        ]);

        return [
            'status'    => 'success',
            'http_code' => $r->status(),
            'data'      => $body,
        ];

    } catch (RequestException $e) {
        $status   = $e->response?->status();
        $rawBody  = $e->response?->body();
        $jsonBody = $e->response?->json();

        // 🔎 Explicit handling for Unauthorized
        if ($status === 401) {
            Log::warning('SP migrate-basic unauthorized (token mismatch?)', [
                'expected_token' => substr($this->token, 0, 6) . '...',
                'body'           => $jsonBody ?: $rawBody,
            ]);

            return [
                'status'    => 'error',
                'http_code' => 401,
                'error'     => 'Unauthorized: check CENTRAL_ADMIN_TOKEN in Student Portal and STUDENT_PORTAL_TOKEN in Central Admin',
                'details'   => $jsonBody ?: $rawBody,
            ];
        }

        Log::error('SP migrate-basic error', [
            'status' => $status,
            'json'   => $jsonBody,
            'body'   => $rawBody,
        ]);

        return [
            'status'    => 'error',
            'http_code' => $status,
            'error'     => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {
        Log::critical('SP migrate-basic unexpected failure', [
            'exception' => $e->getMessage(),
        ]);

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}

// In App\Services\StudentPortalClient.php
public function revokeStudent(string $regno): array
{
    $res = $this->http()
        ->post("{$this->base}/students/revoke", ['regno' => $regno]);

    return $res->json();
}

public function revokeBasic(string $regno): array
{
    return $this->delete("/students/revoke-basic/{$regno}");
}


protected function delete(string $uri, array $data = []): array
{
    $url = rtrim($this->base, '/') . '/' . ltrim($uri, '/');

    $response = Http::withToken($this->token)
        ->acceptJson()
        ->delete($url, $data);

    if ($response->failed()) {
        throw new \RuntimeException("DELETE {$url} failed: " . $response->body());
    }

    return $response->json();
}




}
