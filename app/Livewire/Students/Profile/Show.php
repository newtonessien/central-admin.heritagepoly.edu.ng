<?php

namespace App\Livewire\Students\Profile;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
class Show extends Component
{
public string $regno;

public array $student = [];
public array $paymentSummary = [];
public array $registrationSummary = [];
public array $resultSummary = [];

public array $sessions = [];

public $startSessionId = 35;

/**
 * Active Student 360° tab.
 */
public string $activeTab = 'overview';

public function mount(
string $regno,
StudentPortalClient $studentClient,
AdmissionsPortalClient $admissionsClient
): void {

$this->regno = $regno;

$response = $studentClient->getStudentByRegno($regno);

abort_if(
empty($response['data']),
404,
'Student not found.'
);

$this->student = $response['data'];
$payments = $studentClient->getStudentPaymentHistory($regno);
$registration = $studentClient->getCourseRegistrationHistory($regno);
$this->paymentSummary = $payments['summary'] ?? [];
$this->paymentSummary = $payments['summary'] ?? [];
$this->registrationSummary = $registration['summary'] ?? [];

// Shared lookup
$this->sessions = collect($admissionsClient->getAcadSessions())
->where('id', '>=', $this->startSessionId)
->values()
->toArray();


}

public function setActiveTab(string $tab): void
{
    $this->activeTab = $tab;
}



public function render()
{
return view('livewire.students.profile.show');
}
}
