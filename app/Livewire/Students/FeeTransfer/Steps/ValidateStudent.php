<?php

namespace App\Livewire\Students\FeeTransfer\Steps;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use App\Services\Clients\StudentPortalClient;


class ValidateStudent extends Component
{
public string $regno = '';

protected array $rules = [
'regno' => 'required|string',
];

public function validateStudent()
{

// 1️⃣ Hard guard – do NOT call API if empty
if (blank($this->regno)) {
$this->addError('regno', 'Registration number is required');
return;
}

 // Optional: trim + normalize
$regno = trim($this->regno);

// $student = app(StudentPortalClient::class)
// ->getStudentByRegno($regno, ['with' => 'details']);

$student = app(StudentPortalClient::class)
    ->getStudentWithDetails($regno);


if (! isset($student['data'])) {
$this->addError('regno', 'Student not found');
return;
}

$payload = $student['data'] ?? [];
$programTypeName = $payload['program_type'] ?? null;

if (! isset($payload['program_type'])) {
    logger()->error('DETAILS MISSING', [
        'regno' => $regno,
        'response' => $student,
    ]);

    $this->addError(
        'regno',
        'Student details could not be loaded. Please retry.'
    );
    return;
}




if (! is_string($programTypeName) || trim($programTypeName) === '') {
    $this->addError('regno', 'Program type not found for student');
    return;
}


//$this->dispatch('studentValidated', $student);
$this->dispatch('studentValidated', [
'regno' => $payload['matric_no'] ?? $this->regno,
'name' => $payload['name'] ?? null,
'program' => $payload['program'] ?? null,

// LABEL ONLY
'program_type_name' => $programTypeName,
]);



}

public function render()
{
return view('livewire.students.fee-transfer.steps.validate-student');
}
}
