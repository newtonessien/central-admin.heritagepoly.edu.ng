<?php

namespace App\Livewire\Students\Profile;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;

class CourseRegistrationHistory extends Component
{
    public string $regno;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public ?int $acadSessionId = null;

    public ?int $feePeriodId = null;

    public ?int $levelId = null;

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public array $summary = [];

    public array $registrations = [];

    public array $sessions = [];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(string $regno): void
    {
        $this->regno = $regno;

        $this->loadRegistrationHistory();
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Updates
    |--------------------------------------------------------------------------
    */

    public function updatedAcadSessionId(): void
    {
        $this->loadRegistrationHistory();
    }

    public function updatedFeePeriodId(): void
    {
        $this->loadRegistrationHistory();
    }

    public function updatedLevelId(): void
    {
        $this->loadRegistrationHistory();
    }

    /*
    |--------------------------------------------------------------------------
    | Load Registration History
    |--------------------------------------------------------------------------
    */

    protected function loadRegistrationHistory(): void
    {
        /** @var StudentPortalClient $client */
        $client = app(StudentPortalClient::class);

     
        $response = $client->getCourseRegistrationHistory(
            $this->regno,
            [
                'acad_session_id' => $this->acadSessionId,
                'semester'        => $this->feePeriodId,
                'level_id'        => $this->levelId,
            ]
        );

        $this->summary = $response['summary'] ?? [];

        $this->registrations = $response['data'] ?? [];
    }

    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    public function resetFilters(): void
    {
        $this->reset([
            'acadSessionId',
            'feePeriodId',
            'levelId',
        ]);

        $this->loadRegistrationHistory();
    }


    public function exportPdf()
{
    return redirect()->route(
        'exports.course-registration-history.pdf',
        array_filter([
            'regno'           => $this->regno,
            'acad_session_id' => $this->acadSessionId,
            'semester'        => $this->feePeriodId,
            'level_id'        => $this->levelId,
        ], fn ($value) => $value !== null && $value !== '')
    );
}

    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    public function render()
    {
        return view(
            'livewire.students.profile.course-registration-history'
        );
    }
}
