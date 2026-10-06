<!DOCTYPE html>
<html>
<head>
<style>
body {
font-family: DejaVu Sans, sans-serif;
font-size: 12px;
}

.center { text-align: center; }

.header {
text-align: center;
margin-bottom: 3px;
}

.header img {
height: 60px;
}

.info {
margin-top: 5px;
margin-bottom: 10px;
}

table {
width: 100%;
border-collapse: collapse;

}

th, td {
border: 1px solid #000;
padding: 3px;
}

th {
background: #f2f2f2;
}

.summary {
margin-top: 15px;
}


.page-number {
position: fixed;
bottom: 0;
right: 0;
font-size: 9px;
}

.signature-table {
    width: 100%;
    border: none !important;
    border-collapse: collapse;
    margin-top: 55px;
}

.signature-table td {
    width: 50%;
    border: none !important;
    text-align: center;
    padding: 0 40px;
}

.signature-line {
    width: 70%;
    margin: 0 auto 5px auto;
    border-top: 1px solid #000;
    height: 1px;
}
</style>
</head>
<body>

{{-- HEADER --}}
<div class="header" style="text-align:center; margin-bottom:5px;">
    <img src="{{ public_path('logo/app.jpg') }}"
         style="height:70px; display:block; margin:0 auto 8px auto;">

    <div style="font-size:24px; font-weight:bold; margin:0;">
        HERITAGEPOLYTECHNIC
    </div>

    <div style="font-size:18px; font-weight:bold; margin:2px 0 0 0;">
        STUDENT'S RESULT SHEET
    </div>
</div>

{{-- COURSE INFO --}}
<div class="info">
<table style="width:100%; margin-bottom:10px; border: none;border-collapse:collapse">
<tr>
<td style="width:50%; vertical-align:top;">
<strong>Faculty:</strong> {{ $faculty['name'] ?? '' }} <br>
<strong>Department:</strong> {{ $department['name'] ?? '' }} <br>
<strong>Programme:</strong> {{ $program['name'] ?? '' }} <br>
<strong>Program Type:</strong> {{ $programType['name'] ?? '' }}
</td>

<td style="width:50%; vertical-align:top;">
<strong>Course:</strong>
{{ $course['course_code'] ?? '' }} -
{{ $course['course_title'] ?? '' }} ({{ $course['credit_hours'] ?? '' }}) <br>

<strong>Semester/Academic Period:</strong> {{ $semesterText }} / {{ $academicPeriodText }} <br>
{{-- <strong>Academic Period:</strong> {{ $academicPeriodText }} <br> --}}
<strong>Session:</strong> {{ $session['name'] ?? '' }} <br>
<strong>Total Students:</strong> {{ $totalStudents }}
</td>
</tr>
</table>
</div>

{{-- STUDENT TABLE --}}
<div style="padding-bottom:65px;">
<table>
<thead>
<tr>
<th>#</th>
<th style="text-align: left">Reg. Number</th>
<th style="text-align: left">FullName</th>
<th style="text-align: left">Level</th>
<th>CA &nbsp;</th>
<th>Exam</th>
<th>Total</th>
<th>Grade</th>
<th>Remark</th>
</tr>
</thead>
<tbody>
@foreach($students as $index => $student)
<tr>
<td>{{ $index + 1 }}</td>
<td>{{ $student['matric_no'] }}</td>
<td>{{ $student['name'] }}</td>
<td>{{ $student['student_level_id'].'00' }}</td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>
@endforeach
</tbody>
</table>
</div>


{{-- GRADE SUMMARY --}}
<div class="summary">

    <h4 style="margin: 0 0 8px 0;">
        Grade Summary
    </h4>

    <table>
        <tr>
            <th>A</th>
            <th>B</th>
            <th>C</th>
            <th>D</th>
            <th>E</th>
            <th>F</th>
            <th>Total</th>
        </tr>

        <tr>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td>&nbsp;</td>
        </tr>
    </table>

</div>

{{-- SIGNATURES --}}
<table class="signature-table">
    <tr>
        <td>
            <div class="signature-line"></div>
            <strong>Course Lecturer Signature</strong>
        </td>

        <td>
            <div class="signature-line"></div>
            <strong>HOD's Signature</strong>
        </td>
    </tr>
</table>



<script type="text/php">
if (isset($pdf)) {

    $font = $fontMetrics->get_font("helvetica", "normal");
    $boldFont = $fontMetrics->get_font("helvetica", "bold");

    $pageWidth  = $pdf->get_width();
    $pageHeight = $pdf->get_height();

    $color = array(0, 0, 0);


    /*
    |--------------------------------------------------------------------------
    | FOOTER
    |--------------------------------------------------------------------------
    */

    $footerY = $pageHeight - 20;

    // Generated timestamp
    $pdf->page_text(
        40,
        $footerY,
        "Generated on: {{ now()->format('d M Y, h:i A') }}",
        $font,
        8
    );

    // Page number
    $pdf->page_text(
        $pageWidth - 120,
        $footerY,
        "Page {PAGE_NUM} of {PAGE_COUNT}",
        $font,
        8
    );
}
</script>

</body>
</html>
