<?php

namespace App\Livewire\Admissions\EnrolledStudents;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Arr;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;

class Index extends Component
{

    use WithPagination;
    use WithFileUploads;

    public bool $showImport = false;
    public $csvFile;

    public string $search = '';
    public ?int $perPage = 10;
    public $page = 1;
    public bool $confirmDelete = false;
    public ?int $deleteId = null;

    public array $programTypes = [];
    public array $faculties = [];
    public array $departments = [];
    public array $programs = [];
    public array $acadSessions = [];
    public array $entryModes   = [];


    // Modal state
    public bool $showForm = false;
    public ?int $editId = null;
public array $form = [
    'regno' => '',
    'first_name' => '',
    'last_name' => '',
    'other_names' => '',
    'phone_no' => '',
    'gender' => '',
    'jamb_no' => null,   // still optional, will default NULL
    'jamb_score' => null,
    'program_id' => null,
    'program_type_id' => null,
    'faculty_id' => null,
    'department_id' => null,
    'acad_session_id' => null,
    'entry_mode_id' => null,
    'study_mode_id' => null, // 1=FT, 2=PT
    'is_migrated' => false,
];


    // Inline editing
    public ?int $inlineId = null;
    public array $inline = [];

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function mount(AdmissionsPortalClient $admissions): void
    {
        $this->programTypes = $admissions->getProgramTypes();
        $this->faculties    = $admissions->getFaculties();
        $this->departments  = [];
        $this->programs     = [];

    $this->acadSessions = $admissions->getAcadSessions();
    $this->entryModes   = $admissions->getEntryModes();

    }

    public function updatedFormFacultyId($facultyId)
    {
        $this->departments = app(AdmissionsPortalClient::class)->getDepartments($facultyId) ?? [];
        $this->form['department_id'] = null;
        $this->form['program_id'] = null;
    }

    public function updatedFormDepartmentId($departmentId)
    {
        $this->programs = app(AdmissionsPortalClient::class)->getPrograms(
            $departmentId,
            $this->form['program_type_id']
        ) ?? [];
        $this->form['program_id'] = null;
    }

    public function openCreate()
    {
         //dd('openCreate fired!');
        $this->reset(['editId']);
        $this->form = [
            'regno' => '',
            'jamb_no' => null,
            'jamb_score' => null,
            'program_id' => null,
            'program_type_id' => null,
            'faculty_id' => null,
            'department_id' => null,
            'acad_session_id' => null,
            'entry_mode_id' => null,

        ];
        $this->showForm = true;
    }

    public function openEdit(int $id)
    {
        $row = app(StudentPortalClient::class)->getEnrolled($id)['data'] ?? null;
        if (!$row) {
            $this->dispatch('toast', type: 'error', message: 'Record not found');
            return;
        }

        $this->editId = $id;
        $this->form = Arr::only($row, array_keys($this->form));
        $this->showForm = true;
    }


    public function save()
{
    // ✅ Validation rules
    $this->validate([
        'form.regno'           => 'required|string|max:50',
        'form.first_name'      => 'required|string|max:100',
        'form.last_name'       => 'required|string|max:100',
        'form.other_names'     => 'nullable|string|max:150',
        'form.phone_no'        => 'nullable|string|max:20',
        'form.gender'          => 'nullable|in:M,F',
        'form.jamb_score'      => 'nullable|integer|min:0|max:400',
        'form.program_type_id' => 'required|integer',
        'form.faculty_id'      => 'required|integer',
        'form.department_id'   => 'required|integer',
        'form.program_id'      => 'required|integer',
        'form.acad_session_id' => 'required|integer',
        'form.entry_mode_id'   => 'required|integer',
        'form.study_mode_id'   => 'nullable|integer|in:1,2', // 1=FT, 2=PT
    ]);

    $client  = app(StudentPortalClient::class);
    $payload = $this->form;

    // 🚀 Debug raw form payload
    Log::info('📦 Raw form payload received', $payload);

    /**
     * 🔹 Auto-derive start_session_id
     */
    if (!empty($payload['entry_mode_id'])) {
        $entryMode = collect($this->entryModes)
            ->firstWhere('id', (int) $payload['entry_mode_id']);

        if ($entryMode && !empty($entryMode['is_direct_entry']) && (int) $entryMode['is_direct_entry'] === 1) {
            $payload['start_session_id'] = max(1, ((int) $payload['acad_session_id']) - 1);
        } else {
            $payload['start_session_id'] = (int) $payload['acad_session_id'];
        }
    }

    /**
     * 🔹 Auto-fill school_division_id from program type
     */
    if (!empty($payload['program_type_id'])) {
        $programType = collect($this->programTypes)
            ->firstWhere('id', (int) $payload['program_type_id']);

        if ($programType && isset($programType['school_division_id'])) {
            $payload['school_division_id'] = (int) $programType['school_division_id'];
        }
    }

    // ❌ Remove screening_code before sending (generated by Student Portal)
    unset($payload['screening_code']);

    /**
     * 🔹 Ensure all foreign keys are integers
     */
    foreach (['program_id','program_type_id','faculty_id','department_id','acad_session_id',
              'entry_mode_id','study_mode_id','school_division_id','start_session_id'] as $key) {
        if (isset($payload[$key]) && $payload[$key] !== '') {
            $payload[$key] = (int) $payload[$key];
        }
    }

    /**
     * 🔹 Add bio-data explicitly
     */
    $payload = array_merge($payload, [
        'first_name'  => $this->form['first_name'] ?? '',
        'last_name'   => $this->form['last_name'] ?? '',
        'other_names' => $this->form['other_names'] ?? null,
        'phone_no'    => $this->form['phone_no'] ?? null,
        'gender'      => $this->form['gender'] ?? null,
    ]);

    // 🚀 Debug final transformed payload
    Log::info('🚀 Final payload to StudentPortalClient', [
        'action'  => $this->editId ? 'updateEnrolled' : 'createEnrolled',
        'payload' => $payload,
    ]);

    try {
        if ($this->editId) {
            $client->updateEnrolled($this->editId, $payload);
            Flux::toast('Updated Successfully', variant: 'success', position: 'top-right', duration: 4000);
        } else {
            $client->createEnrolled($payload);
            Flux::toast('Created Successfully', variant: 'success', position: 'top-right', duration: 4000);
        }
    } catch (\Throwable $e) {
        Log::error('❌ Failed to save enrolled student', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        Flux::toast('Save failed — check logs.', variant: 'error', position: 'top-right', duration: 4000);
    }

    // ✅ Reset
    $this->showForm = false;
    $this->reset(['editId', 'form']);
}


public function openDelete(int $id)
{
    $this->deleteId = $id;
    $this->confirmDelete = true;
}

public function cancelDelete()
{
    $this->deleteId = null;
    $this->confirmDelete = false;
}


    public function delete()
{
    if ($this->deleteId) {
        app(StudentPortalClient::class)->deleteEnrolled($this->deleteId);
        Flux::toast('Deleted Successfully', variant: 'success', position: 'top-right', duration: 4000);
    }

    $this->cancelDelete();
}


public function importCsv()
{
    $this->validate([
        'csvFile' => 'required|file|mimes:csv,txt|max:2048',
    ]);

    $path = $this->csvFile->getRealPath();
    $rows = array_map('str_getcsv', file($path));

    // ✅ First row is the header
    $header = array_map('trim', $rows[0]);
    unset($rows[0]); // drop header

    foreach ($rows as $row) {
        // skip empty lines
        if (count(array_filter($row)) === 0) continue;

        $data = array_combine($header, $row);

        // normalize values (treat "NULL" as null)
        foreach ($data as $k => $v) {
            if (is_string($v) && strtoupper(trim($v)) === 'NULL') {
                $data[$k] = null;
            }
        }

        // cast integer FKs
        foreach (['program_type_id','faculty_id','department_id','program_id','acad_session_id','entry_mode_id','study_mode_id'] as $key) {
            $data[$key] = !empty($data[$key]) ? (int)$data[$key] : null;
        }

        try {
            app(StudentPortalClient::class)->createEnrolled($data);
            Log::info('✅ CSV row imported', ['regno' => $data['regno']]);
        } catch (\Throwable $e) {
            Log::error('❌ CSV Import failed for row', [
                'row'   => $row,
                'error' => $e->getMessage(),
            ]);
        }
    }

    $this->showImport = false;

    Flux::toast('CSV import finished', variant: 'success', position: 'top-right', duration: 4000);
}





public function render()
{
    $params = [
        'search'   => $this->search ?: null,
        'per_page' => $this->perPage,
        'page'     => $this->page,
    ];

    $resp = app(StudentPortalClient::class)->listEnrolled($params);
    $items = $resp['data'] ?? [];
    $meta  = $resp['meta'] ?? [];
    $links = $resp['links'] ?? [];

    // Replace IDs with names using lookups already cached
    $faculties   = collect($this->faculties)->keyBy('id');
    $departments = collect($this->departments)->keyBy('id');
    $programs    = collect($this->programs)->keyBy('id');
    $acadSessions= collect($this->acadSessions)->keyBy('id');
    $entryModes  = collect($this->entryModes)->keyBy('id');

    foreach ($items as &$item) {
        $item['faculty']    = $faculties[$item['faculty_id']]['name'] ?? '—';
        $item['department'] = $departments[$item['department_id']]['name'] ?? '—';
        $item['program']    = $programs[$item['program_id']]['name'] ?? '—';
        $item['acad_session'] = $acadSessions[$item['acad_session_id']]['name'] ?? '—';
        $item['entry_mode']   = $entryModes[$item['entry_mode_id']]['name'] ?? '—';
    }
 //Log::info('🎯 EnrolledStudents UI items', ['count' => count($items)]);
    return view('livewire.admissions.enrolled-students.index', compact('items', 'meta','links'));
}

}
