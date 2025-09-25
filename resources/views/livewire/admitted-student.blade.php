<div class="space-y-2">
    {{-- Filters --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Program Type (required) --}}
        <flux:select
            wire:model.live.number="program_type_id"
            label="Program Type"
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
            :disabled="!$program_type_id"
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

        {{-- Load Button --}}
        <flux:button
            variant="primary"
            icon="magnifying-glass"
            wire:click="filterStudents"
            :disabled="!$program_type_id"
            class="cursor-pointer"
        >
            <span wire:loading.remove wire:target="filterStudents">Load Admitted Students</span>
            <span wire:loading wire:target="filterStudents">Loading…</span>
        </flux:button>
    </div>


    {{-- Results --}}
      @if(!empty($students))
    <flux:card class="space-y-4">
        <flux:heading size="lg">Admitted Students Screening List</flux:heading>
        <flux:separator></flux:separator>

        <div class="overflow-x-auto">
            <div class="flex gap-2 mb-4">
    <flux:button variant="outline" icon="arrow-down-tray" wire:click="exportExcel" class="cursor-pointer">
        Export Excel
    </flux:button>
    <flux:button variant="outline" icon="paper-clip" wire:click="exportPdf" class="cursor-pointer">
        Export PDF
    </flux:button>
</div>
              <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="border-b">
        <tr class="border-b">
        <th class="px-3 py-2">RegNo</th>
        <th class="px-3 py-2">Name</th>
        <th class="px-3 py-2">Sex</th>
        <th class="px-3 py-2">Program Type</th>
        <th class="px-3 py-2">Faculty</th>
        <th class="px-3 py-2">Department</th>
        <th class="px-3 py-2">Program</th>
        <th class="px-3 py-2">Screening Code</th>
    </tr>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200">
              @forelse($students as $i => $student)
   <tr class="{{ $i % 2 === 0 ? 'bg-white dark:bg-zinc-900' : 'bg-zinc-50 dark:bg-zinc-800' }}">
        <td class="px-3 py-2">{{ $student['regno'] ?? '—' }}</td>
        <td class="px-3 py-2">{{ $student['name'] ?? '—' }}</td>
        <td class="px-3 py-2">{{ $student['sex'] ?? '—' }}</td>
               <td class="px-3 py-2">{{ $student['program_type'] ?? '—' }}</td>
        <td class="px-3 py-2">{{ $student['faculty'] ?? '—' }}</td>
        <td class="px-3 py-2">{{ $student['department'] ?? '—' }}</td>
        <td class="px-3 py-2">{{ $student['program'] ?? '—' }}</td>
        <td class="px-3 py-2 font-mono">{{ $student['screening_code'] ?? '—' }}</td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-3 py-4 text-center">
            No admitted students found.
        </td>
    </tr>
@endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
<div class="flex items-center justify-between mt-4">
  <div class="text-sm text-zinc-600">
    Page {{ $page }} of {{ $lastPage }} · Total {{ $total }}
  </div>
  <div class="flex gap-2">
    <flux:button
        variant="outline"
        size="sm"
        wire:click="previousPage"
        :disabled="$page <= 1">
      Previous
    </flux:button>
    <flux:button
        variant="outline"
        size="sm"
        wire:click="nextPage"
        :disabled="$page >= $lastPage">
      Next
    </flux:button>
  </div>
</div>

    </flux:card>
        @endif
</div>
