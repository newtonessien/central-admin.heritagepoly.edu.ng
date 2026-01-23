<?php

namespace App\Livewire\Courses;

use Livewire\Component;
use Flux\Flux;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManageCourses extends Component
{
public int $perPage = 10;
public int $page = 1;
public string $search = '';
public bool $showFilters = false;


/* =========================
* DATA LIST
* ========================= */
public array $courses = [];
public ?int $program_type_id = null;
public array $pagination = [];

public array $filters = [
'program_type_id' => null,
'program_id'      => null,
'level_id'        => null,
'semester'        => null,
'search'          => null,
'category'        => null,
];

/* =========================
* MODAL / FORM STATE
* ========================= */
public bool $showModal = false;
public bool $isEditing = false;
public ?int $editingId = null;

public array $form = [
'course_code'       => '',
'course_title'      => '',
'credit_hours'      => '',
'semester'          => '',
'level_id'          => '',
'program_id'        => '',
'program_type_id'   => '',
'elective'          => false,
'is_gst'            => false,
'is_ume'            => false,
'is_de'             => false,
'is_al'             => false,
'is_it'             => false,
'start_session_id'  => '',
'end_session_id'    => '',
'category'          => '',
];

/* =========================
* LOOKUPS (CASCADING)
* ========================= */
public array $programTypes = [];
public array $faculties    = [];
public array $departments  = [];
public array $programs     = [];
public array $levels       = [];
public array $sessions     = [];

/* =========================
* TEMP CASCADE STATE
* ========================= */
public ?int $faculty_id    = null;
public ?int $department_id = null;

/* =========================
* LIFECYCLE
* ========================= */
public function mount(
StudentPortalClient $studentClient,
AdmissionsPortalClient $admissionsClient
) {
$this->loadCourses($studentClient);
$this->loadStaticLookups($studentClient, $admissionsClient);
$this->loadProgramTypes($admissionsClient);
//dd($this->programTypes);
}

public function updatedPage(StudentPortalClient $client)
{
$this->loadCourses($client);
}



public function updatedFilters(StudentPortalClient $client)
{
$this->page = 1; // reset page
$this->loadCourses($client);
}

/* =========================
* COURSES LIST
* ========================= */
public function loadCourses(StudentPortalClient $client): void
{
$resp = $client->getCourses([
'with' => 'details',
'page' => $this->page,
'per_page' => $this->perPage,
...array_filter($this->filters),
]);

$this->courses = $resp['data'] ?? [];
$this->pagination = $resp['meta'] ?? [];
}

/* =========================
* CREATE / EDIT
* ========================= */
public function create(AdmissionsPortalClient $admissions)
{
$this->resetForm();
$this->isEditing = false;
$this->showModal = true;

$this->loadProgramTypes($admissions);
}

public function edit(array $course, AdmissionsPortalClient $admissions)
{

$this->resetForm();

$this->form = [
'course_code'       => $course['course_code'],
'course_title'      => $course['course_title'],
'credit_hours'      => $course['credit_hours'],
'semester'          => $course['semester'],
'level_id'          => $course['level']['id'] ?? null,
'program_id'        => $course['program']['id'] ?? null,
'program_type_id'   => $course['program_type']['id'] ?? null,
'elective'          => $course['elective'],
'is_gst'            => $course['flags']['is_gst'],
'is_ume'            => $course['flags']['is_ume'],
'is_de'             => $course['flags']['is_de'],
'is_al'             => $course['flags']['is_al'],
'is_it'             => $course['flags']['is_it'],
'start_session_id'  => $course['start_session']['id'] ?? null,
'end_session_id'    => $course['end_session']['id'] ?? null,
'category'          => $course['category'],
];

$this->editingId = $course['id'];
$this->isEditing = true;
$this->showModal = true;

// Load cascading lookups
$this->loadProgramTypes($admissions);

if ($this->form['program_type_id']) {
$this->updatedFormProgramTypeId($this->form['program_type_id'], $admissions);
}

if ($course['program']['faculty_id'] ?? null) {
$this->faculty_id = $course['program']['faculty_id'];
$this->updatedFacultyId($this->faculty_id, $admissions);
}

if ($course['program']['department_id'] ?? null) {
$this->department_id = $course['program']['department_id'];
$this->updatedDepartmentId($this->department_id, $admissions);
}
}

/* =========================
* SAVE / DELETE
* ========================= */
public function save(StudentPortalClient $client)
{
    // Capture intent ONCE (local, safe)
    $wasCreate = ! $this->isEditing;

    /* ===============================
     * NORMALIZE INPUT
     * =============================== */

    $this->form['course_code'] = strtoupper(
        preg_replace('/\s+/', '', trim($this->form['course_code']))
    );

    $title = trim($this->form['course_title']);
    $title = str_replace('&', 'and', $title);

    $title = preg_replace_callback(
        '/\b([1-9]|10)\b/',
        fn ($m) => $this->numberToRoman((int) $m[1]),
        $title
    );

    $this->form['course_title'] = mb_convert_case(
        $title,
        MB_CASE_TITLE,
        'UTF-8'
    );

    /* ===============================
     * VALIDATION
     * =============================== */

    $this->validate([
        'form.course_code'      => 'required|string',
        'form.course_title'     => 'required|string',
        'form.credit_hours'     => 'required|integer|max:15',
        'form.semester'         => 'required|in:1,2',
        'form.level_id'         => 'required',
        'form.program_type_id'  => 'required',
        'form.program_id'       => 'required',
        'form.start_session_id' => 'required',
        'form.end_session_id'   => 'required',
        'form.category'         => 'required|string',
        'form.elective'         => 'boolean',
    ]);

    /* ===============================
     * API CALL
     * =============================== */

    $resp = $this->isEditing
        ? $client->updateCourse($this->editingId, $this->form)
        : $client->createCourse($this->form);

    // Semantic success check (CRITICAL)
    if (
        empty($resp) ||
        (!isset($resp['id']) && !isset($resp['data']))
    ) {
        $message = is_array($resp) && isset($resp['message'])
            ? $resp['message']
            : 'Save failed';

        $this->addError('form.course_code', $message);
        return;
    }

    /* ===============================
     * UX: Auto-focus newly added course
     * =============================== */

    if ($wasCreate) {
        $this->filters['search'] = $this->form['course_code'];
        $this->page = 1;
    }

    Flux::toast(
        $wasCreate
            ? 'Course created successfully.'
            : 'Course updated successfully.',
        variant: 'success',
        position: 'top-right',
        duration: 4000
    );

    $this->showModal = false;
    $this->resetForm();
    $this->loadCourses($client);
}


public function deactivate(int $courseId, StudentPortalClient $client)
{
$client->deactivateCourse($courseId);

Flux::toast(
'Course deactivated successfully.',
variant: 'success',
position: 'top-right',
duration: 4000
);

$this->loadCourses($client);
}

/* =========================
* CASCADING LOOKUPS
* ========================= */
protected function loadProgramTypes(AdmissionsPortalClient $admissions): void
{
$this->programTypes = $admissions->getProgramTypes() ?? [];
}

protected function loadStaticLookups(
StudentPortalClient $studentClient,
AdmissionsPortalClient $admissionsClient
): void {
// Student Portal → Levels
$this->levels = $studentClient->getLevels() ?? [];

// Admissions Portal → Sessions
$this->sessions = $admissionsClient->getAcadSessions(true) ?? [];
}



public function updatedFormProgramTypeId($value, AdmissionsPortalClient $admissions)
{
$this->program_type_id = $value;
$this->faculties   = [];
$this->departments = [];
$this->programs    = [];
$this->faculty_id = $this->department_id = null;
$this->form['program_id'] = null;

if ($value) {
$this->faculties = $admissions->getFaculties($value) ?? [];
}
}

public function updatedFacultyId($value, AdmissionsPortalClient $admissions)
{

$this->departments = [];
$this->programs    = [];
$this->department_id = null;
$this->form['program_id'] = null;

if ($value) {
$this->departments = $admissions->getDepartments($value) ?? [];
}
}

public function updatedDepartmentId($value, AdmissionsPortalClient $admissions)
{
$this->programs = [];
$this->form['program_id'] = null;

if ($value) {
$this->programs = $admissions->getPrograms($value,$this->program_type_id) ?? [];
}
}

/* =========================
* HELPERS
* ========================= */
protected function resetForm(): void
{
$this->reset([
'form',
'editingId',
'isEditing',
'faculty_id',
'department_id',
'faculties',
'departments',
'programs',
]);
}

public function loadFilteredCourses(StudentPortalClient $client)
{
    $this->page = 1;

    $this->loadCourses($client);
}


protected function numberToRoman(int $number): string
{
    $map = [
        10 => 'X',
        9  => 'IX',
        8  => 'VIII',
        7  => 'VII',
        6  => 'VI',
        5  => 'V',
        4  => 'IV',
        3  => 'III',
        2  => 'II',
        1  => 'I',
    ];

    return $map[$number] ?? (string) $number;
}

// Cascading filter handlers
public function updatedFiltersProgramTypeId(
    $value,
       AdmissionsPortalClient $admissions
) {
    $this->program_type_id = $value;
    $this->faculties = $value
        ? $admissions->getFaculties($value)
        : [];

    $this->departments = [];
    $this->programs = [];
}

public function updatedFiltersFacultyId(
    $value,
    AdmissionsPortalClient $admissions
) {
    $this->departments = $value
        ? $admissions->getDepartments($value)
        : [];

    $this->programs = [];
}

public function updatedFiltersDepartmentId(
    $value,
    AdmissionsPortalClient $admissions
) {
    $this->programs = $value
        ? $admissions->getPrograms($value, $this->program_type_id)
        : [];
}

public function toggleFilters(): void
{
    $this->showFilters = ! $this->showFilters;
}


public function exportCourses(StudentPortalClient $client): StreamedResponse
{
    $resp = $client->getCourses([
        'with' => 'details',
        'per_page' => 10000, // safe upper bound
        ...array_filter($this->filters),
    ]);

    $courses = $resp['data'] ?? [];

    return response()->streamDownload(function () use ($courses) {
        $handle = fopen('php://output', 'w');

        // Header row
        fputcsv($handle, [
            'Course Code',
            'Course Title',
            'Credit Hours',
            'Program Type',
            'Program',
            'Level',
            'Semester',
            'Category',
            'Elective',
        ]);

        foreach ($courses as $c) {
            fputcsv($handle, [
                $c['course_code'],
                $c['course_title'],
                $c['credit_hours'],
                $c['program_type']['name'] ?? '',
                $c['program']['name'] ?? '',
                $c['level']['id'] ?? '',
                $c['semester'],
                $c['category'],
                $c['elective'] ? '1' : '0',
            ]);
        }

        fclose($handle);
    }, 'courses_export.csv');
}


public function render()
{
return view('livewire.courses.manage-courses');
}
}
