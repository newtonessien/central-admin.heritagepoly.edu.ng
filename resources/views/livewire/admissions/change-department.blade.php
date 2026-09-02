<div class="space-y-6">

{{-- ================================================================
PAGE HEADER
================================================================= --}}

<div>
<flux:heading size="xl">
Change of Department
</flux:heading>

<flux:text class="mt-1">
Change a candidate's faculty, department and programme
before admission processing.
</flux:text>
</div>


{{-- ================================================================
CANDIDATE SEARCH
================================================================= --}}

<flux:card>

<div class="space-y-4">

<div>
<flux:heading size="sm">
Find Candidate
</flux:heading>

<flux:text size="sm" class="mt-1">
Enter the candidate's registration number.
</flux:text>
</div>


<div class="flex flex-col gap-3 sm:flex-row">

<div class="flex-1">

<flux:input
wire:model="regno"
placeholder="Enter Registration Number"
autocomplete="off"
wire:keydown.enter="findCandidate"
/>

@error('regno')
<flux:text
size="sm"
class="mt-1 text-red-600"
>
{{ $message }}
</flux:text>
@enderror

</div>


<flux:button
type="button"
variant="primary"
wire:click="findCandidate"
wire:loading.attr="disabled"
wire:target="findCandidate"
icon="magnifying-glass"
class="cursor-pointer"
>

<span
wire:loading.remove
wire:target="findCandidate"
class="flex items-center gap-2"
>
Find Candidate
</span>


<span
wire:loading.flex
wire:target="findCandidate"
class="items-center gap-2"
>
<flux:icon
name="arrow-path"
class="size-4 animate-spin"
/>

Searching...
</span>

</flux:button>

</div>


{{-- Error --}}
@if ($errorMessage)

<div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-900/50 dark:bg-red-950/30">

<div class="flex items-start gap-2">

<flux:icon
name="exclamation-circle"
class="mt-0.5 size-5 text-red-600"
/>

<flux:text class="text-red-700 dark:text-red-300">
{{ $errorMessage }}
</flux:text>

</div>

</div>

@endif

</div>

</flux:card>


{{-- ================================================================
CANDIDATE DETAILS
================================================================= --}}

@if ($candidate)

<flux:card>

<div class="space-y-6">

{{-- ====================================================
CANDIDATE HEADER
===================================================== --}}

<div class="flex items-start justify-between gap-4">

<div class="min-w-0">

<flux:heading size="lg">
 {{ $this->candidateName() ?: 'Candidate' }}
</flux:heading>

<div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">

<flux:text size="sm">
{{ $candidate['jamb_no'] ?? $candidate['regno']  }}
</flux:text>

@if (!empty($candidate['status']))

<span class="text-zinc-400">•</span>

@if (($candidate['status'] ?? null) === 'admitted')

<flux:badge
color="green"
icon="check-circle"
>
Admitted
</flux:badge>

@elseif (($candidate['status'] ?? null) === 'rejected')

<flux:badge
color="red"
icon="x-circle"
>
Rejected
</flux:badge>

@else

<flux:badge
color="amber"
icon="clock"
>
{{ ucfirst($candidate['status']) }}
</flux:badge>

@endif

@endif

</div>

</div>


<flux:button
type="button"
variant="ghost"
size="sm"
wire:click="resetCandidate"
:disabled="$processing"
>
Clear
</flux:button>

</div>


{{-- ====================================================
ACADEMIC INFORMATION
===================================================== --}}

<div class="grid gap-4 md:grid-cols-2">

{{-- Program Type --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Programme Type
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $currentProgramTypeName ?? '—' }}
</flux:text>

</div>


{{-- Academic Session --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Academic Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $currentSessionName ?? '—' }}
</flux:text>

</div>

</div>


{{-- ====================================================
CURRENT PLACEMENT
===================================================== --}}

<div>

<flux:heading size="sm">
Current Placement
</flux:heading>

<flux:text
size="sm"
class="mt-1"
>
The candidate's existing academic placement.
</flux:text>


<div class="mt-4 grid gap-4 md:grid-cols-3">

{{-- Faculty --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Faculty
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $currentFacultyName ?? '—' }}
</flux:text>

</div>


{{-- Department --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Department
</flux:text>

<flux:text class="mt-1 font-medium">
 {{ $currentDepartmentName ?? '—' }}
</flux:text>

</div>


{{-- Programme --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Programme
</flux:text>

<flux:text class="mt-1 font-medium">
   {{ $currentProgramName ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- ====================================================
NEW PLACEMENT
===================================================== --}}

<div class="border-t pt-6">

<flux:heading size="sm">
New Placement
</flux:heading>

<flux:text
size="sm"
class="mt-1"
>
Select the new faculty, department and programme.
</flux:text>


<div class="mt-4 grid gap-4 md:grid-cols-3">

{{-- =================================================
FACULTY
================================================== --}}

<div>

<flux:select
wire:model.live="facultyId"
placeholder="Select Faculty"
:disabled="$processing"
>
 <flux:select.option value="">
        -Select Faculty-
  </flux:select.option>

@foreach ($faculties as $faculty)

<flux:select.option
value="{{ $faculty['id'] }}"
>
{{ $faculty['name'] }}
</flux:select.option>

@endforeach

</flux:select>

@error('facultyId')

<flux:text
size="sm"
class="mt-1 text-red-600"
>
{{ $message }}
</flux:text>

@enderror

</div>


{{-- =================================================
DEPARTMENT
================================================== --}}

<div>

<flux:select
wire:model.live="departmentId"
placeholder="Select Department"
:disabled="!$facultyId || $processing"
>
 <flux:select.option value="">
        -Select Department-
  </flux:select.option>
@foreach ($departments as $department)

<flux:select.option
value="{{ $department['id'] }}"
>
{{ $department['name'] }}
</flux:select.option>

@endforeach

</flux:select>

@error('departmentId')

<flux:text
size="sm"
class="mt-1 text-red-600"
>
{{ $message }}
</flux:text>

@enderror

</div>


{{-- =================================================
PROGRAMME
================================================== --}}

<div>

<flux:select
wire:model.live="programId"
placeholder="Select Programme"
:disabled="!$departmentId || $processing"
>
 <flux:select.option value="">
        -Select programme-
  </flux:select.option>
@foreach ($programs as $program)

<flux:select.option
value="{{ $program['id'] }}"
>
{{ $program['name'] }}
</flux:select.option>

@endforeach

</flux:select>

@error('programId')

<flux:text
size="sm"
class="mt-1 text-red-600"
>
{{ $message }}
</flux:text>

@enderror

</div>

</div>

</div>


{{-- ====================================================
PROPOSED PLACEMENT PREVIEW
===================================================== --}}

@if ($facultyId && $departmentId && $programId)

<div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/20">

<div class="flex items-start gap-3">

<flux:icon
name="information-circle"
class="mt-0.5 size-5 text-amber-600"
/>

<div>

<flux:heading size="sm">
Proposed Change
</flux:heading>

<flux:text
size="sm"
class="mt-1"
>
You are about to change this candidate's
academic placement. The change will be
committed to the Admissions Portal only.
</flux:text>

</div>

</div>

</div>

@endif


{{-- ====================================================
ACTION
===================================================== --}}

<div class="flex justify-end border-t pt-4">

<flux:button
type="button"
variant="primary"
wire:click="confirmChange"
icon="check"
class="cursor-pointer"
:disabled="
!$facultyId ||
!$departmentId ||
!$programId ||
$processing
"
>
Commit Change
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ================================================================
CONFIRMATION MODAL
================================================================= --}}

<flux:modal
name="change-department-confirmation"
class="md:w-[36rem]"
>

<div class="space-y-6">

{{-- Header --}}
<div>

<flux:heading size="lg">
Confirm Change of Department
</flux:heading>

<flux:text class="mt-1">
Please review the proposed academic placement before
committing the change.
</flux:text>

</div>


@if ($candidate)

{{-- Candidate --}}
<div class="rounded-lg border p-4">

<div class="grid gap-4 sm:grid-cols-2">

<div>

<flux:text
size="sm"
class="text-zinc-500"
>
Candidate
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $this->candidateName() ?? '—' }}
</flux:text>



</div>


<div>

<flux:text
size="sm"
class="text-zinc-500"
>
RegNo
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['regno'] ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- Current vs New --}}
<div class="space-y-3">

<flux:heading size="sm">
Placement Change
</flux:heading>


<div class="grid gap-3 sm:grid-cols-2">

{{-- Current --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Current
</flux:text>

<div class="mt-2 space-y-1">

<flux:text size="sm">
<span class="text-zinc-500">
Faculty:
</span>

 {{ $currentFacultyName ?? '—' }}
</flux:text>

<flux:text size="sm">
<span class="text-zinc-500">
Department:
</span>
    {{ $currentDepartmentName ?? '—' }}
</flux:text>

<flux:text size="sm">
<span class="text-zinc-500">
Programme:
</span>

 {{ $currentProgramName ?? '—' }}
</flux:text>

</div>

</div>


{{-- New --}}
<div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900/50 dark:bg-green-950/20">

<flux:text
size="sm"
class="text-zinc-500"
>
New
</flux:text>

<div class="mt-2 space-y-1">

<flux:text size="sm">
<span class="text-zinc-500">
Faculty:
</span>

@php
$selectedFaculty = collect($faculties)
->firstWhere('id', (int) $facultyId);
@endphp

{{ $selectedFaculty['name'] ?? $facultyId ?? '—' }}
</flux:text>


<flux:text size="sm">
<span class="text-zinc-500">
Department:
</span>

@php
$selectedDepartment = collect($departments)
->firstWhere('id', (int) $departmentId);
@endphp

{{ $selectedDepartment['name'] ?? $departmentId ?? '—' }}
</flux:text>


<flux:text size="sm">
<span class="text-zinc-500">
Programme:
</span>

@php
$selectedProgram = collect($programs)
->firstWhere('id', (int) $programId);
@endphp

{{ $selectedProgram['name'] ?? $programId ?? '—' }}
</flux:text>

</div>

</div>

</div>

</div>


{{-- Warning --}}
<div class="rounded-lg bg-zinc-100 p-4 dark:bg-zinc-900">

<flux:text size="sm">
This action will update the candidate's Faculty,
Department and Programme in the Admissions Portal.
It will not admit the candidate or create/update a
Student Portal record.
</flux:text>

</div>

@endif


{{-- Modal Actions --}}
<div class="flex justify-end gap-3">

<flux:button
type="button"
variant="ghost"
x-on:click="$flux.modal('change-department-confirmation').close()"
:disabled="$processing"
>
Cancel
</flux:button>


<flux:button
type="button"
variant="primary"
icon="check"
class="cursor-pointer"
wire:click="changeDepartment"
wire:loading.attr="disabled"
wire:target="changeDepartment"
:disabled="$processing"
>

<span
wire:loading.remove
wire:target="changeDepartment"
class="flex items-center gap-2"
>
Confirm Change
</span>

<span
wire:loading.flex
wire:target="changeDepartment"
class="items-center gap-2"
>

Saving...

</span>

</flux:button>

</div>

</div>

</flux:modal>

</div>
