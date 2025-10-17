<?php

namespace App\Livewire\Bursary;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Collection;
use App\Services\Clients\AdmissionsPortalClient;

class AdmissionPaymentReport extends Component
{
    public $applicationTypes = [];
    public $faculties = [];
    public $selectedApplicationType = '';
    public $selectedFaculty = 'all';
    public $startDate;
    public $endDate;
     /** @var \Illuminate\Support\Collection */
    public $reports;
    public $loading = false;

    // Summary stats
    public $totalAmount = 0.0;
    public $totalRecords = 0;


    // Pagination
    public $perPage = 15;
    public $page = 1;

    public function mount(AdmissionsPortalClient $admissions): void
    {
        $this->applicationTypes = $admissions->getApplicationTypes();
        $this->faculties = $admissions->getFaculties();
    }

    public function loadReports()
    {


        if(empty($this->selectedApplicationType) || empty($this->startDate) || empty($this->endDate) || empty($this->selectedFaculty)) {
            Flux::toast('Please select Application Type, Start Date, and End Date.', variant: 'warning', position: 'top-right', duration: 4000);
            return;
        }


        $this->loading = true;
            //get application type name
    $applicationTypeName = $this->applicationTypes[$this->selectedApplicationType - 1] ?? 'Unknown';

        $params = [
            'application_type_id' => $this->selectedApplicationType,
            'faculty_id' => $this->selectedFaculty !== 'all' ? $this->selectedFaculty : null,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'application_type_name' => $applicationTypeName['name'] ?? 'Unknown',

        ];

        // Call Admissions API
        $response = app(AdmissionsPortalClient::class)->getPaymentReports($params);

        // Normalize as Collection for easy pagination
        $this->reports = collect($response ?? []);

        // Compute totals
        $this->totalRecords = $this->reports->count();
        $this->totalAmount = $this->reports->sum('amount');

        $this->loading = false;
    }

    public function nextPage()
    {
        if (($this->page * $this->perPage) < count($this->reports)) {
            $this->page++;
        }
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function getPaginatedReportsProperty()
    {
        return $this->reports
            ? $this->reports->forPage($this->page, $this->perPage)
            : collect();
    }


public function export($type)
{

      //get application type name
    $applicationTypeName = $this->applicationTypes[$this->selectedApplicationType - 1] ?? 'Unknown';
     $params = [
        'application_type_id' => $this->selectedApplicationType,
        'faculty_id' => $this->selectedFaculty !== 'all' ? $this->selectedFaculty : null,
        'start_date' => $this->startDate,
        'end_date' => $this->endDate,
        'application_type_name' => $applicationTypeName['name'] ?? 'Unknown',
    ];



    $query = http_build_query(array_filter($params));
    //dd($query);
     $url = match ($type) {
        'excel' => route('exports.admissions.export.excel') . '?' . $query,
        'pdf' => route('exports.admissions.export.pdf') . '?' . $query,
        default => null,
    };

    if ($url) {
        return redirect()->away($url);
    }
}
    public function render()
    {
        return view('livewire.bursary.admission-payment-report');
    }
}
