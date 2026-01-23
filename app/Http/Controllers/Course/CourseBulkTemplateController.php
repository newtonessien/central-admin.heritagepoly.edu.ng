<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Services\Clients\StudentPortalClient;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CourseBulkTemplateController extends Controller
{
    public function download(StudentPortalClient $studentClient): StreamedResponse
    {
        // ==========================
        // Fetch levels from API
        // ==========================
        $levels = $studentClient->getLevels() ?? [];

        // ==========================
        // Create spreadsheet
        // ==========================
        $spreadsheet = new Spreadsheet();

        /*
        |--------------------------------------------------------------------------
        | Sheet 1: COURSES (Editable)
        |--------------------------------------------------------------------------
        */
        $coursesSheet = $spreadsheet->getActiveSheet();
        $coursesSheet->setTitle('courses');

        $headers = [
            'course_code',
            'course_title',
            'credit_hours',
            'semester',
            'level_id',
            'elective',
            'category',
            'is_gst',
            'is_ume',
            'is_de',
            'is_al',
            'is_it',
        ];

        $coursesSheet->fromArray($headers, null, 'A1');

        // Optional example row
        $coursesSheet->fromArray([
            'CSC101',
            'Introduction To Computer Science',
            3,
            1,
            $levels[0]['id'] ?? '',
            0,
            'NBTE',
            0,
            1,
            1,
            0,
            0,
        ], null, 'A2');

        /*
        |--------------------------------------------------------------------------
        | Sheet 2: LEVELS (Reference)
        |--------------------------------------------------------------------------
        */
        $levelsSheet = $spreadsheet->createSheet();
        $levelsSheet->setTitle('levels');

        $levelsSheet->fromArray(
            ['level_id', 'level_name', 'year_of_study'],
            null,
            'A1'
        );

        $row = 2;
        foreach ($levels as $level) {
            $levelsSheet->fromArray([
                $level['id'],
                $level['name'] ?? '',
                $level['year_of_study'] ?? '',
            ], null, "A{$row}");
            $row++;
        }

        /*
        |--------------------------------------------------------------------------
        | Sheet 3: INSTRUCTIONS
        |--------------------------------------------------------------------------
        */
        $instructionsSheet = $spreadsheet->createSheet();
        $instructionsSheet->setTitle('instructions');

        $instructions = [
            '1. Do NOT edit column headers.',
            '2. Use only 0 or 1 for boolean fields (elective, is_gst, etc).',
            '3. Semester must be 1 or 2.',
            '4. level_id must exist in the Levels sheet.',
            '5. Program, Program Type and Sessions are selected in the portal.',
            '6. Duplicate courses per (program + level + semester) are not allowed.',
            '7. Course codes will be auto-capitalized on import.',
            '8. Course titles will be normalized automatically.',
        ];

        $instructionsSheet->fromArray(
            array_map(fn ($i) => [$i], $instructions),
            null,
            'A1'
        );

        // ==========================
        // Download response
        // ==========================
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, 'course_bulk_upload_template.xlsx');
    }
}


// <flux:link
//     href="{{ route('courses.bulk.template') }}"
//     target="_blank"
// >
//     Download Excel Template
// </flux:link>

// <flux:button
//     as="a"
//     href="{{ route('courses.bulk.template') }}"
//     variant="ghost"
// >
//     Download Excel Template
// </flux:button>

//composer require phpoffice/phpspreadsheet


