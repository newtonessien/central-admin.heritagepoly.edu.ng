<?php

namespace App\Livewire\Bursary;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;

class ConsultantSchoolFeesReport extends Component
{
    public $start_date;
    public $end_date;
    public $report = [];

    protected $client;

    public function mount()
    {
        // Resolve the client from the service container
        $this->client = app(StudentPortalClient::class);

    }

    public function fetchReport()
    {
          // Lazy-load client
    $client = $this->client ?? app(StudentPortalClient::class);

        $filters = array_filter([
            'start_date' => $this->start_date,
            'end_date'   => $this->end_date,
        ]);

        $this->report = $client->fetchConsultantSchoolFees($filters);
      
    }

    public function render()
    {
        return view('livewire.bursary.consultant-school-fees-report');
    }
}
