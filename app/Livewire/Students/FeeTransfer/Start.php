<?php

namespace App\Livewire\Students\FeeTransfer;

use Livewire\Component;

class Start extends Component
{
public int $step = 1;
public bool $completed = false;
// Shared state
public string $regno = '';
public array $student = [];

public array $context = [
'acad_session_id' => null,
'semester' => null,
'level_id' => null,
'program_type_id' => null,
'payment_source_type_id' => null,
];

public array $details = [
'amount' => null,
'description' => null,
];

protected $listeners = [
'nextStep' => 'next',
'prevStep' => 'prev',
'studentValidated',
'contextSelected',
'detailsCompleted',
'transferCompleted' => 'showSuccess',
];

public function next() { $this->step++; }
public function prev() { $this->step--; }

public function studentValidated(array $student)
{
$this->student = $student;
 logger()->info('START student state', $this->student);
$this->next();
}

public function contextSelected(array $context)
{
$this->context = $context;
$this->next();
}

public function detailsCompleted(array $details)
{
$this->details = $details;
$this->next();
}

public function goToStep(int $step)
{
if ($step < $this->step) {
$this->step = $step;
}
}

public function showSuccess()
{
$this->completed = true;
}


public function render()
{
return view('livewire.students.fee-transfer.start');
}
}
