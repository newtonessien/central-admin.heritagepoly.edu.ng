<div class="space-y-6">

{{-- =========================================================
HEADER
========================================================== --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">

<div>
<flux:breadcrumbs>

    <flux:breadcrumbs.item :href="route('dashboard')">
        Dashboard
    </flux:breadcrumbs.item>

    <flux:breadcrumbs.item>
        Admissions
    </flux:breadcrumbs.item>

    <flux:breadcrumbs.item>
        Candidate List
    </flux:breadcrumbs.item>

</flux:breadcrumbs>

<flux:heading size="xl" class="mt-3">
    Candidate List
</flux:heading>

<flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
    Browse and review submitted admission candidates.
</flux:text>
</div>


<div class="flex items-center gap-2">

<flux:button
    variant="primary"
    color="amber"
    class="cursor-pointer"
    icon="adjustments-horizontal"
    wire:click="toggleFilters"
>
    {{ $showFilters ? 'Hide Filters' : 'Show Filters' }}
</flux:button>

</div>

</div>


{{-- =========================================================
FILTER PANEL
========================================================== --}}

@if($showFilters)

<flux:card>

<div class="flex flex-col gap-1 mb-5">

    <div class="flex items-center justify-between gap-3">

        <div>
            <flux:heading size="lg">
                Candidate Filters
            </flux:heading>

            <flux:text class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Filter candidates by session, programme type, status and submission period.
            </flux:text>
        </div>

        <flux:button
            variant="ghost"
            size="sm"
            icon="x-mark"
            wire:click="toggleFilters"
        >
            Hide
        </flux:button>

    </div>

</div>


{{-- =====================================================
    PRIMARY FILTERS
====================================================== --}}

<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">

    {{-- Academic Session --}}

    <flux:select
        label="Academic Session"
        wire:model.live="acad_session_id"
        placeholder="Select session"
    >

        @foreach($academicSessions as $session)

            <flux:select.option
                value="{{ $session['id'] }}"
            >
                {{ $session['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>


    {{-- Program Type --}}

    <flux:select
        label="Program Type"
        wire:model.live="program_type_id"
        placeholder="Select program type"
    >

        {{-- @foreach($programTypes as $type) --}}

        @foreach($this->availableProgramTypes as $type)

            <flux:select.option
                value="{{ $type['id'] }}"
            >
                {{ $type['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>


    {{-- Application Type --}}

    <flux:select
        label="Application Type"
        wire:model.live="application_type_id"
        placeholder="All application types"
        :disabled="!$program_type_id"
    >

        <flux:select.option value="">
            All Application Types
        </flux:select.option>

        @foreach($applicationTypes as $type)

            <flux:select.option
                value="{{ $type['id'] }}"
            >
                {{ $type['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>

</div>


{{-- =====================================================
    SECONDARY FILTERS
====================================================== --}}

<div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2 lg:grid-cols-3">

    {{-- Faculty --}}

    <flux:select
        label="Faculty"
        wire:model.live="faculty_id"
        placeholder="All Faculties"
    >

        <flux:select.option value="">
            All Faculties
        </flux:select.option>

        @foreach($faculties as $faculty)

            <flux:select.option
                value="{{ $faculty['id'] }}"
            >
                {{ $faculty['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>


    {{-- Department --}}

    <flux:select
        label="Department"
        wire:model.live="department_id"
        placeholder="All Departments"
        :disabled="!$faculty_id || $loadingDepartments"
    >

        <flux:select.option value="">
            All Departments
        </flux:select.option>

        @foreach($departments as $department)

            <flux:select.option
                value="{{ $department['id'] }}"
            >
                {{ $department['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>


    {{-- Programme --}}

    <flux:select
        label="Programme"
        wire:model.live="program_id"
        placeholder="All Programmes"
        :disabled="!$department_id || $loadingPrograms"
    >

        <flux:select.option value="">
            All Programmes
        </flux:select.option>

        @foreach($programs as $program)

            <flux:select.option
                value="{{ $program['id'] }}"
            >
                {{ $program['name'] }}
            </flux:select.option>

        @endforeach

    </flux:select>

</div>


{{-- =====================================================
    STATUS / SEARCH
====================================================== --}}

<div class="grid grid-cols-1 gap-4 mt-4 lg:grid-cols-3">

    <flux:select
        label="Registration Status"
        wire:model.live="registrationStatus"
    >

        <flux:select.option value="submitted">
            Submitted
        </flux:select.option>

        <flux:select.option value="all">
            All
        </flux:select.option>

    </flux:select>


    <div class="lg:col-span-2">

        <flux:input
            label="Search"
            placeholder="Registration number or candidate name..."
            icon="magnifying-glass"
            wire:model.live.debounce.400ms="search"
        />

    </div>

</div>


{{-- =====================================================
    SUBMISSION DATE
====================================================== --}}

<div class="grid grid-cols-1 gap-4 mt-4 md:grid-cols-2">

    <flux:input
        type="date"
        label="Submitted From"
        wire:model.live="submittedFrom"
    />

    <flux:input
        type="date"
        label="Submitted To"
        wire:model.live="submittedTo"
    />

</div>


{{-- =====================================================
    FILTER ACTIONS
====================================================== --}}

<div class="flex flex-col gap-3 mt-6 pt-5 border-t border-zinc-200 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">

    <flux:text class="text-sm text-zinc-500 dark:text-zinc-400">
        Showing submitted candidates by default.
    </flux:text>


    <div class="flex items-center gap-2">

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
            icon="magnifying-glass"
            wire:click="loadCandidates"
            wire:loading.attr="disabled"
        >
            Search Candidates
        </flux:button>

    </div>

</div>

</flux:card>

@endif


{{-- =========================================================
SUMMARY
========================================================== --}}

@if($acad_session_id && $program_type_id)

<div class="grid grid-cols-1 gap-4 sm:grid-cols-3">

{{-- Candidates --}}

<flux:card>

    <div class="flex items-center justify-between gap-4">

        <div>

            <flux:text class="text-sm text-zinc-500">
                Candidates
            </flux:text>

            <flux:heading size="xl" class="mt-1">
                {{ number_format($total) }}
            </flux:heading>

        </div>

        <flux:icon.users
            class="size-8 text-blue-600 dark:text-blue-400"
        />

    </div>

</flux:card>


{{-- Current Page --}}

<flux:card>

    <div class="flex items-center justify-between gap-4">

        <div>

            <flux:text class="text-sm text-zinc-500">
                Current Page
            </flux:text>

            <flux:heading size="xl" class="mt-1">

                {{ number_format($page) }}

                <span class="text-sm font-normal text-zinc-500">
                    / {{ number_format($lastPage) }}
                </span>

            </flux:heading>

        </div>

        <flux:icon.document-text
            class="size-8 text-violet-600 dark:text-violet-400"
        />

    </div>

</flux:card>


{{-- Status --}}

<flux:card>

    <div class="flex items-center justify-between gap-4">

        <div>

            <flux:text class="text-sm text-zinc-500">
                Registration Status
            </flux:text>

            <div class="mt-2">

                <flux:badge
                    color="green"
                    size="sm"
                >
                    {{ $registrationStatus === 'all'
                        ? 'All'
                        : 'Submitted' }}
                </flux:badge>

            </div>

        </div>

        <flux:icon.check-circle
            class="size-8 text-emerald-600 dark:text-emerald-400"
        />

    </div>

</flux:card>

</div>

@endif


{{-- =========================================================
CANDIDATE LIST
========================================================== --}}

<flux:card class="overflow-hidden p-0">

{{-- =====================================================
LIST HEADER
====================================================== --}}

<div class="flex flex-col gap-3 px-5 py-4 border-b border-zinc-200 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">

<div>

    <flux:heading size="lg">
        Applicants
    </flux:heading>

    @if($total > 0)

        <flux:text class="mt-1 text-sm text-zinc-500">

            Showing
            {{ number_format(($page - 1) * $perPage + 1) }}
            –
            {{ number_format(min($page * $perPage, $total)) }}
            of
            {{ number_format($total) }}

        </flux:text>

    @else

        <flux:text class="mt-1 text-sm text-zinc-500">
            No candidates found.
        </flux:text>

    @endif

</div>

@php

    $sessionName = collect($academicSessions)
        ->firstWhere('id', (int) $acad_session_id)['name']
        ?? null;

    $ptName = collect($programTypes)
        ->firstWhere('id', (int) $program_type_id)['name']
        ?? null;

    $appTypeName = collect($applicationTypes)
        ->firstWhere('id', (int) $application_type_id)['name']
        ?? null;

    $facultyName = collect($faculties)
        ->firstWhere('id', (int) $faculty_id)['name']
        ?? null;

    $departmentName = collect($departments)
        ->firstWhere('id', (int) $department_id)['name']
        ?? null;

    $programName = collect($programs)
        ->firstWhere('id', (int) $program_id)['name']
        ?? null;

    $exportParams = array_filter([
        'acad_session_id'       => $acad_session_id,
        'acad_session_name'     => $sessionName,

        'program_type_id'       => $program_type_id,
        'program_type_name'     => $ptName,

        'application_type_id'   => $application_type_id,
        'application_type_name' => $appTypeName,

        'faculty_id'            => $faculty_id,
        'faculty_name'          => $facultyName,

        'department_id'         => $department_id,
        'department_name'       => $departmentName,

        'program_id'            => $program_id,
        'program_name'          => $programName,

        'status'                => $registrationStatus,

    ], fn ($value) => $value !== null && $value !== '');

@endphp


{{-- Export --}}

<div class="flex items-center gap-2">

<flux:button
href="{{ route('exports.candidates.excel', $exportParams) }}"
icon="arrow-down-tray"
variant="primary"
  size="sm"
>
Excel
</flux:button>

<flux:button
href="{{ route('exports.candidates.pdf', $exportParams) }}"
icon="arrow-down-on-square-stack"
variant="primary"
color="lime"
  size="sm"
>
PDF
</flux:button>

   {{-- Credentials ZIP --}}
    <flux:button
        variant="primary"
        size="sm"
        color="teal"
        class="cursor-pointer"
        icon="archive-box-arrow-down"
        wire:click="downloadMultipleCredentials"
        wire:loading.attr="disabled"
        wire:target="downloadMultipleCredentials"
    >
        <span wire:loading.remove wire:target="downloadMultipleCredentials">
            Credentials ZIP
        </span>

        <span wire:loading wire:target="downloadMultipleCredentials">
            Preparing ZIP...
        </span>
    </flux:button>

</div>



</div>


{{-- =====================================================
LOADING
====================================================== --}}

<div
wire:loading.flex
wire:target="loadCandidates,acad_session_id,program_type_id,application_type_id,faculty_id,department_id,program_id,registrationStatus,search,submittedFrom,submittedTo"
class="min-h-64 items-center justify-center gap-3 px-6 py-12"
>

<flux:icon.arrow-path
    class="size-5 animate-spin text-blue-600"
/>

<flux:text>
    Loading candidates...
</flux:text>

</div>


{{-- =====================================================
TABLE
====================================================== --}}

<div
wire:loading.remove
wire:target="loadCandidates,acad_session_id,program_type_id,application_type_id,faculty_id,department_id,program_id,registrationStatus,search,submittedFrom,submittedTo"
class="overflow-x-auto"
>

@if(count($candidates))

    <table class="min-w-full">

        <thead class="bg-zinc-50 dark:bg-zinc-800/60">

            <tr class="text-left">

                <th class="w-14 px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    #
                </th>

                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    Candidate
                </th>

                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    Sex
                </th>

                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    State
                </th>
                    @if($this->showJambColumn)
                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    JAMB
                </th>
                    @endif
                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    O'Level
                </th>

                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-zinc-500">
                    Status
                </th>

            </tr>

        </thead>


        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

            @foreach($candidates as $candidate)

                <tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

                    {{-- =================================================
                        NUMBER
                    ================================================== --}}

                    <td class="px-5 py-4 text-sm text-zinc-500 align-top">

                        {{ (($page - 1) * $perPage) + $loop->iteration }}

                    </td>


                    {{-- =================================================
                        CANDIDATE
                    ================================================== --}}

                    <td class="px-5 py-4 align-top">

                        <div class="font-medium text-zinc-900 dark:text-zinc-100">
                            {{ $candidate['name'] ?? '—' }}
                        </div>

                        <div class="mt-1 text-xs text-zinc-500">
                            {{ $candidate['regno'] ?? '—' }}
                        </div>


                        {{-- Programme --}}

                        @if(!empty($candidate['program']))

                            <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400">

                                <span class="font-medium">
                                    {{ $candidate['program'] }}
                                </span>

                            </div>

                        @endif


                        {{-- Application Type --}}

                        @if(!empty($candidate['application_type']))

                            <div class="mt-1">

                                <flux:badge
                                    color="blue"
                                    size="sm"
                                >
                                    {{ $candidate['application_type'] }}
                                </flux:badge>

                            </div>

                        @endif

                    </td>


                    {{-- =================================================
                        SEX
                    ================================================== --}}

                    <td class="px-5 py-4 align-top">

                        <flux:text class="text-sm">
                            {{ $candidate['sex'] ?? '—' }}
                        </flux:text>

                    </td>


                    {{-- =================================================
                        STATE
                    ================================================== --}}

                    <td class="px-5 py-4 align-top">

                        <flux:text class="text-sm">
                            {{ $candidate['state'] ?? '—' }}
                        </flux:text>

                    </td>


                    {{-- =================================================
                        JAMB
                    ================================================== --}}
                        @if($this->showJambColumn)
                    <td class="px-5 py-4 align-top">

                        @if(
                            !empty($candidate['jamb_no']) ||
                            ($candidate['jamb_score'] ?? null) !== null
                        )

                            <div class="font-medium text-sm">
                                {{ $candidate['jamb_score'] == 0 ? '—' : $candidate['jamb_score'] }}
                            </div>

                            @if(!empty($candidate['jamb_no']))

                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $candidate['jamb_no'] }}
                                </div>

                            @endif

                        @else

                            <flux:text class="text-sm text-zinc-400">
                                —
                            </flux:text>

                        @endif

                    </td>
                        @endif

                    {{-- =================================================
                        O'LEVEL
                    ================================================== --}}

                    <td class="px-5 py-4 align-top">

                @if(!empty($candidate['olevel_details']))

<flux:button
size="sm"
variant="primary"
color="zinc"
class="cursor-pointer"
icon="eye"
wire:click="viewCandidate({{ $candidate['id'] }})"
>
Review
</flux:button>

@else

<flux:text class="text-sm text-zinc-400">
Not supplied
</flux:text>

@endif

                    </td>


                    {{-- =================================================
                        STATUS
                    ================================================== --}}

                    <td class="px-5 py-4 align-top">

                        <flux:badge
                            color="{{ ($candidate['is_admitted'] ?? 0) ? 'green' : 'zinc' }}"
                            size="sm"
                        >

                            {{ ($candidate['is_admitted'] ?? 0)
                                ? 'Admitted'
                                : 'Submitted' }}

                        </flux:badge>

                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>


@elseif($acad_session_id && $program_type_id)

    {{-- =====================================================
        EMPTY STATE
    ====================================================== --}}

    <div class="py-20 text-center">

        <flux:icon.users
            class="mx-auto size-12 text-zinc-400"
        />

        <flux:heading size="lg" class="mt-4">
            No Candidates Found
        </flux:heading>

        <flux:text class="mt-2 text-zinc-500">
            No candidates match the selected filters.
        </flux:text>

        <flux:button
            variant="ghost"
            class="mt-5"
            wire:click="resetFilters"
        >
            Reset Filters
        </flux:button>

    </div>


@else

    {{-- =====================================================
        INITIAL STATE
    ====================================================== --}}

    <div class="py-20 text-center">

        <flux:icon.funnel
            class="mx-auto size-12 text-zinc-400"
        />

        <flux:heading size="lg" class="mt-4">
            Select Your Filters
        </flux:heading>

        <flux:text class="mt-2 text-zinc-500">
            Select an academic session and programme type
            to view candidates.
        </flux:text>

        <flux:button
            variant="primary"
            icon="adjustments-horizontal"
            class="mt-5"
            wire:click="toggleFilters"
        >
            Show Filters
        </flux:button>

    </div>

@endif

</div>

{{-- =====================================================
PAGINATION
====================================================== --}}

@if($total > 0 && $lastPage > 1)

<div class="flex flex-col gap-3 border-t border-zinc-200 dark:border-zinc-700 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

<flux:text class="text-sm text-zinc-500">
    Page {{ $page }} of {{ $lastPage }}
</flux:text>


<div class="flex items-center gap-2">

    <flux:button
        size="sm"
        variant="ghost"
        icon="chevron-left"
        class="cursor-pointer"
        wire:click="prevPage"
        :disabled="$page <= 1"
    >
        Previous
    </flux:button>

    <flux:button
        size="sm"
        variant="ghost"
            icon="chevron-right"
            class="cursor-pointer"
        wire:click="nextPage"
        :disabled="$page >= $lastPage"
    >
        Next

    </flux:button>

</div>

</div>

@endif

</flux:card>




{{-- =========================================================
CANDIDATE DETAILS MODAL
========================================================== --}}

<flux:modal
name="candidate-details"
class="w-full max-w-4xl"
>

@if(!empty($selectedCandidate))

<div class="space-y-6">

{{-- =====================================================
    HEADER
====================================================== --}}

<div class="flex items-start justify-between gap-4">

    <div class="min-w-0">

        <flux:heading size="lg">
          Candidate Review
        </flux:heading>

        <flux:text class="mt-1">

            {{ $selectedCandidate['name'] ?? 'Unknown Candidate' }}

            @if(!empty($selectedCandidate['regno']))

                <span class="text-zinc-400">
                    ({{ $selectedCandidate['regno'] }})
                </span>

            @endif

        </flux:text>

    </div>



</div>


{{-- =====================================================
    CANDIDATE SUMMARY
====================================================== --}}

<div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/40">

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">

        <div>

            <flux:text class="text-xs text-zinc-500">
                Registration No.
            </flux:text>

            <flux:text class="mt-1 font-medium">
                {{ $selectedCandidate['regno'] ?? '—' }}
            </flux:text>

        </div>


        <div>

            <flux:text class="text-xs text-zinc-500">
                Sex
            </flux:text>

            <flux:text class="mt-1 font-medium">
                {{ $selectedCandidate['sex'] ?? '—' }}
            </flux:text>

        </div>


        <div>

            <flux:text class="text-xs text-zinc-500">
                State
            </flux:text>

            <flux:text class="mt-1 font-medium">
                {{ $selectedCandidate['state'] ?? '—' }}
            </flux:text>

        </div>


        <div>

            <flux:text class="text-xs text-zinc-500">
                Application
            </flux:text>

            <flux:text class="mt-1 font-medium">
                {{ $selectedCandidate['application_type'] ?? '—' }}
            </flux:text>

        </div>

    </div>

</div>


{{-- =====================================================
    CREDENTIALS
====================================================== --}}

<div class="flex flex-col gap-3 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between">

    <div>

        <flux:heading size="sm">
            Credentials
        </flux:heading>

        <flux:text class="mt-1 text-sm text-zinc-500">
            Candidate's uploaded admission credentials.
        </flux:text>

    </div>


    @if(!empty($selectedCandidate['credentials_pdf_url']))

        <flux:button
            href="{{ $selectedCandidate['credentials_pdf_url'] }}"
            target="_blank"
            variant="primary"
            icon="arrow-down-tray"
        >
            Download Credentials
        </flux:button>

    @else

        <flux:badge
            color="zinc"
            size="sm"
        >
            No credentials uploaded
        </flux:badge>

    @endif

</div>


{{-- =====================================================
    O'LEVEL
====================================================== --}}

<div>

    <flux:heading size="sm">
        O'Level Results
    </flux:heading>

    <flux:text class="mt-1 text-sm text-zinc-500">
        Examination details and subject grades supplied by the candidate.
    </flux:text>

</div>


@forelse(
    $selectedCandidate['olevel_details'] ?? []
    as $sitting
)

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">

        {{-- Sitting --}}

        <div class="flex items-center gap-2">

            <flux:badge
                color="blue"
                size="sm"
            >
                {{ $sitting['sitting'] ?? 'Sitting' }}
            </flux:badge>

        </div>


        {{-- Examination information --}}

        <div class="mt-3">

            <flux:text class="text-sm">

                @if(!empty($sitting['exam_type']))
                    <span class="font-medium">
                        {{ $sitting['exam_type'] }}
                    </span>
                @endif

                @if(!empty($sitting['exam_year']))

                    <span class="text-zinc-400">
                        ·
                    </span>

                    {{ $sitting['exam_year'] }}

                @endif

            </flux:text>


            @if(!empty($sitting['school_name']))

                <flux:text class="mt-1 text-xs text-zinc-500">
                    {{ $sitting['school_name'] }}
                </flux:text>

            @endif

        </div>


        {{-- Subject grades --}}

        @if(!empty($sitting['results']))

            <div class="mt-4 flex flex-wrap gap-2">

                @foreach($sitting['results'] as $result)

                    <div class="rounded-md border border-zinc-200 bg-zinc-50 px-2.5 py-1.5 text-xs dark:border-zinc-700 dark:bg-zinc-800">

                        <span class="font-medium">
                            {{ $result['code'] ?? '—' }}
                        </span>

                        <span class="mx-1 text-zinc-400">
                            =
                        </span>

                        <span>
                            {{ $result['grade'] ?? '—' }}
                        </span>

                    </div>

                @endforeach

            </div>

        @else

            <flux:text class="mt-4 text-sm text-zinc-500">
                No subject results supplied.
            </flux:text>

        @endif

    </div>

@empty

    <div class="rounded-xl border border-dashed border-zinc-300 py-10 text-center dark:border-zinc-700">

        <flux:icon.document-text
            class="mx-auto size-10 text-zinc-400"
        />

        <flux:text class="mt-3 text-zinc-500">
            No O'Level details supplied.
        </flux:text>

    </div>

@endforelse


{{-- =====================================================
    FOOTER
====================================================== --}}

<div class="flex justify-end border-t border-zinc-200 pt-4 dark:border-zinc-700">

    <flux:modal.close>

        <flux:button
            variant="ghost"
            class="cursor-pointer"
        >
            Close
        </flux:button>

    </flux:modal.close>

</div>

</div>

@endif

</flux:modal>


</div>

