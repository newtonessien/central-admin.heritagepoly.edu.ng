<?php

namespace App\Http\Controllers\Export;

use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CourseRegistrationHistoryPdfController
{
    public function download(Request $request)
    {
        $studentClient = app(StudentPortalClient::class);

        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $studentResponse = $studentClient->getStudentByRegno(
            $request->regno,
            ['with' => 'details']
        );

        $student = $studentResponse['data'] ?? $studentResponse;

        /*
        |--------------------------------------------------------------------------
        | Course Registration History
        |--------------------------------------------------------------------------
        */

        $historyResponse = $studentClient->getCourseRegistrationHistory(
            $request->regno,
            array_filter([
                'acad_session_id' => $request->acad_session_id,
                'semester'        => $request->semester,
                'level_id'        => $request->level_id,
            ], fn ($value) => $value !== null && $value !== '')
        );

        $summary = $historyResponse['summary'] ?? [];

        $registrations = $historyResponse['data'] ?? [];

        /*
        |--------------------------------------------------------------------------
        | Academic Sessions
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
            'exports.course-registration-history',
            [
                'student'       => $student,
                'summary'       => $summary,
                'registrations' => $registrations,
                'sessions'      => $sessions,
            ]
        );

        $safeRegNo = str_replace(
            ['/', '\\'],
            '_',
            $student['matric_no']
                ?? $student['regno']
                ?? 'student'
        );

        return $pdf->download(
            'course-registration-history-' . $safeRegNo . '.pdf'
        );
    }
}
