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
        $validated = $request->validate([
            'course_id'       => 'required|integer',
            'acad_session_id' => 'required|integer',
            'semester'        => 'required|integer',
            'program_type_id' => 'nullable|integer',
            'faculty_id'      => 'nullable|integer',
            'department_id'   => 'nullable|integer',
            'program_id'      => 'nullable|integer',
            'level_id'        => 'nullable|integer',
        ]);

        $validated['export'] = 1;

        $client = app(StudentPortalClient::class);
        $response = $client->getTutorialList($validated);

        $students = $response['students'] ?? [];
        $course   = $response['course'] ?? [];
        // Get structural names
    $admissions = app(AdmissionsPortalClient::class);

// Load lists
$programTypes = $admissions->getProgramTypes();
$faculties   = $admissions->getFaculties();
$departments = $validated['faculty_id']
                    ? $admissions->getDepartments($validated['faculty_id'])
                    : [];

$programs    = $validated['department_id']
                    ? $admissions->getPrograms(
                        $validated['department_id'],
                        $validated['program_type_id']
                    )
                    : [];

$sessions    = $admissions->getAcadSessions();

// Extract single records
$faculty = collect($faculties)
                ->firstWhere('id', $validated['faculty_id'] ?? null);

$department = collect($departments)
                ->firstWhere('id', $validated['department_id'] ?? null);

$program = collect($programs)
                ->firstWhere('id', $validated['program_id'] ?? null);

$session = collect($sessions)
                ->firstWhere('id', $validated['acad_session_id'] ?? null);

$programType = collect($programTypes)
                ->firstWhere('id', $validated['program_type_id'] ?? null);

 // Convert semester
$semesterText = match((int)$validated['semester']) {
    1 => 'First Semester',
    2 => 'Second Semester',
    default => '—'
};

$semesterSlug = str_replace(' ', '', $semesterText);
$courseCode = $course['course_code'] ?? 'COURSE';
$courseCode = preg_replace('/\s+/', '', $courseCode);
$date = now()->format('Y-m-d');
$filename = "{$courseCode}_{$semesterSlug}_{$date}.pdf";



$totalStudents = count($students);

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
                'totalStudents'
            )
        )->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }
}
