<div class="space-y-6">

{{-- ================================================================
PAGE HEADER
================================================================= --}}
<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

<div>
<flux:heading size="xl">
Admission Processing
</flux:heading>

<flux:text class="mt-1 text-zinc-500">
Review submitted candidates for admission processing.
</flux:text>
</div>

<div class="flex items-center gap-2">
@if ($hasSearched)
<flux:badge color="green" icon="check-circle">
{{ number_format($total) }} candidate{{ $total === 1 ? '' : 's' }}
</flux:badge>
@endif
</div>

</div>


{{-- ================================================================
FILTER PANEL
================================================================= --}}
<div class="rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

{{-- Filter Header --}}
<div class="flex items-center justify-between border-b border-zinc-200 px-5 py-4 dark:border-zinc-700">

<div class="flex items-center gap-3">

<div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
<flux:icon name="funnel" class="size-5 text-zinc-600 dark:text-zinc-300" />
</div>

<div>
<flux:heading size="sm">
Candidate Filters
</flux:heading>

<flux:text class="text-xs text-zinc-500">
Select the admission context before loading candidates.
</flux:text>
</div>

</div>

@if ($hasSearched)
<flux:button
variant="ghost"
size="sm"
wire:click="resetFilters"
wire:loading.attr="disabled"
>
<flux:icon name="x-mark" class="size-4" />
{{-- Clear --}}
</flux:button>
@endif

</div>


{{-- Filter Body --}}
<div class="p-5">

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">

{{-- ====================================================
Academic Session
===================================================== --}}
<flux:field>

<flux:label>
Academic Session
<span class="text-red-500">*</span>
</flux:label>

<flux:select
wire:model="acad_session_id"
placeholder="Select academic session"
>
@foreach ($acadSessions as $session)
<flux:select.option
value="{{ $session['id'] }}"
>
{{ $session['name'] }}
</flux:select.option>
@endforeach
</flux:select>

</flux:field>


{{-- ====================================================
Program Type
===================================================== --}}
<flux:field>

<flux:label>
Program Type
<span class="text-red-500">*</span>
</flux:label>

<flux:select
wire:model.live="program_type_id"
>

  <flux:select.option value="">
        -Select program type-
  </flux:select.option>


{{-- @foreach ($programTypes as $type) --}}
@foreach ($this->availableProgramTypes as $type)
<flux:select.option
value="{{ $type['id'] }}"
>
{{ $type['name'] }}
</flux:select.option>
@endforeach
</flux:select>

</flux:field>


{{-- ====================================================
Application Type
===================================================== --}}
<flux:field>

<flux:label>
Application Type
</flux:label>

<flux:select
    wire:key="application-type-select-{{ $program_type_id ?: 'none' }}"
    wire:model.live="application_type_id"
    placeholder="Select application type"
    :disabled="!$program_type_id || $loadingApplicationTypes"
>

<flux:select.option value="">
        -Select application type-
  </flux:select.option>

@foreach ($applicationTypes as $type)
<flux:select.option
value="{{ $type['id'] }}"
>
{{ $type['name'] }}
</flux:select.option>
@endforeach
</flux:select>

@if ($loadingApplicationTypes)
<flux:description>
Loading application types...
</flux:description>
@endif

</flux:field>


{{-- ====================================================
Faculty
===================================================== --}}
<flux:field>

<flux:label>
Faculty
</flux:label>

<flux:select
wire:model.live="faculty_id"
>
<flux:select.option value="">
        -Select faculty-
  </flux:select.option>
@foreach ($faculties as $faculty)
<flux:select.option
value="{{ $faculty['id'] }}"
>
{{ $faculty['name'] }}
</flux:select.option>
@endforeach
</flux:select>

</flux:field>


{{-- ====================================================
Department
===================================================== --}}
<flux:field>

<flux:label>
Department
</flux:label>

<flux:select
wire:model.live="department_id"
:disabled="!$faculty_id || $loadingDepartments"
>
<flux:select.option value="">
        -Select department-
  </flux:select.option>
@foreach ($departments as $department)
<flux:select.option
value="{{ $department['id'] }}"
>
{{ $department['name'] }}
</flux:select.option>
@endforeach
</flux:select>

@if ($loadingDepartments)
<flux:description>
Loading departments...
</flux:description>
@endif

</flux:field>


{{-- ====================================================
Programme
===================================================== --}}
<flux:field>

<flux:label>
Programme
</flux:label>

<flux:select
wire:model="program_id"
:disabled="!$department_id || $loadingPrograms"
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

@if ($loadingPrograms)
<flux:description>
Loading programmes...
</flux:description>
@endif

</flux:field>

</div>


{{-- ========================================================
SEARCH + ACTION
========================================================= --}}
<div class="mt-5 flex flex-col gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 lg:flex-row lg:items-end">

<div class="flex-1">

<flux:field>

<flux:label>
Candidate Search
</flux:label>

<flux:input
wire:model="search"
wire:keydown.enter="searchCandidates"
placeholder="Search by RegNo, JAMB No, surname or name..."
icon="magnifying-glass"
clearable
/>

</flux:field>

</div>


<div class="flex items-center gap-2">

<flux:button
variant="primary"
icon="magnifying-glass"
wire:click="applyFilters"
wire:loading.attr="disabled"
class="cursor-pointer"
>
<span wire:loading.remove wire:target="applyFilters">
Apply Filters
</span>

<span wire:loading wire:target="applyFilters">
Loading...
</span>
</flux:button>

@if ($hasSearched)
<flux:button
variant="ghost"
wire:click="clearSearch"
wire:loading.attr="disabled"
>
Clear Search
</flux:button>
@endif

</div>

</div>

</div>

</div>


{{-- ================================================================
ACTIVE FILTER SUMMARY
================================================================= --}}
@if ($hasSearched)

<div class="flex flex-wrap items-center gap-2">

<flux:text class="mr-1 text-sm font-medium">
Processing:
</flux:text>

@if ($this->selectedSessionName)
<flux:badge color="blue">
{{ $this->selectedSessionName }}
</flux:badge>
@endif

@if ($this->selectedProgramTypeName)
<flux:badge>
{{ $this->selectedProgramTypeName }}
</flux:badge>
@endif

@if ($this->selectedApplicationTypeName)
<flux:badge>
{{ $this->selectedApplicationTypeName }}
</flux:badge>
@endif

@if ($this->selectedFacultyName)
<flux:badge>
{{ $this->selectedFacultyName }}
</flux:badge>
@endif

@if ($this->selectedDepartmentName)
<flux:badge>
{{ $this->selectedDepartmentName }}
</flux:badge>
@endif

@if ($this->selectedProgramName)
<flux:badge>
{{ $this->selectedProgramName }}
</flux:badge>
@endif

@if (trim($search) !== '')
<flux:badge color="amber">
Search: {{ $search }}
</flux:badge>
@endif

</div>

@endif


{{-- ================================================================
CANDIDATE LIST
================================================================= --}}
<div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

{{-- List Header --}}
<div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">

<div>
<flux:heading size="sm">
Candidates
</flux:heading>

@if ($hasSearched)
<flux:text class="text-xs text-zinc-500">
Submitted candidates matching the selected criteria.
</flux:text>
@else
<flux:text class="text-xs text-zinc-500">
Apply filters to load candidates.
</flux:text>
@endif
</div>

@if ($hasSearched)
<flux:text class="text-sm text-zinc-500">
{{ number_format($total) }} total
</flux:text>
@endif


@if (count($selectedCandidates) > 0)
    <flux:button
        variant="primary"
        icon="clipboard-document-check"
        color="lime"
        wire:click="admitSelected"
        wire:loading.attr="disabled"
        class="cursor-pointer"
    >
        Admit ({{ count($selectedCandidates) }}) Selected
    </flux:button>
@endif

</div>

@if($processingBulkAdmission)

    <div class="rounded-lg border p-4">
        <div class="flex items-center gap-3">

            <div
                class="size-5 animate-spin rounded-full border-2 border-current border-t-transparent"
                aria-label="Processing"
            ></div>

            <div>
                <flux:heading size="sm">
                    Processing admissions...
                </flux:heading>

                <flux:text>
                    Please wait while the selected candidates
                    are migrated and committed.
                </flux:text>
            </div>

        </div>
    </div>

@endif


{{-- Loading --}}
<div
wire:loading.flex
wire:target="applyFilters,searchCandidates,nextPage,previousPage,gotoPage"
class="items-center justify-center gap-3 border-b border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/50"
>
<flux:icon
name="arrow-path"
class="size-5 animate-spin"
/>

<flux:text>
Loading candidates...
</flux:text>
</div>


{{-- ============================================================
Empty State
============================================================= --}}
@if (!$hasSearched)

<div class="flex flex-col items-center justify-center px-6 py-20 text-center">

<div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">

<flux:icon
name="users"
class="size-7 text-zinc-500"
/>

</div>

<flux:heading size="sm">
Ready for admission processing
</flux:heading>

<flux:text class="mt-1 max-w-md text-sm text-zinc-500">
Select the academic session and program type,
refine the filters if required, then click
<strong>Apply Filters</strong>.
</flux:text>

</div>

@elseif (empty($candidates))

<div class="flex flex-col items-center justify-center px-6 py-20 text-center">

<div class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">

<flux:icon
name="magnifying-glass"
class="size-7 text-zinc-500"
/>

</div>

<flux:heading size="sm">
No candidates found
</flux:heading>

<flux:text class="mt-1 max-w-md text-sm text-zinc-500">
No submitted candidates match the selected
admission processing criteria.
</flux:text>

</div>

@else

{{-- ========================================================
TABLE
========================================================= --}}
<div class="overflow-x-auto">

<table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

<thead class="bg-zinc-50 dark:bg-zinc-800/70">

<tr>

<th class="w-12 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
#
</th>


<th class="w-12 px-4 py-3 text-center">
    <input
        type="checkbox"
        wire:model.live="selectAll"
        wire:change="toggleSelectAll"
        class="h-4 w-4 rounded border-zinc-300"
    />
</th>

<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
Candidate
</th>

<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
Programme
</th>

<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
Application Type
</th>

<th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500">
Status
</th>

{{-- <th class="w-24 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
Action
</th> --}}

<th class="w-24 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
Review
</th>

<th class="w-28 px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500">
Action
</th>

</tr>

</thead>


<tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">

@foreach ($candidates as $candidate)


@php

$candidateId = $candidate['id'] ?? null;

$regno = $candidate['regno'] ?? '';

$name = $candidate['name']
?? trim(implode(' ', array_filter([
$candidate['user']['last_name'] ?? null,
$candidate['user']['first_name'] ?? null,
$candidate['user']['other_names'] ?? null,
])));

$program = $candidate['program'] ?? '—';

$applicationType = $candidate['application_type']
?? $candidate['application_type_name']
?? '';


$isAdmitted = (bool) (
    $candidate['is_admitted'] ?? false
);

// Admission flag takes precedence over status.
$status = $isAdmitted
    ? 'admitted'
    : ($candidate['status'] ?? 'pending');

@endphp

<tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/50">


{{-- S/N --}}
<td class="whitespace-nowrap px-4 py-4 text-right text-sm text-zinc-500">
{{ (($this->getPage() - 1) * $perPage) + $loop->iteration }}
</td>

<td class="whitespace-nowrap px-4 py-4 text-right">
    @if (!($candidate['is_admitted'] ?? false))
        <input
            type="checkbox"
            wire:model.live="selectedCandidates"
            value="{{ $candidate['id'] }}"
            class="h-4 w-4 rounded border-zinc-300"
        />
    @endif
</td>


{{-- Candidate --}}
<td class="px-4 py-4">

<div class="min-w-0">

<div class="font-medium text-zinc-900 dark:text-white">
{{ $name ?: '—' }}
</div>

<div class="mt-0.5 flex flex-wrap items-center gap-x-2 gap-y-1">

@if ($regno)
<span class="font-mono text-xs text-zinc-500">
{{ $regno }}
</span>
@endif

@if (!empty($candidate['jamb_no']))
<span class="text-xs text-zinc-400">
JAMB#: {{ $candidate['jamb_no'] }}
</span>
@endif

</div>

</div>

</td>


{{-- Programme --}}
<td class="px-4 py-4">

<div class="text-sm text-zinc-900 dark:text-white">
{{ $program ?: '—' }}
</div>

@if (!empty($candidate['department']))
<div class="mt-0.5 text-xs text-zinc-500">
{{ $candidate['department'] }}
</div>
@endif

</td>


{{-- Application Type --}}
<td class="px-4 py-4">

@if ($applicationType)
<flux:badge>
{{ $applicationType }}
</flux:badge>
@else
<span class="text-sm text-zinc-400">
—
</span>
@endif

</td>



{{-- Status --}}
<td class="px-4 py-4">

    @switch($status)

        @case('admitted')

            <flux:badge
                color="green"
                icon="check-circle"
            >
                Admitted
            </flux:badge>

            @break

        @case('rejected')

            <flux:badge
                color="red"
                icon="x-circle"
            >
                Rejected
            </flux:badge>

            @break

        @case('submitted')

            <flux:badge
                color="amber"
                icon="clock"
            >
                Pending
            </flux:badge>

            @break

              @default

            <flux:badge
                color="zinc"
            >
                {{ ucfirst(str_replace('_', ' ', $status ?: 'Unknown')) }}
            </flux:badge>

    @endswitch

</td>


{{-- Review --}}
<td class="px-4 py-4 text-right">

@if ($candidateId)

<flux:button
size="sm"
variant="ghost"
icon="eye"
class="cursor-pointer"
wire:click="reviewCandidate({{ $candidateId }})"
>
Review
</flux:button>

@else

<span class="text-xs text-zinc-400">
—
</span>

@endif

</td>

{{-- Action --}}
<td class="px-4 py-4 text-right">

@if($candidate['is_admitted'] ?? false)
   <flux:button
        size="sm"
        variant="danger"
        icon="arrow-uturn-left"
        class="cursor-pointer"
        wire:click="confirmRevoke({{ $candidate['id'] }})"
    >

        Revoke
    </flux:button>

@else

<flux:button
size="sm"
variant="primary"
icon="arrows-right-left"
class="cursor-pointer"
wire:click="processCandidate({{ $candidateId }})"
>
Process
</flux:button>

@endif
</td>

</tr>

@endforeach

</tbody>

</table>

</div>


{{-- ========================================================
PAGINATION
========================================================= --}}
@if ($lastPage > 1)

<div class="flex flex-col gap-3 border-t border-zinc-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-700">

<div class="text-sm text-zinc-500">
Page {{ $this->getPage() }} of {{ $lastPage }}
</div>


<div class="flex items-center gap-1">

<flux:button
size="sm"
variant="ghost"
icon="arrow-uturn-left"
class="cursor-pointer"
wire:click="previousPage"
:disabled="$this->getPage() <= 1"
>
Previous
</flux:button>


<flux:button
size="sm"
variant="ghost"
icon="arrow-uturn-right"
class="cursor-pointer"
wire:click="nextPage"
:disabled="$this->getPage() >= $lastPage"
>
Next
</flux:button>

</div>

</div>

@endif

@endif

</div>


{{-- Candidate Review --}}
@include('livewire.admissions.partials.candidate-review-modal')

{{-- Admission Decision --}}
@include('livewire.admissions.partials.admission-decision-modal')

{{-- Bulk Admission Results --}}
@include('livewire.admissions.partials.bulk-processing-results-modal')

{{-- Bulk Admission Confirmation --}}
@include('livewire.admissions.partials.bulk-admission-decision-modal')

{{-- Revoke Admission --}}
@include('livewire.admissions.partials.revoke-admission-modal')

</div>
