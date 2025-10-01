<?php
namespace App\Services\Clients;

use Illuminate\Support\Facades\Log;
use App\Services\Clients\Concerns\CallsRemoteApi;

class AdmissionsPortalClient
{
    use CallsRemoteApi;

    protected string $baseUrl;
    protected string $token;

      public function __construct(?string $baseUrl = null, ?string $token = null)
    {
        $this->baseUrl = rtrim(
            $baseUrl ?: config('services.admissions.url', env('ADMISSIONS_API_URL', '')),
            '/'
        );
        $this->token   = (string) ($token ?: config('services.admissions.token', env('ADMISSIONS_API_TOKEN', '')));

        Log::info('AdmissionsPortalClient constructed', [
            'baseUrl' => $this->baseUrl,
            'token_preview' => $this->token ? substr($this->token, 0, 6) . '...' : null,
        ]);
    }

    /** Base HTTP client */
    protected function httpClient()
    {
        return $this->http($this->token);
    }

    /** Safely unwrap arrays that may be wrapped as {"data": [...]} */
    protected function unwrap(array|null $json): array
    {
        if (is_array($json) && array_key_exists('data', $json) && is_array($json['data'])) {
            return $json['data'];
        }
        return $json ?? [];
    }

    /* ----------------
     | Lookups
     * ---------------*/

    public function getProgramTypes(?int $programTypeId = null, bool $onlyVisible = true): array
    {
        $query = [];
        if ($programTypeId) $query['program_type_id'] = $programTypeId;
        if ($onlyVisible)   $query['is_visible'] = 1;

        $res = $this->httpClient()->get("{$this->baseUrl}/program-type", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getFaculties(bool $onlyVisible = true): array
    {
        $query = $onlyVisible ? ['is_visible' => 1] : [];
        $res = $this->httpClient()->get("{$this->baseUrl}/faculties", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getDepartments(int $facultyId, bool $onlyVisible = true): array
    {
        $query = ['faculty_id' => $facultyId];
        if ($onlyVisible) $query['is_visible'] = 1;

        $res = $this->httpClient()->get("{$this->baseUrl}/departments", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getPrograms(int $departmentId, ?int $programTypeId = null, bool $onlyVisible = true): array
    {
        $query = ['department_id' => $departmentId];
        if ($programTypeId) $query['program_type_id'] = $programTypeId;
        if ($onlyVisible)   $query['is_visible'] = 1;

        $res = $this->httpClient()->get("{$this->baseUrl}/programs", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getAcadSessions(bool $onlyVisible = true): array
    {
        //$res = $this->httpClient()->get("{$this->baseUrl}/acad-sessions")->throw();
        //return $this->unwrap($res->json());

        $query = $onlyVisible ? ['is_visible' => 1] : [];
        $res = $this->httpClient()->get("{$this->baseUrl}/acad-sessions", $query)->throw();
        return $this->unwrap($res->json());


    }

    public function getEntryModes(): array
    {
        $res = $this->httpClient()->get("{$this->baseUrl}/entry-modes")->throw();
        return $this->unwrap($res->json());
    }

    /* -------------
     | Candidates
     * ------------*/

    public function getCandidates(array $filters = []): array
    {
        $res = $this->httpClient()->get("{$this->baseUrl}/candidates", $filters)->throw();
        return $res->json(); // keep full shape (pagination, meta, etc.)
    }

      public function getCandidatesFiltered(array $filters = []): array
    {
        // Backward-compatible with your component; just delegates
        return $this->getCandidates($filters);
    }

    public function getCandidate(int|string $id): array
    {
        $res = $this->httpClient()->get("{$this->baseUrl}/candidates/{$id}")->throw();
        return $res->json();
    }

    public function approveCandidate(int|string $id): array
    {
        $res = $this->httpClient()->post("{$this->baseUrl}/candidates/{$id}/approve")->throw();
        return $res->json();
    }

    public function revokeCandidate(int|string $id): array
    {
        return $this->httpClient()
            ->post("{$this->baseUrl}/candidates/{$id}/revoke")
            ->throw()
            ->json();
    }

    public function candidateForMigration(int $id): array
    {
        try {
            $resp = $this->httpClient()->get("{$this->baseUrl}/candidates/{$id}", [
                'mode' => 'migration',
            ]);

            if ($resp->successful()) {
                $cand = $resp->json();

                Log::info('candidateForMigration success', [
                    'cand_id'          => $id,
                    'has_user'         => isset($cand['user']),
                    'program_type_id'  => $cand['program_type_id'] ?? null,
                    'program_id'       => $cand['program_id'] ?? null,
                    'faculty_id'       => $cand['faculty_id'] ?? null,
                    'department_id'    => $cand['department_id'] ?? null,
                ]);

                return $cand;
            }

            throw new \RuntimeException("Admissions API returned {$resp->status()}");
        } catch (\Throwable $e) {
            Log::error('candidateForMigration failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /* ----------------
     | Migration (Phase 1)
     * ---------------*/

    protected function buildPhase1Payload(array $cand): array
    {
        $u = $cand['user'] ?? null;

        if (!$u && !empty($cand['user_id'])) {
            $uResp = $this->httpClient()->get("{$this->baseUrl}/users/{$cand['user_id']}");
            if ($uResp->successful()) {
                $u = $uResp->json()['data'] ?? $uResp->json();
            }
        }

        $ptypeId = (int)($cand['program_type_id'] ?? $u['program_type_id'] ?? 0);
        if ($ptypeId < 1) {
            throw new \RuntimeException('Missing or invalid required id: program_type_id');
        }

        return [
            // Users
            'username'    => (string)($u['username'] ?? $cand['regno'] ?? ''),
            'email'       => (string)($u['email'] ?? $cand['email'] ?? ''),
            'first_name'  => (string)($u['first_name'] ?? $cand['first_name'] ?? ''),
            'last_name'   => (string)($u['last_name']  ?? $cand['last_name'] ?? ''),
            'other_names' => (string)($u['other_names'] ?? $cand['other_names'] ?? ''),
            'phone_no'    => (string)($u['phone_no'] ?? $cand['phone_no'] ?? ''),
            'gender'      => (string)($u['gender'] ?? $cand['sex'] ?? 'M'),
            'is_role'     => (bool)($u['is_role'] ?? true),
            'program_type_id' => $ptypeId,

            // Students
            'regno'            => (string)($cand['regno'] ?? ''),
            'jamb_score'       => (string)($cand['jamb_score'] ?? ''),
            'jamb_no'          => (string)($cand['jamb_no'] ?? ''),
            'program_id'       => (int)($cand['program_id'] ?? 0),
            'faculty_id'       => (int)($cand['faculty_id'] ?? 0),
            'department_id'    => (int)($cand['department_id'] ?? 0),
            'acad_session_id'  => (int)($cand['acad_session_id'] ?? 0),
            'start_session_id' => (int)($cand['start_session_id'] ?? $cand['acad_session_id'] ?? 0),
            'is_active'        => (bool)($cand['is_active'] ?? true),
        ];
    }

    public function approveAndMigratePhase1(int $id): array
    {
        $approved = $this->approveCandidate($id);
        $cand     = $this->candidateForMigration($id);
        if (!$cand) {
            return ['status' => 'error', 'http_code' => 404, 'error' => "Candidate {$id} not found"];
        }

        $payload = $this->buildPhase1Payload($cand);

        // Call Student Portal client
        $studentPortal = app(StudentPortalClient::class)->migrateBasic($payload);

        return [
            'status'         => 'success',
            'approved'       => $approved,
            'student_portal' => $studentPortal,
        ];
    }

    public function revokeAndDeletePhase1(int $id): array
    {
        $revoked = $this->revokeCandidate($id);
        $cand    = $this->candidateForMigration($id);
        $regno   = $cand['regno'] ?? null;

        if (!$regno) {
            return ['status' => 'error', 'http_code' => 422, 'error' => "Candidate {$id} missing regno"];
        }

        $studentPortal = app(StudentPortalClient::class)->revokeBasic($regno);

        return [
            'status'         => 'success',
            'revoked'        => $revoked,
            'student_portal' => $studentPortal,
        ];
    }


public function getApplicationTypes(): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/application-types");

    return $resp->json('data') ?? [];
}

public function changeApplicationType(string $regno, int $applicationTypeId): array
{
    $resp = $this->httpClient()
        ->patch("{$this->baseUrl}/candidates/change-application-type", [
            'regno'               => $regno,
            'application_type_id' => $applicationTypeId,
        ]);

    return $resp->json() ?? [];
}




}
