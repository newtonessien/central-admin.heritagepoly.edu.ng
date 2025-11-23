<?php

namespace App\Http\Controllers\Export;

use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;

class StudyCenterSummaryExport
{
    protected array $records;
    protected string $filename;

    /**
     * @param array $records  The prepared dataset coming from Livewire
     * @param string|null $filename Optional custom filename
     */
    public function __construct(array $records, ?string $filename = null)
    {
        $this->records = $records;

        // Example: study-center-summary-2025-01-20.xlsx
        $this->filename = $filename
            ?? 'study-center-summary-' . now()->format('Y-m-d-His') . '.xlsx';
    }

    /**
     * Export the summary to an Excel file using SimpleExcelWriter
     *
     * @return string Full storage path (for download)
     */
    public function export(): string
    {
        // Ensure export folder exists
        Storage::makeDirectory('exports');

        $path = storage_path("app/exports/{$this->filename}");

        // Build Excel file
        $writer = SimpleExcelWriter::create($path)
            ->addHeader([
                'Centre/Portal',
                'Transactions',
                'Total Amount',
                'Centre Commission (50%)',
                'Net Total',
            ]);

        foreach ($this->records as $row) {
            $writer->addRow([
                'Centre'            => $row['centre_name'] ?? '',
                'Transactions'      => $row['transactions'] ?? 0,
                'Total Amount'      => $row['total_amount'] ?? 0,
                'Centre Commission' => $row['commission'] ?? 0,
                'Net Total'         => $row['net_total'] ?? 0,
            ]);
        }

        return $path;
    }
}
