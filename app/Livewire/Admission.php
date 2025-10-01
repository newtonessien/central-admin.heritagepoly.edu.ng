<?php
namespace App\Livewire;

use Flux\Flux;
use Livewire\Component;
use App\Services\Clients\AdmissionsPortalClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use App\Services\Clients\StudentPortalClient;

class Admission extends Component
{
    public string $localSearch = '';
    // Selected filters
    public ?int $program_type_id = null;
    public ?int $faculty_id      = null;
    public ?int $department_id   = null;
    public ?int $program_id      = null;
    public string $status        = 'pending'; // 'pending' | 'approved' | ''

    // Option lists
    public array $programTypes = [];
    public array $faculties    = [];
    public array $departments  = [];
    public array $programs     = [];

    // Results (can be paginator or [])
    public array $candidates = [];

    // Loading flags
    public bool $loadingDepartments = false;
    public bool $loadingPrograms    = false;
    public bool $loadingCandidates  = false;

    // Row action loading trackers
    public array $approving = [];
    public array $revoking  = [];

    // Pagination state
    public int $page = 1;
    public int $perPage = 25;
    public int $lastPage = 1;
    public int $total = 0;

    // Bulk selection (persist across pages for current filter set)
    public array $selected = [];
    public bool  $selectAll = false;
    public bool  $bulkWorking = false;

    // Track filter signature to clear selections when filters change
    protected string $lastFilterKey = '';

    public function mount(AdmissionsPortalClient $svc): void
    {
        try {
            $this->programTypes = method_exists($svc, 'getProgramTypes') ? $svc->getProgramTypes() : [];
            $this->faculties    = $svc->getFaculties();
        } catch (\Throwable $e) {
            Log::error('Lookup preload failed', ['error' => $e->getMessage()]);
            $this->programTypes = $this->programTypes ?: [];
            $this->faculties    = $this->faculties ?: [];
        }

        $this->candidates = [];
    }

    /* -----------------------------------
     | Change handlers (cascade + gating)
     * ----------------------------------*/

    public function updatedProgramTypeId(): void
    {
        $this->page = 1;
        $this->resetSelection();

        $this->program_id = null;
        $this->refreshProgramsIfDeptChosen();
        $this->maybeFetchCandidates();
    }

    public function updatedFacultyId(): void
    {
        $this->page = 1;
        $this->resetSelection();

        $this->department_id = null;
        $this->program_id    = null;
        $this->departments   = [];
        $this->programs      = [];

        if ($this->faculty_id) {
            $this->loadingDepartments = true;
            try {
                $svc = app(AdmissionsPortalClient::class);
                $this->departments = $svc->getDepartments((int) $this->faculty_id);
            } catch (\Throwable $e) {
                Log::error('Departments load failed', ['error' => $e->getMessage()]);
                $this->departments = [];
                Flux::toast('Failed to load departments', variant: 'error', position: 'top-right', duration: 4000);
            } finally {
                $this->loadingDepartments = false;
            }
        }

        $this->maybeFetchCandidates();
    }

    public function updatedDepartmentId(): void
    {
        $this->page = 1;
        $this->resetSelection();

        $this->program_id = null;
        $this->programs   = [];

        $this->refreshProgramsIfDeptChosen();
        $this->maybeFetchCandidates();
    }

    public function updatedProgramId(): void
    {
        $this->page = 1;
        $this->resetSelection();

        $this->maybeFetchCandidates();
    }

    public function updatedStatus(): void
    {
        $this->page = 1;
        $this->resetSelection();

        $this->maybeFetchCandidates();
    }

    /* -----------------------
     | Helpers / core actions
     * ----------------------*/

    protected function isSelectionComplete(): bool
    {
        return (bool) ($this->program_type_id && $this->faculty_id && $this->department_id && $this->program_id);
    }

    protected function maybeFetchCandidates(): void
    {
        if ($this->isSelectionComplete()) {
            $this->filterCandidates();
        } else {
            $this->candidates = [];
            $this->total = 0;
            $this->lastPage = 1;
            $this->page = 1;
            $this->resetSelection();
        }
    }

    protected function refreshProgramsIfDeptChosen(): void
    {
        if (!$this->department_id) {
            $this->programs = [];
            return;
        }

        $this->loadingPrograms = true;

        try {
            $svc = app(AdmissionsPortalClient::class);

            // Support both signatures: getPrograms($dept) or getPrograms($dept, $programType)
            $method = new \ReflectionMethod($svc, 'getPrograms');
            if ($method->getNumberOfParameters() >= 2) {
                $this->programs = $svc->getPrograms(
                    (int) $this->department_id,
                    $this->program_type_id ? (int) $this->program_type_id : null
                );
            } else {
                $this->programs = $svc->getPrograms((int) $this->department_id);
            }
        } catch (\Throwable $e) {
            Log::error('Programs load failed', ['error' => $e->getMessage()]);
            $this->programs = [];
            Flux::toast('Failed to load programs', variant: 'error', position: 'top-right', duration: 4000);
        } finally {
            $this->loadingPrograms = false;
        }
    }

    // Core action: fetch candidates with current filters & pagination
public function filterCandidates(): void
{
    // ✅ Only Program Type is mandatory
    if (empty($this->program_type_id)) {
        $this->candidates = [];
        $this->total = 0;
        $this->lastPage = 1;
        $this->page = 1;
        $this->selected = [];
        $this->selectAll = false;
        return;
    }

    $this->loadingCandidates = true;

    // Build filters — only include non-empty values
    $filters = array_filter([
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'status'          => $this->status,
        'page'            => $this->page,
        'per_page'        => $this->perPage,
    ], fn ($v) => $v !== null && $v !== '');

    try {
        $svc = app(AdmissionsPortalClient::class);

        $response = method_exists($svc, 'getCandidatesFiltered')
            ? $svc->getCandidatesFiltered($filters)
            : $svc->getCandidates($filters);

        $data = $response['data'] ?? [];
        $meta = $response['meta'] ?? [];

        // ✅ rows for current page
        $this->candidates = is_array($data) ? $data : [];

        // ✅ pagination
        $this->page     = (int)($meta['current_page'] ?? $this->page);
        $this->perPage  = (int)($meta['per_page'] ?? $this->perPage);
        $this->lastPage = (int)($meta['last_page'] ?? 1);
        $this->total    = (int)($meta['total'] ?? count($this->candidates));

        // ✅ recompute select-all for visible rows
        $pageIds = collect($this->candidates)->pluck('id')->filter()->map(fn($id) => (int)$id)->all();
        $this->selectAll = !empty($pageIds) && empty(array_diff($pageIds, $this->selected));

        // ✅ reset selection if filters changed (not just page)
        $currentKey = $this->currentFilterKey();
        if ($currentKey !== $this->lastFilterKey) {
            $this->selected = [];
            $this->selectAll = false;
            $this->lastFilterKey = $currentKey;
        }
    } catch (\Throwable $e) {
        Log::error('Candidates load failed', ['error' => $e->getMessage()]);
        $this->candidates = [];
        $this->total = 0;
        $this->lastPage = 1;
        Flux::toast('Failed to load candidates', variant: 'error', position: 'top-right', duration: 4000);
    } finally {
        $this->loadingCandidates = false;
    }
}



    public function resetFilters(): void
    {
        $this->reset(['program_type_id', 'faculty_id', 'department_id', 'program_id']);
        $this->departments = [];
        $this->programs    = [];

        // Reset pagination & selections
        $this->page = 1;
        $this->lastPage = 1;
        $this->total = 0;
        $this->resetSelection();

        $this->candidates  = [];
    }

    /* -----------------------
     | Pagination actions
     * ----------------------*/
    public function gotoPage(int $p): void
    {
        if ($p < 1 || ($this->lastPage > 0 && $p > $this->lastPage)) return;
        $this->page = $p;
        $this->filterCandidates();
    }

    public function nextPage(): void
    {
        if ($this->page < $this->lastPage) {
            $this->page++;
            $this->filterCandidates();
        }
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->filterCandidates();
        }
    }

    /* -----------------------
     | Row actions: approve / revoke
     * ----------------------*/
     public function approve(int $id): void
{
        if ($id <= 0) {
        Flux::Toast('Invalid candidate ID.', variant: 'error');
        return;
    }

    if (!empty($this->approving[$id])) return;
    $this->approving[$id] = true;

    try {
        // 1) Approve on Admissions API (existing behavior)
        //    Make sure approveCandidate() throws on failure or returns truthy.
        //app(AdmissionsPortalClient::class)->approveCandidate($id);
         //app(AdmissionsPortalClient::class)->approveCandidateOrFail($id);

         app(AdmissionsPortalClient::class)->approveAndMigratePhase1($id);
        // 2) Optimistic UI (your existing code)
        $items = $this->candidates instanceof LengthAwarePaginator ? $this->candidates->items() : $this->candidates;
        foreach ($items as &$row) {
            if (($row['id'] ?? null) === $id) {
                $row['is_admitted'] = 1;
                $row['status']      = 'approved';
            }
        }
        if ($this->candidates instanceof LengthAwarePaginator) {
            $this->candidates = $items;
        }

        if ($this->status === 'pending') {
            $filtered = array_values(array_filter($items, fn ($r) => ($r['id'] ?? null) !== $id));
            $this->candidates = $filtered;
            $this->total = count($filtered);
        }

        $this->selected = array_values(array_diff($this->selected, [$id]));

        // 3) Fetch full migration payload from Admissions and push to Student Portal
        try {
            // Pull the *full* fields needed for migration (user + candidate)
            $cand = app(AdmissionsPortalClient::class)->candidateForMigration($id);
            // Build the payload Student Portal expects
            $payload = $this->buildStudentMigrationPayload($cand);

            // Send to Student Portal
            $client = new StudentPortalClient();
            $res = $client->migrateBasic($payload);

            // Optional: store migrated_at / student_portal_id if you track them
            // app(AdmissionsPortalClient::class)->markMigrated($id, $res['student_id'] ?? null);

            Flux::toast('Approved & migrated to Student Portal.', variant: 'success', position: 'top-right', duration: 4000);
        } catch (\Throwable $m) {
            Log::warning('Migration failed', ['candidate_id' => $id, 'error' => $m->getMessage()]);
            Flux::toast('Approved (migration deferred).', variant: 'warning', position: 'top-right', duration: 5000);
        }

    } catch (\Throwable $e) {
        Log::error('Approve failed', ['id' => $id, 'error' => $e->getMessage()]);
        Flux::toast('Approval failed. Try again.', variant: 'error', position: 'top-right', duration: 4000);
    } finally {
        unset($this->approving[$id]);
    }
}

/**
 * Map the Admissions API "candidate for migration" shape
 * into the Student Portal expected payload.
 *
 * Expected $cand shape (example):
 * [
 *   'id'=>.., 'regno'=>.., 'jamb_score'=>.., 'jamb_no'=>..,
 *   'program_id'=>.., 'program_type_id'=>.., 'faculty_id'=>.., 'department_id'=>..,
 *   'acad_session_id'=>.., 'is_active'=>..,
 *   'user'=>[
 *     'username'=>..,'email'=>..,'first_name'=>..,'last_name'=>..,'other_names'=>..,
 *     'phone_no'=>..,'gender'=>..,'is_role'=>..,'email_verified_at'=>..,'password'=>..,'is_active'=>..
 *   ]
 * ]
 */
private function buildStudentMigrationPayload(array $cand): array
{
    $u = $cand['user'] ?? [];

    return [
        // Users table (Student Portal)
        'username'           => (string) ($u['username'] ?? $cand['regno'] ?? ''),
        'email'              => (string) ($u['email'] ?? ''),
        'first_name'         => (string) ($u['first_name'] ?? ''),
        'last_name'          => (string) ($u['last_name'] ?? ''),
        'other_names'        => $u['other_names'] ?? null,
        'phone_no'           => $u['phone_no'] ?? null,
        'gender'             => (string) ($u['gender'] ?? 'M'),
        'is_role'            => (int)    ($u['is_role'] ?? 0),
        'email_verified_at'  => $u['email_verified_at'] ?? null, // ISO8601 string is fine
        'password'           => (string) ($u['password'] ?? ''), // send HASH if you have it
        'is_active'          => (bool)   ($u['is_active'] ?? false),
        'program_type_id'    => (int)    ($cand['program_type_id'] ?? 0), // also stored on users

        // Students table (Student Portal)
        'regno'              => (string) ($cand['regno'] ?? ''),
        'jamb_score'         => $cand['jamb_score'] ?? null,
        'jamb_no'            => $cand['jamb_no'] ?? null,
        'program_id'         => (int) ($cand['program_id'] ?? 0),
        'faculty_id'         => (int) ($cand['faculty_id'] ?? 0),
        'department_id'      => (int) ($cand['department_id'] ?? 0),
        'acad_session_id'    => (int) (($cand['acad_session_id'] ?? 1) ?: 1), // default to 1 if null/0
        'start_session_id'    => (int) (($cand['acad_session_id'] ?? 1) ?: 1), // default to 1 if null/0
        'is_active_student'  => (bool) ($cand['is_active'] ?? false),
    ];
}

public function revoke(int $id): void
{
    if (!empty($this->revoking[$id])) return;
    $this->revoking[$id] = true;

    try {
        // 🔥 revoke in Admissions + delete in Students
        $result = app(AdmissionsPortalClient::class)->revokeAndDeletePhase1($id);

        if (($result['status'] ?? null) !== 'success') {
            throw new \RuntimeException($result['error'] ?? 'Unknown revoke error');
        }

        // ✅ Optimistic UI (current page only)
        $items = $this->candidates instanceof \Illuminate\Pagination\LengthAwarePaginator
            ? $this->candidates->items()
            : $this->candidates;

        foreach ($items as &$row) {
            if (($row['id'] ?? null) === $id) {
                $row['is_admitted'] = 0;
                $row['status']      = 'pending';
            }
        }
        $this->candidates = $items;

        // If viewing approved list, drop the revoked row from current page
        if ($this->status === 'approved') {
            $filtered = array_values(array_filter($items, fn ($r) => ($r['id'] ?? null) !== $id));
            $this->candidates = $filtered;
            $this->total = max(0, $this->total - 1);
        }

        // Remove from persisted selection
        $this->selected = array_values(array_diff($this->selected, [$id]));

        Flux::toast(
            'Admission revoked and deleted from Students Portal.',
            variant: 'success',
            position: 'top-right',
            duration: 4000
        );
    } catch (\Throwable $e) {
        Log::error('Revoke failed', ['id' => $id, 'error' => $e->getMessage()]);
        Flux::toast('Revoke failed. Try again.', variant: 'error', position: 'top-right', duration: 4000);
    } finally {
        unset($this->revoking[$id]);
    }
}

    /* -----------------------
     | Bulk selection helpers
     * ----------------------*/

public function updatedSelectAll($value): void
{
    // Use filtered rows if the computed prop exists; otherwise fall back to raw page rows
    $rows = method_exists($this, 'getFilteredCandidatesProperty')
        ? $this->filteredCandidates
        : (is_array($this->candidates) ? $this->candidates : []);

    $pageIds = collect($rows)
        ->pluck('id')
        ->filter(fn ($id) => !is_null($id))
        ->map(fn ($id) => (int) $id)
        ->values()
        ->all();

    if ($value) {
        $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
    } else {
        $this->selected = array_values(array_diff($this->selected, $pageIds));
    }
}


    protected function resetSelection(): void
    {
        $this->selectAll = false;
        $this->selected  = [];
    }

    protected function currentFilterKey(): string
    {
        return sha1(json_encode([
            'program_type_id' => (int) ($this->program_type_id ?? 0),
            'faculty_id'      => (int) ($this->faculty_id ?? 0),
            'department_id'   => (int) ($this->department_id ?? 0),
            'program_id'      => (int) ($this->program_id ?? 0),
            'status'          => (string) $this->status,
        ]));
    }

    // Bulk revoke
public function bulkRevoke(): void
{
    $ids = $this->validatedSelectedIds();
    if (empty($ids)) return;

    $this->bulkWorking = true;
    $svc = app(AdmissionsPortalClient::class);

    $ok = [];
    $fail = [];

    foreach ($ids as $id) {
        try {
            // 🔥 This version deletes from Students Portal too
            $svc->revokeAndDeletePhase1($id);
            $ok[] = $id;
        } catch (\Throwable $e) {
            Log::error('Bulk revoke failed', [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]);
            $fail[] = $id;
        }
    }

    // Optimistic update for only current page rows
    $items = $this->candidates instanceof \Illuminate\Pagination\LengthAwarePaginator
        ? $this->candidates->items()
        : $this->candidates;

    foreach ($items as &$row) {
        if (in_array(($row['id'] ?? null), $ok, true)) {
            $row['is_admitted'] = 0;
            $row['status']      = 'pending';
        }
    }

    if ($this->status === 'approved') {
        // If user is currently viewing only approved, remove them from list
        $items = array_values(array_filter($items, fn ($r) => !in_array(($r['id'] ?? null), $ok, true)));
        $this->total = max(0, $this->total - count($ok));
    }

    if ($this->candidates instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $this->candidates = $items;
    } else {
        $this->candidates = $items;
    }

    // Clear processed IDs from selection
    $this->selected   = array_values(array_diff($this->selected, $ok));
    $this->selectAll  = false;
    $this->bulkWorking = false;

    Flux::toast(
        empty($fail)
            ? 'Selected candidates revoked and deleted from Students Portal.'
            : ('Revoked '.count($ok).' — '.count($fail).' failed.'),
        variant: empty($fail) ? 'success' : 'warning',
        position: 'top-right',
        duration: 4000
    );
}
//bukl approve
public function bulkApprove(): void
{
    $ids = $this->validatedSelectedIds();
    if (empty($ids)) return;

    $this->bulkWorking = true;
    $svc = app(AdmissionsPortalClient::class);

    $ok = [];
    $fail = [];

    foreach ($ids as $id) {
        try {
            $result = $svc->approveAndMigratePhase1($id);
            if (($result['status'] ?? null) === 'success') {
                $ok[] = $id;
            } else {
                $fail[] = $id;
            }
        } catch (\Throwable $e) {
            Log::error('Bulk approve+migrate failed', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);
            $fail[] = $id;
        }
    }

    // ✅ Optimistic UI update
    $items = $this->candidates instanceof \Illuminate\Pagination\LengthAwarePaginator
        ? $this->candidates->items()
        : $this->candidates;

    foreach ($items as &$row) {
        if (in_array(($row['id'] ?? null), $ok, true)) {
            $row['is_admitted'] = 1;
            $row['status']      = 'approved';
        }
    }

    if ($this->status === 'pending') {
        $items = array_values(array_filter($items, fn ($r) => !in_array(($r['id'] ?? null), $ok, true)));
        $this->total = max(0, $this->total - count($ok));
    }

    $this->candidates = $items;
    $this->selected   = array_values(array_diff($this->selected, $ok));
    $this->selectAll  = false;
    $this->bulkWorking = false;

    Flux::toast(
        empty($fail)
            ? 'Selected candidates approved & migrated.'
            : ('Migrated '.count($ok).' — '.count($fail).' failed.'),
        variant: empty($fail) ? 'success' : 'warning',
        position: 'top-right',
        duration: 4000
    );
}





    protected function validatedSelectedIds(): array
    {
        // Persisted selection across pages: trust $selected (ints), unique
        return array_values(array_unique(array_map('intval', $this->selected)));
    }

    protected function makePaginator(array $items, array $meta): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            items: $items,
            total: (int)($meta['total'] ?? count($items)),
            perPage: (int)($meta['per_page'] ?? $this->perPage ?? 25),
            currentPage: (int)($meta['current_page'] ?? $this->page ?? 1),
            options: [
                'path'     => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
    }


  public function getFilteredCandidatesProperty(): array
    {
        if (empty($this->candidates)) {
            return [];
        }

        $q = trim(mb_strtolower((string) $this->localSearch));
        if ($q === '') {
            return $this->candidates;
        }

        return array_values(array_filter($this->candidates, function ($row) use ($q) {
            // new flat fields (preferred)
            $regno = mb_strtolower((string)($row['regno'] ?? ''));
            $name  = mb_strtolower((string)($row['name']  ?? ''));

            // backward-compat with old payloads
            $first = mb_strtolower((string)($row['user']['first_name'] ?? ''));
            $last  = mb_strtolower((string)($row['user']['last_name']  ?? ''));
            $other = mb_strtolower((string)($row['user']['other_names'] ?? ''));

            $full1 = trim($last.' '.$first.' '.$other);
            $full2 = trim($first.' '.$last.' '.$other);

            return str_contains($regno, $q)
                || str_contains($name, $q)
                || str_contains($full1, $q)
                || str_contains($full2, $q);
        }));
    }




public function updatedLocalSearch(): void
{
    $this->selectAll = false;
      $this->selected  = [];
}




    public function render()
    {
        return view('livewire.admission');
    }
}
