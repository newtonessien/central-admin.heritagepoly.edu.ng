<?php

namespace App\Livewire\Reports;

use App\Services\Clients\StudentPortalClient;
use Carbon\Carbon;
use Livewire\Component;

class PortalServiceCharge extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public string $period = 'month';

    public string $startDate = '';

    public string $endDate = '';

    public string $groupBy = 'day';


    /*
    |--------------------------------------------------------------------------
    | Report
    |--------------------------------------------------------------------------
    */

    public array $summary = [];

    public array $breakdown = [];

    public bool $hasReport = false;


    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(): void
    {
        $this->setPeriod('month');
    }


    /*
    |--------------------------------------------------------------------------
    | Period Selection
    |--------------------------------------------------------------------------
    */

    public function setPeriod(string $period): void
    {
        $this->period = $period;

        $today = now();

        match ($period) {

            'today' => $this->setDates(
                $today,
                $today
            ),

            'week' => $this->setDates(
                $today->copy()->startOfWeek(),
                $today
            ),

            'month' => $this->setDates(
                $today->copy()->startOfMonth(),
                $today
            ),

            'custom' => null,

            default => $this->setDates(
                $today->copy()->startOfMonth(),
                $today
            ),
        };

        /*
        |--------------------------------------------------------------------------
        | Default Breakdown
        |--------------------------------------------------------------------------
        */

        $this->groupBy = match ($period) {

            'today' => 'day',

            'week' => 'day',

            'month' => 'day',

            default => 'day',

        };

        /*
        |--------------------------------------------------------------------------
        | Automatically Load Preset Reports
        |--------------------------------------------------------------------------
        */

        if ($period !== 'custom') {
            $this->fetchReport();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Set Dates
    |--------------------------------------------------------------------------
    */

    protected function setDates(
        Carbon $start,
        Carbon $end
    ): void {
        $this->startDate = $start->format('Y-m-d');

        $this->endDate = $end->format('Y-m-d');
    }


    /*
    |--------------------------------------------------------------------------
    | Fetch Report
    |--------------------------------------------------------------------------
    */

    public function fetchReport(): void
    {
        $this->validate([
            'startDate' => [
                'required',
                'date',
            ],

            'endDate' => [
                'required',
                'date',
                'after_or_equal:startDate',
            ],

            'groupBy' => [
                'required',
                'in:day,week,month',
            ],
        ]);


        $client = app(StudentPortalClient::class);


        $response = $client->getPortalServiceChargeReport([
            'start_date' => $this->startDate,

            'end_date' => $this->endDate,

            'group_by' => $this->groupBy,
        ]);


        $this->summary = $response['summary'] ?? [];

        $this->breakdown = $response['breakdown'] ?? [];

        $this->hasReport = true;
    }


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    public function resetReport(): void
    {
        $this->period = 'month';

        $this->groupBy = 'day';

        $this->setDates(
            now()->startOfMonth(),
            now()
        );

        $this->summary = [];

        $this->breakdown = [];

        $this->hasReport = false;
    }


    /*
    |--------------------------------------------------------------------------
    | Display Helpers
    |--------------------------------------------------------------------------
    */

    public function getFormattedStartDateProperty(): string
    {
        return $this->startDate
            ? Carbon::parse($this->startDate)->format('d M Y')
            : '-';
    }


    public function getFormattedEndDateProperty(): string
    {
        return $this->endDate
            ? Carbon::parse($this->endDate)->format('d M Y')
            : '-';
    }


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.reports.portal-service-charge'
        );
    }
}
