<?php

namespace App\Livewire\Admissions\EnrolledStudents;

use App\Services\AdmissionAuditService;
use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Flux\Flux;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class Enrollment extends Component
{
use WithPagination;


/*
|--------------------------------------------------------------------------
| Search / List
|--------------------------------------------------------------------------
*/

public string $search = '';

public int $perPage = 10;


/*
|--------------------------------------------------------------------------
| Screening
|--------------------------------------------------------------------------
*/

public string $regno = '';

public bool $screening = false;

public ?array $screenedCandidate = null;

public ?array $existingEnrollment = null;

public ?string $screeningError = null;

public bool $eligibleForEnrollment = false;


/*
|--------------------------------------------------------------------------
| Enrollment Form
|--------------------------------------------------------------------------
*/

public bool $showEnrollmentForm = false;

public array $form = [
'regno'            => '',
'first_name'       => '',
'last_name'        => '',
'other_names'      => '',
'phone_no'         => '',
'gender'           => '',
'jamb_no'          => null,
'jamb_score'       => null,
'program_type_id'  => null,
'school_division_id'=> null,
'faculty_id'       => null,
'department_id'    => null,
'program_id'       => null,
'acad_session_id'  => null,
'entry_mode_id'    => null,
'study_mode_id'    => null,

];

public array $editForm = [
    'program_type_id'    => null,
    'school_division_id' => null,
    'acad_session_id'    => null,
    'faculty_id'         => null,
    'department_id'      => null,
    'program_id'         => null,
    'entry_mode_id'      => null,
    'study_mode_id'      => null,
    'jamb_no'            => null,
    'jamb_score'         => null,
];


/*
|--------------------------------------------------------------------------
| Confirmation
|--------------------------------------------------------------------------
*/

public bool $showConfirmation = false;

public bool $processing = false;


/*
|--------------------------------------------------------------------------
| Lookups
|--------------------------------------------------------------------------
*/

public array $programTypes = [];

public array $faculties = [];

public array $departments = [];

public array $programs = [];

public array $academicSessions = [];

public array $entryModes = [];


protected $queryString = [
'search',
];


/*
|--------------------------------------------------------------------------
| View
|--------------------------------------------------------------------------
*/

public ?array $viewingStudent = null;

public bool $showViewModal = false;

public bool $loadingStudent = false;


/*
|--------------------------------------------------------------------------
| Edit Enrollment
|--------------------------------------------------------------------------
*/
public ?int $editingStudentId = null;

public bool $editingEnrollment = false;

public bool $processingEdit = false;

public ?array $editingStudent = null;

public bool $loadingEdit = false;


/*
|--------------------------------------------------------------------------
| Delete Enrollment
|--------------------------------------------------------------------------
*/

public ?int $deletingStudentId = null;

public ?array $deletingStudent = null;

public bool $processingDelete = false;




/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

public function mount(
AdmissionsPortalClient $admissions
): void {
$this->programTypes = $admissions->getProgramTypes() ?? [];

$this->faculties = $admissions->getFaculties() ?? [];

$this->loadAcademicSessions();

$this->entryModes =
$admissions->getEntryModes() ?? [];


}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

public function updatingSearch(): void
{
$this->resetPage();
}


/*
|--------------------------------------------------------------------------
| Screen Student
|--------------------------------------------------------------------------
*/

public function screenStudent(): void
{
$this->resetScreeningState();

$this->validate([
'regno' => [
'required',
'string',
'max:50',
],
]);

$this->screening = true;

try {

$regno = strtoupper(trim($this->regno));


/*
|--------------------------------------------------------------------------
| 1. Check Admissions Portal
|--------------------------------------------------------------------------
*/

$admissions = app(
AdmissionsPortalClient::class
);

$admissionResult = $admissions->candidateByRegno(
$regno,
'details'
);


/*
|--------------------------------------------------------------------------
| Candidate exists in Admissions Portal
|--------------------------------------------------------------------------
*/

if (
($admissionResult['status'] ?? null) === 'success'
&&
!empty($admissionResult['data'])
) {

$this->screenedCandidate =
$admissionResult['data'];

$this->screeningError =
'This registration number exists in the Admissions Portal. '
. 'The candidate must be processed through Admission Processing.';

return;
}


/*
|--------------------------------------------------------------------------
| Admissions Portal verification failure
|--------------------------------------------------------------------------
*/

if (
($admissionResult['http_code'] ?? null) !== 404
) {

$this->screeningError =
$admissionResult['error']
?? 'Unable to verify the registration number against the Admissions Portal.';

return;
}


/*
|--------------------------------------------------------------------------
| 2. Check Student Portal
|--------------------------------------------------------------------------
*/

$studentPortal = app(
StudentPortalClient::class
);

$response = $studentPortal->listEnrolled([
'search'   => $regno,
'per_page' => 10,
]);

$students = $response['data'] ?? [];

$existing = collect($students)
->first(function ($student) use ($regno) {

return strtoupper(
trim((string) ($student['regno'] ?? ''))
) === strtoupper($regno);

});


/*
|--------------------------------------------------------------------------
| Already enrolled
|--------------------------------------------------------------------------
*/

if ($existing) {

$this->existingEnrollment = $existing;

$this->screeningError =
'This registration number is already enrolled in the Student Portal.';

return;
}


/*
|--------------------------------------------------------------------------
| 3. Eligible for exceptional enrollment
|--------------------------------------------------------------------------
*/

$this->eligibleForEnrollment = true;

$this->form['regno'] = strtoupper($regno);

} catch (\Throwable $e) {

Log::error(
'Enrolled Student screening failed',
[
'regno' => $this->regno,
'error' => $e->getMessage(),
]
);

$this->screeningError =
'Unable to complete the screening at this time.';

} finally {

$this->screening = false;
}
}


/*
|--------------------------------------------------------------------------
| Continue to Enrollment
|--------------------------------------------------------------------------
*/

public function beginEnrollment(): void
{
if (!$this->eligibleForEnrollment) {
return;
}

/*
|--------------------------------------------------------------------------
| Enter Enrollment Workflow
|--------------------------------------------------------------------------
*/

$this->showEnrollmentForm = true;

/*
|--------------------------------------------------------------------------
| Start at the top of the workflow
|--------------------------------------------------------------------------
*/

$this->resetValidation();
}


/*
|--------------------------------------------------------------------------
| Faculty
|--------------------------------------------------------------------------
*/

public function updatedFormFacultyId(
$facultyId
): void {
$this->departments = [];

$this->programs = [];

$this->form['department_id'] = null;

$this->form['program_id'] = null;

if (!$facultyId) {
return;
}

$this->departments =
app(AdmissionsPortalClient::class)
->getDepartments((int) $facultyId)
?? [];
}


/*
|--------------------------------------------------------------------------
| Department
|--------------------------------------------------------------------------
*/

public function updatedFormDepartmentId(
$departmentId
): void {
$this->programs = [];

$this->form['program_id'] = null;

if (!$departmentId) {
return;
}

$this->programs =
app(AdmissionsPortalClient::class)
->getPrograms(
(int) $departmentId,
$this->form['program_type_id']
? (int) $this->form['program_type_id']
: null
)
?? [];
}


/*
|--------------------------------------------------------------------------
| Programme Type
|--------------------------------------------------------------------------
*/

public function updatedFormProgramTypeId(
$programTypeId
): void {


$this->form['school_division_id'] = null;

/*
|--------------------------------------------------------------------------
| Resolve School Division from Program Type
|--------------------------------------------------------------------------
*/

$programType = collect($this->programTypes)
->firstWhere('id', (int) $programTypeId);

if ($programType) {
$this->form['school_division_id'] =
isset($programType['school_division_id'])
? (int) $programType['school_division_id']
: null;
}

/*
|--------------------------------------------------------------------------
| JAMB is applicable to Undergraduate only
|--------------------------------------------------------------------------
*/

if ((int) $programTypeId !== 1) {
$this->form['jamb_no'] = null;
$this->form['jamb_score'] = null;
}



/*
|--------------------------------------------------------------------------
| Reload programmes when programme type changes.
|--------------------------------------------------------------------------
*/

if (
!$this->form['department_id']
) {
return;
}

$this->programs =
app(AdmissionsPortalClient::class)
->getPrograms(
(int) $this->form['department_id'],
$programTypeId
? (int) $programTypeId
: null
)
?? [];

$this->form['program_id'] = null;
}


/*
|--------------------------------------------------------------------------
| Confirm Enrollment
|--------------------------------------------------------------------------
*/

public function confirmEnrollment(): void
{
$this->validate([
'form.regno' => [
'required',
'string',
'max:50',
],

'form.first_name' => [
'required',
'string',
'max:100',
],

'form.last_name' => [
'required',
'string',
'max:100',
],

'form.other_names' => [
'nullable',
'string',
'max:150',
],

'form.phone_no' => [
'nullable',
'string',
'max:20',
],

'form.gender' => [
'nullable',
'in:M,F',
],

'form.program_type_id' => [
'required',
'integer',
],

'form.faculty_id' => [
'required',
'integer',
],

'form.department_id' => [
'required',
'integer',
],

'form.program_id' => [
'required',
'integer',
],

'form.acad_session_id' => [
'required',
'integer',
],

'form.entry_mode_id' => [
'required',
'integer',
],

'form.study_mode_id' => [
'nullable',
'integer',
'in:1,2',
],
]);

$this->showConfirmation = true;
}


/*
|--------------------------------------------------------------------------
| Enroll
|--------------------------------------------------------------------------
*/

public function enroll(): void
{
if (!$this->eligibleForEnrollment) {
return;
}

$this->processing = true;

try {

$payload = $this->form;

foreach ([
'program_type_id',
'faculty_id',
'department_id',
'program_id',
'acad_session_id',
'entry_mode_id',
'study_mode_id',
] as $key) {

if (
isset($payload[$key]) &&
$payload[$key] !== ''
) {
$payload[$key] =
(int) $payload[$key];
}
}

/*
|--------------------------------------------------------------------------
| Derive start session
|--------------------------------------------------------------------------
*/

if (!empty($payload['entry_mode_id'])) {

$entryMode = collect(
$this->entryModes
)->firstWhere(
'id',
(int) $payload['entry_mode_id']
);


if (
$entryMode &&
!empty($entryMode['is_direct_entry'])
) {

$payload['start_session_id'] =
max(
1,
(int) $payload['acad_session_id'] - 1
);

} else {

$payload['start_session_id'] =
(int) $payload['acad_session_id'];
}

}


/*
|--------------------------------------------------------------------------
| Create
|--------------------------------------------------------------------------
*/

$result = app(StudentPortalClient::class)
->createEnrolled($payload);

$student = $result['data'] ?? null;

if ($student) {

  app(AdmissionAuditService::class)->record(
        candidate: $student,
        action: 'exceptional_enrollment',
        decision: 'created',

    );
}

/*
|--------------------------------------------------------------------------
| Success
|--------------------------------------------------------------------------
*/

Flux::toast(
'Student enrolled successfully.',
variant: 'success',
position: 'top-right',
duration: 4000
);


/*
|--------------------------------------------------------------------------
| Reset workflow
|--------------------------------------------------------------------------
*/

$this->showConfirmation = false;

$this->showEnrollmentForm = false;

$this->eligibleForEnrollment = false;

$this->screenedCandidate = null;

$this->existingEnrollment = null;

$this->regno = '';

$this->resetForm();

$this->resetPage();

} catch (\Throwable $e) {

Log::error(
'Exceptional student enrollment failed',
[
'regno' => $this->form['regno'] ?? null,
'error' => $e->getMessage(),
]
);

Flux::toast(
'Enrollment failed. Please check the logs.',
variant: 'error',
position: 'top-right',
duration: 5000
);

} finally {

$this->processing = false;
}
}


/*
|--------------------------------------------------------------------------
| Reset Screening
|--------------------------------------------------------------------------
*/

public function resetScreeningState(): void
{
$this->screenedCandidate = null;

$this->existingEnrollment = null;

$this->screeningError = null;

$this->eligibleForEnrollment = false;

$this->showEnrollmentForm = false;

$this->showConfirmation = false;
}


/*
|--------------------------------------------------------------------------
| Reset Candidate
|--------------------------------------------------------------------------
*/

public function clearScreening(): void
{
$this->regno = '';

$this->resetScreeningState();

$this->resetForm();
}


/*
|--------------------------------------------------------------------------
| Reset Form
|--------------------------------------------------------------------------
*/

protected function resetForm(): void
{
$this->form = [
'regno'            => '',
'first_name'       => '',
'last_name'        => '',
'other_names'      => '',
'phone_no'         => '',
'gender'           => '',
'jamb_no'          => null,
'jamb_score'       => null,
'program_type_id'  => null,
'faculty_id'       => null,
'department_id'    => null,
'program_id'       => null,
'acad_session_id'  => null,
'entry_mode_id'    => null,
'study_mode_id'    => null,
];

$this->departments = [];

$this->programs = [];
}


public function viewStudent(int $id): void
{
$this->loadingStudent = true;

try {

$result = app(StudentPortalClient::class)
->getEnrolled($id);

$this->viewingStudent =
$result['data']
?? null;

if (!$this->viewingStudent) {
throw new \RuntimeException(
'Unable to load student details.'
);
}

$this->showViewModal = true;

} catch (\Throwable $e) {

Log::error(
'Enrolled Student → view failed',
[
'id'    => $id,
'error' => $e->getMessage(),
]
);

Flux::toast(
'Unable to load student details.',
variant: 'error',
position: 'top-right',
duration: 4000
);

} finally {

$this->loadingStudent = false;
}
}

public function closeViewModal(): void
{
$this->showViewModal = false;

$this->viewingStudent = null;
}

public function nextEnrolledPage(): void
{
$this->nextPage();
}

public function previousEnrolledPage(): void
{
$this->previousPage();
}


private function loadAcademicSessions(): void
{
$admissions = app(AdmissionsPortalClient::class);

$sessions = collect(
$admissions->getAcadSessions() ?? []
);

/*
|--------------------------------------------------------------------------
| Current Academic Session
|--------------------------------------------------------------------------
|
| The current session is identified by is_visible = 1.
|
*/

$currentSession = $sessions
->firstWhere('is_visible', 1);

if (!$currentSession) {
$this->academicSessions = [];

return;
}

/*
|--------------------------------------------------------------------------
| Previous Academic Session
|--------------------------------------------------------------------------
|
| Get the most recent session before the current session.
|
*/

$previousSession = $sessions
->filter(function ($session) use ($currentSession) {
return (int) ($session['id'] ?? 0)
< (int) ($currentSession['id'] ?? 0);
})
->sortByDesc('id')
->first();

/*
|--------------------------------------------------------------------------
| Current + Previous only
|--------------------------------------------------------------------------
*/

$this->academicSessions = collect([
$currentSession,
$previousSession,
])
->filter()
->unique('id')
->values()
->all();
}


public function editStudent(int $id): void
{
    $this->loadingEdit = true;

    try {

        $result = app(StudentPortalClient::class)
            ->getEnrolled($id);

        $student = $result['data'] ?? null;

        if (!$student) {
            throw new \RuntimeException(
                'Unable to load enrolled student.'
            );
        }

        $this->editingStudentId = $id;

        $this->editingStudent = $student;

        $programType = collect($this->programTypes)
    ->firstWhere(
        'id',
        (int) ($student['program_type_id'] ?? 0)
    );

        $this->editForm = [
            'program_type_id'    => $student['program_type_id'] ?? null,

            'school_division_id' => isset($programType['school_division_id'])
                ? (int) $programType['school_division_id']
                : null,
            'acad_session_id'    => $student['acad_session_id'] ?? null,
            'faculty_id'         => $student['faculty_id'] ?? null,
            'department_id'      => $student['department_id'] ?? null,
            'program_id'         => $student['program_id'] ?? null,
            'entry_mode_id'      => $student['entry_mode_id'] ?? null,
            'study_mode_id'      => $student['study_mode_id'] ?? null,
            'jamb_no'            => $student['jamb_no'] ?? null,
            'jamb_score'         => $student['jamb_score'] ?? null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Load dependent academic data
        |--------------------------------------------------------------------------
        */

        $this->loadEditDepartments();

        $this->loadEditPrograms();

        $this->editingEnrollment = true;

        Flux::modal('edit-enrolled-student')->show();

    } catch (\Throwable $e) {

        Log::error(
            'Enrolled Student → edit failed',
            [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]
        );

        Flux::toast(
            'Unable to load student for editing.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

    } finally {

        $this->loadingEdit = false;
    }
}




public function updatedEditFormProgramTypeId($value): void
{
    $this->editForm['school_division_id'] = null;

    $programType = collect($this->programTypes)
        ->firstWhere('id', (int) $value);

    if ($programType) {

        $this->editForm['school_division_id'] =
            isset($programType['school_division_id'])
                ? (int) $programType['school_division_id']
                : null;
    }

    /*
    |--------------------------------------------------------------------------
    | JAMB only applies to UG
    |--------------------------------------------------------------------------
    */

    if ((int) $value !== 1) {

        $this->editForm['jamb_no'] = null;

        $this->editForm['jamb_score'] = null;
    }

    /*
    |--------------------------------------------------------------------------
    | Reset dependent placement
    |--------------------------------------------------------------------------
    */

    $this->editForm['faculty_id'] = null;
    $this->editForm['department_id'] = null;
    $this->editForm['program_id'] = null;

    $this->departments = [];
    $this->programs = [];
}


public function confirmDeleteStudent(int $id): void
{
    $this->processingDelete = true;

    try {

        $result = app(StudentPortalClient::class)
            ->getEnrolled($id);

        $student = $result['data'] ?? null;

        if (!$student) {
            throw new \RuntimeException(
                'Unable to load enrolled student.'
            );
        }

        $this->deletingStudentId = $id;

        $this->deletingStudent = $student;

        Flux::modal(
            'delete-enrolled-student-confirmation'
        )->show();

    } catch (\Throwable $e) {

        Log::error(
            'Enrolled Student → delete confirmation failed',
            [
                'id'    => $id,
                'error' => $e->getMessage(),
            ]
        );

        Flux::toast(
            'Unable to load student details.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

    } finally {

        $this->processingDelete = false;
    }
}

public function deleteStudent(): void
{
    $id = $this->deletingStudentId;

    if (!$id) {
        return;
    }

    $this->processingDelete = true;

    try {

        /*
        |--------------------------------------------------------------------------
        | Get student BEFORE deletion
        |--------------------------------------------------------------------------
        */

        $studentResult = app(StudentPortalClient::class)
            ->getEnrolled($id);

        $student = $studentResult['data'] ?? null;

        if (!$student) {
            throw new \RuntimeException(
                'Unable to retrieve the enrolled student before deletion.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Delete
        |--------------------------------------------------------------------------
        */

        $deleted = app(StudentPortalClient::class)
            ->deleteEnrolled($id);

        if (!$deleted) {
            throw new \RuntimeException(
                'Unable to delete enrolled student.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        app(AdmissionAuditService::class)->record(
            candidate: $student,
            action: 'exceptional_enrollment',
            decision: 'deleted',
        );

        /*
        |--------------------------------------------------------------------------
        | Close modal
        |--------------------------------------------------------------------------
        */

        Flux::modal('delete-enrolled-student-confirmation')->close();

        /*
        |--------------------------------------------------------------------------
        | Reset state
        |--------------------------------------------------------------------------
        */

        $this->deletingStudentId = null;

        $this->resetPage();

        Flux::toast(
            'Enrolled student deleted successfully.',
            variant: 'success',
            position: 'top-right',
            duration: 4000
        );

    } catch (\Throwable $e) {

        Log::error(
            'Exceptional Enrollment → delete failed',
            [
                'student_id' => $id,
                'error'      => $e->getMessage(),
            ]
        );

        Flux::toast(
            'Unable to delete enrolled student. Please try again.',
            variant: 'error',
            position: 'top-right',
            duration: 5000
        );

    } finally {

        $this->processingDelete = false;
    }
}

private function loadEditDepartments(): void
{
    if (!$this->editForm['faculty_id']) {
        $this->departments = [];

        return;
    }

    $client = app(AdmissionsPortalClient::class);

    $result = $client->getDepartments(
        (int) $this->editForm['faculty_id'],
        $this->editForm['program_type_id']
            ? (int) $this->editForm['program_type_id']
            : null
    );

    $this->departments =
        $result['data'] ?? $result ?? [];
}

private function loadEditPrograms(): void
{
    if (!$this->editForm['department_id']) {
        $this->programs = [];

        return;
    }

    $client = app(AdmissionsPortalClient::class);

    $result = $client->getPrograms(
        (int) $this->editForm['department_id'],
        $this->editForm['program_type_id']
            ? (int) $this->editForm['program_type_id']
            : null
    );

    $this->programs =
        $result['data'] ?? $result ?? [];
}

public function updatedEditFormFacultyId($value): void
{
    $this->editForm['department_id'] = null;
    $this->editForm['program_id'] = null;

    $this->programs = [];

    if (!$value) {
        $this->departments = [];

        return;
    }

    $this->loadEditDepartments();
}

public function updatedEditFormDepartmentId($value): void
{
    $this->editForm['program_id'] = null;

    if (!$value) {
        $this->programs = [];

        return;
    }

    $this->loadEditPrograms();
}

public function closeEdit(): void
{
    if ($this->processingEdit) {
        return;
    }

    Flux::modal('edit-enrolled-student')->close();

    $this->editingEnrollment = false;

    $this->editingStudentId = null;
    $this->editingStudent = null;

    $this->editForm = [
        'program_type_id'    => null,
        'school_division_id' => null,
        'acad_session_id'    => null,
        'faculty_id'         => null,
        'department_id'      => null,
        'program_id'         => null,
        'entry_mode_id'      => null,
        'study_mode_id'      => null,
        'jamb_no'            => null,
        'jamb_score'         => null,
    ];
}


public function updateStudent(): void
{

    $this->validate([
        'editForm.program_type_id' => [
            'required',
            'integer',
        ],

        'editForm.school_division_id' => [
            'required',
            'integer',
        ],

        'editForm.acad_session_id' => [
            'required',
            'integer',
        ],

        'editForm.faculty_id' => [
            'required',
            'integer',
        ],

        'editForm.department_id' => [
            'required',
            'integer',
        ],

        'editForm.program_id' => [
            'required',
            'integer',
        ],

        'editForm.entry_mode_id' => [
            'required',
            'integer',
        ],

        'editForm.study_mode_id' => [
            'required',
            'integer',
        ],

        'editForm.jamb_no' => [
            'required_if:editForm.program_type_id,1',
            'nullable',
            'string',
            'max:50',
        ],

        'editForm.jamb_score' => [
            'required_if:editForm.program_type_id,1',
            'nullable',
            'integer',
            'min:0',
            'max:400',
        ],
    ]);


    if (!$this->editingStudentId) {
        return;
    }

    $this->processingEdit = true;

    try {



        $payload = [
    /*
    |--------------------------------------------------------------------------
    | Student Identity
    |--------------------------------------------------------------------------
    | These are required by the Student Portal update endpoint,
    | but are NOT editable in the UI.
    |--------------------------------------------------------------------------
    */

    'regno' => $this->editingStudent['regno'] ?? null,

    'first_name' => $this->editingStudent['first_name']
        ?? $this->editingStudent['user']['first_name']
        ?? null,

    'last_name' => $this->editingStudent['last_name']
        ?? $this->editingStudent['user']['last_name']
        ?? null,

    /*
    |--------------------------------------------------------------------------
    | Academic Placement
    |--------------------------------------------------------------------------
    */

    'program_type_id' => (int) $this->editForm['program_type_id'],

    'school_division_id' => (int) $this->editForm['school_division_id'],

    'acad_session_id' => (int) $this->editForm['acad_session_id'],

    'faculty_id' => (int) $this->editForm['faculty_id'],

    'department_id' => (int) $this->editForm['department_id'],

    'program_id' => (int) $this->editForm['program_id'],

    'entry_mode_id' => (int) $this->editForm['entry_mode_id'],

    'study_mode_id' => $this->editForm['study_mode_id']
        ? (int) $this->editForm['study_mode_id']
        : null,

    /*
    |--------------------------------------------------------------------------
    | JAMB — UG only
    |--------------------------------------------------------------------------
    */

    'jamb_no' => $this->editForm['jamb_no'] ?: null,

    'jamb_score' => $this->editForm['jamb_score'] !== null
        && $this->editForm['jamb_score'] !== ''
            ? (int) $this->editForm['jamb_score']
            : null,
];


        Log::info(
            'Exceptional Enrollment → update',
            [
                'student_id' => $this->editingStudentId,
                'payload'    => $payload,
            ]
        );

        $result = app(StudentPortalClient::class)
        ->updateEnrolled(
        $this->editingStudentId,
        $payload
          );

    $student = $result['data'] ?? null;

    //audit
    if ($student) {

    app(AdmissionAuditService::class)->record(
        candidate: $student,
        action: 'exceptional_enrollment',
        decision: 'updated',
        metadata: [
            'before' => [
                'acad_session_id' =>
                    $this->editingStudent['acad_session_id'] ?? null,

                'program_type_id' =>
                    $this->editingStudent['program_type_id'] ?? null,

                'application_type_id' =>
                    $this->editingStudent['application_type_id'] ?? null,

                'faculty_id' =>
                    $this->editingStudent['faculty_id'] ?? null,

                'department_id' =>
                    $this->editingStudent['department_id'] ?? null,

                'program_id' =>
                    $this->editingStudent['program_id'] ?? null,

                'entry_mode_id' =>
                    $this->editingStudent['entry_mode_id'] ?? null,

                'study_mode_id' =>
                    $this->editingStudent['study_mode_id'] ?? null,

                'jamb_no' =>
                    $this->editingStudent['jamb_no'] ?? null,

                'jamb_score' =>
                    $this->editingStudent['jamb_score'] ?? null,
            ],

            'after' => [
                'acad_session_id' =>
                    $student['acad_session_id']
                        ?? $payload['acad_session_id'],

                'program_type_id' =>
                    $student['program_type_id']
                        ?? $payload['program_type_id'],

                'application_type_id' =>
                    $student['application_type_id']
                        ?? $payload['application_type_id']
                        ?? null,

                'faculty_id' =>
                    $student['faculty_id']
                        ?? $payload['faculty_id'],

                'department_id' =>
                    $student['department_id']
                        ?? $payload['department_id'],

                'program_id' =>
                    $student['program_id']
                        ?? $payload['program_id'],

                'entry_mode_id' =>
                    $student['entry_mode_id']
                        ?? $payload['entry_mode_id'],

                'study_mode_id' =>
                    $student['study_mode_id']
                        ?? $payload['study_mode_id'],

                'jamb_no' =>
                    $student['jamb_no']
                        ?? $payload['jamb_no'],

                'jamb_score' =>
                    $student['jamb_score']
                        ?? $payload['jamb_score'],
            ],
        ],
    );
}

        /*
        |--------------------------------------------------------------------------
        | Close modal
        |--------------------------------------------------------------------------
        */

        Flux::modal('edit-enrolled-student')->close();

        /*
        |--------------------------------------------------------------------------
        | Reset edit state
        |--------------------------------------------------------------------------
        */

        $this->editingStudent = null;
        $this->editingStudentId = null;
        $this->editingEnrollment = false;

        $this->resetEditForm();

        /*
        |--------------------------------------------------------------------------
        | Refresh list
        |--------------------------------------------------------------------------
        */

        $this->resetPage();

        Flux::toast(
            'Enrolled student updated successfully.',
            variant: 'success',
            position: 'top-right',
            duration: 4000
        );

    } catch (\Throwable $e) {

        Log::error(
            'Exceptional Enrollment → update failed',
            [
                'student_id' => $this->editingStudentId,
                'error'      => $e->getMessage(),
            ]
        );

        Flux::toast(
            'Unable to update enrolled student.',
            variant: 'error',
            position: 'top-right',
            duration: 4000
        );

    } finally {

        $this->processingEdit = false;
    }
}

private function resetEditForm(): void
{
    $this->editForm = [
        'program_type_id'    => null,
        'school_division_id' => null,
        'acad_session_id'    => null,
        'faculty_id'         => null,
        'department_id'      => null,
        'program_id'         => null,
        'entry_mode_id'      => null,
        'study_mode_id'      => null,
        'jamb_no'            => null,
        'jamb_score'         => null,
    ];
}

/*
|--------------------------------------------------------------------------
| Render
|--------------------------------------------------------------------------
*/

public function render()
{
$response = app(StudentPortalClient::class)
->listEnrolled([
'search'   => $this->search ?: null,
'per_page' => $this->perPage,
'page'     => $this->getPage(),
]);

return view(
'livewire.admissions.enrolled-students.enrollment',
[
'items' => $response['data'] ?? [],
'meta'  => $response['meta'] ?? [],
'links' => $response['links'] ?? [],
]
);
}


}
