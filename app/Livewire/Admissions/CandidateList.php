<?php

namespace App\Livewire\Admissions;

use App\Services\Clients\AdmissionsPortalClient;
use App\Services\ProgramTypeAccessService;
use Flux\Flux;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;

class CandidateList extends Component
{
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

    public ?int $currentSessionId = null;

    /**
     * Submitted is the default registration status.
     */
    public string $registrationStatus = 'submitted';

    public string $search = '';

    public string $submittedFrom = '';

    public string $submittedTo = '';

    /*
    |--------------------------------------------------------------------------
    | Lookup Data
    |--------------------------------------------------------------------------
    */

    public array $academicSessions = [];

    public array $programTypes = [];

    public array $faculties = [];

    public array $departments = [];

    public array $programs = [];

    public array $applicationTypes = [];

    /*
    |--------------------------------------------------------------------------
    | Candidate Data
    |--------------------------------------------------------------------------
    */

    public array $candidates = [];

    public int $page = 1;

    public int $perPage = 15;

    public int $lastPage = 1;

    public int $total = 0;

    public array $selectedCandidate = [];

    public bool $showCandidateDetails = false;



    /*
    |--------------------------------------------------------------------------
    | UI State
    |--------------------------------------------------------------------------
    */

    public bool $showFilters = false;

    public bool $loadingCandidates = false;

    public bool $loadingDepartments = false;

    public bool $loadingPrograms = false;

    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    |
    | We retain selection because it will still be useful for
    | credential downloads and future candidate actions.
    |
    */

    public array $selected = [];

    public bool $selectAll = false;

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

public function mount(): void
{
    $client = app(AdmissionsPortalClient::class);

    /*
    |--------------------------------------------------------------------------
    | Academic Sessions
    |--------------------------------------------------------------------------
    */

    $this->loadAcademicSessions();


    /*
    |--------------------------------------------------------------------------
    | Program Types
    |--------------------------------------------------------------------------
    */

    $this->programTypes =
        $client->getProgramTypes();

    /*
    |--------------------------------------------------------------------------
    | Faculties
    |--------------------------------------------------------------------------
    */

    $this->faculties =
        $client->getFaculties();

    /*
    |--------------------------------------------------------------------------
    | Default Registration Status
    |--------------------------------------------------------------------------
    */

    $this->registrationStatus = 'submitted';

    /*
    |--------------------------------------------------------------------------
    | Default Program Type
    |--------------------------------------------------------------------------
    */

    $this->program_type_id =
        $this->programTypes[0]['id'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | Load Application Types
    |--------------------------------------------------------------------------
    */

    if ($this->program_type_id) {

        $this->applicationTypes =
            $client->getApplicationTypesByProgramType(
                (int) $this->program_type_id
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Load Candidates
    |--------------------------------------------------------------------------
    */

    $this->loadCandidates();
}

    /*
    |--------------------------------------------------------------------------
    | Academic Session
    |--------------------------------------------------------------------------
    */

    public function updatedAcadSessionId(): void
    {
        $this->resetCandidateState();

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Program Type
    |--------------------------------------------------------------------------
    */

public function updatedProgramTypeId(): void
{
    $this->resetCandidateState();

    /*
    |--------------------------------------------------------------------------
    | Reset dependent filters
    |--------------------------------------------------------------------------
    */

    $this->application_type_id = null;

    $this->department_id = null;

    $this->program_id = null;

    $this->applicationTypes = [];

    $this->departments = [];

    $this->programs = [];

    /*
    |--------------------------------------------------------------------------
    | Load Application Types for selected Program Type
    |--------------------------------------------------------------------------
    */

    if ($this->program_type_id) {

        try {

            $client = app(AdmissionsPortalClient::class);

            $this->applicationTypes =
                $client->getApplicationTypesByProgramType(
                    (int) $this->program_type_id
                );

        } catch (\Throwable $e) {

            Log::error(
                'Candidate list application types load failed',
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
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Load Candidates
    |--------------------------------------------------------------------------
    */

    $this->loadCandidates();
}


/*
|--------------------------------------------------------------------------
| Application Type
|--------------------------------------------------------------------------
*/

public function updatedApplicationTypeId(): void
{
    $this->resetCandidateState();

    $this->loadCandidates();
}

    /*
    |--------------------------------------------------------------------------
    | Faculty
    |--------------------------------------------------------------------------
    */

    public function updatedFacultyId(): void
    {
        $this->resetCandidateState();

        $this->department_id = null;
        $this->program_id = null;

        $this->departments = [];
        $this->programs = [];

        if (!$this->faculty_id) {
            $this->loadCandidates();

            return;
        }

        $this->loadingDepartments = true;

        try {

            $client = app(AdmissionsPortalClient::class);

            $this->departments = $client->getDepartments(
                (int) $this->faculty_id
            );

        } catch (\Throwable $e) {

            Log::error('Candidate list departments load failed', [
                'faculty_id' => $this->faculty_id,
                'error' => $e->getMessage(),
            ]);

            Flux::toast(
                'Unable to load departments.',
                variant: 'error',
                position: 'top-right',
                duration: 4000
            );

        } finally {

            $this->loadingDepartments = false;
        }

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    public function updatedDepartmentId(): void
    {
        $this->resetCandidateState();

        $this->program_id = null;
        $this->programs = [];

        if ($this->department_id) {
            $this->loadPrograms();
        }

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Program
    |--------------------------------------------------------------------------
    */

    public function updatedProgramId(): void
    {
        $this->resetCandidateState();

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Registration Status
    |--------------------------------------------------------------------------
    */

    public function updatedRegistrationStatus(): void
    {
        $this->resetCandidateState();

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    public function updatedSearch(): void
    {
        $this->page = 1;

        $this->resetSelection();

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Load Programs
    |--------------------------------------------------------------------------
    */

    protected function loadPrograms(): void
    {
        if (!$this->department_id) {
            $this->programs = [];

            return;
        }

        $this->loadingPrograms = true;

        try {

            $client = app(AdmissionsPortalClient::class);

            $this->programs = $client->getPrograms(
                (int) $this->department_id,
                $this->program_type_id
                    ? (int) $this->program_type_id
                    : null
            );

        } catch (\Throwable $e) {

            Log::error('Candidate list programs load failed', [
                'department_id' => $this->department_id,
                'program_type_id' => $this->program_type_id,
                'error' => $e->getMessage(),
            ]);

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
| Submission Date Range
|--------------------------------------------------------------------------
*/

public function updatedSubmittedFrom(): void
{
    $this->resetCandidateState();

    $this->loadCandidates();
}

public function updatedSubmittedTo(): void
{
    $this->resetCandidateState();

    $this->loadCandidates();
}

    /*
    |--------------------------------------------------------------------------
    | Load Candidates
    |--------------------------------------------------------------------------
    */

    public function loadCandidates(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Primary Filters
        |--------------------------------------------------------------------------
        |
        | Academic Session + Program Type are the primary filters.
        |
        */

        if (
            !$this->acad_session_id ||
            !$this->program_type_id
        ) {

            $this->candidates = [];

            $this->total = 0;

            $this->lastPage = 1;

            $this->page = 1;

            return;
        }

        $this->loadingCandidates = true;

        try {


        $filters = [
    'acad_session_id' => $this->acad_session_id,

    'program_type_id' => $this->program_type_id,

    'application_type_id' => $this->application_type_id,

    'faculty_id' => $this->faculty_id,

    'department_id' => $this->department_id,

    'program_id' => $this->program_id,

    'q' => trim($this->search),

    'submitted_from' => $this->submittedFrom,

    'submitted_to' => $this->submittedTo,

    'page' => $this->page,

    'per_page' => $this->perPage,
       ];

            /*
            |--------------------------------------------------------------------------
            | Registration Status
            |--------------------------------------------------------------------------
            */

            if ($this->registrationStatus === 'submitted') {

                $filters['is_submitted'] = 1;
            }

            $client = app(AdmissionsPortalClient::class);

            $response = $client->getCandidates(
                array_filter(
                    $filters,
                    fn ($value) =>
                        $value !== null &&
                        $value !== ''
                )
            );

            $this->candidates = $response['data'] ?? [];

            $meta = $response['meta'] ?? [];

            $this->page = (int) (
                $meta['current_page']
                ?? $this->page
            );

            $this->perPage = (int) (
                $meta['per_page']
                ?? $this->perPage
            );

            $this->lastPage = (int) (
                $meta['last_page']
                ?? 1
            );

            $this->total = (int) (
                $meta['total']
                ?? count($this->candidates)
            );

            $this->selectAll = false;
            //$this->showFilters = false;

        } catch (\Throwable $e) {

            Log::error('Candidate list load failed', [
                'error' => $e->getMessage(),
            ]);

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
    | Pagination
    |--------------------------------------------------------------------------
    */

    public function gotoPage(int $page): void
    {
        if (
            $page < 1 ||
            $page > $this->lastPage
        ) {
            return;
        }

        $this->page = $page;

        $this->loadCandidates();
    }

    public function nextPage(): void
    {
        if ($this->page >= $this->lastPage) {
            return;
        }

        $this->page++;

        $this->loadCandidates();
    }

    public function prevPage(): void
    {
        if ($this->page <= 1) {
            return;
        }

        $this->page--;

        $this->loadCandidates();
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

public function resetFilters(): void
{
    $this->acad_session_id = null;

    $this->program_type_id = null;

    $this->application_type_id = null;

    $this->faculty_id = null;

    $this->department_id = null;

    $this->program_id = null;

    $this->registrationStatus = 'submitted';

    $this->search = '';

    $this->submittedFrom = null;

    $this->submittedTo = null;

    /*
    |--------------------------------------------------------------------------
    | Clear dependent lookups
    |--------------------------------------------------------------------------
    */

    $this->applicationTypes = [];

    $this->departments = [];

    $this->programs = [];

    /*
    |--------------------------------------------------------------------------
    | Reset candidate state
    |--------------------------------------------------------------------------
    */

    $this->resetCandidateState();
}

    /*
    |--------------------------------------------------------------------------
    | Toggle Filters
    |--------------------------------------------------------------------------
    */

    public function toggleFilters(): void
    {
        $this->showFilters = !$this->showFilters;
    }

    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    */

    public function updatedSelectAll(bool $value): void
    {
        $ids = collect($this->candidates)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($value) {

            $this->selected = array_values(
                array_unique(
                    array_merge($this->selected, $ids)
                )
            );

            return;
        }

        $this->selected = array_values(
            array_diff($this->selected, $ids)
        );
    }

    protected function resetSelection(): void
    {
        $this->selected = [];

        $this->selectAll = false;
    }

    protected function resetCandidateState(): void
    {
        $this->page = 1;

        $this->lastPage = 1;

        $this->total = 0;

        $this->candidates = [];

        $this->resetSelection();
    }


protected function loadAcademicSessions(): void
{
    $client = app(AdmissionsPortalClient::class);

    /*
    |--------------------------------------------------------------------------
    | Load all academic sessions
    |--------------------------------------------------------------------------
    */

    $allSessions = collect(
        $client->getAcadSessions()
    );

    /*
    |--------------------------------------------------------------------------
    | Current Academic Session
    |--------------------------------------------------------------------------
    |
    | is_visible = 1 is the source of truth.
    |
    */

    $current = $allSessions->firstWhere('is_visible', 1);

    if (!$current) {

        $this->academicSessions = [];
        $this->currentSessionId = null;
        $this->acad_session_id = null;

        return;
    }

    $this->currentSessionId = (int) $current['id'];

    /*
    |--------------------------------------------------------------------------
    | Extract current session year
    |--------------------------------------------------------------------------
    |
    | Example:
    | 2026/2027 → 2026
    |
    */

    preg_match(
        '/^(\d{4})\/(\d{4})$/',
        $current['name'],
        $matches
    );

    if (empty($matches)) {

        $this->academicSessions = [$current];
        $this->acad_session_id = $this->currentSessionId;

        return;
    }

    $currentStartYear = (int) $matches[1];

    /*
    |--------------------------------------------------------------------------
    | Previous / Current / Next
    |--------------------------------------------------------------------------
    */

    $sessionNames = [
        ($currentStartYear - 1) . '/' . $currentStartYear,
        $currentStartYear . '/' . ($currentStartYear + 1),
        ($currentStartYear + 1) . '/' . ($currentStartYear + 2),
    ];

    /*
    |--------------------------------------------------------------------------
    | Keep only sessions that exist
    |--------------------------------------------------------------------------
    */

    $this->academicSessions = $allSessions
        ->filter(
            fn ($session) =>
                in_array($session['name'], $sessionNames, true)
        )
        ->sortByDesc(function ($session) {

            preg_match(
                '/^(\d{4})/',
                $session['name'],
                $matches
            );

            return (int) ($matches[1] ?? 0);
        })
        ->values()
        ->all();

    /*
    |--------------------------------------------------------------------------
    | Default to current session
    |--------------------------------------------------------------------------
    */

    $this->acad_session_id = $this->currentSessionId;
}


/*
|--------------------------------------------------------------------------
| View Candidate O'Level / Credentials
|--------------------------------------------------------------------------
*/

public function viewCandidate(int|string $candidateId): void
{
    $candidate = collect($this->candidates)
        ->firstWhere('id', (int) $candidateId);

    if (!$candidate) {
        return;
    }

    $this->selectedCandidate = $candidate;

    $this->showCandidateDetails = true;

    Flux::modal('candidate-details')->show();
}

public function closeCandidateDetails(): void
{
    $this->showCandidateDetails = false;

    $this->selectedCandidate = [];

    Flux::modal('candidate-details')->close();
}

public function getShowJambColumnProperty(): bool
{
    return collect($this->candidates)
        ->contains(
            fn ($candidate) =>
                ($candidate['is_jamb_application'] ?? false) === true
        );
}


/**
 * Fetch all candidates matching the current Candidate Browser filters.
 *
 * This is intentionally separate from loadCandidates().
 *
 * loadCandidates():
 * - loads only the current UI page
 * - uses $this->page
 * - uses $this->perPage (15)
 *
 * This method:
 * - uses the same filters
 * - ignores UI pagination
 * - fetches every API page
 * - is used by the Credentials ZIP
 */
protected function fetchCandidatesForCredentialZip(): array
{
    $client = app(AdmissionsPortalClient::class);

    /*
    |--------------------------------------------------------------------------
    | Build the same filters used by loadCandidates()
    |--------------------------------------------------------------------------
    */

    $filters = [
        'acad_session_id'     => $this->acad_session_id,
        'program_type_id'     => $this->program_type_id,
        'application_type_id' => $this->application_type_id,
        'faculty_id'          => $this->faculty_id,
        'department_id'       => $this->department_id,
        'program_id'          => $this->program_id,

        'q' => trim($this->search),

        'submitted_from' => $this->submittedFrom,
        'submitted_to'   => $this->submittedTo,

        /*
        |--------------------------------------------------------------------------
        | Large batch for ZIP
        |--------------------------------------------------------------------------
        |
        | This is deliberately independent of the Candidate Browser's
        | 15-record pagination.
        |
        */

        'page'     => 1,
        'per_page' => 200,
    ];


    /*
    |--------------------------------------------------------------------------
    | Registration Status
    |--------------------------------------------------------------------------
    */

    if ($this->registrationStatus === 'submitted') {
        $filters['is_submitted'] = 1;
    }


    /*
    |--------------------------------------------------------------------------
    | Remove empty filters
    |--------------------------------------------------------------------------
    */

    $filters = array_filter(
        $filters,
        fn ($value) =>
            $value !== null &&
            $value !== ''
    );


    /*
    |--------------------------------------------------------------------------
    | Fetch all API pages
    |--------------------------------------------------------------------------
    */

    $allCandidates = [];

    $page = 1;

    do {

        $filters['page'] = $page;

        $response = $client->getCandidates($filters);

        $data = $response['data'] ?? [];

        if (!is_array($data)) {
            $data = [];
        }

        /*
        |--------------------------------------------------------------------------
        | Add current batch
        |--------------------------------------------------------------------------
        */

        foreach ($data as $candidate) {
            $allCandidates[] = $candidate;
        }


        /*
        |--------------------------------------------------------------------------
        | Determine whether another page exists
        |--------------------------------------------------------------------------
        */

        $meta = $response['meta'] ?? [];

        $currentPage = (int) (
            $meta['current_page']
            ?? $page
        );

        $lastPage = (int) (
            $meta['last_page']
            ?? $currentPage
        );


        $page = $currentPage + 1;

    } while ($page <= $lastPage);


    return $allCandidates;
}



/**
 * Build a meaningful filename for the credentials ZIP.
 */
private function credentialsZipFilename(): string
{
    $session = collect($this->academicSessions)
        ->firstWhere('id', (int) $this->acad_session_id)['name']
        ?? null;

    $programType = collect($this->programTypes)
        ->firstWhere('id', (int) $this->program_type_id)['name']
        ?? null;

    $applicationType = collect($this->applicationTypes)
        ->firstWhere('id', (int) $this->application_type_id)['name']
        ?? null;

    $faculty = collect($this->faculties)
        ->firstWhere('id', (int) $this->faculty_id)['name']
        ?? null;

    $department = collect($this->departments)
        ->firstWhere('id', (int) $this->department_id)['name']
        ?? null;

    $program = collect($this->programs)
        ->firstWhere('id', (int) $this->program_id)['name']
        ?? null;


    $parts = [];

    if ($session) {
        $parts[] = $session;
    }

    if ($faculty) {
        $parts[] = $faculty;
    } elseif ($programType) {
        $parts[] = $programType;
    }

    if ($department) {
        $parts[] = $department;
    }

    if ($program) {
        $parts[] = $program;
    }

    if ($applicationType) {
        $parts[] = $applicationType;
    }

    $parts[] = 'Credentials';


    return collect($parts)
        ->map(fn ($part) => Str::slug($part))
        ->filter()
        ->implode('_') . '.zip';
}


public function downloadMultipleCredentials()
{
    /*
    |--------------------------------------------------------------------------
    | Get ALL candidates matching the current filters
    |--------------------------------------------------------------------------
    |
    | Do not use the current paginated page.
    | The ZIP must represent the complete filtered result.
    |
    */

    $candidates = $this->fetchCandidatesForCredentialZip();

    $candidates = collect($candidates)
        ->filter(fn ($candidate) =>
            !empty($candidate['credentials_pdf_url'])
        )
        ->values();

    if ($candidates->isEmpty()) {

        Flux::toast(
            'No credentials found for the current filter.',
            variant: 'warning',
            position: 'top-right',
            duration: 4000
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Meaningful ZIP filename
    |--------------------------------------------------------------------------
    */

     $zipFileName = $this->credentialsZipFilename();


    $tempZipPath =
        sys_get_temp_dir() .
        DIRECTORY_SEPARATOR .
        $zipFileName;


    /*
    |--------------------------------------------------------------------------
    | Create ZIP
    |--------------------------------------------------------------------------
    */

    $zip = new \ZipArchive;

    if (
        $zip->open(
            $tempZipPath,
            \ZipArchive::CREATE |
            \ZipArchive::OVERWRITE
        ) !== true
    ) {

        Flux::toast(
            'Failed to create ZIP file.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

        return;
    }



    $successCount = 0;

/*
|--------------------------------------------------------------------------
| Download credentials concurrently
|--------------------------------------------------------------------------
|
| Process a small batch at a time instead of downloading one file
| after another.
|
*/

$downloadBatchSize = 10;

foreach ($candidates->chunk($downloadBatchSize) as $batch) {

    $responses = Http::pool(function ($pool) use ($batch) {

        foreach ($batch as $candidate) {

            $pool
                ->as((string) $candidate['id'])
                ->timeout(30)
                ->get($candidate['credentials_pdf_url']);
        }

    });


    /*
    |--------------------------------------------------------------------------
    | Add downloaded files to ZIP
    |--------------------------------------------------------------------------
    */

    foreach ($batch as $candidate) {

        $candidateId = (string) $candidate['id'];

        $response = $responses[$candidateId] ?? null;


        if (!$response || !$response->successful()) {

            Log::warning(
                'Failed to download candidate credential.',
                [
                    'candidate_id' => $candidate['id'],
                    'status'       => $response?->status(),
                ]
            );

            continue;
        }


        $filename =
            Str::slug(
                $candidate['name'] ?? 'candidate'
            )
            . '_'
            . (
                $candidate['regno']
                ?? 'unknown'
            )
            . '.pdf';


        $zip->addFromString(
            $filename,
            $response->body()
        );


        $successCount++;
    }
}


    $zip->close();


    /*
    |--------------------------------------------------------------------------
    | Nothing downloaded
    |--------------------------------------------------------------------------
    */

    if ($successCount === 0) {

        if (file_exists($tempZipPath)) {
            unlink($tempZipPath);
        }

        Flux::toast(
            'Failed to download any candidate credentials.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare public download
    |--------------------------------------------------------------------------
    */

    $publicTempDir =
        storage_path('app/public/temp');


    if (!is_dir($publicTempDir)) {

        mkdir(
            $publicTempDir,
            0755,
            true
        );
    }


    $finalZipPath =
        $publicTempDir .
        DIRECTORY_SEPARATOR .
        $zipFileName;


    if (!copy(
        $tempZipPath,
        $finalZipPath
    )) {

        unlink($tempZipPath);

        Flux::toast(
            'Failed to prepare download file.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Cleanup
    |--------------------------------------------------------------------------
    */

    unlink($tempZipPath);


    /*
    |--------------------------------------------------------------------------
    | Log
    |--------------------------------------------------------------------------
    */

    Log::info(
        'Candidate credentials ZIP created.',
        [
            'matched_candidates' =>
                $candidates->count(),

            'credentials_downloaded' =>
                $successCount,

            'zip' =>
                $zipFileName,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Download
    |--------------------------------------------------------------------------
    */

    return redirect(
        url("storage/temp/{$zipFileName}")
    );
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


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.admissions.candidate-list'
        );
    }
}
