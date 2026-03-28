<?php

namespace App\Http\Controllers\Export;

use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;


class RegisteredCoursesPdfController
{


public function courseRegistrationPdf(Request $request)
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

    $student = $student['data'] ?? $student;

    /*
    |--------------------------------------------------------------------------
    | Registered Courses
    |--------------------------------------------------------------------------
    */

    $coursesData = $client->getRegisteredCourses($request->all());

    $courses = collect($coursesData['courses'] ?? []);

    /*
    |--------------------------------------------------------------------------
    | Attach Level from registration endpoint
    |--------------------------------------------------------------------------
    */

    $student['level'] = $coursesData['student']['level'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | Sessions Lookup
    |--------------------------------------------------------------------------
    */

    $sessions = collect(
        app(AdmissionsPortalClient::class)->getAcadSessions()
    )->pluck('name','id');

    /*
    |--------------------------------------------------------------------------
    | Group Courses
    |--------------------------------------------------------------------------
    */

    $grouped = $courses
        ->groupBy('acad_session_id')
        ->map(function ($sessionCourses) {

            return $sessionCourses->groupBy('semester');

        });

    $pdf = Pdf::loadView(
        'exports.course-registration-report',
        [
            'student' => $student,
            'courses' => $grouped,
            'sessions' => $sessions
        ]
    );

  $safeRegNo = str_replace(['/', '\\'], '_', $student['matric_no'] ?? 'student');
    return $pdf->download('course-registration-'.$safeRegNo.'.pdf');
}



}
