<?php
namespace App\Services\Clients;

use App\Services\Clients\Concerns\CallsRemoteApi;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

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
                    'study_center_id'  => $cand['study_center_id'] ?? null,
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
            'program_id'       => (int)($cand['program_id'] ?? null),
            'faculty_id'       => (int)($cand['faculty_id'] ?? null),
            'department_id'    => (int)($cand['department_id'] ?? null),
            'acad_session_id'  => (int)($cand['acad_session_id'] ?? null),
            'start_session_id' => (int)($cand['start_session_id'] ?? $cand['acad_session_id'] ?? null),
            'is_active'        => (bool)($cand['is_active'] ?? true),
            'study_center_id'  => (int)($cand['study_center_id'] ?? null),
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

// Payment Reports
public function getPaymentReports(array $filters = []): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/payments/report", $filters);

    return $resp->json('data') ?? [];
}

public function exportPaymentReports(array $params)
{
    $format = $params['format'] ?? 'excel';

    $response = $this->httpClient()
        ->withOptions(['stream' => true])
        ->get("{$this->baseUrl}/payment-reports/export/{$format}", array_filter($params));

    return $response;
}

public function fetchAdmissionStudyCenterSummary(array $filters = []): array
{
    try {
        $res = $this->httpClient()
            ->get("{$this->baseUrl}/payments/commissions/study-centers", $filters)
            ->throw();

        return $res->json() ?? [];
    } catch (\Throwable $e) {
        Log::error('fetchAdmissionStudyCenterSummary failed', [
            'filters' => $filters,
            'error' => $e->getMessage()
        ]);

        return [];
    }
}

public function getProgramTypeByName(string $name): ?array
{
    $res = $this->httpClient()->get("{$this->baseUrl}/program-type");

    $items = $res['data'] ?? [];

    foreach ($items as $item) {
        if (
            isset($item['name']) &&
            strcasecmp(trim($item['name']), trim($name)) === 0
        ) {
            return $item;
        }
    }

    return null;
}



//------New admits----------

public function admitCandidate(int|string $id): array
{
    return $this->httpClient()
        ->post("{$this->baseUrl}/candidates/{$id}/approve")
        ->throw()
        ->json();
}

public function rejectCandidate(int|string $id, ?string $reason = null): array
{
    return $this->httpClient()
        ->post("{$this->baseUrl}/candidates/{$id}/reject", [
            'reason' => $reason,
        ])
        ->throw()
        ->json();
}


public function commitAdmission(int|string $id): array
{
    $url = "{$this->baseUrl}/candidates/{$id}/commit-admission";

    Log::info('Admissions commit-admission →', [
        'url' => $url,
        'candidate_id' => $id,
    ]);

    try {
        $resp = $this->httpClient()
            ->post($url);

        $resp->throw();

        $body = $resp->json() ?? $resp->body();

        Log::info('Admissions commit-admission ←', [
            'status' => $resp->status(),
            'candidate_id' => $id,
            'body' => $body,
        ]);

        return [
            'status' => 'success',
            'http_code' => $resp->status(),
            'data' => $body,
        ];

    } catch (\Illuminate\Http\Client\RequestException $e) {

        $status = $e->response?->status();
        $rawBody = $e->response?->body();
        $jsonBody = $e->response?->json();

        Log::error('Admissions commit-admission failed', [
            'candidate_id' => $id,
            'status' => $status,
            'json' => $jsonBody,
            'body' => $rawBody,
        ]);

        return [
            'status' => 'error',
            'http_code' => $status,
            'error' => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {

        Log::critical('Admissions commit-admission unexpected failure', [
            'candidate_id' => $id,
            'exception' => $e->getMessage(),
        ]);

        return [
            'status' => 'error',
            'http_code' => 500,
            'error' => $e->getMessage(),
        ];
    }
}


/**
 * Revoke an admission in the Admissions Portal.
 *
 * This should only be called after the corresponding
 * Student Portal admission has been successfully revoked.
 */
public function revokeAdmission(int|string $candidateId): array
{
    $url = "{$this->baseUrl}/candidates/{$candidateId}/revoke-admission";

    Log::info('Admissions revoke admission →', [
        'url'          => $url,
        'candidate_id' => $candidateId,
    ]);

    try {

        $resp = $this->httpClient()
            ->post($url);

        $resp->throw();

        $body = $resp->json() ?? $resp->body();

        Log::info('Admissions revoke admission ←', [
            'status'       => $resp->status(),
            'candidate_id' => $candidateId,
            'body'         => $body,
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

            Log::warning(
                'Admissions revoke admission unauthorized',
                [
                    'candidate_id' => $candidateId,
                    'expected_token' =>
                        substr($this->token, 0, 6) . '...',
                    'body' =>
                        $jsonBody ?: $rawBody,
                ]
            );

            return [
                'status'    => 'error',
                'http_code' => 401,
                'error'     => 'Unauthorized: check Admissions Portal service token.',
                'details'   => $jsonBody ?: $rawBody,
            ];
        }

        Log::error(
            'Admissions revoke admission error',
            [
                'candidate_id' => $candidateId,
                'status'       => $status,
                'json'         => $jsonBody,
                'body'         => $rawBody,
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => $status,
            'error'     => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {

        Log::critical(
            'Admissions revoke admission unexpected failure',
            [
                'candidate_id' => $candidateId,
                'exception'    => $e->getMessage(),
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}

/**
 * Change a candidate's faculty, department and programme
 * in the Admissions Portal.
 *
 * This does NOT admit, reject or migrate the candidate.
 */
public function changeDepartment(
    int|string $candidateId,
    int $facultyId,
    int $departmentId,
    int $programId,
): array {
    $url = "{$this->baseUrl}/candidates/{$candidateId}/change-department";

    $payload = [
        'faculty_id'    => $facultyId,
        'department_id' => $departmentId,
        'program_id'    => $programId,
    ];

    Log::info('Admissions change-department →', [
        'url'           => $url,
        'candidate_id'  => $candidateId,
        'payload'       => $payload,
    ]);

    try {

        $resp = $this->httpClient()
            ->post($url, $payload);

        $resp->throw();

        $body = $resp->json() ?? $resp->body();

        Log::info('Admissions change-department ←', [
            'status'       => $resp->status(),
            'candidate_id' => $candidateId,
            'body'         => $body,
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

            Log::warning(
                'Admissions change-department unauthorized',
                [
                    'candidate_id' => $candidateId,

                    'expected_token' =>
                        substr($this->token, 0, 6) . '...',

                    'body' =>
                        $jsonBody ?: $rawBody,
                ]
            );

            return [
                'status'    => 'error',
                'http_code' => 401,
                'error'     =>
                    'Unauthorized: check Admissions Portal service token.',
                'details'   =>
                    $jsonBody ?: $rawBody,
            ];
        }

        Log::error(
            'Admissions change-department error',
            [
                'candidate_id' => $candidateId,
                'status'       => $status,
                'json'         => $jsonBody,
                'body'         => $rawBody,
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => $status,
            'error'     => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {

        Log::critical(
            'Admissions change-department unexpected failure',
            [
                'candidate_id' => $candidateId,
                'exception'    => $e->getMessage(),
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}



/**
 * Get a candidate by registration number.
 *
 * @param  string  $regno
 * @param  string  $mode
 * @return array
 */
public function candidateByRegno(
    string $regno,
    string $mode = 'details'
): array {
    $url = "{$this->baseUrl}/candidates/by-regno/" .
        rawurlencode(trim($regno));

    Log::info('Admissions candidate by regno →', [
        'url'   => $url,
        'regno' => $regno,
        'mode'  => $mode,
    ]);

    try {

        $response = $this->httpClient()
            ->get($url, [
                'mode' => $mode,
            ]);

        $response->throw();

        $body = $response->json();

        Log::info('Admissions candidate by regno ←', [
            'status' => $response->status(),
            'regno'  => $regno,
            'body'   => $body,
        ]);

        return [
            'status'    => 'success',
            'http_code' => $response->status(),
            'data'      => $body,
        ];

    } catch (RequestException $e) {

        $status   = $e->response?->status();
        $rawBody  = $e->response?->body();
        $jsonBody = $e->response?->json();

        if ($status === 404) {

            Log::warning(
                'Admissions candidate by regno not found',
                [
                    'regno' => $regno,
                    'body'  => $jsonBody ?: $rawBody,
                ]
            );

            return [
                'status'    => 'error',
                'http_code' => 404,
                'error'     => 'Candidate not found.',
                'details'   => $jsonBody ?: $rawBody,
            ];
        }

        if ($status === 401) {

            Log::warning(
                'Admissions candidate by regno unauthorized',
                [
                    'regno' => $regno,
                    'expected_token' =>
                        substr($this->token, 0, 6) . '...',
                    'body' =>
                        $jsonBody ?: $rawBody,
                ]
            );

            return [
                'status'    => 'error',
                'http_code' => 401,
                'error'     =>
                    'Unauthorized: check Admissions Portal service token.',
                'details'   =>
                    $jsonBody ?: $rawBody,
            ];
        }

        Log::error(
            'Admissions candidate by regno error',
            [
                'regno'  => $regno,
                'status' => $status,
                'json'   => $jsonBody,
                'body'   => $rawBody,
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => $status,
            'error'     => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {

        Log::critical(
            'Admissions candidate by regno unexpected failure',
            [
                'regno'     => $regno,
                'exception' => $e->getMessage(),
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}


/**
 * Change a candidate's academic session.
 *
 * This operation only updates acad_session_id
 * in the Admissions Portal.
 *
 * It does not admit, reject, revoke or migrate
 * the candidate to the Student Portal.
 */
public function changeAcademicSession(
    int $candidateId,
    int $acadSessionId
): array {
    $url = "{$this->baseUrl}/candidates/{$candidateId}/change-session";

    $payload = [
        'acad_session_id' => $acadSessionId,
    ];

    Log::info('Admissions change academic session →', [
        'url'             => $url,
        'candidate_id'    => $candidateId,
        'acad_session_id' => $acadSessionId,
    ]);

    try {

        $response = $this->httpClient()
            ->patch($url, $payload);

        $response->throw();

        $body = $response->json() ?? $response->body();

        Log::info('Admissions change academic session ←', [
            'status'          => $response->status(),
            'candidate_id'    => $candidateId,
            'acad_session_id' => $acadSessionId,
            'body'            => $body,
        ]);

        return [
            'status'    => 'success',
            'http_code' => $response->status(),
            'data'      => $body,
        ];

    } catch (RequestException $e) {

        $status   = $e->response?->status();
        $rawBody  = $e->response?->body();
        $jsonBody = $e->response?->json();

        if ($status === 401) {

            Log::warning(
                'Admissions change academic session unauthorized',
                [
                    'candidate_id' =>
                        $candidateId,

                    'acad_session_id' =>
                        $acadSessionId,

                    'expected_token' =>
                        substr($this->token, 0, 6) . '...',

                    'body' =>
                        $jsonBody ?: $rawBody,
                ]
            );

            return [
                'status'    => 'error',
                'http_code' => 401,
                'error'     =>
                    'Unauthorized: check Admissions Portal service token.',
                'details'   =>
                    $jsonBody ?: $rawBody,
            ];
        }

        Log::error(
            'Admissions change academic session error',
            [
                'candidate_id' =>
                    $candidateId,

                'acad_session_id' =>
                    $acadSessionId,

                'status' =>
                    $status,

                'json' =>
                    $jsonBody,

                'body' =>
                    $rawBody,
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => $status,
            'error'     => $jsonBody ?: $rawBody,
        ];

    } catch (\Throwable $e) {

        Log::critical(
            'Admissions change academic session unexpected failure',
            [
                'candidate_id' =>
                    $candidateId,

                'acad_session_id' =>
                    $acadSessionId,

                'exception' =>
                    $e->getMessage(),
            ]
        );

        return [
            'status'    => 'error',
            'http_code' => 500,
            'error'     => $e->getMessage(),
        ];
    }
}

public function getApplicationTypesByProgramType(int $programTypeId): array
{
    $resp = $this->httpClient()
        ->get("{$this->baseUrl}/application-types", [
            'program_type_id' => $programTypeId,
        ])
        ->throw();

    return $resp->json('data') ?? [];
}


}
