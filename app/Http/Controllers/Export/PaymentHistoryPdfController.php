<?php

namespace App\Http\Controllers\Export;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use Illuminate\Http\Request;

class PaymentHistoryPdfController
{
    public function download(Request $request)
    {
        $client = app(StudentPortalClient::class);

        /*
        |--------------------------------------------------------------------------
        | Student Details
        |--------------------------------------------------------------------------
        */

        $student = $client->getStudentByRegno(
            $request->regno,
            ['with' => 'details']
        );

        $student = $student['data'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Payment History
        |--------------------------------------------------------------------------
        */

        $payments = $client->getStudentPaymentHistory(
            $request->regno,
            [
                'acad_session_id' => $request->acad_session_id,
                'fee_period_id'   => $request->fee_period_id,

                // Export everything
                'page'            => 1,
                'per_page'        => 1000,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Sessions Lookup
        |--------------------------------------------------------------------------
        */

        $sessions = collect(
            app(AdmissionsPortalClient::class)->getAcadSessions()
        )->pluck('name', 'id');

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'exports.student-payment-history',
            [
                'student'  => $student,
                'payments' => collect($payments['data'] ?? []),
                'summary'  => $payments['summary'] ?? [],
                'sessions' => $sessions,
            ]
        );

        $safeRegNo = str_replace(
            ['/', '\\'],
            '_',
            $student['matric_no'] ?? $student['regno']
        );

        return $pdf->download(
            "payment-history-{$safeRegNo}.pdf"
        );
    }
}
