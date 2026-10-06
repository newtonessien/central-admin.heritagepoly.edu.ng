<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use App\Services\Clients\StudentPortalClient;
use Spatie\SimpleExcel\SimpleExcelWriter;

class TutorialListExcelController
{
    public function generate(Request $request)
    {
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

        // Tell the API this is an export request
        $validated['export'] = 1;

        $client = app(StudentPortalClient::class);

        $response = $client->getTutorialList($validated);

        $students = $response['students'] ?? [];
        $course   = $response['course'] ?? [];

        // -------------------------------------------------
        // SEMESTER
        // -------------------------------------------------

        $semesterText = match ((int) $validated['semester']) {
            1 => 'First Semester',
            2 => 'Second Semester',
            default => '—',
        };

        // -------------------------------------------------
        // ACADEMIC PERIOD
        // -------------------------------------------------

        $academicPeriodText = match ((int) $validated['academic_period']) {
            1 => 'First Semester',
            2 => 'Second Semester',
            3 => 'Summer',
            default => '—',
        };

        // -------------------------------------------------
        // FILE NAME
        // -------------------------------------------------

        $courseCode = $course['course_code'] ?? 'COURSE';

        // Remove spaces
        $courseCode = preg_replace('/\s+/', '', $courseCode);

        $date = now()->format('Y-m-d');

        $filename = "{$courseCode}_{$semesterText}_{$academicPeriodText}_{$date}.xlsx";

        // -------------------------------------------------
        // CREATE TEMPORARY FILE
        // -------------------------------------------------

        $tempPath = storage_path("app/{$filename}");

        $writer = SimpleExcelWriter::create($tempPath);

        // -------------------------------------------------
        // HEADER
        // -------------------------------------------------

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

        // -------------------------------------------------
        // STUDENTS
        // -------------------------------------------------

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

        return response()
            ->download($tempPath)
            ->deleteFileAfterSend(true);
    }
}