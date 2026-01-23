<?php

namespace App\Livewire\Courses;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use PhpOffice\PhpSpreadsheet\IOFactory;


class BulkUploadCourses extends Component
{
    use WithFileUploads;

    /* =========================
     * STEP CONTROL
     * ========================= */
    public int $step = 1; // 1 = context, 2 = upload, 3 = preview

    /* =========================
     * CONTEXT (UI SELECTED)
     * ========================= */
    public ?int $program_type_id = null;
    public ?int $faculty_id = null;
    public ?int $department_id = null;
    public ?int $program_id = null;
    public ?int $start_session_id = null;
    public ?int $end_session_id = null;

    /* =========================
     * LOOKUPS
     * ========================= */
    public array $programTypes = [];
    public array $faculties = [];
    public array $departments = [];
    public array $programs = [];
    public array $sessions = [];

    /* =========================
     * FILE UPLOAD
     * ========================= */
    public $file;

    /* =========================
     * PARSED DATA (PREVIEW)
     * ========================= */
    public array $rows = [];
    public array $errors = [];

    protected function rules(): array
    {
    return [
        'file' => 'required|file|mimes:xlsx,csv|max:5120', // 5MB
    ];
   }


    /* =========================
     * LIFECYCLE
     * ========================= */
    public function mount(
        AdmissionsPortalClient $admissions,
        StudentPortalClient $studentClient
    ) {
        // Load static lookups
        $this->programTypes = $admissions->getProgramTypes() ?? [];
        $this->sessions = $admissions->getAcadSessions(true) ?? [];
    }

    /* =========================
     * CASCADING LOOKUPS
     * ========================= */
    public function updatedProgramTypeId(
        $value,
        AdmissionsPortalClient $admissions
    ) {
        $this->program_type_id = $value;
        $this->faculties = $value
            ? $admissions->getFaculties($value)
            : [];

        $this->departments = [];
        $this->programs = [];
        $this->faculty_id = $this->department_id = $this->program_id = null;
    }

    public function updatedFacultyId(
        $value,
        AdmissionsPortalClient $admissions
    ) {
        $this->department_id = $value;
        $this->departments = $value
            ? $admissions->getDepartments($value)
            : [];

        $this->programs = [];
        $this->department_id = $this->program_id = null;
    }

    public function updatedDepartmentId(
        $value,
        AdmissionsPortalClient $admissions
    ) {
        $this->programs = $value
            ? $admissions->getPrograms($value, $this->program_type_id)
            : [];

        $this->program_id = null;
    }

    /* =========================
     * STEP NAVIGATION
     * ========================= */
    public function goToUploadStep()
    {
        $this->validate([
            'program_type_id'   => 'required',
            'faculty_id'        => 'required',
            'department_id'     => 'required',
            'program_id'        => 'required',
            'start_session_id'  => 'required',
            'end_session_id'    => 'required',
        ]);

        $this->step = 2;
    }


    public function parseFile()
{
    $this->validate();

    $this->rows = [];
    $this->errors = [];
    $this->step = 2; // stay on upload step until successful

    try {
        $spreadsheet = IOFactory::load($this->file->getRealPath());

        // Prefer "courses" sheet, fallback to first sheet
        $sheet = $spreadsheet->getSheetByName('courses')
            ?? $spreadsheet->getActiveSheet();

        $rawRows = $sheet->toArray(null, true, true, true);

        if (count($rawRows) < 2) {
            $this->errors[] = 'The uploaded file has no data rows.';
            return;
        }

        // Extract headers
        $headers = array_map(
            fn ($h) => trim(strtolower($h)),
            array_shift($rawRows)
        );

        $requiredHeaders = [
            'course_code',
            'course_title',
            'credit_hours',
            'semester',
            'level_id',
            'elective',
            'category',
            'is_gst',
            'is_ume',
            'is_de',
            'is_al',
            'is_it',
        ];

        foreach ($requiredHeaders as $h) {
            if (! in_array($h, $headers, true)) {
                $this->errors[] = "Missing required column: {$h}";
                return;
            }
        }

        // Parse rows
        foreach ($rawRows as $index => $row) {
            $excelRow = $index + 2;

            $data = array_combine($headers, array_values($row));

            $normalized = $this->normalizeRow($data);
            $rowErrors = $this->validateRow($normalized);

            if (! empty($rowErrors)) {
                $this->errors[] = [
                    'row' => $excelRow,
                    'errors' => $rowErrors,
                ];
            }

            $this->rows[] = [
                'row' => $excelRow,
                'data' => $normalized,
                'valid' => empty($rowErrors),
            ];
        }

        $this->step = 3; // move to preview step

    } catch (\Throwable $e) {
        report($e);
        $this->errors[] = 'Unable to read the uploaded file.';
    }
}

protected function normalizeRow(array $row): array
{
    // Course code
    $row['course_code'] = strtoupper(trim($row['course_code']));

    // Title normalization
    $title = trim($row['course_title']);
    $title = str_replace('&', 'and', $title);

    $title = preg_replace_callback(
        '/\b([1-9]|10)\b/',
        fn ($m) => $this->numberToRoman((int) $m[1]),
        $title
    );

    $row['course_title'] = mb_convert_case($title, MB_CASE_TITLE, 'UTF-8');

    // Cast numerics
    $row['credit_hours'] = (int) $row['credit_hours'];
    $row['semester'] = (int) $row['semester'];
    $row['level_id'] = (int) $row['level_id'];

    // Cast booleans
    foreach (['elective','is_gst','is_ume','is_de','is_al','is_it'] as $flag) {
        $row[$flag] = ((int) $row[$flag] === 1) ? 1 : 0;
    }

    return $row;
}

protected function validateRow(array $row): array
{
    $errors = [];

    if (! $row['course_code']) {
        $errors[] = 'course_code is required';
    }

    if (! $row['course_title']) {
        $errors[] = 'course_title is required';
    }

    if ($row['credit_hours'] < 1) {
        $errors[] = 'credit_hours must be at least 1';
    }

    if (! in_array($row['semester'], [1, 2], true)) {
        $errors[] = 'semester must be 1 or 2';
    }

    if ($row['level_id'] < 1) {
        $errors[] = 'invalid level_id';
    }

    return $errors;
}

protected function validRows(): array
{
    return collect($this->rows)
        ->where('valid', true)
        ->pluck('data')
        ->values()
        ->toArray();
}


public function importCourses(StudentPortalClient $client)
{
    $rows = $this->validRows();

    if (empty($rows)) {
        $this->errors[] = 'No valid rows to import.';
        return;
    }

    $payload = [
        'program_id'        => $this->program_id,
        'program_type_id'   => $this->program_type_id,
        'start_session_id'  => $this->start_session_id,
        'end_session_id'    => $this->end_session_id,
        'courses'           => $rows,
    ];

    $resp = $client->bulkImportCourses($payload);

    if (empty($resp['success'])) {
        $this->errors[] = $resp['message'] ?? 'Import failed';
        return;
    }

    // Map API results back to preview rows
    foreach ($resp['results'] as $result) {
        foreach ($this->rows as &$row) {
            if ($row['row'] === $result['row']) {
                $row['import_status'] = $result['status'];
                $row['import_message'] = $result['message'] ?? null;
            }
        }
    }

    $this->step = 4; // Final summary step
}


protected function numberToRoman(int $number): string
{
    $map = [
        1 => 'I',
        2 => 'II',
        3 => 'III',
        4 => 'IV',
        5 => 'V',
        6 => 'VI',
        7 => 'VII',
        8 => 'VIII',
        9 => 'IX',
        10 => 'X',
    ];

    return $map[$number] ?? (string) $number;
}


    public function render()
    {
        return view('livewire.courses.bulk-upload-courses');
    }
}
