<?php
// central-admin/app/Http/Controllers/Export/CandidateExportController.php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class CandidateExportController extends Controller
{
    public function excel(Request $request)
    {
        set_time_limit(120);
        $this->ensureHasFilters($request);
        [$rows, $name] = $this->fetchRows($request);
       // $name = $this->exportFilename($request);
        return $this->xlsx($rows, $name);   // $rows = generator
    }

    public function csv(Request $request)
    {
        $this->ensureHasFilters($request);
        [$rows, $name] = $this->fetchRows($request);
        return $this->csvOut($rows, $name); // $rows = generator
    }

     public function pdf(Request $request)
{
    $this->ensureHasFilters($request);

    [$rows, $name] = $this->fetchRows($request);

    $pdfRows = [];

    foreach ($rows as $row) {
        $pdfRows[] = $this->sanitizeRow($row);
    }

    return $this->pdfOut($pdfRows, $name);
}

    private function ensureHasFilters(Request $request): void
{
    $hasAny =
        (int) $request->query('acad_session_id', 0) > 0 ||
        (int) $request->query('program_type_id', 0) > 0 ||
        (int) $request->query('application_type_id', 0) > 0 ||
        (int) $request->query('faculty_id', 0) > 0 ||
        (int) $request->query('department_id', 0) > 0 ||
        (int) $request->query('program_id', 0) > 0 ||
        $request->query('status') !== null ||
        trim((string) $request->query('q', '')) !== '' ||
        $request->filled('date_from') ||
        $request->filled('date_to');

    if (!$hasAny) {
        throw ValidationException::withMessages([
            'filters' => 'Please apply at least one filter or search before exporting.',
        ]);
    }
}

    /**
     * Pulls all pages from Admissions API as generator.
     */
    private function fetchRows(Request $request): array
    {

          $data = $request->validate([
    'acad_session_id'      => ['nullable', 'integer', 'min:1'],
    'program_type_id'      => ['nullable', 'integer', 'min:1'],
    'application_type_id'  => ['nullable', 'integer', 'min:1'],
    'faculty_id'           => ['nullable', 'integer', 'min:1'],
    'department_id'        => ['nullable', 'integer', 'min:1'],
    'program_id'           => ['nullable', 'integer', 'min:1'],

    'status' => [
        'nullable',
        Rule::in([
            'submitted',
            'registered',
            'paid',
            'payment_pending',
            'draft',
            'admitted',
            'any',
            '',
        ]),
    ],

    'q' => ['nullable', 'string', 'max:200'],

    'date_from' => ['nullable', 'date'],
    'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
     ]);

        $apiUrl = rtrim((string) config('services.admissions.url'), '/');
        $token  = (string) config('services.admissions.token');
        $listUrl = $apiUrl . '/candidates';

        $getJson = function (string $url, array $query = []) use ($token): array {
            $req = Http::timeout(30);
            if ($token !== '') $req = $req->withToken($token);
            return $req->get($url, $query)->throw()->json();
        };

            $params = array_filter([
    'acad_session_id'     => $data['acad_session_id'] ?? null,
    'program_type_id'     => $data['program_type_id'] ?? null,
    'application_type_id' => $data['application_type_id'] ?? null,
    'faculty_id'          => $data['faculty_id'] ?? null,
    'department_id'       => $data['department_id'] ?? null,
    'program_id'          => $data['program_id'] ?? null,
    'status'              => $data['status'] ?? null,
    'q'                   => $data['q'] ?? null,
    'date_from'           => $data['date_from'] ?? null,
    'date_to'             => $data['date_to'] ?? null,


    'per_page'            => 200,
          ], fn ($value) => $value !== null && $value !== '');

$generator = function () use ($listUrl, $getJson, $params) {

    $page = 1;

    while (true) {

        $query = array_merge(
            $params,
            [
                'page' => $page,
            ]
        );

        $json = $getJson(
            $listUrl,
            $query
        );

        foreach ($json['data'] ?? [] as $candidate) {
            yield $this->transformCandidate($candidate);
        }

        $currentPage = (int) (
            $json['meta']['current_page'] ?? $page
        );

        $lastPage = (int) (
            $json['meta']['last_page'] ?? $currentPage
        );

        if ($currentPage >= $lastPage) {
            break;
        }

        $page = $currentPage + 1;
    }
};


$name = $this->exportFilename($request);

        return [$generator(), $name];
    }

 private function sanitizeRow(array $row): array
{
    return array_map(function ($val) {
        if (is_string($val)) {
            return mb_convert_encoding($val, 'UTF-8', 'UTF-8');
        }
        return $val;
    }, $row);
}



private function xlsx(iterable $rows, string $name)
{
    $writer = SimpleExcelWriter::streamDownload(
        $name . '.xlsx'
    );

    foreach ($rows as $row) {
        $writer->addRow(
            $this->sanitizeRow($row)
        );
    }

    $writer->close();
}

private function csvOut(iterable $rows, string $name)
{
    $lines = $this->buildHeaderLinesFromRequest();

   $cols = [
    'RegNo',
    'Name',
    'Sex',
    'State',
    'Program Type',
    'Application Type',
    'JAMB No',
    'JAMB Score',
    'Qualification(s)',
    'Submitted At',
];

    return response()->streamDownload(function () use ($rows, $name, $lines, $cols) {
        $writer = SimpleExcelWriter::streamDownload($name . '.csv');

        foreach ($lines as $line) {
            //$writer->addRow([$line, '', '', '', '', '']);
            $writer->addRow(array_merge([$line], array_fill(0, count($cols) - 1, '')));
        }
        $writer->addRow(['', '', '', '', '', '']);
        $writer->addRow($cols);

        foreach ($rows as $row) {
    $writer->addRow(
        $this->sanitizeRow(
            array_values($row)
        )
    );
       }



        $writer->close();
    }, $name . '.csv', [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}

private function pdfOut(array $rows, string $name)
{

    $req         = request();
    $generatedAt = now()->timezone('Africa/Lagos');

    $pt  = trim((string) $req->query('program_type_name', ''));
    $fac = trim((string) $req->query('faculty_name', ''));
    $dep = trim((string) $req->query('department_name', ''));
    $pro = trim((string) $req->query('program_name', ''));
    $session = trim((string) $req->query('acad_session_name', ''));
    $appType = trim((string) $req->query('application_type_name', ''));

    $hdr = [
        'school'     => 'Heritage Polytechnic',
        'session'          => $session ?: null,
        'faculty'    => $fac !== '' ? 'Faculty of ' . $fac : null,
        'department' => $dep !== '' ? 'Department of ' . $dep : null,
        'program'    => $pro !== '' ? 'Course of Study: ' . $pro : null,
        'ptype'      => $pt ?: null,
        'application_type' => $appType ?: null,

    ];

    $pdf = Pdf::loadView('exports.candidates_pdf', [
            'rows'         => $rows,
            'generated_at' => $generatedAt,
            'hdr'          => $hdr,
        ])->setPaper('a4', 'landscape');

    $dompdf  = $pdf->getDomPDF();
    $dompdf->render();

    $canvas  = $dompdf->getCanvas();
    $w       = $canvas->get_width();
    $h       = $canvas->get_height();
    $metrics = $dompdf->getFontMetrics();
    $font    = $metrics->getFont('DejaVu Sans');
    $size    = 9;

    $canvas->page_text(24, $h - 18, 'Generated: '.$generatedAt->format('Y-m-d H:i'), $font, $size, [0,0,0]);
    $canvas->page_text($w - 140, $h - 18, 'Page {PAGE_NUM} of {PAGE_COUNT}', $font, $size, [0,0,0]);

    return response($dompdf->output(), 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="'.$name.'.pdf',
        'Cache-Control'       => 'private, max-age=0, must-revalidate',
    ]);
}


    private function buildHeaderLinesFromRequest(): array
    {
        $req = request();

        $school     = 'Heritage Polytechnic';
        $faculty    = trim((string) $req->query('faculty_name', ''));
        $department = trim((string) $req->query('department_name', ''));
        $program    = trim((string) $req->query('program_name', ''));
        $ptype      = trim((string) $req->query('program_type_name', ''));

        $session = $req->query('session');
        if (!$session) {
            $y = (int) now()->year;
            $m = (int) now()->month;
            $start = ($m >= 8) ? $y : $y - 1;
            $session = $start . '/' . ($start + 1);
        }

        $lines = [];
        $lines[] = $school;
        if ($faculty !== '')     $lines[] = 'Faculty of ' . $faculty;
        if ($department !== '')  $lines[] = 'Department of ' . $department;
        if ($program !== '')     $lines[] = 'Course of Study: ' . $program;
        if ($ptype !== '')       $lines[] = 'Programme Type: ' . $ptype;
        $lines[] = 'Admission list – ' . $session . ' Session';

        return $lines;
    }


   private function transformCandidate(array $candidate): array
{
    $qualification = $this->formatQualifications($candidate);

    return [
        'RegNo' => $candidate['regno'] ?? '',
        'Name' => $candidate['name'] ?? '',
        'Sex' => $candidate['sex'] ?? '',
        'State' => $candidate['state'] ?? '',

        'Program Type' => $candidate['program_type'] ?? '',
        'Application Type' => $candidate['application_type'] ?? '',

        'JAMB No' => ($candidate['is_jamb_application'] ?? false)
            ? ($candidate['jamb_no'] ?? '')
            : '',

        'JAMB Score' => ($candidate['is_jamb_application'] ?? false)
            ? ($candidate['jamb_score'] ?? '')
            : '',

        'Qualification(s)' => $qualification,

        //'Status' => $candidate['status'] ?? '',

        'Submitted At' => $candidate['submitted_at'] ?? '',
    ];
}

private function formatQualifications(array $candidate): string
{
    $qualifications = [];

    /*
    |--------------------------------------------------------------------------
    | O'Level
    |--------------------------------------------------------------------------
    */

    foreach ($candidate['olevel_details'] ?? [] as $sitting) {

        $examType = trim((string) ($sitting['exam_type'] ?? ''));

        $examYear = $sitting['exam_year'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | Exam label
        |--------------------------------------------------------------------------
        |
        | Example:
        | WASSCE-2025
        | NECO-2026
        |
        */

        $label = collect([
            $examType,
            $examYear,
        ])
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->implode('-');

        /*
        |--------------------------------------------------------------------------
        | Subjects
        |--------------------------------------------------------------------------
        */

        $subjects = collect($sitting['results'] ?? [])
            ->map(function (array $result) {

                $code = $result['code']
                    ?? $result['subject']
                    ?? null;

                $grade = $result['grade'] ?? null;

                if (!$code) {
                    return null;
                }

                return $code . '=' . ($grade ?? '—');
            })
            ->filter()
            ->implode(' | ');

        if ($label !== '' && $subjects !== '') {
            $qualifications[] = $label . ': ' . $subjects;
        } elseif ($label !== '') {
            $qualifications[] = $label;
        } elseif ($subjects !== '') {
            $qualifications[] = $subjects;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Additional Qualification
    |--------------------------------------------------------------------------
    */

    $additional = $this->formatAdditionalQualification($candidate);

    if ($additional !== '') {
        $qualifications[] = $additional;
    }

    return implode(' || ', $qualifications);
}

private function formatAdditionalQualification(array $candidate): string
{
    if (($candidate['application_type_code'] ?? null) !== 'DE') {
        return '';
    }

    $qualifications = $candidate['additional_qualifications'] ?? [];

    if (empty($qualifications)) {
        return '';
    }

    if (!is_array($qualifications)) {
        return (string) $qualifications;
    }

    return collect($qualifications)
        ->map(function ($qualification) {

            if (is_string($qualification)) {
                return $qualification;
            }

            return collect([
                $qualification['qualification'] ?? null,
                $qualification['institution_name'] ?? null,
                $qualification['course_of_study'] ?? null,
                $qualification['class_of_study'] ?? null,
                $qualification['additional_info'] ?? null,
            ])
                ->filter()
                ->implode(' · ');
        })
        ->filter()
        ->implode(' || ');
}


private function exportFilename(Request $request): string
{
    $session = trim((string) $request->query('acad_session_name', ''));
    $ptype   = trim((string) $request->query('program_type_name', ''));
    $appType = trim((string) $request->query('application_type_name', ''));
    $faculty = trim((string) $request->query('faculty_name', ''));
    $dept    = trim((string) $request->query('department_name', ''));
    $program = trim((string) $request->query('program_name', ''));

    $parts = [];

    if ($session !== '') {
        $parts[] = $session;
    }

    if ($faculty !== '') {
        $parts[] = $faculty;
    } elseif ($ptype !== '') {
        $parts[] = $ptype;
    }

    if ($dept !== '') {
        $parts[] = $dept;
    }

    if ($program !== '') {
        $parts[] = $program;
    }

    if ($appType !== '') {
        $parts[] = $appType;
    }

    if (empty($parts)) {
        $parts[] = 'Candidate-List';
    }

    return collect($parts)
        ->map(fn ($part) => Str::slug($part))
        ->filter()
        ->implode('_');
}


}
