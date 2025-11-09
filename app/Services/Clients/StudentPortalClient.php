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


// /* --------------------
 // | School Fee Reports
 // * -------------------*/

    public function getSchoolFeeReports(array $filters): array
    {
        $res = $this->httpClient()
            ->get("{$this->baseUrl}/payments/school-fee-report", $filters)
            ->throw();
        return $this->unwrap($res->json());
          //return $res->json() ?? [];
    }



    public function getOtherPaymentsReport(array $params): array
{

  $res = $this->httpClient()
            ->get("{$this->baseUrl}/payments/other-payments-report", $params)
            ->throw();

        return $this->unwrap($res->json());
          //return $res->json() ?? [];


}

public function getServices(?int $programTypeId = null, bool $onlyVisible = true): array
{
    $query = [];
    if ($programTypeId) $query['program_type_id'] = $programTypeId;
    if ($onlyVisible)   $query['only_visible'] = true;

    try {
        $res = $this->httpClient()
            ->get("{$this->baseUrl}/services", $query)
            ->throw();

        return $this->unwrap($res->json());
          //return $res->json() ?? [];
    } catch (\Throwable $e) {
        report($e);
        return [];
    }
}

public function getLevels(?int $year_of_study = null): array
{
    $query = [];
    if ($year_of_study) $query['year_of_study'] = $year_of_study;
    try {
        $res = $this->httpClient()
            ->get("{$this->baseUrl}/levels", $query)
            ->throw();

        return $this->unwrap($res->json());
    } catch (\Throwable $e) {
        report($e);
        return [];
    }
}


public function createApprovedPayment(array $data)
{
$res = $this->httpClient()->post("{$this->baseUrl}/payments/approved-payments", $data);

        if ($res->failed()) {
            Log::error('ApprovedPayment create failed', [
                'status' => $res->status(),
                'body'   => $res->json(),
            ]);
            throw new \Exception('Failed to create approved payment. See logs.');
        }

        return $res->json();
    }


  /**
     * ✅ 1. Get all approved payments by student's regno
     */

public function getApprovedPaymentsByRegno(string $regno): array
{
    $response = $this->httpClient()->get("{$this->baseUrl}/payments/approved-payments/{$regno}");
    $json = $response->json() ?? [];

    if (isset($json['data']) && is_array($json['data'])) {
        return $json['data'];
    }

    return is_array($json) ? $json : [];
}



    /**
     * ✅ 2. Update an approved payment (only if unpaid)
     */
    public function updateApprovedPayment(int $id, array $payload): array
    {
        $resp = $this->httpClient()->put("{$this->baseUrl}/payments/approved-payments/{$id}", $payload);
        return $resp->json() ?? [];
    }

    /**
     * ✅ 3. Delete an approved payment (only if unpaid)
     */
    public function deleteApprovedPayment(int $id): array
    {
        $resp = $this->httpClient()->delete("{$this->baseUrl}/payments/approved-payments/{$id}");
        return $resp->json() ?? [];
    }


        public function getPaymentByTransRef(string $transRef): ?array
    {
        try {
            $res = $this->httpClient()->get("{$this->baseUrl}/payments/payment-by-trans-ref", [
                'trans_refno' => $transRef
            ])->throw();

            return $res->json('data');
        } catch (RequestException $e) {
            Log::error('Failed to fetch payment by ref: '.$e->getMessage());
            return null;
        }
    }


     /**
     * ✅ 3. Fee Item Reports
     */

    //    public function fetchFeeItems(array $params = [])
    // {
    //     return $this->httpClient()->get("{$this->baseUrl}/fees/items", $params)->throw()->json();
    // }

    public function fetchFeeItems(array $params = [])
{
    try {
        // Defaults for pagination if not provided
        $defaults = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 40,
            'search' => $params['search'] ?? null,
        ];

        // Merge and clean empty params
        $query = array_filter(array_merge($defaults, $params), fn($v) => $v !== null && $v !== '');

        $response = $this->httpClient()
            ->get("{$this->baseUrl}/fees/items", $query)
            ->throw()
            ->json();

        // ✅ Normalize response for Livewire (ensure both data + meta exist)
        return [
            'data' => $response['data'] ?? [],
            'meta' => $response['meta'] ?? [
                'current_page' => 1,
                'per_page' => count($response['data'] ?? []),
                'last_page' => 1,
                'total' => count($response['data'] ?? []),
            ],
        ];

    } catch (\Throwable $e) {
        Log::error('Failed to fetch fee items', ['error' => $e->getMessage()]);
        return [
            'data' => [],
            'meta' => [
                'current_page' => 1,
                'per_page' => 0,
                'last_page' => 1,
                'total' => 0,
            ],
        ];
    }
}


    public function createFeeItem(array $payload)
    {
        return $this->httpClient()->post("{$this->baseUrl}/fees/items", $payload)->throw()->json();
    }

    public function updateFeeItem(int $id, array $payload)
    {
        return $this->httpClient()->put("{$this->baseUrl}/fees/items/{$id}", $payload)->throw()->json();
    }

    public function deleteFeeItem(int $id)
    {
        return $this->httpClient()->delete("{$this->baseUrl}/fees/items/{$id}")->throw()->status() === 200;
    }

        /**
     * Fee item categories
     */
    public function fetchFeeItemCategories(array $params = []): array
    {
        try {
            $res = $this->httpClient()
                ->get("{$this->baseUrl}/fee-item-categories", $params)
                ->throw();

            // If API returns { data: [...] } keep it consistent
            return $res->json() ?? ['data' => []];
        } catch (\Throwable $e) {
            Log::error('fetchFeeItemCategories failed', ['msg' => $e->getMessage()]);
            return ['data' => []];
        }
    }

    public function fetchFeeItemCategory(int $id): array
    {
        try {
            $res = $this->httpClient()
                ->get("{$this->baseUrl}/fee-item-categories/{$id}")
                ->throw();

            return $res->json() ?? [];
        } catch (\Throwable $e) {
            Log::error('fetchFeeItemCategory failed', ['id' => $id, 'msg' => $e->getMessage()]);
            return [];
        }
    }

    public function createFeeItemCategory(array $payload): array
    {
        $res = $this->httpClient()->post("{$this->baseUrl}/fee-item-categories", $payload)->throw();
        return $res->json();
    }

    public function updateFeeItemCategory(int $id, array $payload): array
    {
        $res = $this->httpClient()->put("{$this->baseUrl}/fee-item-categories/{$id}", $payload)->throw();
        return $res->json();
    }

    public function deleteFeeItemCategory(int $id): bool
    {
        $res = $this->httpClient()->delete("{$this->baseUrl}/fee-item-categories/{$id}")->throw();
        return $res->status() === 200;
    }


    /**
 * Program type fee item amounts (PTFIA) — client methods
 */

/**
 * Fetch paginated program_type_fee_item_amounts
 * Accepts filters: program_type_id, fee_item_id, faculty_id, department_id, level_id, page, per_page, search
 */
public function fetchProgramTypeFeeItemAmounts(array $params = []): array
{
    try {
        $defaults = [
            'page' => $params['page'] ?? 1,
            'per_page' => $params['per_page'] ?? 10,
            'search' => $params['search'] ?? null,
        ];
        $query = array_filter(array_merge($defaults, $params), fn($v) => $v !== null && $v !== '');

        $res = $this->httpClient()
            ->get("{$this->baseUrl}/program-type-fee-item-amounts/", $query)
            ->throw();

        $json = $res->json();

        return [
            'data' => $json['data'] ?? [],
            'meta' => $json['meta'] ?? [
                'current_page' => 1,
                'per_page' => count($json['data'] ?? []),
                'last_page' => 1,
                'total' => count($json['data'] ?? []),
            ],
        ];
    } catch (\Throwable $e) {
        Log::error('fetchProgramTypeFeeItemAmounts failed', ['error' => $e->getMessage(), 'params' => $params]);
        return [
            'data' => [],
            'meta' => ['current_page' => 1, 'per_page' => 0, 'last_page' => 1, 'total' => 0],
        ];
    }
}


/**
 * Fetch single program_type_fee_item_amount by id (show)
 */
public function fetchProgramTypeFeeItemAmount(int $id): array
{
    try {
        $res = $this->httpClient()
            ->get("{$this->baseUrl}/program-type-fee-item-amounts/{$id}")
            ->throw();

        return $res->json() ?? [];
    } catch (\Throwable $e) {
        Log::error('fetchProgramTypeFeeItemAmount failed', ['id' => $id, 'error' => $e->getMessage()]);
        return [];
    }
}

/**
 * Create a program_type_fee_item_amount
 * Expected payload:
 *  - program_type_id (int)
 *  - fee_item_id (int)
 *  - faculty_id (int|null)
 *  - department_id (int|null)
 *  - level_id (int|null)
 *  - first_semester_amount (decimal)
 *  - second_semester_amount (decimal)
 */
public function createProgramTypeFeeItemAmount(array $payload): array
{
    try {
        $res = $this->httpClient()
            ->post("{$this->baseUrl}/program-type-fee-item-amounts", $payload)
            ->throw();

        return $res->json() ?? [];
    } catch (\Illuminate\Http\Client\RequestException $e) {
        Log::warning('createProgramTypeFeeItemAmount validation error', ['payload' => $payload, 'resp' => $e->response?->body()]);
        // bubble useful payload back for UI parsing
        throw $e;
    } catch (\Throwable $e) {
        Log::error('createProgramTypeFeeItemAmount failed', ['error' => $e->getMessage(), 'payload' => $payload]);
        throw $e;
    }
}


/**
 * Update program_type_fee_item_amount
 */
public function updateProgramTypeFeeItemAmount(int $id, array $payload): array
{
    try {
        $res = $this->httpClient()
            ->put("{$this->baseUrl}/program-type-fee-item-amounts/{$id}", $payload)
            ->throw();

        return $res->json() ?? [];
    } catch (\Illuminate\Http\Client\RequestException $e) {
        Log::warning('updateProgramTypeFeeItemAmount validation error', ['id' => $id, 'payload' => $payload, 'resp' => $e->response?->body()]);
        throw $e;
    } catch (\Throwable $e) {
        Log::error('updateProgramTypeFeeItemAmount failed', ['id' => $id, 'error' => $e->getMessage(), 'payload' => $payload]);
        throw $e;
    }
}


/**
 * Delete program_type_fee_item_amount
 */
public function deleteProgramTypeFeeItemAmount(int $id): bool
{
    try {
        $res = $this->httpClient()
            ->delete("{$this->baseUrl}/program-type-fee-item-amounts/{$id}")
            ->throw();

        return $res->status() === 200 || $res->status() === 204;
    } catch (\Throwable $e) {
        Log::error('deleteProgramTypeFeeItemAmount failed', ['id' => $id, 'error' => $e->getMessage()]);
        return false;
    }
}




}
