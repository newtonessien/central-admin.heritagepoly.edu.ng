<?php

namespace App\Http\Controllers\Export;

use App\Http\Controllers\Controller;
use App\Services\Clients\RmsClient;
use App\Services\Clients\StudentPortalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ResultsHistoryPdfController extends Controller
{
    public function download(
        Request $request,
        StudentPortalClient $studentClient,
        RmsClient $rmsClient
    ) {
        /*
        |--------------------------------------------------------------------------
        | Student
        |--------------------------------------------------------------------------
        */

        $regno = rawurldecode(
            $request->query('regno')
        );

        abort_if(
            blank($regno),
            422,
            'Student registration number is required.'
        );


        /*
        |--------------------------------------------------------------------------
        | Student Details
        |--------------------------------------------------------------------------
        */

        $studentResponse = $studentClient->getStudentByRegno(
            $regno,
            ['with' => 'details']
        );

        $student = $studentResponse['data']
            ?? $studentResponse;


        abort_if(
            empty($student),
            404,
            'Student not found.'
        );


        /*
        |--------------------------------------------------------------------------
        | Results Filters
        |--------------------------------------------------------------------------
        */

        $filters = array_filter([
            'status' => $request->query('status'),
            'acad_session_id' => $request->query('acad_session_id'),
            'semester' => $request->query('semester'),
            'level_id' => $request->query('level_id'),
        ], fn ($value) => $value !== null && $value !== '');


        /*
        |--------------------------------------------------------------------------
        | Results
        |--------------------------------------------------------------------------
        */

        $resultsResponse = $rmsClient->getStudentResults(
            $regno,
            $filters
        );

        $results = collect(
            $resultsResponse['results'] ?? []
        )->map(function ($result) {

            return [

                'id' => $result['id'] ?? null,

                'matric_no' => $result['matric_no'] ?? null,

                'student_id' => $result['student_id'] ?? null,

                'course_id' => $result['course_id'] ?? null,

                'course_code' =>
                    $result['course']['course_code'] ?? '-',

                'course_title' =>
                    $result['course']['course_title'] ?? '-',

                'credit_hours' =>
                    (int) (
                        $result['course']['credit_hours'] ?? 0
                    ),

                'semester' =>
                    $result['semester'] ?? null,

                'acad_session_id' =>
                    $result['acad_session_id'] ?? null,

                'session' =>
                    $result['academic_session']['name'] ?? '-',

                'level_id' =>
                    $result['level_id'] ?? null,

                'level' =>
                    $result['level']['name'] ?? '-',

                'ca_score' =>
                    $result['ca_score'] ?? null,

                'exam_score' =>
                    $result['exam_score'] ?? null,

                'total_score' =>
                    $result['total_score'] ?? null,

                'grade' =>
                    $result['grade'] ?? '-',

                'grade_point' =>
                    $result['grade_point'] ?? 0,

                'remarks' =>
                    $result['remarks'] ?? null,

                'status' =>
                    $result['status'] ?? null,

                'is_published' =>
                    (bool) (
                        $result['is_published'] ?? false
                    ),

                'approved_at' =>
                    $result['approved_at'] ?? null,

            ];

        });


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $summary = $this->buildSummary(
            $results
        );


        /*
        |--------------------------------------------------------------------------
        | Group Results
        |--------------------------------------------------------------------------
        |
        | Session
        |     Semester
        |          Level
        |
        */

        $groupedResults = $results
            ->groupBy('acad_session_id')
            ->map(function ($sessionResults) {

                return $sessionResults
                    ->groupBy('semester')
                    ->map(function ($semesterResults) {

                        return $semesterResults
                            ->groupBy('level_id');

                    });

            });


        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */

        $pdf = Pdf::loadView(
            'exports.results-history-report',
            [
                'student' => $student,
                'results' => $results,
                'summary' => $summary,
                'groupedResults' => $groupedResults,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | PDF Settings
        |--------------------------------------------------------------------------
        */

        $pdf->setPaper('a4', 'portrait');


        /*
        |--------------------------------------------------------------------------
        | Filename
        |--------------------------------------------------------------------------
        */

        $safeRegNo = str_replace(
            ['/', '\\'],
            '_',
            $student['regno'] ?? $regno
        );


        return $pdf->download(
            'results-history-' . $safeRegNo . '.pdf'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Build Summary
    |--------------------------------------------------------------------------
    */

    protected function buildSummary($results): array
    {
        $sessions = $results
            ->pluck('acad_session_id')
            ->filter()
            ->unique()
            ->count();


        $semesters = $results
            ->map(function ($result) {

                return ($result['acad_session_id'] ?? null)
                    . '-'
                    . ($result['semester'] ?? null);

            })
            ->filter()
            ->unique()
            ->count();


        $courses = $results->count();


        $units = $results->sum(
            fn ($result) =>
                (int) ($result['credit_hours'] ?? 0)
        );


        return [

            'sessions' => $sessions,

            'semesters' => $semesters,

            'courses' => $courses,

            'units' => $units,

        ];
    }
}
