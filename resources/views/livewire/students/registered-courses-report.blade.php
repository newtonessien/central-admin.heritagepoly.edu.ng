<div class="space-y-6 max-w-6xl mx-auto px-3 sm:px-4">

{{-- HEADER --}}
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

<flux:heading size="lg">
Registered Courses Report
</flux:heading>

<div class="flex flex-col sm:flex-row gap-2">

<flux:button
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="arrow-down-circle"
wire:click="downloadPdf">
Download PDF
</flux:button>

<flux:button
variant="ghost"
icon="x-circle"
class="cursor-pointer w-full sm:w-auto"
wire:click="clear">
Clear
</flux:button>

</div>

</div>



{{-- STUDENT SEARCH --}}
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">

<div class="flex flex-col sm:flex-row gap-3 max-w-md">

<flux:input
wire:model="regno"
placeholder="Enter Matric Number"
/>

<flux:button
wire:click="findStudent"
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="user-circle">
Load Student
</flux:button>

</div>

</div>



{{-- STUDENT DETAILS --}}
@if($student)

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 text-sm bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">

<div>
<p class="text-zinc-500">Name</p>
<p class="font-semibold">{{ $student['name'] ?? '' }}</p>
</div>

<div>
<p class="text-zinc-500">Program</p>
<p class="font-semibold">{{ $student['program'] ?? '' }}</p>
</div>

<div>
<p class="text-zinc-500">Department</p>
<p class="font-semibold">{{ $student['department'] ?? '' }}</p>
</div>

<div>
<p class="text-zinc-500">Level</p>
<p class="font-semibold">{{ $level ? $level.'00L' : '-' }}</p>
</div>

</div>

@endif



{{-- FILTERS --}}
@if($student)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4">

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

<div>
<flux:select wire:model="semester">
<option value="">Semester</option>
<option value="1">First Semester</option>
<option value="2">Second Semester</option>
<option value="0">Both Semesters</option>
</flux:select>
</div>

<div>
<flux:select wire:model="session">
<option value="">Session</option>
<option value="0">All Sessions</option>

@foreach($sessions ?? [] as $id => $name)
<option value="{{ $id }}">
{{ $name }}
</option>
@endforeach

</flux:select>
</div>

<div class="flex items-end">
<flux:button
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="folder-arrow-down"
wire:click="search">
Load Registered Courses
</flux:button>
</div>

</div>

</div>

@endif



{{-- REGISTERED COURSES --}}
@if(!empty($courses))

<flux:heading size="sm">
Registered Courses
</flux:heading>

@foreach($courses as $sessionId => $sessionCourses)

<details class="border rounded-lg p-4 mt-4 bg-white dark:bg-zinc-900">

<summary class="font-semibold text-base sm:text-lg cursor-pointer flex flex-col sm:flex-row sm:flex-wrap gap-2 sm:gap-4">

<span>
Session: {{ $sessions[$sessionId] ?? $sessionId }}
</span>

@if(isset($sessionSummary[$sessionId]))

<span class="text-sm text-zinc-600">
Level: {{ $level ? $level.'00L' : '-' }}
</span>

<span class="text-sm text-zinc-600">
First Sem: {{ $sessionSummary[$sessionId]['first_sem'] }} Credits
</span>

<span class="text-sm text-zinc-600">
Second Sem: {{ $sessionSummary[$sessionId]['second_sem'] }} Credits
</span>

<span class="text-sm font-semibold text-emerald-600">
Total: {{ $sessionSummary[$sessionId]['total'] }}
</span>

@endif

</summary>



{{-- SEMESTER SECTIONS --}}
@foreach($sessionCourses as $semester => $semesterCourses)

<div class="mt-4">

<h4 class="font-semibold mb-2">
{{ $semester == 1 ? 'First Semester' : 'Second Semester' }}
</h4>

<div class="overflow-x-auto">

<table class="w-full text-sm min-w-[600px] border rounded">

<thead class="bg-gray-100 dark:bg-zinc-800">

<tr>
<th class="p-2 text-left">Course Code</th>
<th class="p-2 text-left">Course Title</th>
<th class="p-2 text-left">Credit</th>
<th class="p-2 text-left">Registered By</th>
</tr>

</thead>

<tbody>

@php $creditLoad = 0; @endphp

@foreach($semesterCourses as $course)

@php
$creditLoad += $course['credit_hours'];
@endphp

<tr class="border-t">

<td class="p-2">{{ $course['course_code'] }}</td>
<td class="p-2">{{ $course['course_title'] }}</td>
<td class="p-2">{{ $course['credit_hours'] }}</td>
<td class="p-2">{{ $course['registered_by'] }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

<div class="mt-2 text-sm font-semibold text-zinc-600">
Semester Credit Load: {{ $creditLoad }}
</div>

</div>

@endforeach

</details>

@endforeach

@endif



{{-- REGISTRATION MATRIX --}}
@if(!empty($sessionSummary))

<div class="border rounded-lg p-4 bg-white dark:bg-zinc-900">

<flux:heading size="sm">
Registration Matrix
</flux:heading>

<div class="overflow-x-auto">

<table class="w-full mt-3 text-sm min-w-[400px] border">

<thead class="bg-gray-100 dark:bg-zinc-800">

<tr>
<th class="p-2 text-left">Session</th>
<th class="p-2 text-center">First Semester</th>
<th class="p-2 text-center">Second Semester</th>
</tr>

</thead>

<tbody>

@foreach($sessionSummary as $sessionId => $summary)

<tr class="border-t">

<td class="p-2">
{{ $sessions[$sessionId] ?? $sessionId }}
</td>

<td class="p-2 text-center">
{{ $summary['first_sem'] > 0 ? '✓' : '✗' }}
</td>

<td class="p-2 text-center">
{{ $summary['second_sem'] > 0 ? '✓' : '✗' }}
</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

@endif

</div>
