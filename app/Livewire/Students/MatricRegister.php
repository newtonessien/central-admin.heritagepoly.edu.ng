<?php

namespace App\Livewire\Students;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;

class MatricRegister extends Component
{
/* =========================================
| Selected Filters
* ========================================*/
public ?int $program_type_id = null;
public ?int $faculty_id      = null;
public ?int $department_id   = null;
public ?int $program_id      = null;

public ?int $acad_session_id = null;
public ?int $reg_year        = null;

public int $page = 1;
public int $perPage = 20;
public int $lastPage = 1;
public int $total = 0;

/* =========================================
| Lookup Data
* ========================================*/
public array $programTypes = [];
public array $faculties    = [];
public array $departments  = [];
public array $programs     = [];
public array $sessions     = [];

/* =========================================
| Results
* ========================================*/
public array $records = [];

public bool $loading = false;

/* =========================================
| Mount
* ========================================*/
public function mount(): void
{
try {

$svc = app(AdmissionsPortalClient::class);

$this->programTypes = $svc->getProgramTypes() ?? [];
$this->faculties    = $svc->getFaculties() ?? [];
$this->sessions     = $svc->getAcadSessions() ?? [];

} catch (\Throwable $e) {

Log::error('Matric register lookup preload failed', [
'error' => $e->getMessage()
]);
}
}

/* =========================================
| Cascading Dropdowns
* ========================================*/

public function updatedProgramTypeId(): void
{
$this->resetDownstreamFromFaculty();
}

public function updatedFacultyId(): void
{
$this->department_id = null;
$this->program_id = null;

$this->departments = [];
$this->programs = [];

if ($this->faculty_id) {

try {

$svc = app(AdmissionsPortalClient::class);

$this->departments = $svc->getDepartments(
(int)$this->faculty_id
) ?? [];

} catch (\Throwable $e) {

Log::error('Departments load failed', [
'error' => $e->getMessage()
]);
}
}
}

public function updatedDepartmentId(): void
{
$this->program_id = null;
$this->programs = [];

if ($this->department_id) {

try {

$svc = app(AdmissionsPortalClient::class);

$this->programs = $svc->getPrograms(
(int)$this->department_id,
$this->program_type_id
) ?? [];

} catch (\Throwable $e) {

Log::error('Programs load failed', [
'error' => $e->getMessage()
]);
}
}
}

/* =========================================
| Academic Session → Reg Year
* ========================================*/

public function updatedAcadSessionId(): void
{
if (!$this->acad_session_id) {
$this->reg_year = null;
return;
}

$session = collect($this->sessions)
->firstWhere('id', $this->acad_session_id);

if ($session && isset($session['name'])) {

// Example: 2025/2026 → 2025
$this->reg_year = (int) explode('/', $session['name'])[0];

}
}


protected function resetDownstreamFromFaculty(): void
{
$this->faculty_id = null;
$this->department_id = null;
$this->program_id = null;

$this->departments = [];
$this->programs = [];

$this->records = [];
}

/* =========================================
| Generate Register
* ========================================*/
public function generateRegister(): void
{
    $this->validate([
        'program_type_id' => 'required',
        'acad_session_id' => 'required'
    ]);

    $this->loading = true;

    try {

        $response = app(StudentPortalClient::class)
            ->getMatriculationRegister([
                'reg_year'        => $this->reg_year,
                'program_type_id' => $this->program_type_id,
                'faculty_id'      => $this->faculty_id,
                'department_id'   => $this->department_id,
                'program_id'      => $this->program_id,
            ]);

        // $data = $response['data'] ?? [];
        // /*
        // Flatten nested API structure
        // */
        // $this->records = collect($data)
        //     ->flatten(3)
        //     ->values()
        //     ->toArray();

$data = $response['data'] ?? [];
$students = collect($data)
    ->flatten(3)
    ->values();
$this->total = $students->count();
$this->lastPage = (int) ceil($this->total / $this->perPage);
$this->records = $students
    ->slice(($this->page - 1) * $this->perPage, $this->perPage)
    ->values()
    ->toArray();

    } catch (\Throwable $e) {

        Log::error('Matric register load failed', [
            'error' => $e->getMessage()
        ]);

        $this->records = [];

    } finally {

        $this->loading = false;
    }
}


public function gotoPage(int $page): void
{
    if ($page < 1 || $page > $this->lastPage) {
        return;
    }

    $this->page = $page;

    $this->generateRegister();
}

public function nextPage(): void
{
    if ($this->page < $this->lastPage) {
        $this->page++;
        $this->generateRegister();
    }
}

public function prevPage(): void
{
    if ($this->page > 1) {
        $this->page--;
        $this->generateRegister();
    }
}

public function render()
{
return view('livewire.students.matric-register');
}
}
