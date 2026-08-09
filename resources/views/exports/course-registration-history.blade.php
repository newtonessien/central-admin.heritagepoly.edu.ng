<!DOCTYPE html>
<html>

<head>

<meta charset="utf-8">

<title>Student Course Registration History</title>

<style>

@page {
margin: 35px 35px 55px 35px;
}

body {
font-family: DejaVu Sans, sans-serif;
font-size: 11px;
color: #222;
}

h1,
h2,
h3,
h4 {
margin: 0;
padding: 0;
}

table {
width: 100%;
border-collapse: collapse;
}

th,
td {
border: 1px solid #ccc;
padding: 6px;
}

th {
background: #f3f3f3;
text-align: left;
}

.text-center {
text-align: center;
}

.text-right {
text-align: right;
}

.no-border td{
border:none;
}

.header {
text-align: center;
margin-bottom: 18px;
}

.logo {
width: 75px;
height: auto;
margin-bottom: 6px;
}

.university-name {
font-size: 17px;
font-weight: bold;
}

.report-title {
font-size: 13px;
font-weight: bold;
margin-top: 4px;
}

.student-photo {
width: 130px;
height: 130px;
object-fit: cover;
border: 1px solid #ccc;
}

.photo-placeholder {
width: 85px;
height: 105px;
border: 1px solid #ccc;
text-align: center;
vertical-align: middle;
color: #888;
font-size: 9px;
}

.student-info {
margin-top: 10px;
}

.student-info th {
background: #f7f7f7;
}

.summary {
margin-top: 15px;
}

.summary td {
border: 1px solid #ccc;
text-align: center;
}

.summary-value {
font-size: 16px;
font-weight: bold;
}

.summary-label {
font-size: 9px;
color: #666;
margin-top: 3px;
}

.registration {
margin-top: 25px;
}

.registration-header {
background: #f3f3f3;
border: 1px solid #ccc;
padding: 9px 10px;
}

.registration-session {
font-size: 13px;
font-weight: bold;
}

.registration-meta {
margin-top: 3px;
color: #555;
font-size: 10px;
}

.registration-stats {
text-align: right;
font-size: 10px;
}

.courses {
margin-top: 8px;
}

.courses th {
background: #eeeeee;
}

.courses td {
vertical-align: top;
}

.course-total td {
background: #f7f7f7;
font-weight: bold;
}

.registration-footer {
margin-top: 6px;
font-size: 9px;
color: #666;
}

.signatory {
width: 100%;
margin-top: 60px;
border-collapse: collapse;
}

.signatory td {
width: 45%;
border: none;
text-align: center;
vertical-align: top;
padding-top: 40px;
font-size: 10px;
}

.signatory td.spacer {
width: 10%;
}

.signatory .line {
width: 80%;
margin: 0 auto 8px auto;
border-top: 1px solid #222;
}

.signatory .title {
font-weight: bold;
}

.signatory .designation {
font-size: 9px;
color: #666;
margin-top: 3px;
}

.footer {
position: fixed;
bottom: -30px;
left: 0;
right: 0;
text-align: center;
font-size: 9px;
color: #777;
}

.page-number:after {
content: counter(page);
}

.avoid-break {
page-break-inside: avoid;
}

</style>

</head>

<body>


@php

/*
|--------------------------------------------------------------------------
| Logo
|--------------------------------------------------------------------------
*/

$logo = public_path('logo/app.jpg');

/*
|--------------------------------------------------------------------------
| Student Passport
|--------------------------------------------------------------------------
*/

$passport = null;

if (!empty($student['user']['photo'])) {

$passport =  $student['user']['photo'];


}

@endphp


{{-- ============================================================
HEADER
============================================================ --}}

<div class="header">

@if($logo)

<img
src="{{ $logo }}"
class="logo">

@endif

<div class="university-name">
HERITAGE POLYTECHNIC
</div>

<div class="report-title">
STUDENT COURSE REGISTRATION HISTORY
</div>
<hr/>
</div>


{{-- ============================================================
STUDENT INFORMATION
============================================================ --}}

<table class="no-border">

<tr>

<td width="82%">

<table>

<tr>

<th width="25%">Full Name</th>

<td>{{ $student['name'] }} ({{ $student['user']['sex'] }})</td>

</tr>

<tr>
<th>Registration No.</th>
<td>{{ $student['matric_no'] ?? $student['regno'] }}</td>
</tr>

<tr>
<th>Phone Number</th>
<td>{{ $student['user']['phone_no'] ?? 'N/A' }}</td>
</tr>


<tr>

<th>Programme</th>

<td>{{ $student['program'] }}</td>

</tr>

<tr>

<th>Programme Type</th>

<td>{{ $student['program_type'] }}</td>

</tr>

<tr>

<th>Faculty/Dept</th>

<td>{{ $student['faculty'] }} / {{ $student['department'] }}</td>

</tr>

{{-- <tr>

<th>Department</th>

<td>{{ $student['department'] }}</td>

</tr> --}}

</table>

</td>

<td width="18%" class="text-center">


@if(!empty($student['user']['photo']))

<img
src="{{ file_url($student['user']['photo'], 'students') }}"
class="student-photo">

@else

<div class="placeholder">

NO PHOTO

</div>

@endif

</td>

</tr>

</table>


{{-- ============================================================
SUMMARY
============================================================ --}}

<table class="summary">

<tr>

<td width="25%">

<div class="summary-value">
{{ number_format($summary['sessions'] ?? 0) }}
</div>

<div class="summary-label">
Academic Sessions
</div>

</td>


<td width="25%">

<div class="summary-value">
{{ number_format($summary['semesters'] ?? 0) }}
</div>

<div class="summary-label">
Semesters
</div>

</td>


<td width="25%">

<div class="summary-value">
{{ number_format($summary['courses'] ?? 0) }}
</div>

<div class="summary-label">
Courses
</div>

</td>


<td width="25%">

<div class="summary-value">
{{ number_format($summary['units'] ?? 0) }}
</div>

<div class="summary-label">
Credit Units
</div>

</td>

</tr>

</table>


{{-- ============================================================
COURSE REGISTRATION HISTORY
============================================================ --}}

@forelse($registrations as $registration)

<div class="registration avoid-break">

{{-- Registration Header --}}

<table>

<tr>

<td class="registration-header no-border">

<div class="registration-session">

{{ $registration['session'] ?? 'Unknown Session' }}

</div>

<div class="registration-meta">

{{ semester_name($registration['semester'] ?? null) }}

@if(!empty($registration['level']))

&nbsp; • &nbsp;

{{ is_numeric($registration['level'])
? $registration['level'] . '00 Level'
: $registration['level'] }}

@endif

</div>

</td>


<td
width="180"
class="registration-header no-border"
>

<div class="registration-stats">

{{ number_format($registration['total_courses'] ?? 0) }}
Courses

&nbsp; • &nbsp;

{{ number_format($registration['total_units'] ?? 0) }}
Units

</div>

</td>

</tr>

</table>


{{-- Courses --}}

<table class="courses">

<thead>

<tr>

<th
width="6%"
class="text-center"
>
#
</th>

<th width="18%">
Course Code
</th>

<th>
Course Title
</th>

<th
width="12%"
class="text-center"
>
Units
</th>

</tr>

</thead>


<tbody>

@forelse($registration['courses'] ?? [] as $course)

<tr>

<td class="text-center">
{{ $loop->iteration }}
</td>

<td>
{{ $course['course_code'] ?? '-' }}
</td>

<td>
{{ $course['course_title'] ?? '-' }}
</td>

<td class="text-center">
{{ $course['credit_hours'] ?? 0 }}
</td>

</tr>

@empty

<tr>

<td
colspan="4"
class="text-center"
>
No courses registered for this period.
</td>

</tr>

@endforelse


{{-- Total --}}

@if(!empty($registration['courses']))

<tr class="course-total">

<td
colspan="3"
class="text-right"
>
Total Units
</td>

<td class="text-center">

{{ number_format(
$registration['total_units'] ?? 0
) }}

</td>

</tr>

@endif

</tbody>

</table>


{{-- Registration Metadata --}}

@if(
!empty($registration['registered_by']) ||
!empty($registration['registered_on'])
)

<div class="registration-footer">

@if(!empty($registration['registered_by']))

Registered by:
<strong>
{{ $registration['registered_by'] }}
</strong>

@endif


@if(
!empty($registration['registered_by']) &&
!empty($registration['registered_on'])
)

&nbsp; • &nbsp;

@endif


@if(!empty($registration['registered_on']))

Registered:

{{ \Carbon\Carbon::parse(
$registration['registered_on']
)->format('d M Y, h:i A') }}

@endif

</div>

@endif

</div>

@empty

<table style="margin-top:30px;">

<tr>

<td class="text-center">

No course registration history available.

</td>

</tr>

</table>

@endforelse


{{-- ============================================================
SIGNATORY
============================================================ --}}

@include('exports.partials.signatory')


{{-- ============================================================
FOOTER
============================================================ --}}

<div class="footer">

Generated on {{ now()->format('d M Y h:i A') }}

&nbsp; • &nbsp;

Page <span class="page-number"></span>

</div>


</body>

</html>
