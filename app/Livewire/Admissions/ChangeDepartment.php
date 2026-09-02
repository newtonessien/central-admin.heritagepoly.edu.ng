<?php

namespace App\Livewire\Admissions;

use App\Services\AdmissionAuditService;
use App\Services\Clients\AdmissionsPortalClient;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class ChangeDepartment extends Component
{
/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

public string $regno = '';

/*
|--------------------------------------------------------------------------
| Candidate
|--------------------------------------------------------------------------
*/

public ?array $candidate = null;

/*
|--------------------------------------------------------------------------
| New Academic Placement
|--------------------------------------------------------------------------
*/

public ?int $facultyId = null;

public ?int $departmentId = null;

public ?int $programId = null;

public ?int $program_type_id = null;

/*
|--------------------------------------------------------------------------
| Lookup Data
|--------------------------------------------------------------------------
*/

public array $faculties = [];

public array $departments = [];

public array $programs = [];

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

public bool $loadingCandidate = false;

public bool $processing = false;

public string $errorMessage = '';


public ?string $currentFacultyName = null;

public ?string $currentDepartmentName = null;

public ?string $currentProgramName = null;

public ?string $currentProgramTypeName = null;

public ?string $currentSessionName = null;



/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

public function mount(): void
{
$this->loadFaculties();
}

/*
|--------------------------------------------------------------------------
| Load Candidate
|--------------------------------------------------------------------------
*/

public function findCandidate(): void
{
$this->resetError();

$this->validate([
'regno' => [
'required',
'string',
'max:255',
],
]);

$this->loadingCandidate = true;

try {

$client = app(AdmissionsPortalClient::class);

$result = $client->candidateByRegno(
trim($this->regno),
'details'
);

if (($result['status'] ?? null) !== 'success') {

$this->errorMessage =
$result['error']
?? 'Candidate could not be found.';

$this->candidate = null;

return;
}

$this->candidate =
$result['data']['data']
?? $result['data']
?? null;


if (!$this->candidate) {

$this->errorMessage =
'Candidate could not be found.';

return;
}

$this->program_type_id =
isset($this->candidate['program_type_id'])
? (int) $this->candidate['program_type_id']
: null;

$this->resolveCurrentPlacement();

/*
|--------------------------------------------------------------------------
| Set current placement
|--------------------------------------------------------------------------
|
| These are displayed as the candidate's CURRENT placement.
| We do not automatically select them as the new values.
|
*/

$this->facultyId = null;
$this->departmentId = null;
$this->programId = null;

/*
|--------------------------------------------------------------------------
| Load departments/programmes only when faculty is selected.
|--------------------------------------------------------------------------
*/

$this->departments = [];
$this->programs = [];

} catch (\Throwable $e) {

Log::error(
'Change Department → candidate lookup failed',
[
'regno' => $this->regno,
'error' => $e->getMessage(),
]
);

$this->errorMessage =
'Unable to retrieve candidate details.';
} finally {

$this->loadingCandidate = false;
}
}

/*
|--------------------------------------------------------------------------
| Faculty Changed
|--------------------------------------------------------------------------
*/

public function updatedFacultyId($value): void
{
$this->departmentId = null;
$this->programId = null;

$this->departments = [];
$this->programs = [];

if (!$value) {
return;
}

$this->loadDepartments((int) $value);
}

/*
|--------------------------------------------------------------------------
| Department Changed
|--------------------------------------------------------------------------
*/

public function updatedDepartmentId($value): void
{
$this->programId = null;
$this->programs = [];

if (!$value) {
return;
}

$this->loadPrograms(
(int) $value
);
}

/*
|--------------------------------------------------------------------------
| Load Faculties
|--------------------------------------------------------------------------
*/

protected function loadFaculties(): void
{
    try {

        $client = app(AdmissionsPortalClient::class);

        $this->faculties = $client->getFaculties();

    } catch (\Throwable $e) {

        Log::error(
            'Change Department → faculties failed',
            [
                'error' => $e->getMessage(),
            ]
        );

        $this->faculties = [];
    }
}

/*
|--------------------------------------------------------------------------
| Load Departments
|--------------------------------------------------------------------------
*/

protected function loadDepartments(int $facultyId): void
{
    try {

        $client = app(AdmissionsPortalClient::class);

        $this->departments =
            $client->getDepartments($facultyId);

    } catch (\Throwable $e) {

        Log::error(
            'Change Department → departments failed',
            [
                'faculty_id' => $facultyId,
                'error'      => $e->getMessage(),
            ]
        );

        $this->departments = [];
    }
}

/*
|--------------------------------------------------------------------------
| Load Programs
|--------------------------------------------------------------------------
*/

protected function loadPrograms(int $departmentId): void
{
    try {

        $client = app(AdmissionsPortalClient::class);

        $this->programs = $client->getPrograms(
            $departmentId,
            $this->program_type_id
                ? (int) $this->program_type_id
                : null
        );

    } catch (\Throwable $e) {

        Log::error(
            'Change Department → programmes failed',
            [
                'department_id'  => $departmentId,
                'program_type_id' => $this->program_type_id,
                'error'           => $e->getMessage(),
            ]
        );

        $this->programs = [];
    }
}

/*
|--------------------------------------------------------------------------
| Confirm
|--------------------------------------------------------------------------
*/

public function confirmChange(): void
{
$this->resetError();

$this->validate([
'facultyId' => [
'required',
'integer',
'min:1',
],

'departmentId' => [
'required',
'integer',
'min:1',
],

'programId' => [
'required',
'integer',
'min:1',
],
]);

if (!$this->candidate) {

$this->errorMessage =
'Please find a candidate first.';

return;
}

Flux::modal('change-department-confirmation')
->show();
}

/*
|--------------------------------------------------------------------------
| Commit Change
|--------------------------------------------------------------------------
*/


public function changeDepartment(): void
{
$this->resetError();

$this->validate([
'facultyId' => [
'required',
'integer',
'min:1',
],

'departmentId' => [
'required',
'integer',
'min:1',
],

'programId' => [
'required',
'integer',
'min:1',
],
]);

if (!$this->candidate) {

$this->errorMessage =
'Please find a candidate first.';

return;
}

   /*
    |--------------------------------------------------------------------------
    | Capture current placement BEFORE changing it
    |--------------------------------------------------------------------------
    */

    $previousPlacement = [
        'faculty_id' =>
            $this->candidate['faculty_id'] ?? null,

        'department_id' =>
            $this->candidate['department_id'] ?? null,

        'program_id' =>
            $this->candidate['program_id'] ?? null,
    ];

$this->processing = true;

try {


$newPlacement = [
    'faculty_id' => (int) $this->facultyId,
    'department_id' => (int) $this->departmentId,
    'program_id' => (int) $this->programId,
];

$client = app(
AdmissionsPortalClient::class
);

$result = $client->changeDepartment(
candidateId: $this->candidate['id'],
facultyId: $this->facultyId,
departmentId: $this->departmentId,
programId: $this->programId,
);

if (($result['status'] ?? null) !== 'success') {

$message =
$result['error']
?? 'Unable to change candidate programme.';

throw new \RuntimeException(
is_array($message)
? json_encode($message)
: $message
);
}

/*
|--------------------------------------------------------------------------
| Close confirmation modal
|--------------------------------------------------------------------------
*/

Flux::modal(
'change-department-confirmation'
)->close();

/*
|--------------------------------------------------------------------------
| Replace candidate with API response
|--------------------------------------------------------------------------
*/

$updated =
$result['data']['data']
?? null;

if ($updated) {

$this->candidate = array_merge(
$this->candidate,
$updated
);
}

/*
|--------------------------------------------------------------------------
| Clear new selection
|--------------------------------------------------------------------------
*/

$this->facultyId = null;
$this->departmentId = null;
$this->programId = null;

$this->departments = [];
$this->programs = [];


/*
|--------------------------------------------------------------------------
| Update candidate IDs from response
|--------------------------------------------------------------------------
*/

if ($updated) {

    $this->candidate = array_merge(
        $this->candidate,
        $updated
    );
}

/*
|--------------------------------------------------------------------------
| Re-resolve current placement
|--------------------------------------------------------------------------
*/

$this->resolveCurrentPlacement();


/*
|--------------------------------------------------------------------------
| Record Admission Audit
|--------------------------------------------------------------------------
|
| Audit only after the Admissions Portal has successfully
| committed the department/programme change.
|
*/

app(AdmissionAuditService::class)->record(
    candidate: $this->candidate,
    action: 'department_changed',
    decision: 'Lack Requisites',
    reason: 'For audit trail purposes',
    metadata: [
        'previous' => $previousPlacement,
        'new' => $newPlacement,
    ],
);

Flux::toast(
'Candidate programme changed successfully.',
variant: 'success',
position: 'top-right',
duration: 5000
);

} catch (\Throwable $e) {

Log::error(
'Change Department → commit failed',
[
'candidate_id' =>
$this->candidate['id']
?? null,

'regno' =>
$this->candidate['regno']
?? $this->regno,

'error' =>
$e->getMessage(),
]
);

$this->errorMessage =
$e->getMessage();

} finally {

$this->processing = false;
}
}

/*
|--------------------------------------------------------------------------
| Reset
|--------------------------------------------------------------------------
*/

public function resetCandidate(): void
{
if ($this->processing) {
return;
}

$this->candidate = null;

$this->facultyId = null;
$this->departmentId = null;
$this->programId = null;

$this->departments = [];
$this->programs = [];

$this->errorMessage = '';
}

protected function resetError(): void
{
$this->errorMessage = '';
}


protected function resolveCurrentPlacement(): void
{
    /*
    |--------------------------------------------------------------------------
    | Reset current display values
    |--------------------------------------------------------------------------
    */

    $this->currentProgramTypeName = null;
    $this->currentSessionName = null;
    $this->currentFacultyName = null;
    $this->currentDepartmentName = null;
    $this->currentProgramName = null;


    /*
    |--------------------------------------------------------------------------
    | IDs
    |--------------------------------------------------------------------------
    */

    $programTypeId =
        $this->candidate['program_type_id'] ?? null;

    $applicationTypeId =
        $this->candidate['application_type_id'] ?? null;

    $sessionId =
        $this->candidate['acad_session_id'] ?? null;

    $facultyId =
        $this->candidate['faculty_id'] ?? null;

    $departmentId =
        $this->candidate['department_id'] ?? null;

    $programId =
        $this->candidate['program_id'] ?? null;


    $client = app(
        AdmissionsPortalClient::class
    );


    /*
    |--------------------------------------------------------------------------
    | Program Type
    |--------------------------------------------------------------------------
    */

    if ($programTypeId) {

        try {

            $programTypes =
                $client->getProgramTypes();

            $programType = collect($programTypes)
                ->firstWhere(
                    'id',
                    (int) $programTypeId
                );

            $this->currentProgramTypeName =
                $programType['name'] ?? null;

        } catch (\Throwable $e) {

            Log::warning(
                'Change Department → program type lookup failed',
                [
                    'program_type_id' => $programTypeId,
                    'error'           => $e->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Academic Session
    |--------------------------------------------------------------------------
    */

    if ($sessionId) {

        try {

            $sessions =
                $client->getAcadSessions();

            $session = collect($sessions)
                ->firstWhere(
                    'id',
                    (int) $sessionId
                );

            $this->currentSessionName =
                $session['name'] ?? null;

        } catch (\Throwable $e) {

            Log::warning(
                'Change Department → academic session lookup failed',
                [
                    'acad_session_id' => $sessionId,
                    'error'           => $e->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Faculty
    |--------------------------------------------------------------------------
    */

    if ($facultyId) {

        $faculty = collect($this->faculties)
            ->firstWhere(
                'id',
                (int) $facultyId
            );

        $this->currentFacultyName =
            $faculty['name'] ?? null;
    }


    /*
    |--------------------------------------------------------------------------
    | Department
    |--------------------------------------------------------------------------
    */

    if ($facultyId && $departmentId) {

        try {

            $departments =
                $client->getDepartments(
                    (int) $facultyId
                );

            $department = collect($departments)
                ->firstWhere(
                    'id',
                    (int) $departmentId
                );

            $this->currentDepartmentName =
                $department['name'] ?? null;

        } catch (\Throwable $e) {

            Log::warning(
                'Change Department → current department lookup failed',
                [
                    'faculty_id'    => $facultyId,
                    'department_id' => $departmentId,
                    'error'         => $e->getMessage(),
                ]
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Programme
    |--------------------------------------------------------------------------
    */

    if ($departmentId && $programId && $programTypeId) {

        try {

            $programs =
                $client->getPrograms(
                    (int) $departmentId,
                    (int) $programTypeId
                );

            $program = collect($programs)
                ->firstWhere(
                    'id',
                    (int) $programId
                );

            $this->currentProgramName =
                $program['name'] ?? null;

        } catch (\Throwable $e) {

            Log::warning(
                'Change Department → current programme lookup failed',
                [
                    'department_id'   => $departmentId,
                    'program_type_id' => $programTypeId,
                    'program_id'      => $programId,
                    'error'           => $e->getMessage(),
                ]
            );
        }
    }
}


protected function candidateName(): string
{
    $user = $this->candidate['user'] ?? [];

    return trim(implode(' ', array_filter([
        $user['last_name'] ?? null,
        $user['first_name'] ?? null,
        $user['other_names'] ?? null,
    ])));
}

/*
|--------------------------------------------------------------------------
| Render
|--------------------------------------------------------------------------
*/

public function render()
{
return view(
'livewire.admissions.change-department'
);
}
}
