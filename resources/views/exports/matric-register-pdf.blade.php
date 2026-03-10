<!DOCTYPE html>
<html>
<head>

<style>

body{
    font-family: DejaVu Sans, sans-serif;
    font-size:12px;
}

/* Header repeated on every page */
header{
    position: fixed;
    top: -130px;
    left: 0;
    right: 0;
    text-align:center;
    border-bottom:1px solid #000;
    padding-bottom:5px;
}

/* Page margins */
@page{
    margin:135px 40px 80px 40px;
}

/* Footer */
footer{
    position: fixed;
    bottom:-40px;
    right:0;
    font-size:10px;
}

.logo{
   height:55px;
    margin-bottom:5px;
}

.title{
    font-size:16px;
    font-weight:bold;
}

.subtitle{
    font-size:13px;
    font-weight:bold;
}

.filter-row{
    margin-top:2px;
    font-size:11px;
    font-weight: bold;
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:10px;
}

th,td{
    border:1px solid #000;
    padding:5px;
}

th{
    background:#eee;
}

.section-title{
    margin-top:15px;
    font-weight:bold;
}

.page-break{
    page-break-after:always;
}

.signature{
    margin-top:30px;
}

</style>

</head>

@php

$program_type_id = $program_type_id ?? null;
$faculty_id      = $faculty_id ?? null;
$department_id   = $department_id ?? null;
$program_id      = $program_id ?? null;

/*
Determine grouping level
*/

if(empty($faculty_id)){
    $groupBy = 'faculty';
}
elseif(empty($department_id)){
    $groupBy = 'department';
}
elseif(empty($program_id)){
    $groupBy = 'program';
}
else{
    $groupBy = null;
}

/*
Group students
*/

$groupedStudents = $groupBy
    ? collect($students)->groupBy($groupBy)
    : collect(['Students'=>$students]);
/*
Continuous serial numbering
*/
$sn = 1;
@endphp

<body>

{{-- HEADER (REPEATS EVERY PAGE) --}}
<header>
<img src="{{ public_path('logo/app.jpg') }}" class="logo">
<div class="title">HERITAGE POLYTECHNIC</div>
<div class="subtitle">
STUDENT'S MATRICULATION REGISTER (SMR)
</div>

<div class="filter-row">

ProgramType: {{ $program_type_name }}

@if($faculty_id)
&nbsp; | Faculty: {{ $students[0]['faculty'] ?? '' }}
@endif

@if($department_id)
&nbsp; | Department: {{ $students[0]['department'] ?? '' }}
@endif

@if($program_id)
&nbsp; | Course: {{ $students[0]['program'] ?? '' }}
@endif

&nbsp; | Session: {{ $session_name  }}

</div>

</header>


{{-- GROUPED STUDENT SECTIONS --}}
@foreach($groupedStudents as $groupName => $groupStudents)

@php
$sn = 1;
@endphp

@if($groupBy)

<div class="section-title">

@if($groupBy == 'faculty')
Faculty: {{ $groupName }}
@endif

@if($groupBy == 'department')
Department: {{ $groupName }}
@endif

@if($groupBy == 'program')
Course of Study: {{ $groupName }}
@endif
| Total Students: {{ count($groupStudents) }}
</div>
@endif
<table>

<thead>

<tr>
<th width="30">SN</th>
<th>Matric No</th>
<th>FullName</th>
<th>Sex</th>
<th>JAMBNo</th>
<th>State</th>
<th>LGA</th>
<th>DOB</th>
<th>EntryMode</th>
<th>PhoneNo</th>
<th width="120">Signature</th>
</tr>

</thead>

<tbody>
@foreach($groupStudents as $student)
<tr>
<td>{{ $sn++ }}</td>
<td>{{ $student['matric_no'] }}</td>
{{-- <td>{{ $student['fullname'] }}</td> --}}
<td>
@php
$parts = explode(',', $student['fullname']);
$surname = strtoupper(trim($parts[0] ?? ''));
$otherNames = trim($parts[1] ?? '');
@endphp
<strong>{{ $surname }}</strong>@if($otherNames), {{ $otherNames }}@endif
</td>
<td>{{ $student['sex'] }}</td>
<td>{{ $student['jamb_no'] }}</td>
<td>{{ $student['state'] }}</td>
<td>{{ $student['lga'] }}</td>
{{-- <td>{{ $student['dob'] }}</td> --}}
<td>{{ \Carbon\Carbon::parse($student['dob'])->format('d-M-Y') }}</td>
<td>{{ $student['entry_mode'] }}</td>
<td>{{ $student['phone_no'] }}</td>
<td></td>
</tr>
@endforeach
</tbody>
</table>


{{-- HOD SIGNATURE PER GROUP --}}
{{-- <div class="signature">

<table style="border:none">

<tr>

<td style="border:none;width:50%">
HOD Signature: ______________________
</td>

<td style="border:none">
Date: ______________________________
</td>

</tr>

</table>

</div> --}}


{{-- PAGE BREAK BETWEEN GROUPS --}}
@if(!$loop->last)
<div class="page-break"></div>
@endif

@endforeach

<div style="margin-top:25px; font-weight:bold;">
Total Matriculated Students: {{ count($students) }}
</div>


{{-- REGISTRAR SIGNATURE --}}
{{-- <div class="signature">

<table style="border:none">

<tr>

<td style="border:none;width:50%">
Registrar: __________________________
</td>

<td style="border:none">
Date: _______________________________
</td>

</tr>

</table>

</div> --}}

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

     // TIMESTAMP (Left Footer)
    $pdf->page_text(
        40,
        $pageHeight - 40,
        "Faculty Officer's Name/Signature & Date",
        $boldFont,
        10
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
