<?php

namespace App\Services;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AdmissionsService
{
    protected string $baseUrl;
    protected string $token;

    public function __construct()
    {
        // e.g. https://admissions.example.com/api/v1
        $this->baseUrl = rtrim(config('services.admissions.url', env('ADMISSIONS_API_URL', '')), '/');
        $this->token   = (string) config('services.admissions.token', env('ADMISSIONS_API_TOKEN', ''));
    }

    /** Base HTTP client */
    protected function http()
    {

        return Http::acceptJson()->withToken($this->token);
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
     | Lookups (simple)
     * ---------------*/

    public function getProgramTypes(?int $programTypeId = null, bool $onlyVisible = true): array
    {
        $query = [];
        if ($programTypeId) {
            $query['program_type_id'] = $programTypeId;
        }
        if ($onlyVisible) {
            $query['is_visible'] = 1;
        }

        $res = $this->http()->get("{$this->baseUrl}/program-type", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getFaculties(bool $onlyVisible = true): array
    {
        $query = $onlyVisible ? ['is_visible' => 1] : [];
        $res = $this->http()->get("{$this->baseUrl}/faculties", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getDepartments(int $facultyId, bool $onlyVisible = true): array
    {
        $query = ['faculty_id' => $facultyId];
        if ($onlyVisible) {
            $query['is_visible'] = 1;
        }

        $res = $this->http()->get("{$this->baseUrl}/departments", $query)->throw();
        return $this->unwrap($res->json());
    }

    public function getPrograms(int $departmentId, ?int $programTypeId = null, bool $onlyVisible = true): array
    {
        $query = ['department_id' => $departmentId];
        if ($programTypeId) {
            $query['program_type_id'] = $programTypeId;
        }
        if ($onlyVisible) {
            $query['is_visible'] = 1;
        }

        $res = $this->http()->get("{$this->baseUrl}/programs", $query)->throw();
        return $this->unwrap($res->json());
    }

    /* -------------
     | Candidates
     * ------------*/

    public function getCandidates(array $filters = []): array
    {
        $res = $this->http()->get("{$this->baseUrl}/candidates", $filters)->throw();
        return $res->json(); // keep full shape (pagination, meta, etc.)
    }

    public function getCandidatesFiltered(array $filters = []): array
    {
        // Backward-compatible with your component; just delegates
        return $this->getCandidates($filters);
    }

    public function getCandidate(int|string $id): array
    {
        $res = $this->http()->get("{$this->baseUrl}/candidates/{$id}")->throw();
        return $res->json();
    }

    public function approveCandidate(int|string $id): array
    {
        $res = $this->http()->post("{$this->baseUrl}/candidates/{$id}/approve")->throw();
        return $res->json();
    }


public function approveCandidateOrFail(int|string $id): void
{
    Log::info('Approve candidate call', ['url' => "{$this->baseUrl}/candidates/{$id}/approve", 'id' => $id]);

    $this->http()
        ->post("{$this->baseUrl}/candidates/{$id}/approve")
        ->throw();
}



    public function revokeCandidate(int|string $id): array
{
    return $this->http()
        ->post("{$this->baseUrl}/candidates/{$id}/revoke")
        ->throw()
        ->json();
}

   /**
     * Fetch candidate with all fields needed for migration.
     * You have a few implementation options:
     *  A) If your Admissions API has /candidates/{id}?mode=migration → use it.
     *  B) If /candidates/{id} includes 'user' with all fields → use it.
     *  C) Otherwise call /candidates/{id} + /users/{user_id} and merge.
     */
// public function candidateForMigration(int $id): array
// {
//     $base  = $this->baseUrl;
//     $http  = $this->http();

//     try {
//         // Ask Admissions API for candidate + user in one call if supported
//         $resp = $http->get("{$base}/candidates/{$id}", ['include' => 'user']);
//         if ($resp->successful()) {
//             $json = $resp->json();
//             $cand = $json['data'] ?? $json;

//             // If no user embedded, fallback fetch
//             if (empty($cand['user']) && !empty($cand['user_id'])) {
//                 $uResp = $http->get("{$base}/users/{$cand['user_id']}");
//                 if ($uResp->successful()) {
//                     $cand['user'] = $uResp->json()['data'] ?? $uResp->json();
//                     Log::info('candidateForMigration resolved user', [
//                         'cand_id' => $id,
//                         'user_id' => $cand['user_id'],
//                         'user_email' => $cand['user']['email'] ?? null,
//                         'program_type_id' => $cand['program_type_id'] ?? null,
//                     ]);
//                 }
//             }

//             return $cand;
//         }

//         throw new \RuntimeException("Admissions API returned {$resp->status()}");
//     } catch (\Throwable $e) {
//         Log::error('candidateForMigration failed', [
//             'id' => $id,
//             'error' => $e->getMessage(),
//         ]);
//         throw $e;
//     }
// }


public function candidateForMigration(int $id): array
{
    $base  = $this->baseUrl;
    $http  = $this->http();

    try {
        // ✅ Always request migration mode
        $resp = $http->get("{$base}/candidates/{$id}", [
            'mode' => 'migration',
        ]);

        if ($resp->successful()) {
            $cand = $resp->json();

            // Admissions API should return raw candidate + user + program IDs
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





    // Optional: if you added tracking columns on Admissions side
    public function markMigrated(int $id, ?int $studentId): void
    {
        // (no-op here, provided for completeness)
    }


    // === NEW: student portal base + http() ===
protected function studentBase(): string
{
    // e.g. https://students.heritagepoly.edu.ng/api/v1
    return rtrim(config('services.student_portal.url', env('STUDENT_PORTAL_URL', '')), '/');
}

protected function studentToken(): string
{
    return (string) config('services.student_portal.token', env('STUDENT_PORTAL_TOKEN', ''));
}

protected function studentHttp()
{
    return Http::acceptJson()
        ->asJson()
        ->withToken($this->studentToken())
        ->timeout(30);
}

    /* ----------------
     | Phase 1 migration
     * ---------------*/


//new code for phase 1
protected function buildPhase1Payload(array $cand): array
{
     Log::info('Phase1 Payload Debug', [
        'candidate_id' => $cand['id'] ?? null,
        'user' => $cand['user'] ?? null,
        'program_type_id' => $cand['program_type_id'] ?? null,
        'faculty_id' => $cand['faculty_id'] ?? null,
        'department_id' => $cand['department_id'] ?? null,
        'program_id' => $cand['program_id'] ?? null,
           'jamb_no'        => $cand['jamb_no'] ?? null,
        'jamb_score'     => $cand['jamb_score'] ?? null,
    ]);

    // Step 1: ensure we have the linked user
    $u = $cand['user'] ?? null;

    if (!$u && !empty($cand['user_id'])) {
        try {
            $uResp = $this->http()->get("{$this->baseUrl}/users/{$cand['user_id']}");
            if ($uResp->successful()) {
                $u = $uResp->json()['data'] ?? $uResp->json();
                Log::info('Resolved linked user for candidate', [
                    'candidate_id' => $cand['id'] ?? null,
                    'user_id'      => $cand['user_id'],
                    'resolved_id'  => $u['id'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Exception fetching linked user', [
                'candidate_id' => $cand['id'] ?? null,
                'user_id'      => $cand['user_id'],
                'error'        => $e->getMessage(),
            ]);
        }
    }


 $ptypeId = (int)($cand['program_type_id'] ?? $u['program_type_id'] ?? 0);
 //$ptypeId  = 2;
if ($ptypeId < 1) {
    Log::error('Program type missing in candidate payload', [
        'candidate_id' => $cand['id'] ?? null,
        'cand_raw'     => $cand,
        'user_raw'     => $u,
    ]);
    throw new \RuntimeException('Missing or invalid required id: program_type_id');
}

    // Step 3: build payload
    $payload = [
        // USERS table
        'username'          => (string)($u['username'] ?? $cand['regno'] ?? ''),
        'email'             => (string)($u['email'] ?? $cand['email'] ?? ''),
        'first_name'        => (string)($u['first_name'] ?? $cand['first_name'] ?? ''),
        'last_name'         => (string)($u['last_name']  ?? $cand['last_name'] ?? ''),
        'other_names'       => (string)($u['other_names'] ?? $cand['other_names'] ?? ''),
        'phone_no'          => (string)($u['phone_no'] ?? $cand['phone_no'] ?? ''),
        'gender'            => (string)($u['gender'] ?? $cand['sex'] ?? 'M'),
        'is_role'           => (int)($u['is_role'] ?? 0),
        'email_verified_at' => $u['email_verified_at'] ?? ($cand['email_verified_at'] ?? null),
        'password'          => (string)($u['password'] ?? 'Temp@123'),
        'is_active'         => (bool)($u['is_active'] ?? true),
        'program_type_id'   => $ptypeId,

        // STUDENTS table
        'regno'             => (string)($cand['regno'] ?? ''),
        'jamb_score'        => (string)($cand['jamb_score'] ?? ''),
        'jamb_no'           => (string)($cand['jamb_no'] ?? ''),
        'program_id'        => (int)($cand['program_id'] ?? 0),
        'faculty_id'        => (int)($cand['faculty_id'] ?? 0),
        'department_id'     => (int)($cand['department_id'] ?? 0),
        'acad_session_id'   => (int)($cand['acad_session_id'] ?? 0),
        'start_session_id'  => (int)($cand['start_session_id'] ?? $cand['acad_session_id'] ?? 0),
        'program_type_id'   => $ptypeId,   // ✅ for students
        'is_active'         => (bool)($cand['is_active'] ?? true),
    ];

    Log::info('buildPhase1Payload finished', [
        'candidate_id'    => $cand['id'] ?? null,
        'user_id'         => $cand['user_id'] ?? null,
        'program_type_id' => $ptypeId,
        'username'        => $payload['username'],
        'email'           => $payload['email'],
        'jamb_no'         => $payload['jamb_no'],   // ✅ confirm in logs
        'jamb_score'      => $payload['jamb_score'], // ✅ confirm in logs
    ]);

    return $payload;
}



public function approveAndMigratePhase1(int $id): array
{
        Log::info('approveAndMigratePhase1 started', ['id' => $id]);
    try {
        // 1) Approve candidate

        $approved = $this->approveCandidate($id);
        Log::info('approveCandidate returned', ['approved' => $approved]);
        // 2) Fetch candidate data
        $cand = $this->candidateForMigration($id);
        Log::info('Candidate full payload before buildPhase1Payload', $cand);
        Log::info('candidateForMigration returned', ['cand' => $cand]);

        if (!$cand) {
            return [
                'status'    => 'error',
                'http_code' => 404,
                'error'     => "Candidate {$id} not found",
            ];
        }
        Log::info('About to build payload', [
    'cand' => $cand,
      ]);
        // 3) Build payload
        $payload = $this->buildPhase1Payload($cand);
         Log::info('approveAndMigratePhase1 payload', $payload);

         //$payload['program_type_id'] = 2;
         Log::debug('Payload before required checks', $payload);
        // 4) Minimal guards (ensure required fields are present & valid)
        $required = [
            'program_type_id',
            'program_id',
            'faculty_id',
            'department_id',
            'acad_session_id',
            'start_session_id',
        ];

        foreach ($required as $key) {
            if (empty($payload[$key]) || (int)$payload[$key] < 1) {
                return [
                    'status'    => 'error',
                    'http_code' => 422,
                    'error'     => "Missing or invalid required field: {$key}",
                ];
            }
        }

        Log::info('approveAndMigratePhase1 reached', ['id' => $id]);
        Log::info('approveAndMigratePhase1 payload', $payload);
        // 5) Call Student Portal service
        $studentPortal = app(AdmissionsStudentPortalClient::class)->migrateBasic($payload);

        return [
            'status'         => 'success',
            'approved'       => $approved,
            'student_portal' => $studentPortal,
        ];

    } catch (\Throwable $e) {
        // Catch any unexpected error (DB, logic, HTTP)
        Log::error('approveAndMigratePhase1 failed', [
            'candidate_id' => $id,
            'error'        => $e->getMessage(),
            'trace'        => $e->getTraceAsString(),
        ]);

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}


public function revokeAndDeletePhase1(int $id): array
{
    Log::info('revokeAndDeletePhase1 started', ['id' => $id]);

    try {
        // 1) Revoke candidate in Admissions
        $revoked = $this->revokeCandidate($id);
        Log::info('revokeCandidate returned', ['revoked' => $revoked]);

        // 2) Fetch regno
        $cand = $this->candidateForMigration($id);
        $regno = $cand['regno'] ?? null;

        if (!$regno) {
            return [
                'status'    => 'error',
                'http_code' => 422,
                'error'     => "Candidate {$id} missing regno",
            ];
        }

        // 3) Delete from Students Portal
       $studentPortal = app(AdmissionsStudentPortalClient::class)->revokeBasic($regno);


        return [
            'status'         => 'success',
            'revoked'        => $revoked,
            'student_portal' => $studentPortal,
        ];

    } catch (\Throwable $e) {
        Log::error('revokeAndDeletePhase1 failed', [
            'candidate_id' => $id,
            'error'        => $e->getMessage(),
        ]);

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}




}
