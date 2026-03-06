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
margin-top: 5px;
}

.signature {
margin-top: 40px;
width: 35%;
border-top: 1px solid #000;
}

.page-number {
position: fixed;
bottom: 0;
right: 0;
font-size: 9px;
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

<strong>Semester:</strong> {{ $semesterText }} <br>
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
<h4>Grade Summary</h4>
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

{{-- SIGNATURE --}}
{{-- <div class="signature">
Course Lecturer Signature
</div> --}}

{{-- <table style="width:100%; margin-top:60px;">
    <tr>
        <td style="width:50%; text-align:left;">
            <div style="width:70%; border-top:1px solid #000; padding-top:5px;">
                Course Lecturer Signature
            </div>
        </td>

        <td style="width:50%; text-align:right;">
            <div style="width:70%; margin-left:auto; border-top:1px solid #000; padding-top:5px;">
                HOD's Signature
            </div>
        </td>
    </tr>
</table> --}}

{{-- PAGE NUMBER --}}
{{-- <script type="text/php">
if (isset($pdf)) {
$pdf->page_text(
770, 570,
"Page {PAGE_NUM} of {PAGE_COUNT}",
null, 8
);
}
</script> --}}

<script type="text/php">
if (isset($pdf)) {

    $font = $fontMetrics->get_font("helvetica", "normal");
    $boldFont   = $fontMetrics->get_font("helvetica", "bold");
    $size = 9;

    $pageWidth  = $pdf->get_width();
    $pageHeight = $pdf->get_height();

    $y = $pageHeight - 80;

    // Black color
    $color = array(0, 0, 0);

    // LEFT SIGNATURE LINE
    $pdf->line(60, $y, 260, $y, $color, 1);

    $pdf->page_text(
        100,
        $y + 5,
        "Course Lecturer Signature",
        $boldFont,
        $size
    );

    // RIGHT SIGNATURE LINE
    $pdf->line($pageWidth - 260, $y, $pageWidth - 60, $y, $color, 1);

    $pdf->page_text(
        $pageWidth - 220,
        $y + 5,
        "HOD's Signature",
        $boldFont,
        $size
    );

    // TIMESTAMP (Left Footer)
    $pdf->page_text(
        40,
        $pageHeight - 40,
        "Generated on: {{ now()->format('d M Y, h:i A') }}",
        $font,
        8
    );

    // PAGINATION (Right Footer)
    $pdf->page_text(
        $pageWidth - 120,
        $pageHeight - 40,
        "Page {PAGE_NUM} of {PAGE_COUNT}",
        $font,
        8
    );
}
</script>

</body>
</html>
