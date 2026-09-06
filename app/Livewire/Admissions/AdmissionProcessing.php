<?php

namespace App\Livewire\Admissions;

use App\Mail\AdmissionNotification;
use App\Services\AdmissionAuditService;
use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use App\Services\ProgramTypeAccessService;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class AdmissionProcessing extends Component
{
    use WithPagination;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public ?int $acad_session_id = null;

    public ?int $program_type_id = null;

    public ?int $application_type_id = null;

    public ?int $faculty_id = null;

    public ?int $department_id = null;

    public ?int $program_id = null;

    public string $search = '';

    public ?array $selectedCandidate  = null;

    public bool $showReviewModal = false;

    public ?array $processingCandidate = null;

    public bool $showDecisionModal = false;

    public string $admissionDecision = '';

    public string $decisionReason = '';

    public bool $processingDecision = false;


    /*
|--------------------------------------------------------------------------
| Revoke
|--------------------------------------------------------------------------
*/

public ?array $revokingCandidate = null;

public bool $showRevokeModal = false;

public string $revokeReason = '';

public bool $processingRevoke = false;

/*
|--------------------------------------------------------------------------
| Bulk Admission
|--------------------------------------------------------------------------
*/

public bool $showBulkAdmissionConfirm = false;

public bool $processingBulkAdmission = false;

public array $bulkAdmissionResults = [];

public bool $showBulkAdmissionResults = false;

    /*
    |--------------------------------------------------------------------------
    | Lookup data
    |--------------------------------------------------------------------------
    */

    public array $acadSessions = [];

    public ?int $currentSessionId = null;

    public array $programTypes = [];

    public array $applicationTypes = [];

    public array $faculties = [];

    public array $departments = [];

    public array $programs = [];

    /*
    |--------------------------------------------------------------------------
    | Candidate results
    |--------------------------------------------------------------------------
    */

    public array $candidates = [];

    public int $total = 0;

    public int $lastPage = 1;

    /*
    |--------------------------------------------------------------------------
    | UI state
    |--------------------------------------------------------------------------
    */

    public bool $loadingSessions = false;

    public bool $loadingApplicationTypes = false;

    public bool $loadingDepartments = false;

    public bool $loadingPrograms = false;

    public bool $loadingCandidates = false;

    public bool $hasSearched = false;

    public array $selectedCandidates = [];

    public bool $selectAll = false;

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    public int $perPage = 15;

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(AdmissionsPortalClient $service): void
    {
        $this->loadLookups($service);


    }

    /*
    |--------------------------------------------------------------------------
    | Initial lookup loading
    |--------------------------------------------------------------------------
    */

     protected function loadLookups(
    AdmissionsPortalClient $service
): void {
    try {

        $this->loadingSessions = true;

        /*
        |--------------------------------------------------------------------------
        | Academic Sessions
        |--------------------------------------------------------------------------
        |
        | The visible session is the current admission session.
        |
        | We expose:
        |
        |   1. Current session
        |   2. Previous session
        |   3. Two sessions ago
        |
        */

        $allSessions = collect(
            $service->getAcadSessions()
        );

        /*
        |--------------------------------------------------------------------------
        | Current Session
        |--------------------------------------------------------------------------
        */

        $currentSession = $allSessions
            ->first(
                fn ($session) =>
                    (int) ($session['is_visible'] ?? 0) === 1
            );

        if ($currentSession) {

            $this->currentSessionId =
                (int) $currentSession['id'];

            $currentYear =
                $this->extractSessionStartYear(
                    $currentSession['name'] ?? null
                );

            /*
            |--------------------------------------------------------------------------
            | Current + Previous Two
            |--------------------------------------------------------------------------
            */

            $sessions = $allSessions
                ->filter(function ($session) use ($currentYear) {

                    $year = $this->extractSessionStartYear(
                        $session['name'] ?? null
                    );

                    return $year !== null
                        && $year <= $currentYear;
                })
                ->sortByDesc(function ($session) {

                    return $this->extractSessionStartYear(
                        $session['name'] ?? null
                    ) ?? 0;
                })
                ->take(3)
                ->values();

            /*
            |--------------------------------------------------------------------------
            | Ensure Current Session Is Included
            |--------------------------------------------------------------------------
            */

            if (
                !$sessions->contains(
                    fn ($session) =>
                        (int) ($session['id'] ?? 0)
                        === $this->currentSessionId
                )
            ) {
                $sessions->push($currentSession);
            }

            $this->acadSessions = $sessions
                ->sortByDesc(function ($session) {

                    return $this->extractSessionStartYear(
                        $session['name'] ?? null
                    ) ?? 0;
                })
                ->values()
                ->all();

            /*
            |--------------------------------------------------------------------------
            | Default to Current Session
            |--------------------------------------------------------------------------
            */

            $this->acad_session_id =
                $this->currentSessionId;

        } else {

            Log::warning(
                'Admission Processing current academic session not found'
            );

            $this->currentSessionId = null;
            $this->acad_session_id = null;
            $this->acadSessions = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Program Types
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT select a default program type.
        |
        | The admission officer must explicitly choose it.
        |
        */

        $this->programTypes =
            $service->getProgramTypes();

        $this->program_type_id = null;

        /*
        |--------------------------------------------------------------------------
        | Dependent Lookups
        |--------------------------------------------------------------------------
        */

        $this->applicationTypes = [];
        $this->faculties = [];
        $this->departments = [];
        $this->programs = [];

        /*
        |--------------------------------------------------------------------------
        | Faculty
        |--------------------------------------------------------------------------
        */

        $this->faculties =
            $service->getFaculties();

    } catch (\Throwable $e) {

        Log::error(
            'Admission Processing lookup preload failed',
            [
                'error' => $e->getMessage(),
            ]
        );

        Flux::toast(
            'Unable to load admission processing filters.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

    } finally {

        $this->loadingSessions = false;
    }
}


/**
 * Extract the starting year from an academic session name.
 *
 * Examples:
 *
 * 2026/2027 → 2026
 * 2025/2026 → 2025
 */
protected function extractSessionStartYear(
    mixed $sessionName
): ?int {
    if (!is_string($sessionName)) {
        return null;
    }

    if (preg_match('/^(\d{4})/', trim($sessionName), $matches)) {
        return (int) $matches[1];
    }

    return null;
}

    /*
    |--------------------------------------------------------------------------
    | Program Type
    |--------------------------------------------------------------------------
    |
    | Changing a filter must NOT fetch candidates.
    | It only refreshes dependent lookup data.
    |
    */

     public function updatedProgramTypeId(): void
{
    /*
    |--------------------------------------------------------------------------
    | Program Type changed
    |--------------------------------------------------------------------------
    */

    $this->application_type_id = null;

    /*
    |--------------------------------------------------------------------------
    | Clear dependent academic filters
    |--------------------------------------------------------------------------
    */

    $this->department_id = null;
    $this->program_id = null;

    /*
    |--------------------------------------------------------------------------
    | Clear dependent lookup collections
    |--------------------------------------------------------------------------
    */

    $this->applicationTypes = [];
    $this->departments = [];
    $this->programs = [];

    /*
    |--------------------------------------------------------------------------
    | No program type selected
    |--------------------------------------------------------------------------
    */

    if (!$this->program_type_id) {
        return;
    }


     /*
    |--------------------------------------------------------------------------
    | Programme Type Access
    |--------------------------------------------------------------------------
    */

    if (!$this->programTypeAccess()->canAccess(
        (int) $this->program_type_id
    )) {
        $this->program_type_id = null;

        Flux::toast(
            'You are not authorized to process this programme type.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Load application types for selected program type
    |--------------------------------------------------------------------------
    */

    $this->loadApplicationTypes();
}

    /*
    |--------------------------------------------------------------------------
    | Faculty
    |--------------------------------------------------------------------------
    */

    public function updatedFacultyId(): void
    {
        $this->department_id = null;
        $this->program_id = null;

        $this->departments = [];
        $this->programs = [];

        if (!$this->faculty_id) {
            return;
        }

        $this->loadDepartments();
    }

    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    public function updatedDepartmentId(): void
    {
        $this->program_id = null;
        $this->programs = [];

        if (!$this->department_id) {
            return;
        }

        $this->loadPrograms();
    }

    /*
    |--------------------------------------------------------------------------
    | Application Types
    |--------------------------------------------------------------------------
    */

    protected function loadApplicationTypes(): void
    {
        $this->loadingApplicationTypes = true;

        try {
            $service = app(AdmissionsPortalClient::class);

            $this->applicationTypes =
                $service->getApplicationTypesByProgramType(
                    (int) $this->program_type_id
                );

        } catch (\Throwable $e) {
            Log::error(
                'Admission Processing application types load failed',
                [
                    'program_type_id' => $this->program_type_id,
                    'error' => $e->getMessage(),
                ]
            );

            $this->applicationTypes = [];

            Flux::toast(
                'Unable to load application types.',
                variant: 'error',
                position: 'top-right',
                duration: 4000
            );
        } finally {
            $this->loadingApplicationTypes = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Departments
    |--------------------------------------------------------------------------
    */

    protected function loadDepartments(): void
    {
        $this->loadingDepartments = true;

        try {
            $service = app(AdmissionsPortalClient::class);

            $this->departments = $service->getDepartments(
                (int) $this->faculty_id
            );

        } catch (\Throwable $e) {
            Log::error(
                'Admission Processing departments load failed',
                [
                    'faculty_id' => $this->faculty_id,
                    'error' => $e->getMessage(),
                ]
            );

            $this->departments = [];

            Flux::toast(
                'Unable to load departments.',
                variant: 'error',
                position: 'top-right',
                duration: 4000
            );
        } finally {
            $this->loadingDepartments = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Programs
    |--------------------------------------------------------------------------
    */

    protected function loadPrograms(): void
    {
        $this->loadingPrograms = true;

        try {
            $service = app(AdmissionsPortalClient::class);

            $this->programs = $service->getPrograms(
                (int) $this->department_id,
                $this->program_type_id
                    ? (int) $this->program_type_id
                    : null
            );

        } catch (\Throwable $e) {
            Log::error(
                'Admission Processing programs load failed',
                [
                    'department_id' => $this->department_id,
                    'program_type_id' => $this->program_type_id,
                    'error' => $e->getMessage(),
                ]
            );

            $this->programs = [];

            Flux::toast(
                'Unable to load programmes.',
                variant: 'error',
                position: 'top-right',
                duration: 4000
            );
        } finally {
            $this->loadingPrograms = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    |
    | This is the ONLY method that loads candidates.
    |
    */

    public function applyFilters(): void
    {
        if (!$this->validateFilters()) {
            return;
        }

        $this->resetPage();

        $this->hasSearched = true;

        $this->fetchCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Filter validation
    |--------------------------------------------------------------------------
    */

    protected function validateFilters(): bool
    {
        /*
         * Academic session is mandatory for admission processing.
         */
        if (!$this->acad_session_id) {
            Flux::toast(
                'Please select an academic session.',
                variant: 'warning',
                position: 'top-right',
                duration: 4000
            );

            return false;
        }

        /*
         * Program Type is also required because the same person
         * may have different programme-type records.
         */
        if (!$this->program_type_id) {
            Flux::toast(
                'Please select a program type.',
                variant: 'warning',
                position: 'top-right',
                duration: 4000
            );

            return false;
        }

            /*
    |--------------------------------------------------------------------------
    | Programme Type Access
    |--------------------------------------------------------------------------
    |
    | This is the server-side authorization check.
    |
    */

    $this->programTypeAccess()
        ->authorize((int) $this->program_type_id);

        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Candidate API request
    |--------------------------------------------------------------------------
    */

    protected function fetchCandidates(): void
    {
        $this->loadingCandidates = true;


        try {
            $service = app(AdmissionsPortalClient::class);

            $filters = $this->candidateFilters();

            $response = $service->getCandidates($filters);

            $this->candidates = is_array($response['data'] ?? null)
                ? $response['data']
                : [];

            $meta = is_array($response['meta'] ?? null)
                ? $response['meta']
                : [];

            $this->total = (int) (
                $meta['total']
                ?? count($this->candidates)
            );

            $this->lastPage = max(
                1,
                (int) ($meta['last_page'] ?? 1)
            );

        } catch (\Throwable $e) {
            Log::error(
                'Admission Processing candidates load failed',
                [
                    'filters' => $this->candidateFilters(),
                    'error' => $e->getMessage(),
                ]
            );

            $this->candidates = [];
            $this->total = 0;
            $this->lastPage = 1;


            Flux::toast(
                'Unable to load candidates.',
                variant: 'error',
                position: 'top-right',
                duration: 4000
            );
        } finally {
            $this->loadingCandidates = false;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Centralized Candidate Filters
    |--------------------------------------------------------------------------
    */

    protected function candidateFilters(): array
    {
        return array_filter(
            [
                'acad_session_id' => $this->acad_session_id,

                'program_type_id' => $this->program_type_id,

                'application_type_id' => $this->application_type_id,

                'faculty_id' => $this->faculty_id,

                'department_id' => $this->department_id,

                'program_id' => $this->program_id,

                /*
                 * Search RegNo / Name.
                 */
                'q' => trim($this->search),

                /*
                 * Candidate Browser API pagination.
                 */
                'page' => $this->getPage(),

                'per_page' => $this->perPage,
            ],
            fn ($value) =>
                $value !== null
                && $value !== ''
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    |
    | Search is explicit. Typing does not continuously hit the API.
    |
    */

    public function searchCandidates(): void
    {
        if (!$this->validateFilters()) {
            return;
        }

        $this->resetPage();

        $this->hasSearched = true;

        $this->fetchCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Clear Search
    |--------------------------------------------------------------------------
    */

    public function clearSearch(): void
    {
        $this->search = '';

        $this->resetPage();

        /*
         * If the officer has already applied filters,
         * reload using the same filters without the search term.
         */
        if ($this->hasSearched) {
            $this->fetchCandidates();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    public function gotoPage(int $page): void
    {
        if ($page < 1) {
            return;
        }

        if ($this->lastPage > 0 && $page > $this->lastPage) {
            return;
        }

        $this->setPage($page);

        if ($this->hasSearched) {
            $this->fetchCandidates();
        }
    }

    public function nextPage(): void
    {
        if (!$this->hasSearched) {
            return;
        }

        if ($this->getPage() >= $this->lastPage) {
            return;
        }

        $this->setPage($this->getPage() + 1);

        $this->fetchCandidates();
    }

    public function previousPage(): void
    {
        if (!$this->hasSearched) {
            return;
        }

        if ($this->getPage() <= 1) {
            return;
        }

        $this->setPage($this->getPage() - 1);

        $this->fetchCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    public function resetFilters(): void
    {
        $this->reset([
            'acad_session_id',
            'program_type_id',
            'application_type_id',
            'faculty_id',
            'department_id',
            'program_id',
            'search',
        ]);

        $this->applicationTypes = [];
        $this->departments = [];
        $this->programs = [];

        $this->candidates = [];

        $this->total = 0;
        $this->lastPage = 1;

        $this->hasSearched = false;

        $this->resetPage();
    }

    /*
    |--------------------------------------------------------------------------
    | Summary helpers
    |--------------------------------------------------------------------------
    */

    public function getSelectedSessionNameProperty(): ?string
    {
        if (!$this->acad_session_id) {
            return null;
        }

        $session = collect($this->acadSessions)
            ->firstWhere('id', $this->acad_session_id);

        return $session['name'] ?? null;
    }

    public function getSelectedProgramTypeNameProperty(): ?string
    {
        if (!$this->program_type_id) {
            return null;
        }

        $item = collect($this->programTypes)
            ->firstWhere('id', $this->program_type_id);

        return $item['name'] ?? null;
    }

    public function getSelectedApplicationTypeNameProperty(): ?string
    {
        if (!$this->application_type_id) {
            return null;
        }

        $item = collect($this->applicationTypes)
            ->firstWhere('id', $this->application_type_id);

        return $item['name'] ?? null;
    }

    public function getSelectedFacultyNameProperty(): ?string
    {
        if (!$this->faculty_id) {
            return null;
        }

        $item = collect($this->faculties)
            ->firstWhere('id', $this->faculty_id);

        return $item['name'] ?? null;
    }

    public function getSelectedDepartmentNameProperty(): ?string
    {
        if (!$this->department_id) {
            return null;
        }

        $item = collect($this->departments)
            ->firstWhere('id', $this->department_id);

        return $item['name'] ?? null;
    }

    public function getSelectedProgramNameProperty(): ?string
    {
        if (!$this->program_id) {
            return null;
        }

        $item = collect($this->programs)
            ->firstWhere('id', $this->program_id);

        return $item['name'] ?? null;
    }


    public function reviewCandidate(int $id): void
{
    $candidate = collect($this->candidates)
        ->firstWhere('id', $id);

    if (!$candidate) {
        Flux::toast(
            'Candidate could not be found in the current list.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    $this->selectedCandidate = $candidate;
    $this->showReviewModal = true;
}

public function closeReview(): void
{
    $this->showReviewModal = false;
    $this->selectedCandidate = null;
}


public function processCandidate(int $id): void
{
    $candidate = collect($this->candidates)
        ->firstWhere('id', $id);

    if (!$candidate) {
        Flux::toast(
            'Candidate could not be found in the current list.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Already admitted
    |--------------------------------------------------------------------------
    |
    | Do not allow an already admitted candidate to enter the normal
    | admission decision flow.
    |
    */

    if ((bool) ($candidate['is_admitted'] ?? false)) {
        Flux::toast(
            'This candidate has already been admitted.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    $this->processingCandidate = $candidate;

    $this->admissionDecision = '';

    $this->decisionReason = '';

    $this->showDecisionModal = true;
}

public function closeDecisionModal(): void
{
    if ($this->processingDecision) {
        return;
    }

    $this->showDecisionModal = false;

    $this->processingCandidate = null;

    $this->admissionDecision = '';

    $this->decisionReason = '';

    Flux::modal('admission-decision')->close();
}


public function confirmAdmissionDecision(): void
{
    $this->validate([
        'admissionDecision' => [
            'required',
            Rule::in(['admit', 'reject']),
        ],

        'decisionReason' => [
            Rule::requiredIf(
                fn () => $this->admissionDecision === 'reject'
            ),
            'nullable',
            'string',
            'max:1000',
        ],
    ]);

    if (!$this->processingCandidate) {
        Flux::toast(
            'No candidate selected.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    $candidateId = (int) $this->processingCandidate['id'];

    $this->processingDecision = true;

    try {

        if ($this->admissionDecision === 'reject') {

            $this->rejectCandidate($candidateId);

            return;
        }

        $this->admitCandidate($candidateId);

    } catch (\Throwable $e) {

        Log::error('Admission decision failed', [
            'candidate_id' => $candidateId,
            'decision'     => $this->admissionDecision,
            'error'        => $e->getMessage(),
        ]);

        Flux::toast(
            'Unable to process the admission decision. Please try again.',
            variant: 'error',
            position: 'top-right',
            duration: 5000
        );

    } finally {

        $this->processingDecision = false;
    }
}


 protected function rejectCandidate(int $candidateId): void
{
    $client = app(AdmissionsPortalClient::class);
    $audit  = app(AdmissionAuditService::class);

    /*
    |--------------------------------------------------------------------------
    | Reject candidate in Admissions Portal
    |--------------------------------------------------------------------------
    */

    $response = $client->rejectCandidate(
        $candidateId,
        $this->decisionReason
    );

    if (($response['status'] ?? null) !== 'success') {

        throw new \RuntimeException(
            $this->extractApiError(
                $response['error']
                    ?? $response['message']
                    ?? null
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Retrieve authoritative candidate data for audit
    |--------------------------------------------------------------------------
    */

    $candidate = $client->candidateForMigration($candidateId);

    if (!$candidate) {
        Log::warning(
            'Candidate rejected but audit candidate retrieval failed',
            [
                'candidate_id' => $candidateId,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Do not fail the rejection itself because the audit lookup failed.
        |--------------------------------------------------------------------------
        |
        | The Admissions Portal rejection has already succeeded.
        |
        */
    } else {

        /*
        |--------------------------------------------------------------------------
        | Admission Audit
        |--------------------------------------------------------------------------
        */

        $audit->record(
            candidate: $candidate,
            action: 'rejected',
            decision: 'reject',
            reason: $this->decisionReason,
            metadata: [
                'admissions_response' => $response,
            ],
        );
    }


       /*
    |--------------------------------------------------------------------------
    | Close modal and reset state
    |--------------------------------------------------------------------------
    */

$this->processingDecision = false;

$this->showDecisionModal = false;

Flux::modal('admission-decision')->close();

$this->processingCandidate = null;

$this->admissionDecision = '';

$this->decisionReason = '';

    /*
    |--------------------------------------------------------------------------
    | Refresh UI
    |--------------------------------------------------------------------------
    */



    $this->fetchCandidates();

    Flux::toast(
        'Candidate rejected successfully.',
        variant: 'success',
        position: 'top-right',
        duration: 4000
    );
}


protected function admitCandidate(int $candidateId): void
{
    /*
    |--------------------------------------------------------------------------
    | Execute admission
    |--------------------------------------------------------------------------
    |
    | All admission business logic lives in processAdmission().
    |
    */

    $this->processAdmission($candidateId);

    /*
    |--------------------------------------------------------------------------
    | Admission successful — close decision modal
    |--------------------------------------------------------------------------
    |
    | processingDecision is still true here, so we must reset it BEFORE
    | calling closeDecisionModal().
    |
    */

    $this->processingDecision = false;

    $this->closeDecisionModal();

    /*
    |--------------------------------------------------------------------------
    | Refresh candidates
    |--------------------------------------------------------------------------
    */

    $this->fetchCandidates();

    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    Flux::toast(
        'Candidate admitted and committed successfully.',
        variant: 'success',
        position: 'top-right',
        duration: 5000
    );
}


protected function buildAdmissionPayload(array $candidate): array
{
    /*
    |--------------------------------------------------------------------------
    | User
    |--------------------------------------------------------------------------
    */

    $user = is_array($candidate['user'] ?? null)
        ? $candidate['user']
        : [];

    /*
    |--------------------------------------------------------------------------
    | Academic Placement
    |--------------------------------------------------------------------------
    */

    $programTypeId = (int) ($candidate['program_type_id'] ?? 0);
    $programId     = (int) ($candidate['program_id'] ?? 0);
    $facultyId     = (int) ($candidate['faculty_id'] ?? 0);
    $departmentId  = (int) ($candidate['department_id'] ?? 0);
    $sessionId     = (int) ($candidate['acad_session_id'] ?? 0);

    if ($programTypeId < 1) {
        throw new \RuntimeException(
            'Candidate is missing program_type_id.'
        );
    }

    if ($programId < 1) {
        throw new \RuntimeException(
            'Candidate is missing program_id.'
        );
    }

    if ($facultyId < 1) {
        throw new \RuntimeException(
            'Candidate is missing faculty_id.'
        );
    }

    if ($departmentId < 1) {
        throw new \RuntimeException(
            'Candidate is missing department_id.'
        );
    }

    if ($sessionId < 1) {
        throw new \RuntimeException(
            'Candidate is missing acad_session_id.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing Admissions Portal account
    |--------------------------------------------------------------------------
    */

    $username = $this->nullableString(
        $user['username'] ?? null
    );

    $email = $this->nullableString(
        $user['email'] ?? null
    );

    $firstName = $this->nullableString(
        $user['first_name'] ?? null
    );

    $lastName = $this->nullableString(
        $user['last_name'] ?? null
    );

    if (!$username) {
        throw new \RuntimeException(
            'Candidate user is missing username.'
        );
    }

    if (!$email) {
        throw new \RuntimeException(
            'Candidate user is missing email.'
        );
    }

    if (!$firstName) {
        throw new \RuntimeException(
            'Candidate user is missing first name.'
        );
    }

    if (!$lastName) {
        throw new \RuntimeException(
            'Candidate user is missing last name.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Existing password
    |--------------------------------------------------------------------------
    |
    | Password is already hashed in Admissions Portal.
    | Never hash it again here.
    |
    */

    $password = $user['password'] ?? null;

    if (!$password) {
        throw new \RuntimeException(
            'Candidate user is missing password.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payload
    |--------------------------------------------------------------------------
    */

    return [

        /*
        |--------------------------------------------------------------------------
        | USER
        |--------------------------------------------------------------------------
        */

        'username' => $username,

        'email' => $email,

        'first_name' => $firstName,

        'last_name' => $lastName,

        'other_names' => $this->nullableString(
            $user['other_names'] ?? null
        ),

        'fmr_names' => $this->nullableString(
            $user['fmr_names'] ?? null
        ),

        'phone_no' => $this->nullableString(
            $user['phone_no'] ?? null
        ),

        'gender' => $user['gender'] ?? null,

        'marital_status' => $user['marital_status'] ?? null,

        'religion' => $user['religion'] ?? null,

        'blood_group' => $user['blood_group'] ?? null,

        'chronic_condition' => $user['chronic_condition'] ?? null,

        'disability' => $user['disability'] ?? null,

        'genotype' => $user['genotype'] ?? null,

        'dob' => $user['dob'] ?? null,

        'address' => $user['address'] ?? null,

        'state_residence' => $user['state_residence'] ?? null,

        'city' => $user['city'] ?? null,

        'zip_code' => $user['zip_code'] ?? null,

        'mailing_address' => $user['mailing_address'] ?? null,

        'country_id' => $this->nullableId(
            $user['country_id'] ?? null
        ),

        'continent_id' => $this->nullableId(
            $user['continent_id'] ?? null
        ),

        'nationality_id' => $this->nullableId(
            $user['nationality_id'] ?? null
        ),

        'state_id' => $this->nullableId(
            $user['state_id'] ?? null
        ),

        'l_g_a_id' => $this->nullableId(
            $user['l_g_a_id'] ?? null
        ),

        /*
        |--------------------------------------------------------------------------
        | Existing account credentials
        |--------------------------------------------------------------------------
        */

        'is_role' => (int) (
            $user['is_role'] ?? 0
        ),

        /*
        | Always active after migration.
        */
        'is_active' => true,

        'email_verified_at' =>
            $user['email_verified_at'] ?? null,

        /*
        | Already hashed — do not hash again.
        */
        'password' => $password,

        /*
        |--------------------------------------------------------------------------
        | STUDENT
        |--------------------------------------------------------------------------
        */

        'regno' => $candidate['regno'] ?? null,

        'jamb_score' => $candidate['jamb_score'] ?? null,

        'jamb_no' => $candidate['jamb_no'] ?? null,

        'program_id' => $programId,

        'program_type_id' => $programTypeId,

        'study_mode_id' => $this->nullableId(
            $candidate['study_mode_id'] ?? null
        ),

        'faculty_id' => $facultyId,

        'department_id' => $departmentId,

        'class_of_pass_id' => $this->nullableId(
            $candidate['class_of_pass_id'] ?? null
        ),

        'acad_session_id' => $sessionId,

        'school_division_id' => $this->nullableId(
            $candidate['school_division_id'] ?? null
        ),

        'start_session_id' => $this->nullableId(
            $candidate['start_session_id'] ?? null
        ),

        'prev_uni' => $candidate['prev_uni'] ?? null,
    ];
}

protected function nullableString(mixed $value): ?string
{
    if ($value === null) {
        return null;
    }

    $value = trim((string) $value);

    return $value === ''
        ? null
        : $value;
}

protected function nullableId(mixed $value): ?int
{
    if ($value === null || $value === '') {
        return null;
    }

    $value = (int) $value;

    return $value > 0
        ? $value
        : null;
}


public function toggleCandidate(int $candidateId): void
{
    if (in_array($candidateId, $this->selectedCandidates, true)) {
        $this->selectedCandidates = array_values(
            array_diff($this->selectedCandidates, [$candidateId])
        );
    } else {
        $this->selectedCandidates[] = $candidateId;
    }

    $this->selectAll = false;
}

public function toggleSelectAll(): void
{
    if ($this->selectAll) {
        $this->selectedCandidates = collect($this->candidates ?? [])
            ->filter(fn ($candidate) => !($candidate['is_admitted'] ?? false))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        return;
    }

    $this->selectedCandidates = [];
}


protected function clearSelection(): void
{
    $this->selectedCandidates = [];
    $this->selectAll = false;
}

 public function admitSelected(): void
{
    $candidateIds = collect($this->selectedCandidates)
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    if (empty($candidateIds)) {
        Flux::toast(
            'Please select at least one candidate.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Verify selected candidates still exist in current result set
    |--------------------------------------------------------------------------
    */

    $availableIds = collect($this->candidates ?? [])
        ->filter(fn ($candidate) => !($candidate['is_admitted'] ?? false))
        ->pluck('id')
        ->map(fn ($id) => (int) $id)
        ->all();

    $candidateIds = array_values(
        array_intersect($candidateIds, $availableIds)
    );

    if (empty($candidateIds)) {
        $this->clearSelection();

        Flux::toast(
            'None of the selected candidates is available for admission.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Open confirmation
    |--------------------------------------------------------------------------
    */

    $this->showBulkAdmissionConfirm = true;
}



protected function processAdmission(int $candidateId): array
{
    $admissions   = app(AdmissionsPortalClient::class);
    $studentPortal = app(StudentPortalClient::class);
    $audit         = app(AdmissionAuditService::class);

    /*
    |--------------------------------------------------------------------------
    | 1. Get candidate
    |--------------------------------------------------------------------------
    */

    $candidate = $admissions->candidateForMigration($candidateId);

    if (!$candidate) {
        throw new \RuntimeException(
            "Candidate {$candidateId} was not found."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 2. Already admitted protection
    |--------------------------------------------------------------------------
    */

    if (($candidate['is_admitted'] ?? false) === true) {
        throw new \RuntimeException(
            "Candidate {$candidate['regno']} is already admitted."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 3. Build Student Portal payload
    |--------------------------------------------------------------------------
    */

    $payload = $this->buildAdmissionPayload($candidate);

    /*
    |--------------------------------------------------------------------------
    | 4. Migrate to Student Portal
    |--------------------------------------------------------------------------
    */

    $studentResult = $studentPortal->migrateAdmission($payload);

    if (($studentResult['status'] ?? null) !== 'success') {

        throw new \RuntimeException(
            $this->extractApiError(
                $studentResult['error']
                    ?? $studentResult['message']
                    ?? null
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 5. Commit admission in Admissions Portal
    |--------------------------------------------------------------------------
    */

    $commitResult = $admissions->commitAdmission($candidateId);

    if (($commitResult['status'] ?? null) !== 'success') {

        Log::critical(
            'Student Portal migration succeeded but Admissions commit failed',
            [
                'candidate_id'   => $candidateId,
                'regno'          => $candidate['regno'] ?? null,
                'student_result' => $studentResult,
                'commit_result'  => $commitResult,
            ]
        );

        throw new \RuntimeException(
            $this->extractApiError(
                $commitResult['error']
                    ?? $commitResult['message']
                    ?? null
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | 6. Admission Audit
    |--------------------------------------------------------------------------
    */

    $audit->record(
        candidate: $candidate,
        action: 'admitted',
        decision: 'admit',
        reason: null,
        metadata: [
            'student_portal_response' =>
                $studentResult,

            'admissions_commit_response' =>
                $commitResult,
        ],
    );

    /*
    |--------------------------------------------------------------------------
    | 7. Resolve Academic Context
    |--------------------------------------------------------------------------
    |
    | candidateForMigration() gives us the academic IDs.
    | The notification needs the corresponding names.
    |
    */

    $academicContext  = $this->buildAcademicContext(
        $candidate
    );


    /*
|--------------------------------------------------------------------------
| 8. Send Admission Notification
|--------------------------------------------------------------------------
|
| Email failure must NOT undo a successful admission.
|
*/

$email = data_get(
    $candidate,
    'user.email'
);

if ($email) {

    try {

        Mail::to($email)->send(
            new AdmissionNotification(
                $candidate,
                $academicContext
            )
        );

        Log::info(
            'Admission notification sent',
            [
                'candidate_id' =>
                    $candidate['id'] ?? null,

                'regno' =>
                    $candidate['regno'] ?? null,

                'email' =>
                    $email,
            ]
        );

    } catch (\Throwable $mailException) {

        Log::error(
            'Admission notification failed',
            [
                'candidate_id' =>
                    $candidate['id'] ?? null,

                'regno' =>
                    $candidate['regno'] ?? null,

                'email' =>
                    $email,

                'error' =>
                    $mailException->getMessage(),
            ]
        );
    }

} else {

    Log::warning(
        'Admission notification skipped: candidate has no email',
        [
            'candidate_id' =>
                $candidate['id'] ?? null,

            'regno' =>
                $candidate['regno'] ?? null,
        ]
    );
}



    /*
    |--------------------------------------------------------------------------
    | 9. Return result
    |--------------------------------------------------------------------------
    */

    return [
        'status'      => 'success',
        'candidate_id' => $candidateId,
        'regno'        => $candidate['regno'] ?? null,
    ];
}

protected function buildAcademicContext(array $candidate): array
{
    $admissions = app(AdmissionsPortalClient::class);

    $programTypeId = (int) ($candidate['program_type_id'] ?? 0);
    $facultyId     = (int) ($candidate['faculty_id'] ?? 0);
    $departmentId  = (int) ($candidate['department_id'] ?? 0);
    $programId     = (int) ($candidate['program_id'] ?? 0);
    $sessionId     = (int) ($candidate['acad_session_id'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Programme Type
    |--------------------------------------------------------------------------
    */

    $programType = collect($this->programTypes)
        ->firstWhere('id', $programTypeId);

    /*
    |--------------------------------------------------------------------------
    | Faculty
    |--------------------------------------------------------------------------
    */

    $faculty = collect($this->faculties)
        ->firstWhere('id', $facultyId);

    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    $departments = $admissions->getDepartments($facultyId);

    $department = collect($departments)
        ->firstWhere('id', $departmentId);

    /*
    |--------------------------------------------------------------------------
    | Programme
    |--------------------------------------------------------------------------
    */

    $programs = $admissions->getPrograms(
        $departmentId,
        $programTypeId
    );

    $program = collect($programs)
        ->firstWhere('id', $programId);

    /*
    |--------------------------------------------------------------------------
    | Academic Session
    |--------------------------------------------------------------------------
    */

    $session = collect($this->acadSessions)
        ->firstWhere('id', $sessionId);

    return [
        'program_type' => $programType['name'] ?? null,
        'faculty'      => $faculty['name'] ?? null,
        'department'   => $department['name'] ?? null,
        'program'      => $program['name'] ?? null,
        'session'      => $session['name'] ?? null,

        'program_type_id' => $programTypeId,
        'faculty_id'      => $facultyId,
        'department_id'   => $departmentId,
        'program_id'      => $programId,
        'acad_session_id' => $sessionId,
    ];
}


public function getSelectedCandidateCountProperty(): int
{
    return count($this->selectedCandidates);
}

public function confirmBulkAdmission(): void
{
    if ($this->processingBulkAdmission) {
        return;
    }

    $candidateIds = collect($this->selectedCandidates)
        ->map(fn ($id) => (int) $id)
        ->filter(fn ($id) => $id > 0)
        ->unique()
        ->values()
        ->all();

    if (empty($candidateIds)) {
        $this->showBulkAdmissionConfirm = false;

        Flux::toast(
            'No candidates are selected.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Close confirmation and begin processing
    |--------------------------------------------------------------------------
    */

    $this->showBulkAdmissionConfirm = false;

    $this->processingBulkAdmission = true;

    $this->bulkAdmissionResults = [
        'successful' => [],
        'failed'     => [],
    ];

    try {

        /*
        |--------------------------------------------------------------------------
        | Process independently
        |--------------------------------------------------------------------------
        |
        | One failure must NOT stop the remaining candidates.
        |
        */

        foreach ($candidateIds as $candidateId) {

            try {

                $result = $this->processAdmission($candidateId);

                if (($result['status'] ?? null) === 'success') {

                    $this->bulkAdmissionResults['successful'][] = [
                        'id'    => $candidateId,
                        'regno' => $result['regno'] ?? null,
                    ];

                    continue;
                }

                $this->bulkAdmissionResults['failed'][] = [
                    'id'    => $candidateId,
                    'regno' => $result['regno'] ?? null,
                    'error' => $result['error']
                        ?? 'Admission failed.',
                ];

            } catch (\Throwable $e) {

                Log::error(
                    'Bulk admission candidate failed',
                    [
                        'candidate_id' => $candidateId,
                        'error'        => $e->getMessage(),
                    ]
                );

                $this->bulkAdmissionResults['failed'][] = [
                    'id'    => $candidateId,
                    'regno' => null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Clear selection
        |--------------------------------------------------------------------------
        */

        $this->clearSelection();

        /*
        |--------------------------------------------------------------------------
        | Refresh candidate list
        |--------------------------------------------------------------------------
        */

        if ($this->hasSearched) {
            $this->fetchCandidates();
        }

        /*
        |--------------------------------------------------------------------------
        | Show results
        |--------------------------------------------------------------------------
        */

        $this->showBulkAdmissionResults = true;

    } finally {

        $this->processingBulkAdmission = false;
    }
}

public function cancelBulkAdmission(): void
{
    if ($this->processingBulkAdmission) {
        return;
    }

    $this->showBulkAdmissionConfirm = false;
}

public function closeBulkAdmissionResults(): void
{
    $this->showBulkAdmissionResults = false;

    $this->bulkAdmissionResults = [];
}


public function getBulkSuccessfulCountProperty(): int
{
    return count(
        $this->bulkAdmissionResults['successful'] ?? []
    );
}

public function getBulkFailedCountProperty(): int
{
    return count(
        $this->bulkAdmissionResults['failed'] ?? []
    );
}

public function getBulkProcessedCountProperty(): int
{
    return $this->bulkSuccessfulCount
        + $this->bulkFailedCount;
}


public function confirmRevoke(int $candidateId): void
{

    $candidate = collect($this->candidates)
        ->firstWhere('id', $candidateId);

    if (!$candidate) {
        Flux::toast(
            'Candidate could not be found.',
            variant: 'danger',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Only admitted candidates can be revoked
    |--------------------------------------------------------------------------
    */

    if (!(bool) ($candidate['is_admitted'] ?? false)) {
        Flux::toast(
            'This candidate is not currently admitted.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    $this->revokingCandidate = $candidate;

    $this->revokeReason = '';

    $this->showRevokeModal = true;

    Flux::modal('revoke-admission')->show();
}

public function closeRevokeModal(): void
{
    if ($this->processingRevoke) {
        return;
    }

    $this->showRevokeModal = false;

    $this->revokingCandidate = null;

    $this->revokeReason = '';

    Flux::modal('revoke-admission')->close();
}

public function revokeAdmission(): void
{
    if ($this->processingRevoke) {
        return;
    }

    $candidate = $this->revokingCandidate;

    if (!$candidate) {
        Flux::toast(
            'No candidate selected for revocation.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    $candidateId = (int) ($candidate['id'] ?? 0);
    $regno       = $candidate['regno'] ?? null;

    if ($candidateId < 1 || !$regno) {
        Flux::toast(
            'Invalid candidate information.',
            variant: 'danger',
            position: 'top-right',
            duration: 4000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Validate reason
    |--------------------------------------------------------------------------
    */

    $this->validate([
        'revokeReason' => [
            'required',
            'string',
            'min:3',
            'max:1000',
        ],
    ], [
        'revokeReason.required' =>
            'Please provide a reason for revoking the admission.',
    ]);

    $this->processingRevoke = true;

    try {

        $studentPortal = app(StudentPortalClient::class);
        $admissions    = app(AdmissionsPortalClient::class);
        $audit         = app(AdmissionAuditService::class);

    $authoritativeCandidate =
    $admissions->candidateForMigration($candidateId);

     $candidate = $this->revokingCandidate;

if (!$candidate) {
    throw new \RuntimeException(
        'No candidate is selected for revocation.'
    );
}

if (!(bool) ($candidate['is_admitted'] ?? false)) {
    throw new \RuntimeException(
        'This candidate is not currently admitted.'
    );
}

        /*
        |--------------------------------------------------------------------------
        | 1. Student Portal
        |--------------------------------------------------------------------------
        |
        | This MUST succeed before we touch the Admissions Portal.
        |
        */

        Log::info(
            'Admission Processing → Student Portal revoke',
            [
                'candidate_id' => $candidateId,
                'regno'        => $regno,
            ]
        );

        $studentResult = $studentPortal
            ->revokeAdmission($regno);

        if (($studentResult['status'] ?? null) !== 'success') {

            throw new \RuntimeException(
                $this->extractApiError(
                    $studentResult['error']
                        ?? $studentResult['message']
                        ?? null
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Admissions Portal
        |--------------------------------------------------------------------------
        |
        | Only execute this after Student Portal succeeds.
        |
        */

        Log::info(
            'Admission Processing → Admissions Portal revoke',
            [
                'candidate_id' => $candidateId,
                'regno'        => $regno,
            ]
        );

        $admissionsResult = $admissions
            ->revokeAdmission($candidateId);

        if (($admissionsResult['status'] ?? null) !== 'success') {

            /*
            |--------------------------------------------------------------------------
            | Critical inconsistency
            |--------------------------------------------------------------------------
            |
            | Student Portal has already been revoked, but Admissions
            | Portal failed to update.
            |
            */

            Log::critical(
                'Student Portal revoked but Admissions Portal revoke failed',
                [
                    'candidate_id'      => $candidateId,
                    'regno'             => $regno,
                    'student_result'    => $studentResult,
                    'admissions_result' => $admissionsResult,
                ]
            );

            throw new \RuntimeException(
                $this->extractApiError(
                    $admissionsResult['error']
                        ?? $admissionsResult['message']
                        ?? null
                )
            );
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Audit
        |--------------------------------------------------------------------------
        */

        $audit->record(
            candidate: $authoritativeCandidate,
            action: 'revoked',
            decision: 'revoke',
            reason: $this->revokeReason,
            metadata: [
                'student_portal_response' =>
                    $studentResult,

                'admissions_response' =>
                    $admissionsResult,
            ],
        );

        /*
        |--------------------------------------------------------------------------
        | 4. Close modal
        |--------------------------------------------------------------------------
        */

          $this->processingRevoke = false;
          $this->showRevokeModal = false;
          Flux::modal('revoke-admission')->close();
          $this->revokingCandidate = null;
          $this->revokeReason = '';

        /*
        |--------------------------------------------------------------------------
        | 5. Refresh candidate list
        |--------------------------------------------------------------------------
        */

        $this->fetchCandidates();

        Flux::toast(
            'Admission revoked successfully.',
            variant: 'success',
            position: 'top-right',
            duration: 5000
        );

    } catch (\Throwable $e) {

        Log::error(
            'Admission revoke failed',
            [
                'candidate_id' => $candidateId,
                'regno'        => $regno,
                'error'        => $e->getMessage(),
            ]
        );

        Flux::toast(
            $e->getMessage(),
            variant: 'danger',
            position: 'top-right',
            duration: 6000
        );

    } finally {

        $this->processingRevoke = false;
    }
}


protected function programTypeAccess(): ProgramTypeAccessService
{
    return app(ProgramTypeAccessService::class);
}

public function getAvailableProgramTypesProperty(): array
{
    return $this->programTypeAccess()
        ->filterArray($this->programTypes);
}


protected function extractApiError(mixed $error): string
{
    if (is_string($error) && $error !== '') {
        return $error;
    }

    if (is_array($error)) {
        if (isset($error['message']) && is_string($error['message'])) {
            return $error['message'];
        }

        if (isset($error['error']) && is_string($error['error'])) {
            return $error['error'];
        }

        return json_encode($error, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            ?: 'An unknown API error occurred.';
    }

    if ($error instanceof \Throwable) {
        return $error->getMessage();
    }

    return 'An unknown API error occurred.';
}



    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view('livewire.admissions.admission-processing');
    }
}
