<?php
namespace App\Services\Clients;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;
use App\Services\Clients\Concerns\CallsRemoteApi;

class StudentPortalClient
{
    use CallsRemoteApi;

    protected string $baseUrl;
    protected string $token;
    protected string $centralBase;
    protected string $centralToken;

    public function __construct(
        ?string $baseUrl = null,
        ?string $token = null,
        ?string $centralBase = null,
        ?string $centralToken = null
    ) {
        $this->baseUrl     = rtrim($baseUrl ?: config('services.student_portal.url', ''), '/');
        $this->token       = (string) ($token ?: config('services.student_portal.token', ''));
        // $this->centralBase = rtrim($centralBase ?: config('services.student_portal_central.url', ''), '/');
        // $this->centralToken= (string) ($centralToken ?: config('services.student_portal_central.token', ''));

        Log::info('StudentPortalClient constructed', [
            'baseUrl'        => $this->baseUrl,
            'token_preview'  => $this->token ? substr($this->token, 0, 6) . '...' : null,
            // 'central_base'   => $this->centralBase,
            // 'central_preview'=> $this->centralToken ? substr($this->centralToken, 0, 6) . '...' : null,
        ]);
    }

    /** Main portal HTTP client */
    protected function httpClient()
    {
        return $this->http($this->token);
    }

    /** Central Admin HTTP client */
    // protected function httpCentral()
    // {
    //     return $this->http($this->centralToken)->baseUrl($this->centralBase);
    // }

    /* --------------------
     | Migration: Phase 1
     * -------------------*/

    public function migrateBasic(array $payload): array
    {
        $url = "{$this->baseUrl}/students/migrate-basic";

        Log::info('SP migrate-basic →', ['url' => $url, 'payload' => $payload]);

        try {
            $resp = $this->httpClient()->post($url, $payload);
            $resp->throw();

            $body = $resp->json() ?? $resp->body();

            Log::info('SP migrate-basic ←', [
                'status' => $resp->status(),
                'body'   => $body,
            ]);

            return [
                'status'    => 'success',
                'http_code' => $resp->status(),
                'data'      => $body,
            ];
        } catch (RequestException $e) {
            $status   = $e->response?->status();
            $rawBody  = $e->response?->body();
            $jsonBody = $e->response?->json();

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

    /* --------------------
     | Revocation
     * -------------------*/

    public function revokeStudent(string $regno): array
    {
        $resp = $this->httpClient()->post("{$this->baseUrl}/students/revoke", ['regno' => $regno]);
        return $resp->json();
    }

    public function revokeBasic(string $regno): array
    {
        return $this->delete("/students/revoke-basic/{$regno}");
    }

    protected function delete(string $uri, array $data = []): array
    {
        $url = rtrim($this->baseUrl, '/') . '/' . ltrim($uri, '/');

        $resp = $this->httpClient()->delete($url, $data);

        if ($resp->failed()) {
            throw new \RuntimeException("DELETE {$url} failed: " . $resp->body());
        }

        return $resp->json();
    }

    /* --------------------
     | Enrolled Students (Central)
     * -------------------*/

    public function listEnrolled(array $params = [])
    {
        return $this->httpClient()->get("{$this->baseUrl}/students/enrolled-students", $params)->json();

    }

    public function getEnrolled(int $id)
    {
        return $this->httpClient()->get("{$this->baseUrl}/students/enrolled-students/{$id}")->json();
    }

    public function createEnrolled(array $payload)
    {
        $res = $this->httpClient()->post("{$this->baseUrl}/students/enrolled-students", $payload);

        if ($res->failed()) {
            Log::error('EnrolledStudent create failed', [
                'status' => $res->status(),
                'body'   => $res->json(),
            ]);
            throw new \Exception('Failed to create enrolled student. See logs.');
        }

        return $res->json();
    }


    public function updateEnrolled(int $id, array $payload)
{
    $res = $this->httpClient()->put("{$this->baseUrl}/students/enrolled-students/{$id}", $payload);

    if ($res->failed()) {
        logger()->error('EnrolledStudent update failed', [
            'id'     => $id,
            'status' => $res->status(),
            'body'   => $res->json()
        ]);
        throw new \Exception("Failed to update enrolled student #{$id}. See logs.");
    }

    return $res->json();
}


    public function deleteEnrolled(int $id)
    {
        return $this->httpClient()->delete("{$this->baseUrl}/students/enrolled-students/{$id}")->successful();
    }

    /* --------------------
     | Admitted Students
     * -------------------*/
  public function getAdmittedStudents(array $filters = []): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/students/admitted", $filters);

    return $resp->json() ?? ['data' => [], 'meta' => []];
}

public function getStudents(array $filters = []): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/students", $filters);

    return $resp->json() ?? ['data' => [], 'meta' => []];
}

public function getStudentByRegno(string $regno, array $params = []): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/students/{$regno}", $params);

    return $resp->json() ?? [];
}

public function migrateStudentBasic(array $payload): array
{
    $resp = $this->httpClient()
        ->post("{$this->baseUrl}/students/migrate-basic", $payload);

    return $resp->json() ?? [];
}



}
