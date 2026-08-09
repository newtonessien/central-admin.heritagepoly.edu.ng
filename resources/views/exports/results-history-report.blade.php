<!DOCTYPE html>
<html>

<head>

<meta charset="utf-8">

<title>Student Academic Results History</title>

<style>

@page {
margin: 25px 25px 45px 25px;
}

body {
font-family: DejaVu Sans, sans-serif;
font-size: 10px;
color: #222;
line-height: 1.4;
}

h1,
h2,
h3,
h4,
p {
margin: 0;
padding: 0;
}

table {
width: 100%;
border-collapse: collapse;
}

th,
td {
border: 1px solid #d4d4d4;
padding: 5px 6px;
vertical-align: middle;
}

th {
background: #f3f4f6;
font-weight: bold;
}

.text-center {
text-align: center;
}

.text-right {
text-align: right;
}

.text-left {
text-align: left;
}

.header {
text-align: center;
}

.logo {
width: 75px;
height: 75px;
object-fit: contain;
margin-bottom: 5px;
}

.university-name {
font-size: 16px;
font-weight: bold;
}

.report-title {
font-size: 13px;
font-weight: bold;
margin-top: 4px;
}

.report-subtitle {
font-size: 9px;
color: #666;
margin-top: 3px;
}

.student-section {
margin-top: 15px;
}

.student-photo {
width: 130px;
height: 130px;
object-fit: cover;
}

.student-photo-cell {
width: 100px;
text-align: center;
vertical-align: middle;
}

.student-details th {
width: 15%;
background: #f8f8f8;
text-align: left;
}

.student-details td {
width: 35%;
}

.summary {
margin-top: 12px;
}

.summary-box {
text-align: center;
padding: 6px;
}

.summary-label {
font-size: 6px;
color: #666;
text-transform: uppercase;
}

.summary-value {
font-size: 14px;
font-weight: bold;
margin-top: 2px;
}

.result-section {
margin-top: 18px;
}

.result-header {
background: #f3f4f6;
border: 1px solid #d4d4d4;
padding: 8px 10px;
}

.result-header-title {
font-size: 12px;
font-weight: bold;
}

.result-header-meta {
margin-top: 3px;
color: #555;
font-size: 9px;
}

.result-table {
margin-top: 6px;
}

.result-table th {
font-size: 8px;
}

.result-table td {
font-size: 9px;
}

.course-code {
font-weight: bold;
}

.grade {
font-weight: bold;
}

.grade-a {
color: #15803d;
}

.grade-ab {
color: #13c956;
}

.grade-b {
color: #2563eb;
}

.grade-b {
color: #05256b;
}

.grade-c {
color: #b45309;
}

.grade-bc {
color: #723405;
}

.grade-cd {
color: #a6ae0e;
}

.grade-d,
.grade-e {
color: #c2410c;
}

.grade-f {
color: #dc2626;
}

.status {
font-size: 8px;
font-weight: bold;
}

.status-published {
color: #15803d;
}

.status-submitted {
color: #b45309;
}

.status-draft {
color: #52525b;
}

.result-total {
background: #f8fafc;
font-weight: bold;
}

.gpa-box {
margin-top: 6px;
text-align: right;
}

.gpa-label {
font-size: 9px;
color: #666;
}

.gpa-value {
font-size: 12px;
font-weight: bold;
}

.empty {
text-align: center;
padding: 20px;
color: #666;
}

.signatory {
margin-top: 45px;
}

.footer {
position: fixed;
bottom: -40px;
left: 0;
right: 0;
text-align: center;
font-size: 8px;
color: #777;
}

.page-number:after {
content: counter(page);
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

$passport = file_url(
$student['user']['photo'],
'students'
);

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$totalSessions = $summary['sessions'] ?? 0;

$totalSemesters = $summary['semesters'] ?? 0;

$totalCourses = $summary['courses'] ?? 0;

$totalUnits = $summary['units'] ?? 0;

@endphp


{{-- ============================================================
HEADER
============================================================ --}}

<div class="header">

@if($logo && file_exists($logo))

<img
src="{{ $logo }}"
class="logo"
>

@endif

<div class="university-name">
HERITAGE POLYTECHNIC
</div>

<div class="report-title">
STUDENT ACADEMIC RESULTS HISTORY
</div>

<div class="report-subtitle">
Student 360° Academic Record
</div>

</div>


{{-- ============================================================
STUDENT INFORMATION
============================================================ --}}

<div class="student-section">

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

</div>


{{-- ============================================================
SUMMARY
============================================================ --}}

<table class="summary">

<tr>

<td class="summary-box">

<div class="summary-label">
Sessions
</div>

<div class="summary-value">
{{ number_format($totalSessions) }}
</div>

</td>


<td class="summary-box">

<div class="summary-label">
Semesters
</div>

<div class="summary-value">
{{ number_format($totalSemesters) }}
</div>

</td>


<td class="summary-box">

<div class="summary-label">
Courses
</div>

<div class="summary-value">
{{ number_format($totalCourses) }}
</div>

</td>


<td class="summary-box">

<div class="summary-label">
Credit Units
</div>

<div class="summary-value">
{{ number_format($totalUnits) }}
</div>

</td>

</tr>

</table>


{{-- ============================================================
ACADEMIC RESULTS
============================================================ --}}

@forelse($groupedResults as $sessionId => $semesters)

@foreach($semesters as $semesterId => $levels)

@foreach($levels as $levelId => $levelResults)

@php

$firstResult = $levelResults->first();

$sessionName =
$firstResult['session']
?? 'Unknown Session';

$levelName =
$firstResult['level']
?? 'Unknown Level';

$totalCourses =
$levelResults->count();

$totalUnits =
$levelResults->sum(
fn ($result) =>
(int) ($result['credit_hours'] ?? 0)
);

$qualityPoints =
$levelResults->sum(
fn ($result) =>
(
(float) (
$result['grade_point'] ?? 0
)
)
*
(
(int) (
$result['credit_hours'] ?? 0
)
)
);

$gpa = $totalUnits > 0
? $qualityPoints / $totalUnits
: 0;

@endphp


<div class="result-section">

{{-- Registration Header --}}

<div class="result-header">

<div class="result-header-title">

{{ $sessionName }}

&nbsp; • &nbsp;

{{ semester_name($semesterId) }}

@if(!empty($levelName))

&nbsp; • &nbsp;

{{ is_numeric($levelName)
? $levelName . '00 Level'
: $levelName }}

@endif

</div>


<div class="result-header-meta">

{{ number_format($totalCourses) }}
{{ $totalCourses === 1 ? 'Course' : 'Courses' }}

&nbsp; • &nbsp;

{{ number_format($totalUnits) }}
{{ $totalUnits === 1 ? 'Unit' : 'Units' }}

&nbsp; • &nbsp;

GPA:
<strong>
{{ number_format($gpa, 2) }}
</strong>

</div>

</div>


{{-- ====================================================
COURSES
===================================================== --}}

<table class="result-table">

<thead>

<tr>

<th width="4%" class="text-center">
#
</th>

<th width="12%">
Course
</th>

<th width="29%">
Course Title
</th>

<th width="7%" class="text-center">
Unit
</th>

<th width="8%" class="text-center">
CA
</th>

<th width="8%" class="text-center">
Exam
</th>

<th width="8%" class="text-center">
Total
</th>

<th width="7%" class="text-center">
Grade
</th>

<th width="7%" class="text-center">
GP
</th>

<th width="10%" class="text-center">
Status
</th>

</tr>

</thead>


<tbody>

@forelse($levelResults as $result)

@php

$grade =
$result['grade'] ?? '-';

$gradeClass = match ($grade) {

'A' => 'grade-a',

'AB' => 'grade-ab',

'B' => 'grade-b',

'BC' => 'grade-bc',

'C' => 'grade-c',

'CD' => 'grade-cd',

'D' => 'grade-d',

'E' => 'grade-e',

'F' => 'grade-f',

default => '',

};


$status =
$result['status'] ?? '-';

$statusClass = match ($status) {

'published' =>
'status-published',

'submitted' =>
'status-submitted',

'draft' =>
'status-draft',

default => '',

};

@endphp


<tr>

<td class="text-center">
{{ $loop->iteration }}
</td>


<td>
<span class="course-code">
{{ $result['course_code'] ?? '-' }}
</span>
</td>


<td>
{{ $result['course_title'] ?? '-' }}
</td>


<td class="text-center">
{{ $result['credit_hours'] ?? 0 }}
</td>


<td class="text-center">
{{ $result['ca_score'] ?? '-' }}
</td>


<td class="text-center">
{{ $result['exam_score'] ?? '-' }}
</td>


<td class="text-center">
{{ $result['total_score'] ?? '-' }}
</td>


<td
class="text-center grade {{ $gradeClass }}"
>
{{ $grade }}
</td>


<td class="text-center">
{{ number_format(
(float) (
$result['grade_point'] ?? 0
),
1
) }}
</td>


<td
class="text-center status {{ $statusClass }}"
>
{{ ucfirst($status) }}
</td>

</tr>

@empty

<tr>

<td
colspan="10"
class="empty"
>
No results available.
</td>

</tr>

@endforelse

</tbody>


{{-- =================================================
TOTAL
================================================== --}}

<tfoot>

<tr class="result-total">

<td
colspan="3"
class="text-right"
>
Total Units
</td>

<td class="text-center">
{{ number_format($totalUnits) }}
</td>

<td colspan="4"></td>

<td class="text-center">
GPA
</td>

<td class="text-center">
{{ number_format($gpa, 2) }}
</td>

</tr>

</tfoot>

</table>

</div>

@endforeach

@endforeach

@empty

<div class="empty">

No academic results history available.

</div>

@endforelse


{{-- ============================================================
SIGNATORY
============================================================ --}}

@include('exports.partials.signatory')


{{-- ============================================================
FOOTER
============================================================ --}}

<div class="footer">

Generated on
{{ now()->format('d M Y h:i A') }}

&nbsp; • &nbsp;

Page <span class="page-number"></span>

</div>


</body>

</html>
