<?php

namespace App\Livewire\Students\Profile;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;

class PaymentHistory extends Component
{
    public string $regno;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public ?int $acadSessionId = null;

    public ?int $feePeriodId = null;

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public array $summary = [];

    public array $payments = [];

    public array $pagination = [];

     public array $sessions = [];

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    public int $page = 1;

    public int $perPage = 4;

    public function mount(string $regno): void
    {
        $this->regno = $regno;

        $this->loadPayments();
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public function updatedAcadSessionId(): void
    {
        $this->page = 1;

        $this->loadPayments();
    }

    public function updatedFeePeriodId(): void
    {
        $this->page = 1;

        $this->loadPayments();
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

public function gotoPage(int $page): void
{
    if (
        $page >= 1 &&
        $page <= ($this->pagination['last_page'] ?? 1)
    ) {
        $this->page = $page;

        $this->loadPayments();
    }
}

public function nextPage(): void
{
    if ($this->page < ($this->pagination['last_page'] ?? 1)) {

        $this->page++;

        $this->loadPayments();
    }
}

public function previousPage(): void
{
    if ($this->page > 1) {

        $this->page--;

        $this->loadPayments();
    }
}



    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    */

     public function exportPdf()
{
    return redirect()->route(
        'exports.student-payment-history.pdf',
        [
            'regno' => $this->regno,
            'acad_session_id' => $this->acadSessionId,
            'fee_period_id'   => $this->feePeriodId,
        ]
    );
}

    public function resetFilters(): void
    {
        $this->reset([
            'acadSessionId',
            'feePeriodId',

        ]);



        $this->loadPayments();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function loadPayments(): void
    {
        /** @var StudentPortalClient $client */
        $client = app(StudentPortalClient::class);

   
        $response = $client->getStudentPaymentHistory(
            $this->regno,
            [
                'acad_session_id' => $this->acadSessionId,
                'fee_period_id'   => $this->feePeriodId,
                'page'            => $this->page,
                'per_page'        => $this->perPage,
            ]
        );




        $this->summary = $response['summary'] ?? [];

        $this->payments = $response['data'] ?? [];

        $this->pagination = $response['pagination'] ?? [];

    }

    public function render()
    {
        return view('livewire.students.profile.payment-history');
    }
}
