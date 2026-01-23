<?php

namespace App\Livewire\Students\FeeTransfer\Steps;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;


class TransferContext extends Component
{
public $acad_session_id;
public $semester;
public $level_id;
public $program_type_id;
public $payment_source_type_id;

public $sessions = [];
public $levels = [];
public $programTypes = [];
public $paymentSources = [];
public array $student;

public function mount(array $student)
{
$this->student = $student;
//$this->sessions = app(AdmissionsPortalClient::class)->getAcadSessions();
$this->sessions = collect(
    app(AdmissionsPortalClient::class)->getAcadSessions()
)
->where('id', '>=', 16)
->values() // reset keys (important for Blade loops)
->all();
$this->levels = app(StudentPortalClient::class)->getLevels();
$this->paymentSources = app(StudentPortalClient::class)->getPaymentSourceTypes();
}

public function proceed()
{
$this->validate([
'acad_session_id' => 'required',
'semester' => 'required|in:1,2,3',
'level_id' => 'required',
//'program_type_id' => 'required',
'payment_source_type_id' => 'required',
]);

$this->dispatch('contextSelected', [
'acad_session_id' => $this->acad_session_id,
'acad_session_name' =>
collect($this->sessions)->firstWhere('id', $this->acad_session_id)['name'],
'semester' => $this->semester,
'level_id' => $this->level_id,
'level_name' =>
collect($this->levels)->firstWhere('id', $this->level_id)['name'],
'payment_source_type_id' => $this->payment_source_type_id,
'payment_source_type_name' =>
collect($this->paymentSources)->firstWhere('id', $this->payment_source_type_id)['name'],
]);

}

public function render()
{
return view('livewire.students.fee-transfer.steps.transfer-context');
}
}
