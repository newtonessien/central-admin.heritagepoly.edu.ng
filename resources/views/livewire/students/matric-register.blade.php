<div>
<flux:card class="space-y-6">

{{-- =========================================
HEADER
========================================== --}}
<div class="flex items-center justify-between">
    <flux:heading>Student Matriculation Register</flux:heading>

    <div class="flex items-center gap-2">

        @php
        $exportParams = array_filter([
            'program_type_id' => $program_type_id,
            'faculty_id'      => $faculty_id,
            'department_id'   => $department_id,
            'program_id'      => $program_id,
            'acad_session_id' => $acad_session_id,
        ], fn($v) => $v !== null && $v !== '');
        @endphp

        @if(count($records) > 0 && Route::has('exports.matric-register.pdf'))

            <a href="{{ route('exports.matric-register.pdf', $exportParams) }}"
               target="_blank"
               class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white">
                Export PDF
            </a>

            <a href="{{ route('exports.matric-register.excel', $exportParams) }}"
               class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white">
                Export Excel
            </a>

        @endif

    </div>
</div>


{{-- =========================================
FILTERS
========================================== --}}

{{-- =========================================
SECOND ROW
========================================== --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-3">

    {{-- Academic Session --}}
    <flux:select
        wire:model.live.number="acad_session_id"
        label="Academic Session"
        searchable
        variant="listbox"
        placeholder="- Select Session -"
    >
        @foreach($sessions as $s)
            <flux:select.option value="{{ $s['id'] }}">
                {{ $s['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>

        {{-- Program Type --}}
    <flux:select
        wire:model.live.number="program_type_id"
        label="Program Type"
        searchable
        variant="listbox"
        placeholder="- Select Program Type -"
    >
        @foreach($programTypes as $pt)
            <flux:select.option value="{{ $pt['id'] }}">
                {{ $pt['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>


    {{-- Faculty --}}
    <flux:select
        wire:model.live.number="faculty_id"
        label="Faculty"
        searchable
        variant="listbox"
        placeholder="- Select Faculty -"
    >
        @foreach($faculties as $f)
            <flux:select.option value="{{ $f['id'] }}">
                {{ $f['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>


    {{-- Department --}}
    <flux:select
        wire:model.live.number="department_id"
         searchable
        variant="listbox"
        :disabled="!$faculty_id"
        placeholder="- Select Department -"
    >
        @foreach($departments as $d)
            <flux:select.option value="{{ $d['id'] }}">
                {{ $d['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>

        {{-- Program --}}
    <flux:select
        wire:model.live.number="program_id"
         searchable
        variant="listbox"
        :disabled="!$department_id"
        placeholder="- Select Course of Study -"
    >
        @foreach($programs as $p)
            <flux:select.option value="{{ $p['id'] }}">
                {{ $p['name'] }}
            </flux:select.option>
        @endforeach
    </flux:select>


    {{-- =========================================
LOAD BUTTON
========================================== --}}
<div>
    <flux:button
        variant="primary"
        icon="magnifying-glass"
        wire:click="generateRegister"
        wire:loading.attr="disabled"
        class="cursor-pointer"
        :disabled="!$program_type_id || !$acad_session_id"
    >
        <span wire:loading.remove>Generate Register</span>
        <span wire:loading>Loading...</span>
    </flux:button>
</div>

</div>


{{-- =========================================
RESULTS
========================================== --}}
@if(count($records) > 0)
<hr/>
<flux:table>

<flux:table.columns>
    <flux:table.column>#</flux:table.column>
    <flux:table.column>Matric No</flux:table.column>
    <flux:table.column>Name</flux:table.column>
    <flux:table.column>Sex</flux:table.column>
    <flux:table.column>JAMB No</flux:table.column>
    <flux:table.column>State</flux:table.column>
    <flux:table.column>LGA</flux:table.column>
    <flux:table.column>DOB</flux:table.column>
    <flux:table.column>Entry Mode</flux:table.column>
    <flux:table.column>Phone</flux:table.column>
    <flux:table.column>Signature</flux:table.column>
</flux:table.columns>

<flux:table.rows>

@foreach($records as $index => $student)

<flux:table.row>

    <flux:table.cell>{{ $index + 1 }}</flux:table.cell>

    <flux:table.cell>{{ $student['matric_no'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['fullname'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['sex'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['jamb_no'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['state'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['lga'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['dob'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['entry_mode'] ?? '' }}</flux:table.cell>

    <flux:table.cell>{{ $student['phone_no'] ?? '' }}</flux:table.cell>

    <flux:table.cell></flux:table.cell>

</flux:table.row>

@endforeach

</flux:table.rows>

</flux:table>

@endif

@if(count($records) > 0)
<div class="flex justify-between items-center pt-4">

<flux:button
variant="subtle"
wire:click="prevPage"
:disabled="$page <= 1"
>
Prev
</flux:button>

<flux:text size="sm">
Page {{ $page }} of {{ $lastPage }} | Total Students: {{ $total }}
</flux:text>

<flux:button
variant="subtle"
wire:click="nextPage"
:disabled="$page >= $lastPage"
>
Next
</flux:button>

</div>
@endif

{{-- NO RECORDS MESSAGE --}}
</div>


</flux:card>

