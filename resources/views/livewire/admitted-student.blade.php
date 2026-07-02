<div class="space-y-6">
    {{-- Filters --}}
    <flux:card class="p-6 transition-all duration-300">
        {{-- Filter Header with Toggle --}}
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon.funnel class="size-5" />
                Filter Students
            </flux:heading>

            <flux:button
                variant="ghost"
                size="sm"
                wire:click="$toggle('showFilters')"
                class="cursor-pointer"
            >
                <span class="flex items-center gap-2">
                    <span>{{ $showFilters ? 'Hide' : 'Show' }} Filters</span>
                    <flux:icon.chevron-up class="size-4 transition-transform duration-300 {{ $showFilters ? '' : 'rotate-180' }}" />
                </span>
            </flux:button>
        </div>

        {{-- Filter Content --}}
        <div x-data="{ show: @entangle('showFilters') }"
             x-show="show"
             x-transition:enter.duration.300ms
             x-transition:leave.duration.300ms
             class="space-y-4">

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                {{-- Academic Session (required) --}}
                <flux:select
                    wire:model.live.number="acad_session_id"
                    label="Academic Session *"
                    searchable
                    indicator="checkbox"
                    variant="listbox"
                    placeholder="- Select Academic Session -"
                >
                    @foreach($acadSessions as $session)
                        <flux:select.option value="{{ $session['id'] }}">
                            {{ $session['name'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Program Type (required) --}}
                <flux:select
                    wire:model.live.number="program_type_id"
                    label="Program Type *"
                    searchable
                    indicator="checkbox"
                    variant="listbox"
                    placeholder="- Select Program Type -"
                >
                    @foreach($programTypes as $pt)
                        <flux:select.option value="{{ $pt['id'] }}">
                            {{ $pt['name'] }} @isset($pt['code']) ({{ $pt['code'] }}) @endisset
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Faculty (optional) --}}
                <flux:select
                    wire:model.live.number="faculty_id"
                    label="Faculty"
                    searchable
                    indicator="checkbox"
                    variant="listbox"
                    placeholder="- Select Faculty -"
                    :disabled="!$acad_session_id || !$program_type_id"
                >
                    @foreach($faculties as $f)
                        <flux:select.option value="{{ $f['id'] }}">
                            {{ $f['name'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Department (optional) --}}
                <flux:select
                    wire:model.live.number="department_id"
                    label="Department"
                    searchable
                    indicator="checkbox"
                    variant="listbox"
                    placeholder="- Select Department -"
                    :disabled="!$faculty_id"
                >
                    @foreach($departments as $d)
                        <flux:select.option value="{{ $d['id'] }}">
                            {{ $d['name'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Program (optional) --}}
                <flux:select
                    wire:model.live.number="program_id"
                    label="Program"
                    searchable
                    indicator="checkbox"
                    variant="listbox"
                    placeholder="- Select Program -"
                    :disabled="!$department_id"
                >
                    @foreach($programs as $p)
                        <flux:select.option value="{{ $p['id'] }}">
                            {{ $p['name'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                {{-- Date Range --}}
                <flux:input
                    type="date"
                    wire:model.live="start_date"
                    label="Start Date"
                />

                <flux:input
                    type="date"
                    wire:model.live="end_date"
                    label="End Date"
                />
            </div>

            {{-- Load Button --}}
            <div class="flex items-center gap-3 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <flux:button
                    variant="primary"
                    icon="magnifying-glass"
                    wire:click="filterStudents"
                    :disabled="!$acad_session_id || !$program_type_id"
                    class="cursor-pointer min-w-[180px]"
                >
                    <span wire:loading.remove wire:target="filterStudents">Load Admitted Students</span>
                    <span wire:loading wire:target="filterStudents">Loading…</span>
                </flux:button>
            </div>
        </div>
    </flux:card>

    {{-- Results --}}
    @if($searched)
        @if($infoMessage)
            <flux:card class="p-5 border-amber-300 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-800">
                <div class="flex items-start gap-3">
                    <flux:icon.information-circle class="size-6 text-amber-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <h3 class="font-semibold text-amber-800 dark:text-amber-300">No Admissions Found</h3>
                        <p class="text-sm mt-1 text-amber-700 dark:text-amber-400">{{ $infoMessage }}</p>
                    </div>
                </div>
            </flux:card>
        @endif

        @if(!empty($students))
            <flux:card class="p-6">
                {{-- Results Header --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-3">
                        <flux:heading size="lg">Admitted Students Screening List</flux:heading>
                        <flux:badge variant="info" size="sm">
                            {{ $total }} students
                        </flux:badge>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Export Buttons --}}
                        <flux:button
                            variant="outline"
                            icon="arrow-down-tray"
                            wire:click="exportExcel"
                            size="sm"
                            class="cursor-pointer"
                        >
                            Excel
                        </flux:button>
                        <flux:button
                            variant="outline"
                            icon="paper-clip"
                            wire:click="exportPdf"
                            size="sm"
                            class="cursor-pointer"
                        >
                            PDF
                        </flux:button>

                        {{-- Show Filters Button (when hidden) --}}
                        <flux:button
                            variant="ghost"
                            size="sm"
                            wire:click="$set('showFilters', true)"
                            class="cursor-pointer"
                            x-show="!@entangle('showFilters')"
                        >
                            <span class="flex items-center gap-1.5">
                                <flux:icon.funnel class="size-4" />
                                Filters
                            </span>
                        </flux:button>
                    </div>
                </div>

                <flux:separator class="mb-4" />

                {{-- Table --}}
                <div class="overflow-x-auto -mx-6 px-6">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Reg No.</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Name</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Sex</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Program Type</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Faculty</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Department</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Program</th>
                                <th class="px-3 py-2.5 text-left font-semibold text-zinc-600 dark:text-zinc-400">Screening Code</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @forelse($students as $i => $student)
                                <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-zinc-900' : 'bg-zinc-50 dark:bg-zinc-800/50' }} hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors duration-150">
                                    <td class="px-3 py-2.5 font-mono text-sm text-zinc-900 dark:text-white">{{ $student['regno'] ?? '—' }}</td>
                                    <td class="px-3 py-2.5 font-medium text-zinc-900 dark:text-white">{{ ucwords(strtolower($student['name'] ?? '—')) }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ ($student['sex'] ?? '') === 'Male' ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300' : 'bg-pink-100 dark:bg-pink-900/30 text-pink-800 dark:text-pink-300' }}">
                                            {{ $student['sex'] ?? '—' }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2.5 text-zinc-700 dark:text-zinc-300">{{ $student['program_type'] ?? '—' }}</td>
                                    <td class="px-3 py-2.5 text-zinc-700 dark:text-zinc-300">{{ ucwords(strtolower($student['faculty'] ?? '—')) }}</td>
                                    <td class="px-3 py-2.5 text-zinc-700 dark:text-zinc-300">{{ ucwords(strtolower($student['department'] ?? '—')) }}</td>
                                    <td class="px-3 py-2.5 text-zinc-700 dark:text-zinc-300">{{ ucwords(strtolower($student['program'] ?? '—')) }}</td>
                                    <td class="px-3 py-2.5">
                                        <span class="inline-block font-mono text-xs px-2 py-1 rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 border border-zinc-200 dark:border-zinc-700">
                                            {{ $student['screening_code'] ?? '—' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                        <div class="flex flex-col items-center gap-2">
                                            <flux:icon.user-circle class="size-8 opacity-50" />
                                            <span>No admitted students found.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($lastPage > 1)
                    <div class="flex flex-wrap items-center justify-between gap-3 mt-6 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">
                            Page {{ $page }} of {{ $lastPage }}
                            <span class="hidden sm:inline">· Total {{ $total }} students</span>
                        </div>
                        <div class="flex gap-2">
                            <flux:button
                                variant="outline"
                                size="sm"
                                wire:click="previousPage"
                                :disabled="$page <= 1"
                                class="cursor-pointer"
                            >
                              
                                Previous
                            </flux:button>
                            <flux:button
                                variant="outline"
                                size="sm"
                                wire:click="nextPage"
                                :disabled="$page >= $lastPage"
                                class="cursor-pointer"
                            >
                                Next

                            </flux:button>
                        </div>
                    </div>
                @endif
            </flux:card>
        @endif
    @endif
</div>
