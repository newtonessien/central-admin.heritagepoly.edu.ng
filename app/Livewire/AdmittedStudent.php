<?php

namespace App\Livewire;

use Livewire\Component;
use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Barryvdh\DomPDF\Facade\Pdf;


class AdmittedStudent extends Component
{

   public ?string $selectedAcadSessionName = null;
public ?string $selectedProgramTypeName = null;
public ?string $selectedFacultyName     = null;
public ?string $selectedDepartmentName  = null;
public ?string $selectedProgramName     = null;



    // Filters
    public ?int $program_type_id = null;
    public ?int $faculty_id      = null;
    public ?int $department_id   = null;
    public ?int $program_id      = null;

    // Options
    public array $programTypes = [];
    public array $faculties    = [];
    public array $departments  = [];
    public array $programs     = [];

    // Results + state
    public array $students = [];
    public bool  $loading  = false;

    // Pagination
    public int $page = 1;
    public int $lastPage = 1;
    public int $perPage = 25;
    public int $total = 0;

    public function mount(AdmissionsPortalClient $admissions): void
    {
        $this->programTypes = $admissions->getProgramTypes();
        $this->faculties    = $admissions->getFaculties();
        $this->departments  = [];
        $this->programs     = [];
    }

    /** Cast + cascade loaders **/
    public function updatedProgramTypeId($value): void
    {
        $this->program_type_id = $value ? (int) $value : null;
        // reset downstream program list if program type changes
        $this->programs = [];
        $this->program_id = null;
    }

    public function updatedFacultyId($value): void
    {
        $this->faculty_id = $value ? (int) $value : null;

        if ($this->faculty_id) {
            $this->departments = app(AdmissionsPortalClient::class)->getDepartments($this->faculty_id);
        } else {
            $this->departments = [];
        }

        $this->department_id = null;
        $this->programs = [];
        $this->program_id = null;
    }

    public function updatedDepartmentId($value): void
    {
        $this->department_id = $value ? (int) $value : null;

        if ($this->department_id && $this->program_type_id) {
            $this->programs = app(AdmissionsPortalClient::class)
                ->getPrograms($this->department_id, $this->program_type_id);
        } else {
            $this->programs = [];
        }

        $this->program_id = null;
    }

    public function updatedProgramId($value): void
    {
        $this->program_id = $value ? (int) $value : null;
    }

    /** Fetch admitted students */
    public function filterStudents(): void
    {
             logger()->info('FilterStudents called', [
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'page'            => $this->page,
         'per_page'        => $this->perPage,  // 👈 this line must be here
    ]);


        if (!$this->program_type_id) {
            $this->students = [];
            $this->page = 1;
            $this->lastPage = 1;
            $this->total = 0;
            return;
        }

        $this->loading = true;

        try {
            $filters = array_filter([
                'program_type_id' => $this->program_type_id,
                'faculty_id'      => $this->faculty_id,
                'department_id'   => $this->department_id,
                'program_id'      => $this->program_id,
                'with'            => 'details',
                'page'            => $this->page,
                'per_page'        => $this->perPage,
            ], fn($v) => $v !== null && $v !== '');

            $resp = app(StudentPortalClient::class)->getAdmittedStudents($filters);
            logger()->info('Student API response', $resp);
            $this->students = $resp['data'] ?? [];
            logger()->info('Students assigned', ['count' => count($this->students)]);

            $this->selectedProgramTypeName = collect($this->programTypes)
    ->firstWhere('id', $this->program_type_id)['name'] ?? null;

$this->selectedFacultyName = collect($this->faculties)
    ->firstWhere('id', $this->faculty_id)['name'] ?? null;

$this->selectedDepartmentName = collect($this->departments)
    ->firstWhere('id', $this->department_id)['name'] ?? null;

$this->selectedProgramName = collect($this->programs)
    ->firstWhere('id', $this->program_id)['name'] ?? null;

// Acad session is always “latest” or passed filter
$this->selectedAcadSessionName = $this->students['data'][0]['acad_session'] ?? null;

            $data = $resp['data'] ?? [];
            $meta = $resp['meta'] ?? [];
            $this->students  = is_array($data) ? $data : [];
            $this->page      = (int)($meta['current_page'] ?? 1);
            $this->lastPage  = (int)($meta['last_page'] ?? 1);
            $this->total     = (int)($meta['total'] ?? count($this->students));
            $this->perPage   = (int)($meta['per_page'] ?? $this->perPage);

        } finally {
            $this->loading = false;
        }
    }

    /** Pagination actions */
 public function nextPage(): void
{
    if ($this->page < $this->lastPage) {
        $this->page++;
        $this->filterStudents();
    }
}

public function previousPage(): void
{
    if ($this->page > 1) {
        $this->page--;
        $this->filterStudents();
    }
}


    public function gotoPage(int $p): void
    {
        $p = max(1, min($p, $this->lastPage));
        if ($p !== $this->page) {
            $this->page = $p;
            $this->filterStudents();
        }
    }


public function exportExcel()
{
    $filters = array_filter([
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'with'            => 'details',
        'per_page'        => 1000,
    ]);

    $resp = app(\App\Services\Clients\StudentPortalClient::class)
        ->getAdmittedStudents($filters);

    $students = $resp['data'] ?? [];

    return response()->streamDownload(function () use ($students) {
        $writer = SimpleExcelWriter::streamDownload('admitted-students.xlsx');

        // 🔹 Add filter summary
        $writer->addRow(['Export: Admitted Students']);
        $writer->addRow(['Academic Session', $this->selectedAcadSessionName ?? '—']);
        $writer->addRow(['Program Type', $this->selectedProgramTypeName ?? '—']);
        $writer->addRow(['Faculty', $this->selectedFacultyName ?? '—']);
        $writer->addRow(['Department', $this->selectedDepartmentName ?? '—']);
        $writer->addRow(['Program', $this->selectedProgramName ?? '—']);
        $writer->addRow([]); // blank line before table

        // 🔹 Add header row
        $writer->addHeader([
            'RegNo', 'Name', 'Sex', 'Program Type',
            'Faculty', 'Department', 'Program', 'Screening Code'
        ]);

        foreach ($students as $s) {
            $writer->addRow([
                $s['regno'] ?? '—',
                $s['name'] ?? '—',
                $s['sex'] ?? '—',
                $s['program_type'] ?? '—',
                $s['faculty'] ?? '—',
                $s['department'] ?? '—',
                $s['program'] ?? '—',
                $s['screening_code'] ?? '—',
            ]);
        }

        $writer->close();
    }, 'admitted-students.xlsx');
}

public function exportPdf()
{
    $filters = array_filter([
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'with'            => 'details',
        'per_page'        => 1000,
    ]);

    $resp = app(\App\Services\Clients\StudentPortalClient::class)
        ->getAdmittedStudents($filters);

    $students = $resp['data'] ?? [];

    $pdf = Pdf::loadView('exports.admitted-student', [
        'students'     => $students,
        'acadSession'  => $this->selectedAcadSessionName ?? '—',
        'programType'  => $this->selectedProgramTypeName ?? '—',
        'faculty'      => $this->selectedFacultyName ?? '—',
        'department'   => $this->selectedDepartmentName ?? '—',
        'program'      => $this->selectedProgramName ?? '—',
    ])->setPaper('a4', 'landscape');

    return response()->streamDownload(
        fn () => print($pdf->output()),
        'admitted-students.pdf'
    );
}





protected function currentFilters(): array
{
    return array_filter([
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'per_page'        => $this->perPage ?? 25,
        'page'            => $this->page ?? 1,
    ]);
}






    public function render()
    {
        return view('livewire.admitted-student');
    }
}
