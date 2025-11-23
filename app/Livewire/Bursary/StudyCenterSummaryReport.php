<?php

namespace App\Livewire\Bursary;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use App\Http\Controllers\Export\StudyCenterSummaryExport;

class StudyCenterSummaryReport extends Component
{
    public $centers = [];
    public $study_center_id = '';
    public $start_date;
    public $end_date;

    public array $summaryData = [];
    public $report = [];
    public $combinedCentreCommissions = [];
    public $combinedCentreTransactions = [];
    public $combinedCentreAmounts = [];
    public $portalCommission = 0;

    public function mount()
    {
        $client = app(StudentPortalClient::class);
        $this->centers = $client->fetchStudyCenters();
    }

    public function fetchReport()
    {
        $studentClient   = app(StudentPortalClient::class);
        $admissionClient = app(AdmissionsPortalClient::class);

        $filters = array_filter([
            'study_center_id' => $this->study_center_id,
            'start_date'      => $this->start_date,
            'end_date'        => $this->end_date,
        ]);

        // Fetch individual reports
        $schoolFees = $studentClient->fetchStudyCenterSummary($filters);
        $admissions = $admissionClient->fetchAdmissionStudyCenterSummary($filters);

        // --- Reset combined arrays ---
        $this->combinedCentreCommissions = [];
        $this->combinedCentreTransactions = [];
        $this->combinedCentreAmounts = [];

        // Combine school fees
        foreach ($schoolFees['centers'] ?? [] as $row) {
            $centre = $row['study_center_name'];
            $this->combinedCentreCommissions[$centre] = $row['center_commission'];
            $this->combinedCentreTransactions[$centre] = $row['transactions'];
            $this->combinedCentreAmounts[$centre] = $row['total_amount'];
        }

        // Combine admissions (add to existing if same centre)
        foreach ($admissions['centers'] ?? [] as $row) {
            $centre = $row['study_center_name'];
            if (isset($this->combinedCentreCommissions[$centre])) {
                $this->combinedCentreCommissions[$centre] += $row['center_commission'];
                $this->combinedCentreTransactions[$centre] += $row['transactions'];
                $this->combinedCentreAmounts[$centre] += $row['total_amount'];
            } else {
                $this->combinedCentreCommissions[$centre] = $row['center_commission'];
                $this->combinedCentreTransactions[$centre] = $row['transactions'];
                $this->combinedCentreAmounts[$centre] = $row['total_amount'];
            }
        }

        // Portal commission is only from school fees
        $this->portalCommission = $schoolFees['grand_totals']['total_consultant_commission'] ?? 0;
        $this->combinedCentreCommissions['Portal (N2K/Fee)'] = $this->portalCommission;
        $this->combinedCentreTransactions['Portal (N2K/Fee)'] = $schoolFees['grand_totals']['transactions'] ?? 0;
        $this->combinedCentreAmounts['Portal (N2K/Fee)'] = $schoolFees['grand_totals']['total_amount'] ?? 0;

        // Build export/summary data
        $this->summaryData = [];
        foreach ($this->combinedCentreCommissions as $centreName => $commission) {
            $this->summaryData[] = [
                'centre_name'  => $centreName,
                'transactions' => $this->combinedCentreTransactions[$centreName] ?? 0,
                'total_amount' => $this->combinedCentreAmounts[$centreName] ?? 0,
                'commission'   => $commission,
                'net_total'    => $commission,
            ];
        }

        // Save combined report for Blade
        $this->report = [
            'school_fees' => $schoolFees,
            'admissions'  => $admissions,
            'combined'    => $this->combinedCentreCommissions,
            'portal'      => $this->portalCommission,
        ];
    }

    public function exportExcel()
    {
        $export = new StudyCenterSummaryExport($this->summaryData);
        $filePath = $export->export();

        return response()->download($filePath);
    }

    public function render()
    {
        return view('livewire.bursary.study-center-summary-report');
    }
}
