<?php

namespace App\Livewire\Registration;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;

class TutorialList extends Component
{
    /* =========================================
     | Selected Filters
     * ========================================*/
    public ?int $program_type_id = null;
    public ?int $faculty_id      = null;
    public ?int $department_id   = null;
    public ?int $program_id      = null;

    public ?int $semester        = null;
    public ?int $level_id        = null;   // Course level
    public ?int $course_id       = null;
    public ?int $acad_session_id = null;

    /* =========================================
     | Lookup Data
     * ========================================*/
    public array $programTypes = [];
    public array $faculties    = [];
    public array $departments  = [];
    public array $programs     = [];
    public array $courses      = [];
    public array $sessions     = [];
    public array $levels       = [];

    /* =========================================
     | Results & Pagination
     * ========================================*/
    public array $students = [];
    public int $page = 1;
    public int $perPage = 30;
    public int $lastPage = 1;
    public int $total = 0;

    public bool $loading = false;

    /* =========================================
     | Mount (Load Initial Lookups Only)
     * ========================================*/
    public function mount(): void
    {
        try {
            $svc = app(AdmissionsPortalClient::class);

            $this->programTypes = $svc->getProgramTypes() ?? [];
            $this->faculties    = $svc->getFaculties() ?? [];
            $this->sessions     = $svc->getAcadSessions() ?? [];

            // If you have a levels endpoint, use it.
            // Otherwise fallback to static levels.
            $svcl = app(StudentPortalClient::class);
            $this->levels = method_exists($svcl, 'getLevels')
                ? ($svcl->getLevels() ?? [])
                : [
                    ['id' => 1, 'name' => '100'],
                    ['id' => 2, 'name' => '200'],
                    ['id' => 3, 'name' => '300'],
                    ['id' => 4, 'name' => '400'],
                    ['id' => 5, 'name' => '500'],
                ];

        } catch (\Throwable $e) {
            Log::error('Tutorial lookup preload failed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /* =========================================
     | Cascading Dropdown Logic
     * ========================================*/

    public function updatedProgramTypeId(): void
    {
        $this->resetDownstreamFromFaculty();
    }

    public function updatedFacultyId(): void
    {
        $this->department_id = null;
        $this->program_id = null;
        $this->semester = null;
        $this->level_id = null;
        $this->course_id = null;

        $this->departments = [];
        $this->programs = [];
        $this->courses = [];

        if ($this->faculty_id) {
            try {
                $svc = app(AdmissionsPortalClient::class);
                $this->departments = $svc->getDepartments((int)$this->faculty_id) ?? [];
            } catch (\Throwable $e) {
                Log::error('Departments load failed', ['error' => $e->getMessage()]);
            }
        }
    }

    public function updatedDepartmentId(): void
    {
        $this->program_id = null;
        $this->semester = null;
        $this->level_id = null;
        $this->course_id = null;

        $this->programs = [];
        $this->courses = [];

        if ($this->department_id) {
            try {
                $svc = app(AdmissionsPortalClient::class);
                $this->programs = $svc->getPrograms(
                    (int)$this->department_id,
                    $this->program_type_id
                ) ?? [];
            } catch (\Throwable $e) {
                Log::error('Programs load failed', ['error' => $e->getMessage()]);
            }
        }
    }

    public function updatedProgramId(): void
    {
        $this->semester = null;
        $this->level_id = null;
        $this->course_id = null;
        $this->courses = [];
    }

    public function updatedSemester(): void
    {
        $this->loadCoursesIfReady();
    }

    public function updatedLevelId(): void
    {
        $this->loadCoursesIfReady();
    }

    protected function loadCoursesIfReady(): void
    {
        if (!$this->program_id || !$this->semester || !$this->level_id) {
            $this->courses = [];
            $this->course_id = null;
            return;
        }

        try {
            $svc = app(StudentPortalClient::class);

            $this->courses = $svc->getCoursesFilter([
                'program_id' => $this->program_id,
                'semester'   => $this->semester,
                'level_id'   => $this->level_id,
            ]) ?? [];

        } catch (\Throwable $e) {
            Log::error('Course load failed', ['error' => $e->getMessage()]);
            $this->courses = [];
        }
    }

    protected function resetDownstreamFromFaculty(): void
    {
        $this->faculty_id = null;
        $this->department_id = null;
        $this->program_id = null;
        $this->semester = null;
        $this->level_id = null;
        $this->course_id = null;

        $this->departments = [];
        $this->programs = [];
        $this->courses = [];
    }

    /* =========================================
     | Load Tutorial List
     * ========================================*/
    public function loadTutorialList(): void
    {
        $this->validate([
            'course_id'       => 'required',
            'acad_session_id' => 'required',
            'semester'        => 'required',
            'level_id'        => 'required',
        ]);

        $this->loading = true;

        try {
            $response = app(StudentPortalClient::class)
                ->getTutorialList([
                    'course_id'       => $this->course_id,
                    'acad_session_id' => $this->acad_session_id,
                    'semester'        => $this->semester,
                    'program_type_id' => $this->program_type_id,
                    'faculty_id'      => $this->faculty_id,
                    'department_id'   => $this->department_id,
                    'program_id'      => $this->program_id,
                    'level_id'        => $this->level_id,   // ✅ Included
                    'page'            => $this->page,
                    'per_page'        => $this->perPage,
                ]);

            $this->students = $response['students'] ?? [];
            $this->total    = $response['total'] ?? 0;
            $this->page     = $response['current_page'] ?? 1;
            $this->lastPage = $response['last_page'] ?? 1;

        } catch (\Throwable $e) {
            Log::error('Tutorial list load failed', [
                'error' => $e->getMessage()
            ]);
            $this->students = [];
            $this->total = 0;
        } finally {
            $this->loading = false;
        }
    }

    /* =========================================
     | Pagination
     * ========================================*/
    public function gotoPage(int $page): void
    {
        if ($page < 1 || $page > $this->lastPage) {
            return;
        }

        $this->page = $page;
        $this->loadTutorialList();
    }

    public function nextPage(): void
    {
        if ($this->page < $this->lastPage) {
            $this->page++;
            $this->loadTutorialList();
        }
    }

    public function prevPage(): void
    {
        if ($this->page > 1) {
            $this->page--;
            $this->loadTutorialList();
        }
    }

    public function render()
    {
        return view('livewire.registration.tutorial-list');
    }
}
