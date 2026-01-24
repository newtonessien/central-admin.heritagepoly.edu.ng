<?php

namespace App\Livewire\Students;
use Livewire\Component;
use App\Services\Clients\StudentPortalClient;
use Illuminate\Support\Facades\Auth;

class MarkAsScreened extends Component
{
public string $regno = '';
public array $student = [];
public bool $loading = false;
public ?string $message = null;

public function validateStudent()
{

$this->validate([
'regno' => 'required|string'
]);
$this->reset(['student', 'message']);

$resp = app(StudentPortalClient::class)
->getStudentByRegno($this->regno);

if (! isset($resp['data'])) {
$this->addError('regno', 'Student not found');
return;
}

$this->student = $resp['data'];
}

public function markScreened()
{

$this->loading = true;
$resp = app(StudentPortalClient::class)
->markStudentAsScreened($this->regno, Auth::user()->email);
$this->loading = false;

if (isset($resp['message'])) {
$this->message = $resp['message'] ?? null;
}

// Refresh student state
$this->validateStudent();
}

public function render()
{
return view('livewire.students.mark-as-screened');
}
}

