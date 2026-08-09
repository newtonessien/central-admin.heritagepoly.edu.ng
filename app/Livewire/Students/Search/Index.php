<?php

namespace App\Livewire\Students\Search;

use App\Services\Clients\StudentPortalClient;
use Flux\Flux;
use Livewire\Component;

class Index extends Component
{
public string $regno = '';

/**
 * Find student by Registration Number.
 */
public function findStudent(StudentPortalClient $client)
{
$this->validate([
'regno' => ['required', 'string'],
]);


$response = $client->getStudentByRegno(trim($this->regno));



if (empty($response['data'])) {

Flux::toast(
"Student not found. Please verify the Registration Number",
variant: 'success',
position: 'top-right',
duration: 5000
);

return;
}

return redirect()->route('students.profile', [
    'regno' => $response['data']['matric_no'] ?? $response['data']['regno'],
]);

}

/**
 * Reset search form.
 */
public function resetSearch(): void
{
$this->reset('regno');
$this->resetValidation();
}

public function render()
{
return view('livewire.students.search.index');
}
}
