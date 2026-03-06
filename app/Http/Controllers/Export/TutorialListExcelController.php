<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use App\Services\Clients\StudentPortalClient;
//use App\Services\Clients\AdmissionsPortalClient;
use Spatie\SimpleExcel\SimpleExcelWriter;

class TutorialListExcelController
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

        $client   = app(StudentPortalClient::class);
        $response = $client->getTutorialList($validated);

        $students = $response['students'] ?? [];
        $course   = $response['course'] ?? [];

        // Semester
        $semesterText = match((int)$validated['semester']) {
            1 => 'First Semester',
            2 => 'Second Semester',
            default => '—'
        };

        $semesterSlug = str_replace(' ', '', $semesterText);

        $courseCode = $course['course_code'] ?? 'COURSE';
        $courseCode = preg_replace('/\s+/', '', $courseCode);

        $date = now()->format('Y-m-d');

        $filename = "{$courseCode}_{$semesterSlug}_{$date}.xlsx";

        // Create temporary file
        $tempPath = storage_path("app/{$filename}");

        $writer = SimpleExcelWriter::create($tempPath);

        // Header row (like your table headers)
        $writer->addRow([
            '#',
            'Reg. Number',
            'Full Name',
            'Level',
            'CA',
            'Exam',
            'Total',
            'Grade',
            'Remark',
        ]);

        // Students
        foreach ($students as $index => $student) {
            $writer->addRow([
                $index + 1,
                $student['matric_no'] ?? '',
                $student['name'] ?? '',
                ($student['student_level_id'] ?? '') . '00',
                '',
                '',
                '',
                '',
                '',
            ]);
        }

        $writer->close();

        return response()->download($tempPath)->deleteFileAfterSend(true);
    }
}
