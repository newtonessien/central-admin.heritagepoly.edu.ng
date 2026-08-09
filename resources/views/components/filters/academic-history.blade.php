@props([
    'sessions' => [],
    'levels' => [],
    'programTypes' => [],
    'faculties' => [],
    'departments' => [],

    'showSession' => true,
    'showSemester' => true,
    'showLevel' => false,
    'showProgramType' => false,
    'showFaculty' => false,
    'showDepartment' => false,
])

<flux:card>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Academic Session --}}
        @if($showSession)

            <flux:select
                wire:model.live="acadSessionId"
                label="Academic Session">

                <option value="">All Sessions</option>

                @foreach($sessions as $session)

                    <option value="{{ $session['id'] }}">
                        {{ $session['name'] }}
                    </option>

                @endforeach

            </flux:select>

        @endif


        {{-- Semester --}}
        @if($showSemester)

            <flux:select
                wire:model.live="feePeriodId"
                label="Semester">

                <option value="">All Semesters</option>

                <option value="1">
                    First Semester
                </option>

                <option value="2">
                    Second Semester
                </option>

                <option value="3">
                    Full Session
                </option>

            </flux:select>

        @endif


        {{-- Level --}}
        @if($showLevel)

            <flux:select
                wire:model.live="levelId"
                label="Level">

                <option value="">
                    All Levels
                </option>

                @foreach($levels as $level)

                    <option value="{{ $level['id'] }}">
                        {{ $level['name'] }}
                    </option>

                @endforeach

            </flux:select>

        @endif


        {{-- Program Type --}}
        @if($showProgramType)

            <flux:select
                wire:model.live="programTypeId"
                label="Program Type">

                <option value="">
                    All Program Types
                </option>

                @foreach($programTypes as $programType)

                    <option value="{{ $programType['id'] }}">
                        {{ $programType['name'] }}
                    </option>

                @endforeach

            </flux:select>

        @endif


        {{-- Faculty --}}
        @if($showFaculty)

            <flux:select
                wire:model.live="facultyId"
                label="Faculty">

                <option value="">
                    All Faculties
                </option>

                @foreach($faculties as $faculty)

                    <option value="{{ $faculty['id'] }}">
                        {{ $faculty['name'] }}
                    </option>

                @endforeach

            </flux:select>

        @endif


        {{-- Department --}}
        @if($showDepartment)

            <flux:select
                wire:model.live="departmentId"
                label="Department">

                <option value="">
                    All Departments
                </option>

                @foreach($departments as $department)

                    <option value="{{ $department['id'] }}">
                        {{ $department['name'] }}
                    </option>

                @endforeach

            </flux:select>

        @endif


        {{-- Actions --}}
@if (isset($actions))

    <div class="mt-6 flex items-center justify-end gap-3">

        {{ $actions }}

    </div>

@endif

    </div>

</flux:card>
