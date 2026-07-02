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
public ?int $acad_session_id = null;
public ?int $program_type_id = null;
public ?int $faculty_id      = null;
public ?int $department_id   = null;
public ?int $program_id      = null;
public ?string $start_date = null;
public ?string $end_date = null;
public $showFilters = true; // Default to showing filters

// Options
public array $programTypes = [];
public array $faculties    = [];
public array $departments  = [];
public array $programs     = [];

// Results + state
public array $students = [];
public bool  $loading  = false;
public array $acadSessions = [];

// UI messages
public bool $searched = false;
public ?string $infoMessage = null;
protected int $minimumAcadSessionId = 16;

// Pagination
public int $page = 1;
public int $lastPage = 1;
public int $perPage = 25;
public int $total = 0;

public function mount(AdmissionsPortalClient $admissions): void
{

$this->acadSessions = collect($admissions->getAcadSessions())
    ->where('id', '>=', $this->minimumAcadSessionId)
    ->values()
    ->toArray();
$this->programTypes = $admissions->getProgramTypes();
$this->faculties    = $admissions->getFaculties();
$this->departments  = [];
$this->programs     = [];

}

public function updatedAcadSessionId($value): void
{
$this->acad_session_id = $value ? (int) $value : null;

$this->program_type_id = null;
$this->faculty_id = null;
$this->department_id = null;
$this->program_id = null;

$this->programs = [];
$this->departments = [];
$this->students = [];

// Reset pagination whenever session changes
$this->page = 1;
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

$this->searched = true;
$this->infoMessage = null;

logger()->info('FilterStudents called', [
'acad_session_id' => $this->acad_session_id,
'program_type_id' => $this->program_type_id,
'faculty_id'      => $this->faculty_id,
'department_id'   => $this->department_id,
'program_id'      => $this->program_id,
'page'            => $this->page,
'per_page'        => $this->perPage,  // 👈 this line must be here
]);


if (!$this->program_type_id || !$this->acad_session_id) {
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
'acad_session_id' => $this->acad_session_id,

'start_date'      => $this->start_date,
'end_date'        => $this->end_date,

'with'            => 'details',
'page'            => $this->page,
'per_page'        => $this->perPage,
], fn($v) => $v !== null && $v !== '');

$resp = app(StudentPortalClient::class)->getAdmittedStudents($filters);
logger()->info('Student API response', $resp);

$this->students = $resp['data'] ?? [];

if (empty($this->students)) {

    if ($this->faculty_id || $this->department_id || $this->program_id) {

        $this->infoMessage =
            "No admitted students match the selected filters. Try selecting another Faculty, Department or Program.";

    } else {

        $this->infoMessage =
            "No students have been admitted for the selected Academic Session and Program Type.";
    }

} else {

    $this->infoMessage = null;
}

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
//$this->selectedAcadSessionName = $this->students['data'][0]['acad_session'] ?? null;

$this->selectedAcadSessionName = collect($this->acadSessions)
->firstWhere('id', $this->acad_session_id)['name'] ?? null;

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

  $this->showFilters = false; // Auto-hide filters after search

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
        'acad_session_id' => $this->acad_session_id,
        'program_type_id' => $this->program_type_id,
        'faculty_id'      => $this->faculty_id,
        'department_id'   => $this->department_id,
        'program_id'      => $this->program_id,
        'start_date'      => $this->start_date,
        'end_date'        => $this->end_date,
        'with'            => 'details',
        'per_page'        => 1000,
    ]);

    $resp = app(StudentPortalClient::class)
        ->getAdmittedStudents($filters);

    $students = $resp['data'] ?? [];

    return response()->streamDownload(function () use ($students) {

        $writer = SimpleExcelWriter::streamDownload('admitted-students.xlsx');

        /*
        |--------------------------------------------------------------------------
        | REPORT HEADER
        |--------------------------------------------------------------------------
        */

        $writer->addRow(['HERITAGE POLYTECHNIC']);
        $writer->addRow(['ADMITTED STUDENTS SCREENING REPORT']);
        $writer->addRow(['Generated On', now()->format('d F, Y h:i A')]);
        $writer->addRow([]);

        /*
        |--------------------------------------------------------------------------
        | REPORT INFORMATION
        |--------------------------------------------------------------------------
        */

        $writer->addRow(['REPORT INFORMATION']);

        $writer->addRow([
            'Academic Session',
            $this->selectedAcadSessionName ?? 'All'
        ]);

        $writer->addRow([
            'Program Type',
            $this->selectedProgramTypeName ?? 'All'
        ]);

        $writer->addRow([
            'Faculty',
            $this->selectedFacultyName ?? 'All'
        ]);

        $writer->addRow([
            'Department',
            $this->selectedDepartmentName ?? 'All'
        ]);

        $writer->addRow([
            'Program',
            $this->selectedProgramName ?? 'All'
        ]);

        $writer->addRow([
            'Start Date',
            $this->start_date ?: 'All'
        ]);

        $writer->addRow([
            'End Date',
            $this->end_date ?: 'All'
        ]);

        $writer->addRow([
            'Total Students',
            count($students)
        ]);

        $writer->addRow([]);
        $writer->addRow([]);

        /*
        |--------------------------------------------------------------------------
        | STUDENT LIST
        |--------------------------------------------------------------------------
        */

        $writer->addHeader([
            'SN',
            'Registration No',
            'Student Name',
            'Sex',
            'Program Type',
            'Faculty',
            'Department',
            'Program',
            'Screening Code',
        ]);

        foreach ($students as $index => $student) {

            $writer->addRow([
                $index + 1,
                $student['regno'] ?? '',
                ucwords(strtolower($student['name'] ?? '')),
                $student['sex'] ?? '',
                $student['program_type'] ?? '',
                ucwords(strtolower($student['faculty'] ?? '')),
                ucwords(strtolower($student['department'] ?? '')),
                ucwords(strtolower($student['program'] ?? '')),
                $student['screening_code'] ?? '',
            ]);
        }

        $writer->close();

    }, 'admitted-students.xlsx');
}

public function exportPdf()
{
$filters = array_filter([
'acad_session_id' => $this->acad_session_id,
'program_type_id' => $this->program_type_id,
'faculty_id'      => $this->faculty_id,
'department_id'   => $this->department_id,
'program_id'      => $this->program_id,

'start_date'      => $this->start_date,
'end_date'        => $this->end_date,

'with'            => 'details',
'per_page'        => 1000,
]);

$resp = app(StudentPortalClient::class)
->getAdmittedStudents($filters);

$students = $resp['data'] ?? [];

$pdf = Pdf::loadView('exports.admitted-student', [
    'students'     => $students,
    'acadSession'  => $this->selectedAcadSessionName ?? '2026/2027',
    'programType'  => $this->selectedProgramTypeName ?? '—',
    'faculty'      => $this->selectedFacultyName ?? '—',
    'department'   => $this->selectedDepartmentName ?? '—',
    'program'      => $this->selectedProgramName ?? '—',
    'start_date'   => $this->start_date,
    'end_date'     => $this->end_date,
])
->setPaper('a4', 'landscape');

// Get DomPDF instance
$domPdf = $pdf->getDomPDF();

// Render the document first
$domPdf->render();

// Add page numbers
$canvas = $domPdf->getCanvas();

$font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');

$canvas->page_text(
    700,     // X position (adjust for landscape)
    570,     // Y position
    "Page {PAGE_NUM} of {PAGE_COUNT}",
    $font,
    9,
    [0, 0, 0]
);

return response()->streamDownload(
    fn () => print($domPdf->output()),
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

'start_date'      => $this->start_date,
'end_date'        => $this->end_date,

'acad_session_id' => $this->acad_session_id,
'per_page'        => $this->perPage ?? 25,
'page'            => $this->page ?? 1,
]);
}



public function showFilters()
{
    $this->showFilters = true;
}


public function render()
{
return view('livewire.admitted-student');
}
}
