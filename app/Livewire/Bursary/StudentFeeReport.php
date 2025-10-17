<?php

namespace App\Livewire\Bursary;

use Flux\Flux;
use Livewire\Component;
use Livewire\WithPagination;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Spatie\SimpleExcel\SimpleExcelWriter;
use App\Services\Clients\StudentPortalClient;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Clients\AdmissionsPortalClient;


class StudentFeeReport extends Component
{
    use WithPagination;
    public  $programTypeId = null;
    public  $facultyId = null;
    public ?string $startDate = null;
    public ?string $endDate = null;

    public array $programTypes = [];
    public array $faculties = [];
    public array $reports = [];

    public int $totalRecords = 0;
    public float $totalAmount = 0.0;
    public int $perPage = 20;

    protected StudentPortalClient $studentPortalClient;
    protected AdmissionsPortalClient $admissions;

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->studentPortalClient = app(StudentPortalClient::class);
        $this->admissions = app(AdmissionsPortalClient::class);

        $this->programTypes = $this->admissions->getProgramTypes();
        $this->faculties = $this->admissions->getFaculties();
    }

    public function hydrate(): void
    {
        $this->studentPortalClient = app(StudentPortalClient::class);
        $this->admissions = app(AdmissionsPortalClient::class);
    }

    public function updatingProgramTypeId() { $this->resetPage(); }
    public function updatingFacultyId() { $this->resetPage(); }
    public function updatingStartDate() { $this->resetPage(); }
    public function updatingEndDate() { $this->resetPage(); }


    public function loadReports(): void
    {

      if($this->facultyId == 'all') {
            $this->facultyId = null;
            }
            if(empty($this->programTypeId) || empty($this->startDate) || empty($this->endDate)) {
                Flux::toast('Please select Program Type, Start Date, and End Date.', variant: 'warning', position: 'top-right', duration: 4000);
                 return;
            }

        $params = [
            'program_type_id' => $this->programTypeId,
            'faculty_id'      => $this->facultyId,
            'start_date'      => $this->startDate,
            'end_date'        => $this->endDate,
        ];

        try {
            $this->reports = $this->studentPortalClient->getSchoolFeeReports($params);
            $this->totalRecords = count($this->reports);
            $this->totalAmount = collect($this->reports)->sum('amount');

                  // $this->dispatchBrowserEvent('notify', [
            //     'type' => $this->totalRecords ? 'success' : 'info',
            //     'message' => $this->totalRecords ? "Loaded {$this->totalRecords} records." : 'No records found.',
            // ]);
            $this->resetPage();
        } catch (\Throwable $e) {
            report($e);
            // $this->dispatchBrowserEvent('notify', [
            //     'type' => 'error',
            //     'message' => 'Failed to load reports: ' . $e->getMessage(),
            // ]);
        }
    }

    protected function paginateCollection(Collection $items, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
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
        if (empty($this->reports)) {
            $this->dispatchBrowserEvent('notify', ['type' => 'warning', 'message' => 'No data to export.']);
            return;
        }

        $filename = 'student_fee_reports_' . now()->format('Ymd_His') . '.xlsx';
        $path = storage_path("app/temp/{$filename}");
        @mkdir(storage_path('app/temp'), 0777, true);

        $writer = SimpleExcelWriter::create($path)
            ->addHeader(['Reg No', 'Fullname', 'Faculty', 'Department', 'Amount', 'Transaction Date']);

        foreach ($this->reports as $row) {
            $writer->addRow([
                $row['regno'] ?? '',
                $row['fullname'] ?? '',
                $row['faculty'] ?? '',
                $row['department'] ?? '',
                number_format($row['amount'] ?? 0, 2),
                $row['trans_date'] ?? '',
            ]);
        }

        $writer->close();

        return response()->download($path)->deleteFileAfterSend(true);
    }

public function exportPdf()
{
    if (empty($this->reports)) {
        $this->dispatchBrowserEvent('notify', ['type' => 'warning', 'message' => 'No data to export.']);
        return;
    }

    $totalRecords = count($this->reports);
    $totalAmount = collect($this->reports)->sum('amount');

    $pdf = Pdf::loadView('exports.student_fee_report', [
        'reports' => $this->reports,
        'filters' => [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ],
    ])
        ->setPaper('A4', 'landscape')
        ->setOption('defaultFont', 'DejaVu Sans');

    // Generate the underlying DomPDF object
    $dompdf = $pdf->getDomPDF();
    $dompdf->render();

    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
    $size = 9;
    $w = $canvas->get_width();
    $h = $canvas->get_height();

    // Left footer text
    $leftText = "Total Records: {$totalRecords} | Total Amount: ₦" . number_format($totalAmount, 2);
    // Right footer text with dynamic page numbers
    $rightText = "Page {PAGE_NUM} of {PAGE_COUNT} | Generated: " . now()->format('d M Y, h:i A');

    // Ensure UTF-8 encoding (₦ symbol fix)
    $leftText = mb_convert_encoding($leftText, 'UTF-8', 'UTF-8');
    $rightText = mb_convert_encoding($rightText, 'UTF-8', 'UTF-8');

    // Add text to each page
    $canvas->page_text(40, $h - 35, $leftText, $font, $size, [0, 0, 0]);
    $canvas->page_text($w - 260, $h - 35, $rightText, $font, $size, [0, 0, 0]);

    // Return response
    return response()->streamDownload(fn() => print($dompdf->output()), 'student_fee_report.pdf');
}



    public function render()
    {
        $paginatedReports = $this->paginateCollection(collect($this->reports), $this->perPage);
        return view('livewire.bursary.student-fee-report', [
            'paginatedReports' => $paginatedReports,
        ]);
    }

}
