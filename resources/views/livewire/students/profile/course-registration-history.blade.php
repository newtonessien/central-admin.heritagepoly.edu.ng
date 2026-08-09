<div class="space-y-6">

{{-- =========================================================
FILTERS
========================================================== --}}

<x-filters.academic-history
    :sessions="$sessions"
    :acad-session-id="$acadSessionId"
    :fee-period-id="$feePeriodId"
    :level-id="$levelId"
>
<x-slot:actions>

{{-- Reset --}}

<flux:button
variant="ghost"
icon="arrow-path"
wire:click="resetFilters"
>
Reset
</flux:button>

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

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

<flux:card>

<flux:heading size="sm">
Sessions
</flux:heading>

<flux:heading size="xl" class="mt-2">
{{ number_format($summary['sessions'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Academic Sessions
</flux:text>

</flux:card>


<flux:card>

<flux:heading size="sm">
Semesters
</flux:heading>

<flux:heading size="xl" class="mt-2">
{{ number_format($summary['semesters'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Registered Semesters
</flux:text>

</flux:card>


<flux:card>

<flux:heading size="sm">
Courses
</flux:heading>

<flux:heading size="xl" class="mt-2">
{{ number_format($summary['courses'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Registered Courses
</flux:text>

</flux:card>


<flux:card>

<flux:heading size="sm">
Units
</flux:heading>

<flux:heading size="xl" class="mt-2">
{{ number_format($summary['units'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Credit Units
</flux:text>

</flux:card>

</div>


{{-- =========================================================
REGISTRATION HISTORY
========================================================== --}}

<div class="space-y-5">

{{-- <div>

<flux:heading size="lg">
Course Registration History
</flux:heading>

<flux:text class="mt-1 text-zinc-500">
Courses registered by academic session and semester.
</flux:text>

</div> --}}


@forelse($registrations as $registration)

<flux:card class="overflow-hidden">

{{-- =================================================
REGISTRATION HEADER
================================================== --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

<div>

<div class="flex flex-wrap items-center gap-2">

<flux:heading size="lg">

{{ $registration['session'] ?? 'Unknown Session' }}

</flux:heading>

<flux:badge
color="blue"
size="sm"
>
{{ semester_name($registration['semester'] ?? '-') . ' Semester' }}
</flux:badge>

@if(!empty($registration['level']))

<flux:badge
color="zinc"
size="sm"
>
{{ is_numeric($registration['level'])
? $registration['level'] . '00 Level'
: $registration['level'] }}
</flux:badge>

@endif

</div>

<flux:text class="mt-1">

Course Registration

</flux:text>

</div>


{{-- Registration Statistics --}}

<div class="flex items-center gap-6">

<div class="text-right">

<flux:text class="text-xs text-zinc-500">
Courses
</flux:text>

<div class="font-semibold">
{{ number_format($registration['total_courses'] ?? 0) }}
</div>

</div>


<div class="text-right">

<flux:text class="text-xs text-zinc-500">
Units
</flux:text>

<div class="font-semibold">
{{ number_format($registration['total_units'] ?? 0) }}
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

<th class="px-4 py-3 w-14 text-center">
#
</th>

<th class="px-4 py-3">
Course Code
</th>

<th class="px-4 py-3">
Course Title
</th>

<th class="px-4 py-3 text-center">
Units
</th>

</tr>

</thead>


<tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

@forelse($registration['courses'] ?? [] as $course)

<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

<td class="px-4 py-3 text-center text-zinc-500">

{{ $loop->iteration }}

</td>


<td class="px-4 py-3">

<span class="font-medium">

{{ $course['course_code'] ?? '-' }}

</span>

</td>


<td class="px-4 py-3">

{{ $course['course_title'] ?? '-' }}

</td>


<td class="px-4 py-3 text-center">

<flux:badge color="zinc">

{{ $course['credit_hours'] ?? 0 }}

</flux:badge>

</td>

</tr>

@empty

<tr>

<td
colspan="4"
class="px-4 py-10 text-center"
>

<flux:text class="text-zinc-500">

No courses registered for this period.

</flux:text>

</td>

</tr>

@endforelse

</tbody>


{{-- Registration Total --}}

@if(!empty($registration['courses']))

<tfoot>

<tr class="bg-zinc-50 dark:bg-zinc-800/50">

<td
colspan="3"
class="px-4 py-3 text-right font-semibold"
>

Total Units

</td>

<td class="px-4 py-3 text-center">

<flux:badge color="green">

{{ number_format($registration['total_units'] ?? 0) }}

</flux:badge>

</td>

</tr>

</tfoot>

@endif

</table>

</div>


{{-- =================================================
REGISTRATION META
================================================== --}}

@if(
!empty($registration['registered_by']) ||
!empty($registration['registered_on'])
)

<div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">

<div class="flex flex-wrap items-center justify-between gap-3">

@if(!empty($registration['registered_by']))

<flux:text class="text-xs text-zinc-500">

Registered by:

<span class="font-medium text-zinc-700 dark:text-zinc-300">

{{ $registration['registered_by'] }}

</span>

</flux:text>

@endif


@if(!empty($registration['registered_on']))

<flux:text class="text-xs text-zinc-500">

Registered:

<span class="font-medium text-zinc-700 dark:text-zinc-300">

{{ \Carbon\Carbon::parse($registration['registered_on'])->format('d M Y, h:i A') }}

</span>

</flux:text>

@endif

</div>

</div>

@endif

</flux:card>

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
No Course Registration History
</flux:heading>

<flux:text class="mt-2 text-zinc-500">

No course registration records were found
for the selected filters.

</flux:text>

</div>

</flux:card>

@endforelse

</div>

</div>
