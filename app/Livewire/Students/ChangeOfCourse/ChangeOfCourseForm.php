<?php

namespace App\Livewire\Students\ChangeOfCourse;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use Illuminate\Support\Facades\Auth;

class ChangeOfCourseForm extends Component
{
public string $regno;
public bool $isSubmitting = false;
public ?array $student = null;
public ?string $reason = null;

public bool $isEligible = false;
public ?string $eligibilityMessage = null;
public ?string $error = null;

// Program selector state
public array $faculties = [];
public array $departments = [];
public array $programs = [];

public ?int $facultyId = null;
public ?int $departmentId = null;
public ?int $programId = null;
public ?int $programTypeId = null;


// Phase toggle
public string $changeType = 'same_program_type';
// Phase 2 only
public ?int $toProgramTypeId = null;

// Program types for selector
public array $programTypes = [];


protected function rules(): array
{
return [
'facultyId'    => ['required', 'integer'],
'departmentId' => ['required', 'integer'],
'programId'    => ['required', 'integer'],
];
}

protected function validateNotSameProgram(): void
{
if ($this->programId == ($this->student['program_id'] ?? null)) {
$this->addError(
'programId',
'New program must be different from current program.'
);

throw new \RuntimeException('Same program selected');
}
}



public function mount(string $regno)
{
$this->regno = $regno;
$this->findStudent(app(StudentPortalClient::class));
}

/**
 * Fetch student from Students Portal
 */
public function findStudent(StudentPortalClient $client)
{
$this->reset(['student', 'error', 'isEligible', 'eligibilityMessage']);

$res = $client->getStudentByRegno(
$this->regno,
['with' => 'details']
);
//dd($res);
//Log::info('Student payload', $res['data']);


if (!isset($res['data'])) {
$this->setIneligible('Student not found.');
return;
}

// Normalize response
$this->student = $res['data'];

$this->evaluateEligibility($this->student);
if ($this->isEligible) {
$this->resolveProgramTypeId();   // ✅ correct place
$this->loadFaculties(app(AdmissionsPortalClient::class));
}


}


protected function resolveProgramTypeId(): void
{
$client = app(AdmissionsPortalClient::class);

$pt = $client->getProgramTypeByName($this->student['program_type']);

if (! $pt || empty($pt['id'])) {
$this->setIneligible(
'Unable to resolve program type for this student.'
);
return;
}

$this->programTypeId = (int) $pt['id'];
}

/**
 * Phase 1 eligibility checks (UI mirror of backend rules)
 */
protected function evaluateEligibility(array $student): void
{
// Phase 1: NOT matriculated yet
if (!empty($student['matric_no'])) {
$this->setIneligible(
'Student has been matriculated. Change of course not allowed.'
);
return;
}

if (!empty($student['is_graduated'])) {
$this->setIneligible(
'Graduated student cannot change course.'
);
return;
}

if (
empty($student['program']) ||
empty($student['program_type'])
) {
$this->setIneligible(
'Student academic placement is incomplete.'
);
return;
}

// ✅ PASSED Phase 1
$this->isEligible = true;
$this->eligibilityMessage =
'Student is eligible for Change of Course.';
// Load program hierarchy
//$this->loadFaculties(app(AdmissionsPortalClient::class));
}
/**
 * Helper for ineligible states
 */
protected function setIneligible(string $message): void
{
$this->isEligible = false;
$this->eligibilityMessage = $message;
}


/**
 * Load faculties once eligibility is confirmed
 */
protected function loadFaculties(AdmissionsPortalClient $client): void
{
$this->faculties = $client->getFaculties() ?? [];
}

/**
 * When faculty changes
 */
public function updatedFacultyId($value, AdmissionsPortalClient $client)
{
$this->reset(['departmentId', 'programId', 'departments', 'programs']);

if (! $value) {
return;
}

$this->departments = $client->getDepartments($value);
}

/**
 * When department changes
 */
public function updatedDepartmentId($value, AdmissionsPortalClient $client)
{

$this->reset(['programId', 'programs']);

if (! $value) {
return;
}
$this->programs = $client->getPrograms($value, $this->programTypeId);
}


//phase 2 only
protected function loadProgramTypes(AdmissionsPortalClient $client): void
{
$this->programTypes = $client->getProgramTypes();
}

public function updatedChangeType(string $value, AdmissionsPortalClient $client)
{
    // Reset downstream selections
    $this->reset([
        'toProgramTypeId',
        'facultyId',
        'departmentId',
        'programId',
        'faculties',
        'departments',
        'programs',
    ]);

    if ($value === 'inter_program_type') {
        // Load program types only when needed
        $this->loadProgramTypes($client);
        $this->loadFaculties($client);
    }

    if ($value === 'same_program_type') {
        // Lock program type back to student’s current
        $this->programTypeId = $this->programTypeId; // no-op, clarity
        $this->loadFaculties($client);
    }
}

public function updatedToProgramTypeId($value, AdmissionsPortalClient $client)
{
    $this->reset([
        'facultyId',
        'departmentId',
        'programId',
        'faculties',
        'departments',
        'programs',
    ]);

    if (! $value) {
        return;
    }

    // Override context program type
    $this->programTypeId = $value;

    // Load faculties for the NEW program type
    $this->loadFaculties($client);

}




public function submit(StudentPortalClient $client)
{
if (! $this->isEligible) {
Flux::toast('Student is not eligible for change of course.', variant: 'danger');
return;
}

$this->validate();
$this->validateNotSameProgram();

$this->isSubmitting = true;

try {
/** -----------------------------------------
 * STEP 1: Create change request
 * -------------------------------------- */
$createPayload = [
'change_type'     => $this->changeType,
'student_id'      => $this->student['id'],
'to_faculty_id'   => $this->facultyId,
'to_department_id'=> $this->departmentId,
'to_program_id'   => $this->programId,
'reason'          => $this->reason,
'requested_by'    => Auth::user()->email,
];

if ($this->changeType === 'inter_program_type') {
    $createPayload['to_program_type_id'] = $this->toProgramTypeId;
}

$createResp = $client->createChangeOfCourse($createPayload);

if (empty($createResp['success']) || empty($createResp['data']['id'])) {
throw new \Exception($createResp['message'] ?? 'Unable to create change request.');
}

$changeId = $createResp['data']['id'];

/** -----------------------------------------
 * STEP 2: Approve & apply immediately
 * -------------------------------------- */
$approveResp = $client->approveChangeOfCourse(
$changeId,
[
'performed_by' => Auth::user()->email,
]
);

if (empty($approveResp['success'])) {
throw new \Exception($approveResp['message'] ?? 'Approval failed.');
}

/** -----------------------------------------
 * SUCCESS
 * -------------------------------------- */
Flux::toast(
'Change of course applied successfully.',
variant: 'success',
position: 'top-right',
duration: 4000
);

// Optional: redirect back to lookup
return redirect()->route('students.change-of-course');

} catch (\Throwable $e) {
Flux::toast(
$e->getMessage(),
variant: 'danger',
position: 'top-right'
);
} finally {
$this->isSubmitting = false;
}
}


public function render()
{
return view('livewire.students.change-of-course.form');
}
}
