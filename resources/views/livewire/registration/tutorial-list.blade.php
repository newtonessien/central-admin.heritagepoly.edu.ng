<div>
<flux:card class="space-y-6">

    {{-- =========================================
        HEADER
    ========================================== --}}
    <div class="flex items-center justify-between">
        <flux:heading> Tutorial List / Result Sheet </flux:heading>

        <div class="flex items-center gap-2">

            @if(count($students) > 0)
             @php
    $exportParams = array_filter([
        'course_id'       => $course_id,
        'acad_session_id' => $acad_session_id,
        'semester'        => $semester,
        'program_type_id' => $program_type_id,
        'faculty_id'      => $faculty_id,
        'department_id'   => $department_id,
        'program_id'      => $program_id,
        'level_id'        => $level_id,
    ], fn($v) => $v !== null && $v !== '');
@endphp

@if(count($students) > 0)
    <a href="{{ route('exports.tutorial-list.pdf', $exportParams) }}"
       target="_blank"
          class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white">

        {{-- Icon --}}
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-4 h-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
        </svg>

        Export PDF
    </a>
@endif
<a href="{{ route('exports.tutorial-list.excel', $exportParams) }}"
   class="inline-flex items-center gap-2 rounded-md px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white">
    Export Excel
</a>
@endif

        </div>
    </div>

    {{-- =========================================
        FILTER SECTION
    ========================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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
            label="Department"
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
            label="Program"
            searchable
            variant="listbox"
            :disabled="!$department_id"
            placeholder="- Select Program -"
        >
            @foreach($programs as $p)
                <flux:select.option value="{{ $p['id'] }}">
                    {{ $p['name'] }}
                </flux:select.option>
            @endforeach
        </flux:select>

    </div>

    {{-- =========================================
        SECOND ROW FILTERS
    ========================================== --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

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

        {{-- Semester --}}
        <flux:select
            wire:model.live.number="semester"
            label="Semester"
            variant="listbox"
            :disabled="!$program_id"
            placeholder="- Select Semester -"
        >
            <flux:select.option value="1">First Semester</flux:select.option>
            <flux:select.option value="2">Second Semester</flux:select.option>
        </flux:select>

        {{-- Course Level --}}
        <flux:select
            wire:model.live.number="level_id"
            label="Course Level"
            variant="listbox"
            :disabled="!$semester"
            placeholder="- Select Level -"
        >
            @foreach($levels as $lvl)
                <flux:select.option value="{{ $lvl['id'] }}">
                    {{ $lvl['name'] }}
                </flux:select.option>
            @endforeach
        </flux:select>

        {{-- Course --}}
        <flux:select
            wire:model.live.number="course_id"
            label="Course"
            searchable
            variant="listbox"
            :disabled="!$level_id"
            placeholder="- Select Course -"
        >
            @foreach($courses as $c)
                <flux:select.option value="{{ $c['id'] }}">
                    {{ $c['course_code'] }} - {{ $c['course_title'] }} ({{ $c['credit_hours'] }})
                </flux:select.option>
            @endforeach
        </flux:select>

    </div>

    {{-- =========================================
        LOAD BUTTON
    ========================================== --}}
    <div>
        <flux:button
            variant="primary"
            icon="magnifying-glass"
            wire:click="loadTutorialList"
            wire:loading.attr="disabled"
            :disabled="!$course_id || !$acad_session_id"
        >
            <span wire:loading.remove>Load Tutorial List</span>
            <span wire:loading>Loading...</span>
        </flux:button>
    </div>

    {{-- =========================================
        RESULTS TABLE
    ========================================== --}}
    @if(count($students) > 0)

        <div class="pt-4">
            <flux:text size="sm" class="text-zinc-600">
                Total Students: {{ $total }}
            </flux:text>
        </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column>#</flux:table.column>
                <flux:table.column>Reg No</flux:table.column>
                <flux:table.column>Name</flux:table.column>
                <flux:table.column>Level</flux:table.column>
                <flux:table.column>CA</flux:table.column>
                <flux:table.column>Exam</flux:table.column>
                <flux:table.column>Total</flux:table.column>
                <flux:table.column>Grade</flux:table.column>
                <flux:table.column>Remark</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach($students as $index => $student)
                    <flux:table.row>
                        <flux:table.cell>
                            {{ ($page - 1) * $perPage + $index + 1 }}
                        </flux:table.cell>
                        <flux:table.cell>{{ $student['matric_no'] }}</flux:table.cell>
                        <flux:table.cell>{{ $student['name'] }}</flux:table.cell>
                        <flux:table.cell>{{ $student['student_level_id'].'00' }}</flux:table.cell>
                        <flux:table.cell></flux:table.cell>
                        <flux:table.cell></flux:table.cell>
                        <flux:table.cell></flux:table.cell>
                        <flux:table.cell></flux:table.cell>
                        <flux:table.cell></flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        {{-- Pagination --}}
        <div class="flex justify-between items-center pt-4">

            <flux:button variant="subtle" wire:click="prevPage" :disabled="$page <= 1">
                Prev
            </flux:button>

            <flux:text size="sm">
                Page {{ $page }} of {{ $lastPage }}
            </flux:text>

            <flux:button variant="subtle" wire:click="nextPage" :disabled="$page >= $lastPage">
                Next
            </flux:button>

        </div>

    @endif

</flux:card>
</div>
