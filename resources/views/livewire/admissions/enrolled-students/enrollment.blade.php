<div class="space-y-6">

{{-- ========================================================= --}}
{{-- PAGE HEADER --}}
{{-- ========================================================= --}}

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

<div>
<flux:heading size="xl">
Enrolled Students
</flux:heading>

<flux:text class="mt-1">
Exceptional enrollment for approved transfer and deferred-admission cases.
</flux:text>
</div>

@if (!empty($meta['total']))
<flux:text class="text-sm">
{{ number_format($meta['total']) }} enrolled
</flux:text>
@endif

</div>


{{-- ========================================================= --}}
{{-- SCREEN STUDENT --}}
{{-- ========================================================= --}}

<flux:card>
    <div class="space-y-4">

        <div>
            <flux:heading size="sm">
                Screen Student
            </flux:heading>

            <flux:text class="mt-1">
                Enter the registration number before starting an exceptional enrollment.
            </flux:text>
        </div>

        <div class="flex flex-row items-start gap-3">

            {{-- Input --}}
            <div class="flex-1 min-w-0">
                <flux:input
                    wire:model="regno"
                    label="Registration Number"
                    placeholder="Enter registration number"
                    autocomplete="off"
                    :disabled="$screening || $processing"
                />
            </div>

            {{-- Button --}}
            <div class="shrink-0 pt-6">
                <flux:button
                    type="button"
                    wire:click="screenStudent"
                    variant="primary"
                    icon="magnifying-glass"
                    :disabled="$screening || $processing"
                    class="cursor-pointer"
                >
                    @if ($screening)
                        Screening...
                    @else
                        Enroll Student
                    @endif
                </flux:button>
            </div>

        </div>

    </div>
</flux:card>


{{-- ========================================================= --}}
{{-- SCREENING BLOCKED --}}
{{-- ========================================================= --}}

@if ($screenedCandidate)

<flux:card>

<div class="space-y-5">

<div class="flex items-start gap-3">

<div class="mt-0.5 rounded-full bg-red-100 p-2 dark:bg-red-950">

<flux:icon
name="x-circle"
class="text-red-600 dark:text-red-400"
/>

</div>

<div>

<flux:heading size="sm">
Enrollment Not Allowed
</flux:heading>

<flux:text class="mt-1">
This registration number exists in the Admissions Portal.
The candidate must be processed through Admission Processing.
</flux:text>

</div>

</div>


<div class="rounded-lg border p-4">

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

<div>
<flux:text size="sm" class="text-zinc-500">
Candidate
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $screenedCandidate['name'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $screenedCandidate['regno'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Programme
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $screenedCandidate['program'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Academic Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $screenedCandidate['acadSession'] ?? '—' }}
</flux:text>
</div>

</div>

</div>


<div class="flex justify-end">

<flux:button
type="button"
variant="ghost"
wire:click="clearScreening"
>
Clear
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ========================================================= --}}
{{-- ALREADY ENROLLED --}}
{{-- ========================================================= --}}

@if ($existingEnrollment)

<flux:card>

<div class="space-y-5">

<div class="flex items-start gap-3">

<div class="mt-0.5 rounded-full bg-amber-100 p-2 dark:bg-amber-950">

<flux:icon
name="exclamation-triangle"
class="text-amber-600 dark:text-amber-400"
/>

</div>

<div>

<flux:heading size="sm">
Student Already Enrolled
</flux:heading>

<flux:text class="mt-1">
This registration number already exists in the Student Portal.
</flux:text>

</div>

</div>


<div class="rounded-lg border p-4">

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

<div>
<flux:text size="sm" class="text-zinc-500">
Student
</flux:text>

<flux:text class="mt-1 font-medium">
{{ trim(
($existingEnrollment['first_name'] ?? '') . ' ' .
($existingEnrollment['last_name'] ?? '') . ' ' .
($existingEnrollment['other_names'] ?? '')
) ?: '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $existingEnrollment['regno'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Programme
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $existingEnrollment['program_name'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Screening Code
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $existingEnrollment['screening_code'] ?? '—' }}
</flux:text>
</div>

</div>

</div>


<div class="flex justify-end">

<flux:button
type="button"
variant="ghost"
wire:click="clearScreening"
>
Clear
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ========================================================= --}}
{{-- ELIGIBLE --}}
{{-- ========================================================= --}}

@if ($eligibleForEnrollment)

<flux:card>

<div class="space-y-5">

<div class="flex items-start gap-3">

<div class="mt-0.5 rounded-full bg-green-100 p-2 dark:bg-green-950">

<flux:icon
name="check-circle"
class="text-green-600 dark:text-green-400"
/>

</div>

<div>

<flux:heading size="sm">
Student Cleared for Exceptional Enrollment
</flux:heading>

<flux:text class="mt-1">
No matching Admissions Portal candidate was found.
You may proceed with the enrollment.
</flux:text>

</div>

</div>


<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $regno }}
</flux:text>

</div>


<div class="flex justify-end gap-3">

<flux:button
type="button"
variant="ghost"
wire:click="clearScreening"
>
Cancel
</flux:button>


<flux:button
type="button"
variant="primary"
wire:click="beginEnrollment"
icon="arrow-right"
class="cursor-pointer"
>
Continue Enrollment
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ========================================================= --}}
{{-- SCREENING ERROR --}}
{{-- ========================================================= --}}

@if ($screeningError && !$screenedCandidate && !$existingEnrollment)

<div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950">

<div class="flex items-start gap-3">

<flux:icon
name="exclamation-triangle"
class="mt-0.5 text-red-600 dark:text-red-400"
/>

<flux:text class="text-red-700 dark:text-red-300">
{{ $screeningError }}
</flux:text>

</div>

</div>
</div>
@endif


{{-- ========================================================= --}}
{{-- ENROLLMENT FORM --}}
{{-- ========================================================= --}}

@if ($showEnrollmentForm && $eligibleForEnrollment)

<flux:card>

<div class="space-y-6">

<div>

<flux:heading size="lg">
Exceptional Enrollment
</flux:heading>

<flux:text class="mt-1">
Enter the student's details and academic placement.
</flux:text>

</div>


{{-- ------------------------------------------------ --}}
{{-- Bio-data --}}
{{-- ------------------------------------------------ --}}

<div>

<flux:heading size="sm">
Student Information
</flux:heading>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

<flux:input
label="Registration Number"
wire:model="form.regno"
readonly
/>

<flux:input
label="First Name"
wire:model="form.first_name"
placeholder="Enter first name"
/>

<flux:input
label="Last Name"
wire:model="form.last_name"
placeholder="Enter last name"
/>

<flux:input
label="Other Names"
wire:model="form.other_names"
placeholder="Enter other names (if any)"
/>

<flux:input
label="Phone Number"
wire:model="form.phone_no"
placeholder="Enter phone number"
/>

<flux:select
label="Gender"
wire:model="form.gender"
>
<flux:select.option value="">
Select gender
</flux:select.option>

<flux:select.option value="M">
Male
</flux:select.option>

<flux:select.option value="F">
Female
</flux:select.option>
</flux:select>

</div>

</div>


{{-- ------------------------------------------------ --}}
{{-- Academic Placement --}}
{{-- ------------------------------------------------ --}}

<div class="border-t pt-6">

<flux:heading size="sm">
Academic Placement
</flux:heading>

<flux:text class="mt-1">
Select the programme and academic session for the enrollment.
</flux:text>


<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

<flux:select
label="Programme Type"
wire:model.live="form.program_type_id"
>

<flux:select.option value="">
Select programme type
</flux:select.option>

@foreach ($programTypes as $type)

<flux:select.option
value="{{ $type['id'] }}"
>
{{ $type['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Academic Session"
wire:model="form.acad_session_id"
>

<flux:select.option value="">
Select academic session
</flux:select.option>

@foreach ($academicSessions as $session)

<flux:select.option
value="{{ $session['id'] }}"
>
{{ $session['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Faculty"
wire:model.live="form.faculty_id"
>

<flux:select.option value="">
Select faculty
</flux:select.option>

@foreach ($faculties as $faculty)

<flux:select.option
value="{{ $faculty['id'] }}"
>
{{ $faculty['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Department"
wire:model.live="form.department_id"
:disabled="!$form['faculty_id']"
>

<flux:select.option value="">
Select department
</flux:select.option>

@foreach ($departments as $department)

<flux:select.option
value="{{ $department['id'] }}"
>
{{ $department['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Programme"
wire:model="form.program_id"
:disabled="!$form['department_id']"
>

<flux:select.option value="">
Select programme
</flux:select.option>

@foreach ($programs as $program)

<flux:select.option
value="{{ $program['id'] }}"
>
{{ $program['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Entry Mode"
wire:model="form.entry_mode_id"
>

<flux:select.option value="">
Select entry mode
</flux:select.option>

@foreach ($entryModes as $mode)

<flux:select.option
value="{{ $mode['id'] }}"
>
{{ $mode['name'] }}
</flux:select.option>

@endforeach

</flux:select>


<flux:select
label="Study Mode"
wire:model="form.study_mode_id"
>

<flux:select.option value="">
Select study mode
</flux:select.option>

<flux:select.option value="1">
Full-Time
</flux:select.option>

<flux:select.option value="2">
Part-Time
</flux:select.option>

</flux:select>


{{-- ========================================================= --}}
{{-- JAMB Details - Undergraduate Only --}}
{{-- ========================================================= --}}

@if ((int) ($form['program_type_id'] ?? 0) === 100)

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">

<flux:input
wire:model="form.jamb_no"
label="JAMB No."
placeholder="JAMB registration number"
autocomplete="off"
maxlength="50"
:disabled="$processing"
/>

<flux:input
wire:model="form.jamb_score"
label="JAMB Score"
placeholder="0 – 400"
type="number"
min="0"
max="400"
:disabled="$processing"
/>

</div>

@endif


</div>

</div>


{{-- ------------------------------------------------ --}}
{{-- Actions --}}
{{-- ------------------------------------------------ --}}

<div class="flex justify-end gap-3 border-t pt-6">

<flux:button
type="button"
variant="ghost"
wire:click="clearScreening"
:disabled="$processing"
>
Cancel
</flux:button>


<flux:button
type="button"
variant="primary"
icon="check"
class="cursor-pointer"
wire:click="confirmEnrollment"
:disabled="$processing"
>
Review Enrollment
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ========================================================= --}}
{{-- EXISTING ENROLLED STUDENTS --}}
{{-- ========================================================= --}}
@if (!$showEnrollmentForm)
<flux:card>

<div class="space-y-4">

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

<div>

<flux:heading size="sm">
Enrolled Students
</flux:heading>

<flux:text class="mt-1">
Students enrolled through exceptional enrollment.
</flux:text>

</div>


<div class="w-full sm:w-80">

<flux:input
wire:model.live.debounce.300ms="search"
placeholder="Search registration number or name..."
/>

</div>

</div>


{{-- Desktop --}}
<div class="hidden overflow-x-auto md:block">

<table class="w-full text-sm">

<thead>

<tr class="border-b">

<th class="px-4 py-3 text-left">
#
</th>

<th class="px-4 py-3 text-left">
Student
</th>

<th class="px-4 py-3 text-left">
Programme
</th>

<th class="px-4 py-3 text-left">
Session
</th>

<th class="px-4 py-3 text-left">
Screening
</th>

<th class="px-4 py-3 text-right">
Action
</th>

</tr>

</thead>


<tbody>

@forelse ($items as $index => $row)

<tr class="border-b last:border-0">

<td class="px-4 py-4 text-zinc-500">
{{ (($meta['current_page'] ?? 1) - 1) * ($meta['per_page'] ?? $perPage) + $index + 1 }}
</td>


<td class="px-4 py-4">

<div class="font-medium">

{{ trim(
($row['first_name'] ?? '') . ' ' .
($row['last_name'] ?? '') . ' ' .
($row['other_names'] ?? '')
) ?: '—' }}

</div>

<div class="mt-1 text-xs text-zinc-500">
{{ $row['regno'] ?? '—' }}
</div>

</td>


<td class="px-4 py-4">

<div>
{{ $row['program_name'] ?? '—' }}
</div>

<div class="mt-1 text-xs text-zinc-500">
{{ $row['department_name'] ?? '—' }}
</div>

</td>


<td class="px-4 py-4">
{{ $row['acad_session_name'] ?? '—' }}
</td>


<td class="px-4 py-4">

<flux:badge
color="zinc"
>
{{ $row['screening_code'] ?? '—' }}
</flux:badge>

</td>

{{--
<td class="px-4 py-4 text-right">

<flux:button
size="sm"
variant="ghost"
wire:click="viewStudent({{ $row['id'] }})"
:disabled="$loadingStudent"
icon="eye"
class="cursor-pointer"
>
View
</flux:button>

<flux:button
size="sm"
variant="ghost"
icon="pencil"
class="cursor-pointer"
wire:click="editStudent({{ $row['id'] }})"
:disabled="$loadingEdit"
>
Edit
</flux:button>

<flux:button
size="sm"
variant="ghost"
icon="trash"
class="cursor-pointer"
wire:click="confirmDeleteStudent({{ $row['id'] }})"
:disabled="$processingDelete"
>
Delete
</flux:button>

</td> --}}


{{-- Action --}}
<td class="px-4 py-4 text-right">

<flux:dropdown position="bottom" align="end">

<flux:button
variant="ghost"
size="sm"
icon:ellipsis-vertical
icon:trailing="chevron-down"
>
Actions
</flux:button>

<flux:menu>

{{-- View --}}
<flux:menu.item
icon="eye"
wire:click="viewStudent({{ $row['id'] }})"
:disabled="$loadingStudent"
class="cursor-pointer"
>
View
</flux:menu.item>

{{-- Edit --}}
<flux:menu.item
icon="pencil"
wire:click="editStudent({{ $row['id'] }})"
:disabled="$loadingEdit"
class="cursor-pointer"
>
Edit
</flux:menu.item>

<flux:menu.separator />

{{-- Delete --}}
<flux:menu.item
icon="trash"
variant="danger"
class="cursor-pointer"
wire:click="confirmDeleteStudent({{ $row['id'] }})"
:disabled="$processingDelete"
>
Delete
</flux:menu.item>

</flux:menu>

</flux:dropdown>

</td>

</tr>

@empty


<td
colspan="6"
class="px-4 py-12 text-center"
>

<flux:icon
name="academic-cap"
class="mx-auto h-8 w-8 text-zinc-400"
/>

<flux:text class="mt-2">
No enrolled students found.
</flux:text>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>


{{-- Mobile --}}
<div class="space-y-3 md:hidden">

@forelse ($items as $row)

<div class="rounded-lg border p-4">

<div class="flex items-start justify-between gap-3">

<div>

<div class="font-medium">

{{ trim(
($row['first_name'] ?? '') . ' ' .
($row['last_name'] ?? '') . ' ' .
($row['other_names'] ?? '')
) ?: '—' }}

</div>

<div class="mt-1 text-xs text-zinc-500">
{{ $row['regno'] ?? '—' }}
</div>

</div>

<flux:badge color="zinc">
{{ $row['screening_code'] ?? '—' }}
</flux:badge>

</div>


<div class="mt-4 space-y-2 text-sm">

<div class="flex justify-between gap-4">

<span class="text-zinc-500">
Programme
</span>

<span class="text-right">
{{ $row['program_name'] ?? '—' }}
</span>

</div>


<div class="flex justify-between gap-4">

<span class="text-zinc-500">
Department
</span>

<span class="text-right">
{{ $row['department_name'] ?? '—' }}
</span>

</div>


<div class="flex justify-between gap-4">

<span class="text-zinc-500">
Session
</span>

<span class="text-right">
{{ $row['acad_session_name'] ?? '—' }}
</span>

</div>

</div>

</div>

@empty

<div class="py-10 text-center">

<flux:text>
No enrolled students found.
</flux:text>

</div>

@endforelse

</div>


{{-- Pagination --}}
@if (!empty($meta['last_page']) && $meta['last_page'] > 1)

<div class="flex items-center justify-between border-t pt-4">

<flux:text class="text-sm">
Page {{ $meta['current_page'] }}
of
{{ $meta['last_page'] }}
</flux:text>


<div class="flex gap-2">

@if (!empty($links['prev']))

<flux:button
size="sm"
variant="ghost"
icon="arrow-left"
class="cursor-pointer"
wire:click="previousEnrolledPage()"
>
Previous
</flux:button>

@endif


@if (!empty($links['next']))

<flux:button
size="sm"
variant="ghost"
icon="arrow-right"
class="cursor-pointer"
wire:click="nextEnrolledPage()"
>
Next
</flux:button>

@endif

</div>

</div>

@endif

</div>

</flux:card>
@endif



{{-- Confirm  Enrollment --}}
@include('livewire.admissions.enrolled-students.partials.enrollment-confirmation-modal')
{{-- Confirm  Enrollment --}}
@include('livewire.admissions.enrolled-students.partials.enrollment-view-student-modal')
{{-- view student  Enrollment --}}
@include('livewire.admissions.enrolled-students.partials.enrollment-delete-confirmation-modal')
{{-- edit student  Enrollment --}}
@include('livewire.admissions.enrolled-students.partials.enrollment-edit-student-modal')


</div>
