<div class="space-y-8 max-w-6xl mx-auto px-3 sm:px-4">

{{-- PAGE HEADER --}}
<div class="flex items-center justify-between">
<flux:heading size="lg">
Student Course Registration
</flux:heading>
</div>


{{-- STEP INDICATOR --}}
<div class="flex flex-wrap items-center gap-4 sm:gap-6 text-sm font-medium">

<div class="flex items-center gap-2">
<div class="w-7 h-7 rounded-full flex items-center justify-center
{{ $step >= 1 ? 'bg-emerald-600 text-white' : 'bg-zinc-200 dark:bg-zinc-700' }}">
1
</div>
<span class="{{ $step >= 1 ? 'text-emerald-600 font-semibold' : 'text-zinc-500' }}">
Find Student
</span>
</div>

<div class="hidden sm:block h-px flex-1 bg-zinc-300 dark:bg-zinc-700"></div>

<div class="flex items-center gap-2">
<div class="w-7 h-7 rounded-full flex items-center justify-center
{{ $step >= 2 ? 'bg-emerald-600 text-white' : 'bg-zinc-200 dark:bg-zinc-700' }}">
2
</div>
<span class="{{ $step >= 2 ? 'text-emerald-600 font-semibold' : 'text-zinc-500' }}">
Session & Level
</span>
</div>

<div class="hidden sm:block h-px flex-1 bg-zinc-300 dark:bg-zinc-700"></div>

<div class="flex items-center gap-2">
<div class="w-7 h-7 rounded-full flex items-center justify-center
{{ $step >= 3 ? 'bg-emerald-600 text-white' : 'bg-zinc-200 dark:bg-zinc-700' }}">
3
</div>
<span class="{{ $step >= 3 ? 'text-emerald-600 font-semibold' : 'text-zinc-500' }}">
Register Courses
</span>
</div>

</div>



{{-- STEP 1 : FIND STUDENT --}}
@if($step === 1)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">

<div class="flex flex-col sm:flex-row gap-3 max-w-md">

<flux:input
wire:model="regno"
placeholder="Enter Matric Number"
/>

<flux:button
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="folder-arrow-down"
wire:click="findStudent">
Load Student
</flux:button>

</div>

</div>

@endif

{{-- STUDENT INFO --}}
@if($student && $step === 2)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">

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

</div>

</div>

@endif



{{-- SESSION FILTERS --}}
@if($student)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

<flux:select wire:model="session">
<option value="">Session</option>
@foreach($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>

<flux:select wire:model="semester">
<option value="">Semester</option>
<option value="1">First Semester</option>
<option value="2">Second Semester</option>
</flux:select>

<flux:select wire:model="level">
<option value="">Level</option>
@foreach($levels as $lvl)
<option value="{{ $lvl['id'] }}">{{ $lvl['name'] }}</option>
@endforeach
</flux:select>

<div class="flex items-center gap-2 mt-2">

<input
type="checkbox"
wire:model="includeLowerLevels"
class="rounded border-zinc-300 text-emerald-600 focus:ring-emerald-500"
/>

<span class="text-sm text-zinc-600 dark:text-zinc-300">
Include Lower Levels
</span>

</div>

</div>

<div class="mt-4">

<flux:button
wire:click="loadCourses"
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="arrow-down-on-square-stack">
Load Courses
</flux:button>

<flux:button
    variant="ghost"
    class="cursor-pointer"
    wire:click="backToStep1"
>
← Change Student
</flux:button>

</div>

</div>

@endif



{{-- STEP 3 : AVAILABLE COURSES --}}
@if($step === 3 && $coursesByLevel)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">

<flux:heading size="sm">
Available Courses:

<flux:badge color="emerald" size="sm">Selected CH: {{ $selectedCredits }}</flux:badge>
<flux:badge color="blue" size="sm">Remaining CH: {{ $maxCreditLoad - $selectedCredits }}</flux:badge>
<flux:badge color="amber" size="sm">Maximum CH: {{ $maxCreditLoad }}</flux:badge>


@if($selectedCredits > $maxCreditLoad)
<flux:badge color="red" size="sm">⚠ Credit load exceeded</flux:badge>
@endif
</flux:heading>


@foreach($coursesByLevel as $levelId => $levelCourses)

<div
x-data="{ open: {{ $levelId == $level ? 'true' : 'false' }} }"
class="mt-4 border rounded-lg border-zinc-200 dark:border-zinc-700"
>

<div
@click="open = !open"
class="cursor-pointer px-4 py-3 font-semibold text-emerald-600 flex justify-between items-center"
>

<span>{{ $levelId }}00 Level Courses</span>
<span x-text="open ? '−' : '+'"></span>

</div>


<div x-show="open" x-transition>

<div class="overflow-x-auto">

<table class="w-full text-sm min-w-[500px]">

<thead class="bg-zinc-100 dark:bg-zinc-800">

<tr>
<th class="p-2"></th>
<th class="p-2 text-left">Course Code</th>
<th class="p-2 text-left">Course Title</th>
<th class="p-2 text-left">Credit</th>
</tr>

</thead>

<tbody>

@foreach($levelCourses as $course)

<tr class="border-t border-zinc-200 dark:border-zinc-700">

<td class="p-2">
    <input
        type="checkbox"
        value="{{ $course['id'] }}"
        wire:model.live="selectedCourses"
        class="w-4 h-4 rounded cursor-pointer"
        style="accent-color: #059669;"
    />
</td>

<td class="p-2">{{ $course['course_code'] }}</td>
<td class="p-2">{{ $course['course_title'] }}</td>
<td class="p-2">{{ $course['credit_hours'] }}</td>

</tr>

@endforeach

</tbody>

</table>

</div>

</div>

</div>

@endforeach

<hr class="my-6 border-t border-zinc-200 dark:border-zinc-700">
<flux:button
variant="primary"
class="cursor-pointer w-full sm:w-auto"
icon="folder-plus"
wire:click="registerCourses">
Register Selected Courses
</flux:button>

</div>

@endif



{{-- REGISTERED COURSES --}}
@if($registeredCourses)

<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-xl p-6">

<flux:heading size="sm">
Registered Courses
</flux:heading>


<div class="overflow-x-auto">

<table class="w-full mt-4 text-sm min-w-[700px]">

<thead class="bg-zinc-100 dark:bg-zinc-800">

<tr>
<th class="p-3 text-left">Course Code</th>
<th class="p-3 text-left">Title</th>
<th class="p-3 text-left">Credit</th>
<th class="p-3 text-left">Registered By</th>
<th class="p-3 text-left">Action</th>
</tr>

</thead>

<tbody>

@php $creditLoad = 0; @endphp

@foreach($registeredCourses as $course)

@php $creditLoad += $course['credit_hours']; @endphp

<tr class="border-t border-zinc-200 dark:border-zinc-700">

<td class="p-3 font-medium">{{ $course['course_code'] }}</td>
<td class="p-3">{{ $course['course_title'] }}</td>
<td class="p-3">{{ $course['credit_hours'] }}</td>
<td class="p-3">{{ $course['registered_by'] }}</td>

<td class="p-3 flex gap-2">

<flux:button
size="xs"
class="cursor-pointer"
icon="wrench"
wire:click="toggleCourse({{ $course['id'] }})">
Set as inActive
</flux:button>

<flux:button
size="xs"
class="cursor-pointer"
icon="trash"
variant="danger"
wire:click="deleteCourse({{ $course['id'] }})">
Delete
</flux:button>

</td>

</tr>

@endforeach

</tbody>

</table>

</div>


<div class="mt-4 font-semibold text-emerald-600">
Total Credit Load: {{ $creditLoad }}
</div>

</div>

@endif

</div>
