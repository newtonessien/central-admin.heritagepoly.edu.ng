<div class="space-y-6">

{{-- =========================================================
FILTERS
========================================================== --}}

<x-filters.academic-history
:sessions="$sessions"
:acad-session-id="$acadSessionId"
:fee-period-id="$semester"
:level-id="$levelId"
>

<x-slot:actions>

{{-- Status --}}

<flux:select
wire:model.live="status"
class="min-w-36"
>

<flux:select.option value="published">
Published
</flux:select.option>

<flux:select.option value="submitted">
Submitted
</flux:select.option>

<flux:select.option value="draft">
Draft
</flux:select.option>

<flux:select.option value="">
All Statuses
</flux:select.option>

</flux:select>
{{-- Reset --}}

<flux:button
variant="ghost"
icon="arrow-path"
wire:click="resetFilters"
>
Reset
</flux:button>


{{-- Export --}}

<flux:button
variant="primary"
class="cursor-pointer"
size="sm"
icon="document-arrow-down"
wire:click="exportPdf"
>
PDF
</flux:button>


</x-slot:actions>

</x-filters.academic-history>


{{-- =========================================================
SUMMARY
========================================================== --}}

<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">

{{-- Sessions --}}

<flux:card>

<flux:heading size="sm">
Sessions
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
{{ number_format($summary['sessions'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Academic Sessions
</flux:text>

</flux:card>


{{-- Semesters --}}

<flux:card>

<flux:heading size="sm">
Semesters
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
{{ number_format($summary['semesters'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Result Semesters
</flux:text>

</flux:card>


{{-- Courses --}}

<flux:card>

<flux:heading size="sm">
Courses
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
{{ number_format($summary['courses'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Results Recorded
</flux:text>

</flux:card>


{{-- Units --}}

<flux:card>

<flux:heading size="sm">
Units
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
{{ number_format($summary['units'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Credit Units
</flux:text>

</flux:card>

</div>


{{-- =========================================================
RESULTS
========================================================== --}}

<div class="space-y-5">

{{-- <div>

<flux:heading size="lg">
Academic Results
</flux:heading>

<flux:text class="mt-1 text-zinc-500">
Academic performance by session and semester.
</flux:text>

</div> --}}


@php

/*
|--------------------------------------------------------------------------
| Group Results
|--------------------------------------------------------------------------
|
| Session
|   └── Semester
|         └── Level
|
*/

$groupedResults = collect($results)
->groupBy('acad_session_id')
->map(function ($sessionResults) {

return $sessionResults
->groupBy('semester')
->map(function ($semesterResults) {

return $semesterResults
->groupBy('level_id');

});

});

@endphp


@forelse($groupedResults as $sessionId => $semesters)

@foreach($semesters as $semesterId => $levels)

@foreach($levels as $levelId => $levelResults)

@php

$firstResult = $levelResults->first();

$sessionName =
$firstResult['session'] ?? 'Unknown Session';

$levelName =
$firstResult['level'] ?? 'Unknown Level';

$totalCourses =
$levelResults->count();

$totalUnits =
$levelResults->sum(
fn ($result) =>
(int) ($result['credit_hours'] ?? 0)
);

/*
|--------------------------------------------------------------------------
| GPA
|--------------------------------------------------------------------------
|
| Weighted GPA:
|
| Sum(Grade Point × Credit Unit)
| --------------------------------
|        Total Credit Units
|
*/

$qualityPoints =
$levelResults->sum(
fn ($result) =>
(
(float) ($result['grade_point'] ?? 0)
)
*
(
(int) ($result['credit_hours'] ?? 0)
)
);

$gpa = $totalUnits > 0
? $qualityPoints / $totalUnits
: 0;

@endphp


<flux:card class="overflow-hidden">

{{-- =================================================
RESULT HEADER
================================================== --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

<div>

<div class="flex flex-wrap items-center gap-2">

<flux:heading size="lg">
{{ $sessionName }}
</flux:heading>


<flux:badge
color="blue"
size="sm"
>
{{ $semesterId == 1
? 'First Semester'
: ($semesterId == 2
? 'Second Semester'
: 'Semester '.$semesterId) }}
</flux:badge>


<flux:badge
color="zinc"
size="sm"
>
{{ $levelName }}
</flux:badge>

</div>


<flux:text class="mt-1">
Academic Result
</flux:text>

</div>


{{-- Statistics --}}

<div class="flex items-center gap-6">

<div class="text-right">

<flux:text class="text-xs text-zinc-500">
Courses
</flux:text>

<div class="font-semibold">
{{ number_format($totalCourses) }}
</div>

</div>


<div class="text-right">

<flux:text class="text-xs text-zinc-500">
Units
</flux:text>

<div class="font-semibold">
{{ number_format($totalUnits) }}
</div>

</div>


<div class="text-right">

<flux:text class="text-xs text-zinc-500">
GPA
</flux:text>

<div class="font-semibold">
{{ number_format($gpa, 2) }}
</div>

</div>

</div>

</div>


{{-- =================================================
COURSE TABLE
================================================== --}}

<div class="mt-6 overflow-x-auto">

<table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

<thead>

<tr class="text-left">

<th class="w-12 px-4 py-3 text-center">
#
</th>

<th class="px-4 py-3">
Course
</th>

<th class="px-4 py-3">
Title
</th>

<th class="px-4 py-3 text-center">
Unit
</th>

<th class="px-4 py-3 text-center">
CA
</th>

<th class="px-4 py-3 text-center">
Exam
</th>

<th class="px-4 py-3 text-center">
Total
</th>

<th class="px-4 py-3 text-center">
Grade
</th>

<th class="px-4 py-3 text-center">
GP
</th>

<th class="px-4 py-3 text-center">
Status
</th>

</tr>

</thead>


<tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

@foreach($levelResults as $result)

<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

{{-- Number --}}

<td class="px-4 py-3 text-center text-zinc-500">
{{ $loop->iteration }}
</td>


{{-- Course Code --}}

<td class="px-4 py-3">

<span class="font-medium">
{{ $result['course_code'] ?? '-' }}
</span>

</td>


{{-- Course Title --}}

<td class="px-4 py-3 min-w-64">

{{ $result['course_title'] ?? '-' }}

</td>


{{-- Unit --}}

<td class="px-4 py-3 text-center">

<flux:badge color="zinc">
{{ $result['credit_hours'] ?? 0 }}
</flux:badge>

</td>


{{-- CA --}}

<td class="px-4 py-3 text-center">

{{ $result['ca_score'] ?? '-' }}

</td>


{{-- Exam --}}

<td class="px-4 py-3 text-center">

{{ $result['exam_score'] ?? '-' }}

</td>


{{-- Total --}}

<td class="px-4 py-3 text-center font-medium">

{{ $result['total_score'] ?? '-' }}

</td>


{{-- Grade --}}

<td class="px-4 py-3 text-center">

@php
$grade = $result['grade'] ?? '-';

$gradeColor = match ($grade) {
'A' => 'green',
'B' => 'blue',
'C' => 'amber',
'D', 'E' => 'orange',
'F' => 'red',
default => 'zinc',
};
@endphp

<flux:badge color="{{ $gradeColor }}">
{{ $grade }}
</flux:badge>

</td>


{{-- Grade Point --}}

<td class="px-4 py-3 text-center">

{{ number_format(
(float) ($result['grade_point'] ?? 0),
1
) }}

</td>


{{-- Status --}}

<td class="px-4 py-3 text-center">

@php

$status = $result['status'] ?? '';

$statusColor = match ($status) {
'published' => 'green',
'submitted' => 'amber',
'draft' => 'zinc',
default => 'zinc',
};

@endphp

<flux:badge
color="{{ $statusColor }}"
size="sm"
>
{{ ucfirst($status ?: 'Unknown') }}
</flux:badge>

</td>

</tr>

@endforeach

</tbody>


{{-- =================================================
RESULT TOTAL
================================================== --}}

<tfoot>

<tr class="bg-zinc-50 dark:bg-zinc-800/50">

<td
colspan="3"
class="px-4 py-3 text-right font-semibold"
>
Total
</td>


<td class="px-4 py-3 text-center">

<flux:badge color="green">
{{ number_format($totalUnits) }}
</flux:badge>

</td>


<td
colspan="4"
class="px-4 py-3"
>
</td>


<td class="px-4 py-3 text-center">

<flux:badge color="blue">
{{ number_format($gpa, 2) }}
</flux:badge>

</td>


<td></td>

</tr>

</tfoot>

</table>

</div>


{{-- =================================================
RESULT META
================================================== --}}

<div class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-700">

<div class="flex flex-wrap items-center justify-between gap-3">

<flux:text class="text-xs text-zinc-500">

{{ $totalCourses }}
{{ $totalCourses === 1 ? 'course' : 'courses' }}

·

{{ $totalUnits }}
{{ $totalUnits === 1 ? 'unit' : 'units' }}

</flux:text>


<flux:text class="text-xs text-zinc-500">

GPA:
<span class="font-semibold text-zinc-700 dark:text-zinc-300">
{{ number_format($gpa, 2) }}
</span>

</flux:text>

</div>

</div>

</flux:card>

@endforeach

@endforeach

@empty

{{-- =====================================================
EMPTY STATE
====================================================== --}}

<flux:card>

<div class="py-16 text-center">

<flux:icon.academic-cap
class="mx-auto size-12 text-zinc-400"
/>

<flux:heading
size="lg"
class="mt-4"
>
No Academic Results
</flux:heading>

<flux:text class="mt-2 text-zinc-500">

No results were found for the selected
filters.

</flux:text>

</div>

</flux:card>

@endforelse

</div>

</div>
