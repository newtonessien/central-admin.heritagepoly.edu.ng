<?php

namespace App\Livewire\Bursary;

use Livewire\Component;
use Illuminate\Support\Facades\Log;
use Flux\Flux;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;

class ProgramTypeFeeItemAmountManager extends Component
{
    public $programTypes = [];
    public $faculties = [];
    public $departments = []; // Add departments
    public $levels = [];
    public $acadSessions = []; // Add academic sessions
    public $feeItems = [];
    public $records = [];

    // Add pagination properties
    public $currentPage = 1;
    public $lastPage = 1;
    public $perPage = 25;
    public $total = 0;
    public $from = 0;
    public $to = 0;

    public $filters = [
        'program_type_id' => '',
        'faculty_id' => '',
        'department_id' => '', // Add department filter
        'level_id' => '',
        'acad_session_id' => '', // Add academic session filter
    ];

    public $form = [
        'id' => null,
        'program_type_id' => '',
        'fee_item_id' => '',
        'faculty_id' => '',
        'department_id' => '', // Add department
        'level_id' => '',
        'acad_session_id' => '', // Add academic session
        'first_semester_amount' => '',
        'second_semester_amount' => '',
    ];

    public $showForm = false;

    protected $studentClient;
    protected $admissionClient;

    public function mount()
    {
        // Initialize clients in mount
        $this->studentClient = app(StudentPortalClient::class);
        $this->admissionClient = app(AdmissionsPortalClient::class);

        $this->loadDropdownData();
        $this->fetchRecords();




    }

    protected function ensureClientsInitialized()
    {
        if (!$this->studentClient) {
            $this->studentClient = app(StudentPortalClient::class);
        }
        if (!$this->admissionClient) {
            $this->admissionClient = app(AdmissionsPortalClient::class);
        }
    }

    protected function loadDropdownData()
    {
        try {
            $this->ensureClientsInitialized();

            Log::info('Loading dropdown data...');

            // Get raw data from APIs
            $rawProgramTypes = $this->admissionClient->getProgramTypes();
            $rawFaculties = $this->admissionClient->getFaculties();
            $rawLevels = $this->studentClient->getLevels();
            //$rawFeeItems = $this->studentClient->fetchFeeItems();
            $rawAcadSessions = $this->admissionClient->getAcadSessions(); // Add academic sessions

             // Handle paginated response from fetchFeeItems
        $feeItemsResponse = $this->studentClient->fetchFeeItems();

            Log::info('Raw data fetched', [
                'program_types_count' => is_countable($rawProgramTypes) ? count($rawProgramTypes) : 0,
                'faculties_count' => is_countable($rawFaculties) ? count($rawFaculties) : 0,
                'levels_count' => is_countable($rawLevels) ? count($rawLevels) : 0,
                //'fee_items_count' => is_countable($rawFeeItems) ? count($rawFeeItems) : 0,
                'fee_items_response' => $feeItemsResponse, // Log the full response to see structure
                'acad_sessions_count' => is_countable($rawAcadSessions) ? count($rawAcadSessions) : 0,
            ]);

            // Transform data to ensure consistent structure
            $this->programTypes = $this->transformDropdownData($rawProgramTypes, 'Program Types');
            $this->faculties = $this->transformDropdownData($rawFaculties, 'Faculties');
            $this->levels = $this->transformDropdownData($rawLevels, 'Levels');
            //$this->feeItems = $this->transformDropdownData($rawFeeItems, 'Fee Items');
             // Extract fee items from paginated response
        $feeItemsData = $feeItemsResponse['data'] ?? $feeItemsResponse;
        $this->feeItems = $this->transformDropdownData($feeItemsData, 'Fee Items');

            $this->acadSessions = $this->transformDropdownData($rawAcadSessions, 'Academic Sessions');
            $this->departments = []; // Initialize empty, will load based on faculty selection
                  Log::info('Dropdown data transformed', [
                'program_types' => count($this->programTypes),
                'faculties' => count($this->faculties),
                'levels' => count($this->levels),
                'fee_items' => count($this->feeItems),
                'acad_sessions' => count($this->acadSessions),
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to load dropdown data', ['error' => $e->getMessage()]);
            // Initialize empty arrays to prevent errors
            $this->programTypes = [];
            $this->faculties = [];
            $this->levels = [];
            $this->feeItems = [];
            $this->acadSessions = [];
            $this->departments = [];

            Flux::toast("Failed to load dropdown data: " . $e->getMessage(), variant: 'destructive');
        }
    }

    // Add method to load departments when faculty changes
    public function updatedFormFacultyId($facultyId)
    {

        if ($facultyId) {
            try {
                $this->ensureClientsInitialized();
                $rawDepartments = $this->admissionClient->getDepartments($facultyId);
                $this->departments = $this->transformDropdownData($rawDepartments, 'Departments');

                Log::info('Departments loaded for faculty', [
                    'faculty_id' => $facultyId,
                    'departments_count' => count($this->departments)
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to load departments', [
                    'faculty_id' => $facultyId,
                    'error' => $e->getMessage()
                ]);
                $this->departments = [];
            }
        } else {
            $this->departments = [];
            $this->form['department_id'] = '';
        }
    }

    // Also update when filter faculty changes
    public function updatedFiltersFacultyId($facultyId)
    {
        if ($facultyId) {
            try {
                $this->ensureClientsInitialized();
                $rawDepartments = $this->admissionClient->getDepartments($facultyId);
                $this->departments = $this->transformDropdownData($rawDepartments, 'Departments');

                Log::info('Departments loaded for filter faculty', [
                    'faculty_id' => $facultyId,
                    'departments_count' => count($this->departments)
                ]);
            } catch (\Throwable $e) {
                Log::error('Failed to load departments for filter', [
                    'faculty_id' => $facultyId,
                    'error' => $e->getMessage()
                ]);
                $this->departments = [];
            }
        } else {
            $this->departments = [];
            $this->filters['department_id'] = '';
        }
    }

    public function fetchRecords()
    {
        try {
            $this->ensureClientsInitialized();

            // Clean filters - remove empty values
            $cleanFilters = array_filter($this->filters, function($value) {
                return $value !== '' && $value !== null;
            });

            // Always include pagination in the request
            $cleanFilters['page'] = $this->currentPage;

            Log::info('Fetching records with filters', [
                'filters' => $cleanFilters,
                'has_filters' => !empty($cleanFilters)
            ]);

            $response = $this->studentClient->fetchProgramTypeFeeItemAmounts($cleanFilters);

            Log::info('API Response received', ['response_keys' => array_keys($response)]);

            // Handle paginated response
            if (isset($response['data']) && is_array($response['data'])) {
                // This is a paginated response
                $this->records = $response['data']['data'] ?? $response['data'];

                // Always set pagination properties from API response
                $this->currentPage = $response['data']['current_page'] ?? 1;
                $this->lastPage = $response['data']['last_page'] ?? 1;
                $this->perPage = $response['data']['per_page'] ?? 25;
                $this->total = $response['data']['total'] ?? 0;
                $this->from = $response['data']['from'] ?? 0;
                $this->to = $response['data']['to'] ?? 0;

                Log::info('Paginated data extracted', [
                    'records_count' => count($this->records),
                    'current_page' => $this->currentPage,
                    'last_page' => $this->lastPage,
                    'total' => $this->total,
                    'has_filters' => !empty($cleanFilters)
                ]);
            } else {
                // This might be a direct data array
                $this->records = $response['data'] ?? $response;

                // For direct data, set basic pagination
                $this->currentPage = 1;
                $this->lastPage = 1;
                $this->total = count($this->records);
                $this->from = 1;
                $this->to = $this->total;

                Log::info('Direct data extracted', [
                    'records_count' => count($this->records),
                    'has_filters' => !empty($cleanFilters)
                ]);
            }

            // Ensure records is always an array
            if (!is_array($this->records)) {
                $this->records = [];
            }

            Log::info('Records processed successfully', [
                'count' => count($this->records),
                'has_filters' => !empty($cleanFilters),
                'show_pagination' => $this->lastPage > 1 // Always show if multiple pages
            ]);

        } catch (\Throwable $e) {
            Log::error('Failed to fetch ProgramTypeFeeItemAmounts', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            Flux::toast("Failed to fetch records: " . $e->getMessage(), variant: 'warning');
            $this->records = [];
            $this->resetPagination();
        }
    }

    // Update the shouldShowPagination method to ALWAYS show when there are multiple pages
    public function shouldShowPagination()
    {
        // Show pagination whenever we have multiple pages, regardless of filters
        return $this->lastPage > 1;
    }

    // Keep the hasActiveFilters method for UI indicators
    public function hasActiveFilters()
    {
        return !empty(array_filter($this->filters, function($value) {
            return $value !== '' && $value !== null;
        }));
    }

    public function updatedFilters()
    {
        // Reset to first page when filters change
        $this->currentPage = 1;
        // Auto-refresh when filters change
        $this->fetchRecords();
    }

    // Add pagination methods
    public function gotoPage($page)
    {
        $this->currentPage = $page;
        $this->fetchRecords();
    }

    public function previousPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
            $this->fetchRecords();
        }
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->lastPage) {
            $this->currentPage++;
            $this->fetchRecords();
        }
    }

    public function save()
    {
        try {
            $this->ensureClientsInitialized();

            // Validate required fields based on your requirements
            if (empty($this->form['program_type_id']) ||
                empty($this->form['fee_item_id']) ||
                empty($this->form['level_id']) ||
                empty($this->form['acad_session_id'])) {
                Flux::toast("Program Type, Fee Item, Level, and Academic Session are required", variant: 'warning');
                return;
            }

            $payload = [
                'program_type_id' => (int) $this->form['program_type_id'],
                'fee_item_id' => (int) $this->form['fee_item_id'],
                'faculty_id' => $this->form['faculty_id'] ? (int) $this->form['faculty_id'] : null,
                'department_id' => $this->form['department_id'] ? (int) $this->form['department_id'] : null,
                'level_id' => (int) $this->form['level_id'],
                'acad_session_id' => (int) $this->form['acad_session_id'],
                'first_semester_amount' => (float) ($this->form['first_semester_amount'] ?: 0),
                'second_semester_amount' => (float) ($this->form['second_semester_amount'] ?: 0),
            ];

            Log::info('Saving ProgramTypeFeeItemAmount', ['payload' => $payload]);

            if ($this->form['id']) {
                $this->studentClient->updateProgramTypeFeeItemAmount($this->form['id'], $payload);
                Flux::toast('Template updated successfully!', variant: 'success');
            } else {
                $this->studentClient->createProgramTypeFeeItemAmount($payload);
                Flux::toast('Template created successfully!', variant: 'success');
            }

            $this->resetForm();
            $this->fetchRecords();
            $this->showForm = false;

        } catch (\Throwable $e) {
            $message = $this->simplifyMessage($e->getMessage());
            Flux::toast("Failed to save: " . $message, variant: 'warning');
            Log::error('Failed to save ProgramTypeFeeItemAmount', [
                'error' => $e->getMessage(),
                'form' => $this->form,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function edit($id)
    {
        try {
            Log::info('Editing record', ['id' => $id]);

            // Find the record in current records
            $record = collect($this->records)->firstWhere('id', $id);

            if ($record) {
                $this->form = [
                    'id' => $record['id'] ?? null,
                    'program_type_id' => $record['program_type']['id'] ?? '',
                    'fee_item_id' => $record['fee_item']['id'] ?? '',
                    'faculty_id' => $record['faculty']['id'] ?? '',
                    'department_id' => $record['department']['id'] ?? '',
                    'level_id' => $record['level']['id'] ?? '',
                    'acad_session_id' => $record['acad_session']['id'] ?? '',
                    'first_semester_amount' => $record['first_semester_amount'] ?? 0,
                    'second_semester_amount' => $record['second_semester_amount'] ?? 0,
                ];

                // Load departments if faculty is selected
                if ($this->form['faculty_id']) {
                    $this->updatedFormFacultyId($this->form['faculty_id']);
                }

                $this->showForm = true;
                Log::info('Edit form populated', ['form' => $this->form]);
            } else {
                Log::warning('Record not found for editing', ['id' => $id]);
                Flux::toast("Record not found", variant: 'warning');
            }
        } catch (\Throwable $e) {
            Log::error('Failed to edit record', ['error' => $e->getMessage()]);
            Flux::toast("Failed to edit record: " . $e->getMessage(), variant: 'warning');
        }
    }

    public function delete($id)
    {
        try {
            $this->ensureClientsInitialized();

            $this->studentClient->deleteProgramTypeFeeItemAmount($id);
            $this->fetchRecords();
            Flux::toast('Template deleted successfully!', variant: 'success');
        } catch (\Throwable $e) {
            $message = $this->simplifyMessage($e->getMessage());
            Flux::toast("Failed to delete: " . $message, variant: 'warning');
            Log::error('Failed to delete ProgramTypeFeeItemAmount', ['error' => $e->getMessage()]);
        }
    }

    public function createNew()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    protected function resetForm()
    {
        $this->form = [
            'id' => null,
            'program_type_id' => '',
            'fee_item_id' => '',
            'faculty_id' => '',
            'department_id' => '',
            'level_id' => '',
            'acad_session_id' => '',
            'first_semester_amount' => '',
            'second_semester_amount' => '',
        ];
        $this->departments = [];
    }

    protected function transformDropdownData($data, $type = 'Unknown')
    {
        if (!is_array($data)) {
            Log::warning("{$type} data is not an array", ['data' => $data]);
            return [];
        }

        $transformed = [];

        foreach ($data as $index => $item) {
            try {
                $id = null;
                $name = null;

                // Handle array items
                if (is_array($item)) {
                    $id = $item['id'] ?? $item['ID'] ?? $item['Id'] ?? null;
                    $name = $item['name'] ?? $item['Name'] ?? $item['NAME'] ?? $item['title'] ?? $item['Title'] ?? 'Unknown';
                }
                // Handle object items
                elseif (is_object($item)) {
                    $id = $item->id ?? $item->ID ?? $item->Id ?? null;
                    $name = $item->name ?? $item->Name ?? $item->NAME ?? $item->title ?? $item->Title ?? 'Unknown';
                }

                // Only add if we have valid id and name
                if ($id && $name && $name !== 'Unknown') {
                    $transformed[] = [
                        'id' => (string) $id, // Ensure string for select comparison
                        'name' => (string) $name
                    ];
                } else {
                    Log::warning("Invalid {$type} item skipped", [
                        'index' => $index,
                        'item' => $item,
                        'found_id' => $id,
                        'found_name' => $name
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error("Error transforming {$type} item", [
                    'index' => $index,
                    'item' => $item,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }

        Log::info("{$type} transformation result", [
            'input_count' => count($data),
            'output_count' => count($transformed),
            'sample' => $transformed[0] ?? 'No items'
        ]);

        return $transformed;
    }

    protected function simplifyMessage($message)
    {
        if (preg_match('/\{.*"errors":\{.*"([^"]+)".*:\["([^"]+)"/', $message, $matches)) {
            return $matches[2];
        }
        return $message;
    }

    public function clearAllFilters()
{
    $this->filters = [
        'program_type_id' => '',
        'faculty_id' => '',
        'department_id' => '',
        'level_id' => '',
        'acad_session_id' => '',
    ];
    $this->departments = []; // Clear departments when clearing filters
    $this->currentPage = 1;
    $this->fetchRecords();
    Flux::toast('All filters cleared', variant: 'success');
}

    public function render()
    {
        return view('livewire.bursary.program-type-fee-item-amount-manager');
    }
}
