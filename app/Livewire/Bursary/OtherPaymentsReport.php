<?php

namespace App\Livewire\Bursary;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\SimpleExcel\SimpleExcelWriter;

class OtherPaymentsReport extends Component
{
    use WithPagination;

    // Filters (if you allow "all" as string in the UI, make these string|int|null)
    public $programTypeId = null;
    public $facultyId = null;
    public $serviceId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    // Lookups & data
    public array $programTypes = [];
    public array $faculties = [];
    public array $services = [];

    // Reports stored as a Collection for easy paging
    public Collection $reports;

    // summary + pagination
    public int $totalRecords = 0;
    public float $totalAmount = 0.0;
    public int $perPage = 20;
    protected $paginationTheme = 'tailwind';

    protected StudentPortalClient $studentPortalClient;
    protected AdmissionsPortalClient $admissions;

    public function mount(?StudentPortalClient $studentPortalClient = null, ?AdmissionsPortalClient $admissions = null): void
    {
        // initialize clients (safe for Livewire hydration)
        $this->studentPortalClient = $studentPortalClient ?? app(StudentPortalClient::class);
        $this->admissions = $admissions ?? app(AdmissionsPortalClient::class);

        // load lookups
        $this->programTypes = $this->admissions->getProgramTypes();
        $this->faculties = $this->admissions->getFaculties();
        $this->services = $this->studentPortalClient->getServices();

        // initialize empty collection
        $this->reports = collect([]);
    }

    // reset page when filters change
    public function updatingProgramTypeId() { $this->resetPage(); }
    public function updatingFacultyId()     { $this->resetPage(); }
    public function updatingServiceId()     { $this->resetPage(); }
    public function updatingStartDate()     { $this->resetPage(); }
    public function updatingEndDate()       { $this->resetPage(); }

    /**
     * Load reports from the Student Portal API and keep as Collection
     */
    public function loadReports(): void
    {
        // normalize "all" selections to null if your UI sends "all" strings
        $program = ($this->programTypeId === 'all' || $this->programTypeId === 0) ? null : $this->programTypeId;
        $faculty = ($this->facultyId === 'all' || $this->facultyId === 0) ? null : $this->facultyId;
        $service = ($this->serviceId === 'all' || $this->serviceId === 0) ? null : $this->serviceId;

        $params = [
            'program_type_id' => $program,
            'faculty_id'      => $faculty,
            'service_id'      => $service,
            'start_date'      => $this->startDate,
            'end_date'        => $this->endDate,
        ];

        // ensure client exists (safety for rehydration)
        if (!isset($this->studentPortalClient)) {
            $this->studentPortalClient = app(StudentPortalClient::class);
        }

        $rows = $this->studentPortalClient->getOtherPaymentsReport($params) ?? [];

        // store as Collection for pagination & summary
        $this->reports = collect($rows);

        $this->totalRecords = $this->reports->count();
        $this->totalAmount = (float) $this->reports->sum('amount');

        // reset to first page after loading new data
        $this->resetPage();
    }

    /**
     * Simple collection paginator for Livewire view
     */
    protected function paginateCollection(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage() ?: 1;
        $currentItems = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $currentItems,
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    public function exportExcel()
    {
        if ($this->reports->isEmpty()) {
            $this->dispatchBrowserEvent('notify', ['type' => 'warning', 'message' => 'No data to export.']);
            return;
        }

        $filename = 'other_payments_report_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path("app/temp/{$filename}");
        @mkdir(storage_path('app/temp'), 0777, true);

        $writer = SimpleExcelWriter::create($path)
            ->addHeader(['Reg No', 'Fullname', 'Faculty', 'Department', 'Service', 'Amount', 'Transaction Date']);

        foreach ($this->reports as $row) {
            $writer->addRow([
                $row['regno'] ?? '',
                $row['fullname'] ?? '',
                $row['faculty'] ?? '',
                $row['department'] ?? '',
                $row['service'] ?? '',
                number_format($row['amount'] ?? 0, 2),
                $row['trans_date'] ?? '',
            ]);
        }

        $writer->close();

        return response()->download($path)->deleteFileAfterSend(true);
    }

    public function exportPdf()
    {
        if ($this->reports->isEmpty()) {
            $this->dispatchBrowserEvent('notify', ['type' => 'warning', 'message' => 'No data to export.']);
            return;
        }

        $totalRecords = $this->reports->count();
        $totalAmount = $this->reports->sum('amount');

        $pdf = Pdf::loadView('exports.other_payments_report', [
            'reports' => $this->reports->toArray(),
            'filters' => [
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
                'faculty_id' => $this->facultyId,
                'service_id' => $this->serviceId,
                'program_type_id' => $this->programTypeId,
            ],
        ])->setPaper('A4', 'landscape')
          ->setOption('defaultFont', 'DejaVu Sans');

        $dompdf = $pdf->getDomPDF();
        $dompdf->render(); // important: render before adding page_text
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $size = 9;
        $w = $canvas->get_width();
        $h = $canvas->get_height();

        $leftText = "Total Records: {$totalRecords} | Total Amount: ₦" . number_format($totalAmount, 2);
        $rightText = "Page {PAGE_NUM} of {PAGE_COUNT} | Generated: " . now()->format('d M Y, h:i A');

        // ensure UTF-8
        $leftText = mb_convert_encoding($leftText, 'UTF-8', 'UTF-8');
        $rightText = mb_convert_encoding($rightText, 'UTF-8', 'UTF-8');

        $canvas->page_text(40, $h - 35, $leftText, $font, $size, [0, 0, 0]);
        $canvas->page_text($w - 320, $h - 35, $rightText, $font, $size, [0, 0, 0]);

        return response()->streamDownload(fn() => print($dompdf->output()), 'other_payments_report.pdf');
    }

    public function render()
    {
        // provide paginatedReports to the view (avoid undefined variable)
        $paginatedReports = $this->paginateCollection($this->reports ?? collect([]), $this->perPage);

        // ensure totals are up to date
        $this->totalRecords = $this->reports->count();
        $this->totalAmount = (float) $this->reports->sum('amount');

        return view('livewire.bursary.other-payments-report', [
            'paginatedReports' => $paginatedReports,
            'totalRecords' => $this->totalRecords,
            'totalAmount' => $this->totalAmount,
        ]);
    }
}
