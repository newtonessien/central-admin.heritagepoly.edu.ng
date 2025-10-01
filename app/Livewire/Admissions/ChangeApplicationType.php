<?php

namespace App\Livewire\Admissions;

use Flux\Flux;
use Livewire\Component;
use App\Services\Clients\AdmissionsPortalClient;

class ChangeApplicationType extends Component
{
  public $regno;
    public $application_type_id;
    public $applicationTypes = [];

    protected $rules = [
        'regno' => 'required|string',
        'application_type_id' => 'required|integer',
    ];

    public function mount(AdmissionsPortalClient $client)
{
    $items = $client->getApplicationTypes();

    $excluded = config('services.excluded_application_types', []);

    $this->applicationTypes = collect($items)
        ->reject(fn ($type) => in_array((string) ($type['id'] ?? ''), $excluded))
        ->values()
        ->toArray();
}



public function update(AdmissionsPortalClient $client)
{
    $this->validate();

    $data = $client->changeApplicationType($this->regno, $this->application_type_id);

    if ($data['success'] ?? false) {
        $this->reset(['regno', 'application_type_id']);
        Flux::toast($data['message'] ?? 'Application type updated.', variant: 'success', position: 'top-right', duration: 4000);
    } else {
        Flux::toast($data['message'] ?? 'Failed to update application type.', variant: 'warning', position: 'top-right', duration: 4000);
    }
}


    public function render()
    {
        return view('livewire.admissions.change-application-type');
    }
}

