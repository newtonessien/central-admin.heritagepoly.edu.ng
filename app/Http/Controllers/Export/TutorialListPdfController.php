<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;

class TutorialListPdfController
{
    public function generate(Request $request)
    {
        // -------------------------------------------------
        // 1. VALIDATION
        // -------------------------------------------------

        $validated = $request->validate([
            'course_id'       => 'required|integer',
            'acad_session_id' => 'required|integer',
            'semester'        => 'required|integer|in:1,2',
            'academic_period' => 'required|integer|in:1,2,3',

            'program_type_id' => 'nullable|integer',
            'faculty_id'      => 'nullable|integer',
            'department_id'   => 'nullable|integer',
            'program_id'      => 'nullable|integer',
            'level_id'        => 'nullable|integer',
        ]);

        // -------------------------------------------------
        // 2. LOAD TUTORIAL LIST
        // -------------------------------------------------

        $validated['export'] = 1;

        $client = app(StudentPortalClient::class);

        $response = $client->getTutorialList($validated);

        $students = $response['students'] ?? [];
        $course   = $response['course'] ?? [];

        // -------------------------------------------------
        // 3. LOAD STRUCTURAL NAMES
        // -------------------------------------------------

        $admissions = app(AdmissionsPortalClient::class);

        $programTypes = $admissions->getProgramTypes();

        $faculties = $admissions->getFaculties();

        $departments = $validated['faculty_id']
            ? $admissions->getDepartments(
                $validated['faculty_id']
            )
            : [];

        $programs = $validated['department_id']
            ? $admissions->getPrograms(
                $validated['department_id'],
                $validated['program_type_id']
            )
            : [];

        $sessions = $admissions->getAcadSessions();

        // -------------------------------------------------
        // 4. EXTRACT SELECTED RECORDS
        // -------------------------------------------------

        $faculty = collect($faculties)
            ->firstWhere(
                'id',
                $validated['faculty_id'] ?? null
            );

        $department = collect($departments)
            ->firstWhere(
                'id',
                $validated['department_id'] ?? null
            );

        $program = collect($programs)
            ->firstWhere(
                'id',
                $validated['program_id'] ?? null
            );

        $session = collect($sessions)
            ->firstWhere(
                'id',
                $validated['acad_session_id'] ?? null
            );

        $programType = collect($programTypes)
            ->firstWhere(
                'id',
                $validated['program_type_id'] ?? null
            );

        // -------------------------------------------------
        // 5. SEMESTER TEXT
        // -------------------------------------------------

        $semesterText = match ((int) $validated['semester']) {
            1 => 'First Semester',
            2 => 'Second Semester',
            default => '—',
        };

        // -------------------------------------------------
        // 6. ACADEMIC PERIOD TEXT
        // -------------------------------------------------

        $academicPeriodText = match ((int) $validated['academic_period']) {
            1 => 'First Semester',
            2 => 'Second Semester',
            3 => 'Summer',
            default => '—',
        };

        // -------------------------------------------------
        // 7. FILE NAME
        // -------------------------------------------------

        $courseCode = $course['course_code'] ?? 'COURSE';

        $courseCode = preg_replace('/\s+/', '', $courseCode);

        $date = now()->format('Y-m-d');

        $filename =
            "{$courseCode}_{$semesterText}_{$academicPeriodText}_{$date}.pdf";

        // -------------------------------------------------
        // 8. TOTAL STUDENTS
        // -------------------------------------------------

        $totalStudents = count($students);

        // -------------------------------------------------
        // 9. GENERATE PDF
        // -------------------------------------------------

        $pdf = Pdf::loadView(
            'exports.tutorial-list',
            compact(
                'students',
                'course',
                'faculty',
                'department',
                'program',
                'programType',
                'session',
                'semesterText',
                'academicPeriodText',
                'totalStudents'
            )
        )->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
