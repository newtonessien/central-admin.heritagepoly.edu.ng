<?php

namespace App\Http\Controllers\Export;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
use App\Services\Clients\AdmissionsPortalClient;

class AdmissionPaymentExportController extends Controller
{
    protected $admissions;

    public function __construct(AdmissionsPortalClient $admissions)
    {
        $this->admissions = $admissions;
    }




public function exportExcel(Request $request)
{
    $params = [
        'application_type_id' => $request->query('application_type_id'),
        'faculty_id' => $request->query('faculty_id'),
        'start_date' => $request->query('start_date'),
        'end_date' => $request->query('end_date'),
    ];

    $data = $this->admissions->getPaymentReports($params);

    // Ensure temporary directory exists
    Storage::makeDirectory('temp');

    $filename = 'admission_payments_' . now()->format('Ymd_His') . '.xlsx';
    $path = storage_path("app/temp/{$filename}");

    // Initialize writer for XLSX
    $writer = SimpleExcelWriter::create($path, 'xlsx');

    // 🟦 Add Report Header Section
    $writer->addRow(['University of Uyo']);
    $writer->addRow(['Admissions Payment Report']);

    if (!empty($params['start_date']) && !empty($params['end_date'])) {
        $writer->addRow(['Period:', \Carbon\Carbon::parse($params['start_date'])->format('d M Y') . ' - ' . \Carbon\Carbon::parse($params['end_date'])->format('d M Y')]);
    }

    if (!empty($params['faculty_id']) && isset($data[0]['faculty'])) {
        $writer->addRow(['Faculty:', $data[0]['faculty']]);
    }

    $writer->addRow([]); // blank line before table

    // 🟩 Table header
    $writer->addHeader(['Reg No', 'Fullname', 'Faculty', 'Department', 'Amount', 'Transaction Date']);

    // 🟨 Table data
    foreach ($data as $row) {
        $writer->addRow([
            $row['regno'] ?? '',
            $row['fullname'] ?? '',
            $row['faculty'] ?? '',
            $row['department'] ?? '',
            number_format($row['amount'] ?? 0, 2),
            isset($row['trans_date']) ? \Carbon\Carbon::parse($row['trans_date'])->format('d M Y') : '',
        ]);
    }

    // 🟥 Summary footer
    $totalAmount = collect($data)->sum(fn($r) => $r['amount'] ?? 0);
    $totalRecords = count($data);

    $writer->addRow([]);
    $writer->addRow(['Total Records', $totalRecords]);
    $writer->addRow(['Total Amount (₦)', number_format($totalAmount, 2)]);

    $writer->close();

    // Return file for download
    return response()->download($path, $filename, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ])->deleteFileAfterSend(true);
}


    // public function exportExcel(Request $request)
    // {
    //     $params = [
    //         'application_type_id' => $request->query('application_type_id'),
    //         'faculty_id' => $request->query('faculty_id'),
    //         'start_date' => $request->query('start_date'),
    //         'end_date' => $request->query('end_date'),
    //     ];

    //     $data = $this->admissions->getPaymentReports($params);

    //     $filename = 'admission_payments_' . now()->format('Ymd_His') . '.xlsx';
    //     $path = storage_path("app/temp/{$filename}");

    //     @mkdir(storage_path('app/temp'), 0777, true);

    //     $writer = SimpleExcelWriter::create($path)
    //         ->addHeader(['Reg No', 'Fullname', 'Faculty', 'Department', 'Amount', 'Transaction Date']);

    //     foreach ($data as $row) {
    //         $writer->addRow([
    //             $row['regno'] ?? '',
    //             $row['fullname'] ?? '',
    //             $row['faculty'] ?? '',
    //             $row['department'] ?? '',
    //             $row['amount'] ?? '',
    //             $row['trans_date'] ?? '',
    //         ]);
    //     }

    //     $writer->close();

    //     return response()->download($path)->deleteFileAfterSend(true);
    // }


public function exportPdf(Request $request)
{
    $params = [
        'application_type_id' => $request->query('application_type_id'),
        'faculty_id' => $request->query('faculty_id'),
        'start_date' => $request->query('start_date'),
        'end_date' => $request->query('end_date'),
        'application_type_name' => $request->query('application_type_name'),
    ];

    $data = $this->admissions->getPaymentReports($params);
    $generatedAt = Carbon::now();

    // ✅ Configure Dompdf properly
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('isPhpEnabled', true);

    $dompdf = new Dompdf($options);

// If using local files, also set the base path
$dompdf->setBasePath(public_path());

    $html = view('exports.admission-payments-pdf', [
        'title' => 'Admission Payments Report',
        'payments' => $data,
        'filters' => $params,
        'generatedAt' => $generatedAt,
    ])->render();

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();

    // ✅ Use Canvas API safely (works fine when accessed *after* render)
    $canvas = $dompdf->getCanvas();
    $fontMetrics = $dompdf->getFontMetrics();
    $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
    $size = 10;

    $w = $canvas->get_width();
    $h = $canvas->get_height();

    // Left footer — Generated date
    $canvas->page_text(
        24,
        $h - 18,
        'Generated: ' . $generatedAt->format('d M Y H:i'),
        $font,
        $size,
        [0, 0, 0]
    );

    // Right footer — Page numbering
    $canvas->page_text(
        $w - 140,
        $h - 18,
        'Page {PAGE_NUM} of {PAGE_COUNT}',
        $font,
        $size,
        [0, 0, 0]
    );

    // return response()->streamDownload(function () use ($dompdf) {
    //     echo $dompdf->output();
    // }, 'admission_payments_' . now()->format('Ymd_His') . '.pdf');


    return response($dompdf->output(), 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="'. now()->format('Ymd_His') .'.pdf',
        'Cache-Control'       => 'private, max-age=0, must-revalidate',
    ]);

}

}
