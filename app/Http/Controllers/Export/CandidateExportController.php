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


//use Illuminate\Routing\Attributes\Middleware as RouteMiddleware; // ⬅️ import this

//#[RouteMiddleware('export.filters')]  // ✅ correct spelling + correct place (constructor)
class CandidateExportController extends Controller
{


    public function excel(Request $request)
    {
        $this->ensureHasFilters($request);             // belt & suspenders
        [$rows,$name] = $this->fetchRows($request);
        return $this->xlsx($rows,$name);
    }

    public function csv(Request $request)
    {
        $this->ensureHasFilters($request);
        [$rows,$name] = $this->fetchRows($request);
        return $this->csvOut($rows,$name);
    }

    public function pdf(Request $request)
    {
        $this->ensureHasFilters($request);
        [$rows,$name] = $this->fetchRows($request);
        return $this->pdfOut($rows,$name);
    }

    private function ensureHasFilters(Request $request): void
    {
        $status = $request->query('status');
        $q      = trim((string) $request->query('q', ''));

        $hasAny =
            (int) $request->query('program_type_id', 0) > 0 ||
            (int) $request->query('faculty_id', 0)      > 0 ||
            (int) $request->query('department_id', 0)   > 0 ||
            (int) $request->query('program_id', 0)      > 0 ||
            in_array($status, ['pending','approved'], true) ||
            $q !== '';

        if (! $hasAny) {
            throw ValidationException::withMessages([
                'filters' => 'Please apply at least one filter or search before exporting.',
            ]);
        }
    }

    /**
     * Pulls all pages from Admissions API and maps them to flat export rows.
     * Adds guards: require at least one filter/search; abort if no records.
     */
private function fetchRows(Request $request): array
{
    $status = $request->query('status');
    $q      = trim((string) $request->query('q', ''));

    $hasAny =
        (int) $request->query('program_type_id', 0) > 0 ||
        (int) $request->query('faculty_id', 0)      > 0 ||
        (int) $request->query('department_id', 0)   > 0 ||
        (int) $request->query('program_id', 0)      > 0 ||
        in_array($status, ['pending','approved'], true) ||
        $q !== '';

    if (! $hasAny) {
        throw ValidationException::withMessages([
            'filters' => 'Please apply at least one filter or search before exporting.',
        ]);
    }

    // 1) Validate filters (same as UI)
    $data = $request->validate([
        'program_type_id' => ['nullable','integer','min:1'],
        'faculty_id'      => ['nullable','integer','min:1'],
        'department_id'   => ['nullable','integer','min:1'],
        'program_id'      => ['nullable','integer','min:1'],
        'status'          => ['nullable', Rule::in(['pending','approved','any',''])],
        'q'               => ['nullable','string','max:200'],
    ]);

    // 2) Guard again via normalized data
    $active = [
        'program_type_id' => $data['program_type_id'] ?? null,
        'faculty_id'      => $data['faculty_id'] ?? null,
        'department_id'   => $data['department_id'] ?? null,
        'program_id'      => $data['program_id'] ?? null,
        'status'          => $data['status'] ?? null,
        'q'               => isset($data['q']) ? trim($data['q']) : null,
    ];

    $hasActive = collect($active)->contains(function ($v, $k) {
        if ($k === 'status') {
            return in_array($v, ['pending','approved'], true);
        }
        return !is_null($v) && $v !== '';
    });

    if (! $hasActive) {
        throw ValidationException::withMessages([
            'filters' => 'Please apply at least one filter or search before exporting.',
        ]);
    }

    // 3) Build bases from services.admissions.url
    $apiUrl = rtrim((string) config('services.admissions.url'), '/');
    $token  = (string) config('services.admissions.token');

    $u       = parse_url($apiUrl);
    $origin  = ($u['scheme'] ?? 'http') . '://' . ($u['host'] ?? 'localhost') . (isset($u['port']) ? ':' . $u['port'] : '');
    $apiBase = $apiUrl;
    $listUrl = $apiBase . '/candidates';

    $makeAbsolute = function (string $url) use ($origin, $apiBase, $listUrl): string {
        $url = trim($url);
        if ($url === '') return $listUrl;
        if (preg_match('~^https?://~i', $url)) return $url;
        if (str_starts_with($url, '?'))        return $listUrl . $url;
        if (str_starts_with($url, '/api/'))    return $origin . $url;
        if (str_starts_with($url, 'api/'))     return $origin . '/' . $url;
        if (str_starts_with($url, '/'))        return $origin . $url;
        return $apiBase . '/' . ltrim($url, '/');
    };

    $getJson = function (string $url, array $query = []) use ($token, $makeAbsolute): array {
        $abs = $makeAbsolute($url);
        $req = Http::timeout(30);
        if ($token !== '') $req = $req->withToken($token);
        return $req->get($abs, $query)->throw()->json();
    };

    // 4) Initial query
    $params = array_filter([
        'program_type_id' => $data['program_type_id'] ?? null,
        'faculty_id'      => $data['faculty_id'] ?? null,
        'department_id'   => $data['department_id'] ?? null,
        'program_id'      => $data['program_id'] ?? null,
        'status'          => $data['status'] ?? null,
        'q'               => $active['q'] ?? null,
        'per_page'        => 100,
    ], fn($v) => $v !== null && $v !== '');

    $rows = [];

    // 5) First page
    $json = $getJson($listUrl, $params);

    $collect = function (array $json) use (&$rows) {
        foreach ($json['data'] ?? [] as $c) {
            $olevel = collect($c['olevel_details'] ?? [])
                ->map(function ($sit) {
                    $title = ($sit['sitting'] ?? '') ?: (($sit['is_sitting'] ?? false) ? 'Second Sitting' : 'First Sitting');
                    $subs  = collect($sit['results'] ?? [])
                            ->map(fn($r) => ($r['code'] ?? $r['subject'] ?? '') . '=' . ($r['grade'] ?? ''))
                            ->implode(' | ');
                    return trim($title . ' : ' . $subs);
                })
                ->filter()
                ->implode('  ||  ');

            $rows[] = [
                'RegNo'          => $c['regno'] ?? '',
                'Name'           => $c['name'] ?? '',
                'Sex'            => $c['sex'] ?? '',
                'State'          => $c['state'] ?? '',
                'Olevel Details' => $olevel,
                'Status'         => (isset($c['status']) ? $c['status'] : (($c['is_admitted'] ?? 0) ? 'approved' : 'pending')),
            ];
        }
    };

    $collect($json);

    // 6) Follow pagination
    $next = $json['links']['next'] ?? null;
    while (!empty($next)) {
        $json = $getJson($next);
        $collect($json);
        $next = $json['links']['next'] ?? null;
    }

    // 7) Guard: no data for the selected filters
    if (count($rows) === 0) {
        throw ValidationException::withMessages([
            'filters' => 'No records found for the selected filters/search.',
        ]);
    }

    // 8) Filename with NAMES (fallback to ids)
    $tags = [];

    if (in_array(($data['status'] ?? null), ['pending','approved'], true)) {
        $tags[] = 'status-' . $data['status'];
    }

    $slug = function (?string $val): string {
        $s = Str::slug((string) $val, '-');
        return $s !== '' ? Str::limit($s, 50, '') : '';
    };

    $ptName = $request->query('program_type_name');
    $facNm  = $request->query('faculty_name');
    $depNm  = $request->query('department_name');
    $prgNm  = $request->query('program_name');

    if ($pt = $slug($ptName)) { $tags[] = 'program-type-' . $pt; }
    elseif (!empty($data['program_type_id'])) { $tags[] = 'program-type-' . (int) $data['program_type_id']; }

    if ($fac = $slug($facNm)) { $tags[] = 'faculty-' . $fac; }
    elseif (!empty($data['faculty_id'])) { $tags[] = 'faculty-' . (int) $data['faculty_id']; }

    if ($dep = $slug($depNm)) { $tags[] = 'department-' . $dep; }
    elseif (!empty($data['department_id'])) { $tags[] = 'department-' . (int) $data['department_id']; }

    if ($prg = $slug($prgNm)) { $tags[] = 'program-' . $prg; }
    elseif (!empty($data['program_id'])) { $tags[] = 'program-' . (int) $data['program_id']; }

    $name = 'candidates_' . now()->format('Ymd_His') . (count($tags) ? '__' . implode('_', $tags) : '');

    return [$rows, $name];
}


   private function xlsx(array $rows, string $name)
{
    $lines = $this->buildHeaderLinesFromRequest();

    // Define table columns (keep it consistent with your $rows shape)
    $cols = ['RegNo','Name','Sex','State','Olevel Details','Status'];

    return response()->streamDownload(function () use ($rows, $name, $lines, $cols) {
        $writer = \Spatie\SimpleExcel\SimpleExcelWriter::streamDownload($name . '.xlsx');

        // ── Preamble (each line goes into the first column; others empty)
        foreach ($lines as $line) {
            $writer->addRow([$line, '', '', '', '', '']);
        }

        // Blank spacer row
        $writer->addRow(['', '', '', '', '', '']);

        // Table header row (we write it ourselves instead of addHeader())
        $writer->addRow($cols);

        // Table data rows (ordered to match $cols)
        foreach ($rows as $r) {
            $writer->addRow([
                $r['RegNo'] ?? '',
                $r['Name'] ?? '',
                $r['Sex'] ?? '',
                $r['State'] ?? '',
                $r['Olevel Details'] ?? '',
                $r['Status'] ?? '',
            ]);
        }

        // flush to browser
        $writer->toBrowser();
    }, $name . '.xlsx', [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ]);
}


 private function csvOut(array $rows, string $name)
{
    $lines = $this->buildHeaderLinesFromRequest();
    $cols  = ['RegNo','Name','Sex','State','Olevel Details','Status'];

    return response()->streamDownload(function () use ($rows, $name, $lines, $cols) {
        $writer = \Spatie\SimpleExcel\SimpleExcelWriter::streamDownload($name . '.csv');

        // Preamble lines
        foreach ($lines as $line) {
            $writer->addRow([$line, '', '', '', '', '']);
        }

        // Blank spacer
        $writer->addRow(['', '', '', '', '', '']);

        // Header row
        $writer->addRow($cols);

        // Data
        foreach ($rows as $r) {
            $writer->addRow([
                $r['RegNo'] ?? '',
                $r['Name'] ?? '',
                $r['Sex'] ?? '',
                $r['State'] ?? '',
                $r['Olevel Details'] ?? '',
                $r['Status'] ?? '',
            ]);
        }

        $writer->toBrowser();
    }, $name . '.csv', [
        'Content-Type' => 'text/csv; charset=UTF-8',
    ]);
}


    private function pdfOut(array $rows, string $name)
{
    $req         = request();
    $generatedAt = now()->timezone('Africa/Lagos');

    // Use names from query if available
    $pt  = trim((string) $req->query('program_type_name', ''));
    $fac = trim((string) $req->query('faculty_name', ''));
    $dep = trim((string) $req->query('department_name', ''));
    $pro = trim((string) $req->query('program_name', ''));

    // Session label (or override via ?session=…)
    //$session = $this->sessionLabel($req);

    $hdr = [
        'school'     => 'Heritage Polytechnic',
        'faculty'    => $fac !== '' ? 'Faculty of ' . $fac : null,
        'department' => $dep !== '' ? 'Department of ' . $dep : null,
        'program'    => $pro !== '' ? 'Course of Study: ' . $pro : null,
        'ptype'      => $pt ?: null,
        //'session'    => "Admission list – {$session} Session",
    ];

    $pdf = Pdf::loadView('exports.candidates_pdf', [
            'rows'         => $rows,
            'generated_at' => $generatedAt,
            'hdr'          => $hdr,
        ])->setPaper('a4', 'landscape');

    // render + footer (timestamp + page numbers)
    $dompdf  = $pdf->getDomPDF();
    $dompdf->render();

    $canvas  = $dompdf->getCanvas();
    $w       = $canvas->get_width();
    $h       = $canvas->get_height();
    $metrics = $dompdf->getFontMetrics();
    $font    = $metrics->getFont('DejaVu Sans');
    $size    =  9; // or just 9

    $canvas->page_text(24,       $h - 18, 'Generated: '.$generatedAt->format('Y-m-d H:i'), $font, $size, [0,0,0]);
    $canvas->page_text($w - 140, $h - 18, 'Page {PAGE_NUM} of {PAGE_COUNT}',   $font, $size, [0,0,0]);

    return response($dompdf->output(), 200, [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="'.$name.'.pdf',
        'Cache-Control'       => 'private, max-age=0, must-revalidate',
    ]);
}

private function buildHeaderLinesFromRequest(): array
{
    $req = request();

    // These names are coming from your Blade exportParams
    $school = 'Heritage Polytechnic';
    $faculty = trim((string) $req->query('faculty_name', ''));
    $department = trim((string) $req->query('department_name', ''));
    $program = trim((string) $req->query('program_name', ''));
    $ptype = trim((string) $req->query('program_type_name', ''));

    // Session label, or override with ?session=...
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
    if ($ptype !== '')       $lines[] = 'Programme Type: ' . $ptype; // optional line
    $lines[] = 'Admission list – ' . $session . ' Session';

    return $lines;
}




}
