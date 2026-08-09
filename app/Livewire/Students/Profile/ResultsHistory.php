<?php

namespace App\Livewire\Students\Profile;

use App\Services\Clients\RmsClient;
use Livewire\Component;

class ResultsHistory extends Component
{
    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    */

    public string $regno;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public ?int $acadSessionId = null;

    public ?int $semester = null;

    public ?int $levelId = null;

    public string $status = '';

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    public array $summary = [];

    public array $results = [];

    public array $sessions = [];

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    */

    public function mount(
        string $regno,
        array $sessions = []
    ): void {
        $this->regno = $regno;

        $this->sessions = $sessions;

        $this->loadResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Filter Updates
    |--------------------------------------------------------------------------
    */

    public function updatedAcadSessionId(): void
    {
        $this->loadResults();
    }

    public function updatedSemester(): void
    {
        $this->loadResults();
    }

    public function updatedLevelId(): void
    {
        $this->loadResults();
    }

    public function updatedStatus(): void
    {
        $this->loadResults();
    }

    /*
    |--------------------------------------------------------------------------
    | Load Results
    |--------------------------------------------------------------------------
    */

    protected function loadResults(): void
    {
        /** @var RmsClient $client */
        $client = app(RmsClient::class);

        $filters = [
            'status' => $this->status,
            'acad_session_id' => $this->acadSessionId,
            'semester' => $this->semester,
            'level_id' => $this->levelId,
        ];

        /*
        |--------------------------------------------------------------------------
        | Remove Empty Filters
        |--------------------------------------------------------------------------
        */

        $filters = array_filter(
            $filters,
            fn ($value) => $value !== null && $value !== ''
        );

        /*
        |--------------------------------------------------------------------------
        | Request RMS Results
        |--------------------------------------------------------------------------
        */

        $response = $client->getStudentResults(
            $this->regno,
            $filters
        );

        $rawResults = $response['results'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $this->results = collect($rawResults)
            ->map(fn ($result) => $this->transformResult($result))
            ->values()
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $this->summary = $this->buildSummary(
            $this->results
        );
    }



    /*
    |--------------------------------------------------------------------------
    | Transform Result
    |--------------------------------------------------------------------------
    */

    protected function transformResult(array $result): array
    {
        return [
            'id' => $result['id'] ?? null,

            'matric_no' => $result['matric_no'] ?? null,

            'student_id' => $result['student_id'] ?? null,

            'course_id' => $result['course_id'] ?? null,

            'course_code' => $result['course']['course_code'] ?? '-',

            'course_title' => $result['course']['course_title'] ?? '-',

            'credit_hours' => (int) (
                $result['course']['credit_hours'] ?? 0
            ),

            'semester' => $result['semester'] ?? null,

            'acad_session_id' => $result['acad_session_id'] ?? null,

            'session' => $result['academic_session']['name'] ?? '-',

            'level_id' => $result['level_id'] ?? null,

            'level' => $result['level']['name'] ?? '-',

            'ca_score' => $result['ca_score'],

            'exam_score' => $result['exam_score'],

            'total_score' => $result['total_score'],

            'grade' => $result['grade'] ?? '-',

            'grade_point' => $result['grade_point'],

            'remarks' => $result['remarks'] ?? null,

            'status' => $result['status'] ?? null,

            'is_published' => (bool) (
                $result['is_published'] ?? false
            ),

            'approved_at' => $result['approved_at'] ?? null,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    protected function buildSummary(array $results): array
    {
        $collection = collect($results);

        $sessions = $collection
            ->pluck('acad_session_id')
            ->filter()
            ->unique()
            ->count();

        $semesters = $collection
            ->map(function ($result) {
                return ($result['acad_session_id'] ?? null)
                    . '-'
                    . ($result['semester'] ?? null);
            })
            ->filter()
            ->unique()
            ->count();

        $courses = $collection->count();

        $units = $collection->sum(
            fn ($result) => (int) ($result['credit_hours'] ?? 0)
        );

        return [
            'sessions' => $sessions,
            'semesters' => $semesters,
            'courses' => $courses,
            'units' => $units,
        ];
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
            'semester',
            'levelId',
        ]);

        /*
         * Published remains the default status.
         */

        $this->status = '';

        $this->loadResults();
    }

    public function exportPdf()
{
    return redirect()->route(
        'exports.results-history.pdf',
        array_filter([
            'regno' => $this->regno,
            'acad_session_id' => $this->acadSessionId,
            'semester' => $this->semester,
            'level_id' => $this->levelId,
            'status' => $this->status,
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
            'livewire.students.profile.results-history'
        );
    }
}
