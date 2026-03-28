<!DOCTYPE html>
<html>
<head>

<meta charset="utf-8">

<style>

body{
font-family: DejaVu Sans, sans-serif;
font-size:12px;
margin:2px;
}

.header{
text-align:center;
margin-bottom:15px;
}

.logo{
position:absolute;
top:20px;
left:40px;
}

.logo img{
height:60px;
}

.title{
font-size:18px;
font-weight:bold;
letter-spacing:1px;
}

.subtitle{
font-size:13px;
margin-top:4px;
}

table{
width:100%;
border-collapse:collapse;
margin-top:5px;
}

th,td{
border:1px solid #000;
padding:3px;
}

th{
background:#eee;
}

.section{
margin-top:20px;
}

.summary{
margin-top:5px;
font-weight:bold;
}

.page-break{
page-break-after:always;
}

.footer{
position:fixed;
bottom:10px;
left:0;
right:0;
text-align:center;
font-size:10px;
}

.signature{
margin-top:50px;
width:100%;
}

.signature td{
border:none;
text-align:center;
padding-top:40px;
}

</style>

</head>

<body>


{{-- Logo --}}
<div class="header">
<div style="text-align:center; margin-bottom:8px;">
<img src="{{ public_path('logo/app.jpg') }}" style="height:70px;">
</div>

<div class="title">
HERITAGE POLYTECHNIC
</div>

<div class="subtitle">
STUDENT COURSE REGISTRATION REPORT
</div>

</div>


{{-- Student Info --}}

<table>

<tr>
<td><strong>Matric No</strong></td>
<td>{{ $student['matric_no'] ?? '' }}</td>

<td><strong>FullName</strong></td>
<td>{{ $student['name'] ?? '' }}</td>
</tr>

<tr>
<td><strong>Program</strong></td>
<td>{{ $student['program'] ?? '' }}</td>

<td><strong>Department</strong></td>
<td>{{ $student['department'] ?? '' }}</td>
</tr>

<tr>
<td><strong>Level</strong></td>
<td>{{ isset($student['level']) ? $student['level'].'00L' : '' }}</td>

<td><strong>Date Generated</strong></td>
<td>{{ now()->format('d M Y') }}</td>
</tr>

</table>

{{-- Courses --}}

@foreach($courses as $sessionName => $sessionCourses)
<div class="section">
<h3>
Academic Session: {{ $sessions[$sessionName] ?? $sessionName }}
</h3>
@foreach($sessionCourses as $semester => $semesterCourses)
<h4>
{{ $semester == 1 ? 'First Semester' : 'Second Semester' }}
</h4>

<table>

<thead>

<tr>
<th width="5%">Sn</th>
<th width="20%">Course Code</th>
<th width="65%">Course Title</th>
<th width="10%">Credit</th>
{{-- <th width="20%">Registered By</th> --}}
</tr>

</thead>

<tbody>

@php $credits = 0; $sn = 0;@endphp
@foreach($semesterCourses as $course)
@php
$credits += $course['credit_hours'];
$sn++
@endphp

<tr>
<td>{{ $sn }}</td>
<td>{{ $course['course_code'] }}</td>
<td>{{ $course['course_title'] }}</td>
<td>{{ $course['credit_hours'] }}</td>
{{-- <td>{{ $course['registered_by'] }}</td> --}}
</tr>

@endforeach

</tbody>
</table>

<div class="summary">
Semester Credit Load: {{ $credits }}
</div>
@endforeach

</div>

{{-- Credit Load Summary --}}

@php
$firstTotal = 0;
$secondTotal = 0;
@endphp

@foreach($courses as $sessionId => $sessionCourses)

@foreach($sessionCourses as $semester => $semesterCourses)

@foreach($semesterCourses as $course)

@if($semester == 1)
@php $firstTotal += $course['credit_hours']; @endphp
@elseif($semester == 2)
@php $secondTotal += $course['credit_hours']; @endphp
@endif

@endforeach

@endforeach

@endforeach

@php
$totalCredits = $firstTotal + $secondTotal;
@endphp

<table style="margin-top:25px; width:60%; border:1px solid #000;">

<tr>
<th colspan="2" style="background:#eee;">Credit Load Summary</th>
</tr>

<tr>
<td>First Semester</td>
<td>{{ $firstTotal }}</td>
</tr>

<tr>
<td>Second Semester</td>
<td>{{ $secondTotal }}</td>
</tr>

<tr>
<td><strong>Total Credits</strong></td>
<td><strong>{{ $totalCredits }}</strong></td>
</tr>

</table>

{{-- Page break between sessions --}}
@if(!$loop->last)
<div class="page-break"></div>
@endif

@endforeach



{{-- Signature Section --}}

<table class="signature">

<tr>

<td>
_________________________<br>
Registry
</td>

<td>
_________________________<br>
Head of Department
</td>


</tr>

</table>



{{-- Page Number --}}
<div class="footer">
<span class="pagenum"></span>
</div>


<script type="text/php">

if (isset($pdf)) {

$font = $fontMetrics->get_font("DejaVu Sans", "normal");

$pdf->page_text(
520,
820,
"Page {PAGE_NUM} of {PAGE_COUNT}",
$font,
9
);

}

</script>


</body>
</html>
