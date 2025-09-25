<?php

namespace App\Services\Clients;

use App\Services\Clients\Concerns\CallsRemoteApi;
use Illuminate\Support\Facades\Log;


class StudentPortalClient
{
    use CallsRemoteApi;

    protected string $baseUrl;
    protected ?string $token;

    public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = $baseUrl ?: config('services.student_portal.url');
        $this->token   = $token   ?: config('services.student_portal.token');

        // 🚨 Log the token on boot (just first 12 chars for safety)
        Log::info('StudentPortalClient initialized', [
            'baseUrl' => $this->baseUrl,
            'token_preview' => $this->token ? substr($this->token, 0, 12) . '...' : null,
        ]);
    }

    protected function httpWithAuth()
    {
        // 🚨 Log before making any request
        Log::info('StudentPortalClient using Bearer', [
            'token' => $this->token,
        ]);

        return $this->http($this->token);
    }

    public function getAdmittedStudents(array $filters = []): array
    {
        $resp = $this->httpWithAuth()
            ->get("{$this->baseUrl}/students/admitted", $filters);

        return $resp->json() ?? ['data' => [], 'meta' => []];
    }

    public function getStudents(array $filters = []): array
    {
        $resp = $this->httpWithAuth()
            ->get("{$this->baseUrl}/students", $filters);

        return $resp->json() ?? ['data' => [], 'meta' => []];
    }

    public function getStudentByRegno(string $regno, array $params = []): array
    {
        $resp = $this->httpWithAuth()
            ->get("{$this->baseUrl}/students/{$regno}", $params);

        return $resp->json() ?? [];
    }

    public function migrateStudentBasic(array $payload): array
    {
        $resp = $this->httpWithAuth()
            ->post("{$this->baseUrl}/students/migrate-basic", $payload);

        return $resp->json() ?? [];
    }
}
