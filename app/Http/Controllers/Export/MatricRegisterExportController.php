<?php

namespace App\Http\Controllers\Export;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;
use App\Services\Clients\AdmissionsPortalClient;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\SimpleExcel\SimpleExcelWriter;

class MatricRegisterExportController
{

    protected function getStudents(Request $request)
    {
        /*
        Get academic session to derive reg_year
        */
        $sessions = app(AdmissionsPortalClient::class)->getAcadSessions();

        $session = collect($sessions)
            ->firstWhere('id', $request->acad_session_id);

        if (!$session) {
            throw new \Exception('Academic session not found');
        }

        $reg_year = (int) explode('/', $session['name'])[0];

        /*
        Call Student Portal API
        */
        $response = app(StudentPortalClient::class)
            ->getMatriculationRegister([
                'reg_year'        => $reg_year,
                'program_type_id' => $request->program_type_id,
                'faculty_id'      => $request->faculty_id,
                'department_id'   => $request->department_id,
                'program_id'      => $request->program_id,
            ]);

        /*
        Flatten API structure
        */
        return collect($response['data'] ?? [])
            ->flatten(3)
            ->values();
    }

    /*
    -----------------------------------------
    PDF EXPORT
    -----------------------------------------
    */
 public function pdf(Request $request)
{
    try {

$students = $this->getStudents($request)->toArray();
$admissions = app(AdmissionsPortalClient::class);
$programTypes = collect($admissions->getProgramTypes());
$faculties = collect($admissions->getFaculties());
$sessions = collect($admissions->getAcadSessions());
$programTypeName = $programTypes
    ->firstWhere('id', $request->program_type_id)['name'] ?? '';
$facultyName = $faculties
    ->firstWhere('id', $request->faculty_id)['name'] ?? '';
$sessionName = $sessions
    ->firstWhere('id', $request->acad_session_id)['name'] ?? '';

        $pdf = Pdf::loadView(
            'exports.matric-register-pdf',
            [
                'students'        => $students,
                'program_type_name' => $programTypeName,
                'faculty_name' => $facultyName,
                'session_name' => $sessionName,
                'total'           => count($students),
                'program_type_id' => $request->program_type_id,
                'faculty_id'      => $request->faculty_id,
                'department_id'   => $request->department_id,
                'program_id'      => $request->program_id,
                'acad_session_id' => $request->acad_session_id,
            ]
        )->setPaper('A4', 'landscape');

    $parts = array_filter([
    'Matriculation-Register',
    str_replace(' ', '-', $programTypeName),
    str_replace(' ', '-', $facultyName),
    str_replace('/', '-', $sessionName),
    now()->format('Y-m-d')
      ]);

$fileName = implode('_', $parts) . '.pdf';

        //return $pdf->stream($fileName);
        return $pdf->download($fileName);

    } catch (\Throwable $e) {

        Log::error('Matric register PDF export failed', [
            'error' => $e->getMessage()
        ]);

        abort(500, 'Unable to generate PDF');
    }
}


    /*
    -----------------------------------------
    EXCEL EXPORT
    -----------------------------------------
    */
    public function excel(Request $request)
{
    try {

        $students = $this->getStudents($request);

        $fileName = 'matriculation-register-' . now()->format('Y-m-d') . '.xlsx';

        $writer = SimpleExcelWriter::streamDownload($fileName);

        $students->each(function ($student, $index) use ($writer) {

            $writer->addRow([
                'SN'         => $index + 1,
                'Matric No'  => $student['matric_no'],
                'Name'       => $student['fullname'],
                'Faculty'    => $student['faculty'],
                'Department' => $student['department'],
                'Program'    => $student['program'],
                'Sex'        => $student['sex'],
                'JAMB No'    => $student['jamb_no'],
                'State'      => $student['state'],
                'LGA'        => $student['lga'],
                'DOB'        => $student['dob'],
                'Entry Mode' => $student['entry_mode'],
                'Phone'      => $student['phone_no'],
            ]);

        });

        return $writer->toBrowser();

    } catch (\Throwable $e) {

        Log::error('Matric register Excel export failed', [
            'error' => $e->getMessage()
        ]);

        abort(500, 'Unable to export Excel');
    }
}

}
