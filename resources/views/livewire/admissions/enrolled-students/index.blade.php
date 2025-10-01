<div class="space-y-4">
{{-- 🔎 Top Bar --}}
<div class="flex items-center justify-between flex-wrap gap-3">
<flux:input
placeholder="Search regno or JAMB No..."
wire:model.live.debounce.300ms="search"
class="w-72"
/>

<flux:button type="button" color="primary" wire:click="openCreate" class="cursor-pointer">
Add Student
</flux:button>

<flux:button type="button" color="primary" wire:click="$set('showImport', true)" class="cursor-pointer">
    Import CSV
</flux:button>


</div>

{{-- 🖥 Desktop Table --}}
<flux:card class="hidden md:block">
<div class="overflow-x-auto">
<table class="min-w-full text-sm">
<thead>
<tr class="border-b">
<th class="py-2 px-3">Regno</th>
<th class="py-2 px-3">Full Name</th>
<th class="py-2 px-3">Phone</th>
<th class="py-2 px-3">Gender</th>
<th class="py-2 px-3">JAMB No</th>
<th class="py-2 px-3">Score</th>
<th class="py-2 px-3">Faculty</th>
<th class="py-2 px-3">Department</th>
<th class="py-2 px-3">Program</th>
<th class="py-2 px-3">Prog. Type</th>
<th class="py-2 px-3">Acad. Session</th>
<th class="py-2 px-3">Entry Mode</th>
<th class="py-2 px-3">Screening Code</th>
<th class="py-2 px-3 text-right">Actions</th>
</tr>
</thead>
<tbody>
@forelse($items as $row)
<tr class="border-b">
<td class="py-2 px-3 font-medium">{{ $row['regno'] }}</td>
<td class="py-2 px-3">
{{ $row['first_name'] }} {{ $row['last_name'] }}
@if(!empty($row['other_names']))
({{ $row['other_names'] }})
@endif
</td>
<td class="py-2 px-3">{{ $row['phone_no'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['gender'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['jamb_no'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['jamb_score'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['faculty_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['department_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['program_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['program_type_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['acad_session_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['entry_mode_name'] ?? '—' }}</td>
<td class="py-2 px-3">{{ $row['screening_code'] ?? '—' }}</td>

<td class="py-2 px-3 text-right space-x-1">
<button wire:click="openEdit({{ $row['id'] }})" class="px-2 py-1 bg-yellow-500 text-white rounded cursor-pointer">Edit</button>
<button wire:click="openDelete({{ $row['id'] }})" class="px-2 py-1 bg-red-600 text-white rounded cursor-pointer">Delete</button>
</td>
</tr>
@empty
<tr>
<td colspan="14" class="py-6 text-center text-zinc-500">No records</td>
</tr>
@endforelse
</tbody>
</table>
</div>




<div class="mt-4 flex justify-between">
    @if(!empty($meta['current_page']))
        <span>Page {{ $meta['current_page'] }} of {{ $meta['last_page'] }}</span>
    @endif

    <div class="space-x-2">
        @if($links['prev'])
            <a href="#" wire:click.prevent="$set('page', {{ $meta['current_page'] - 1 }})"
               class="px-3 py-1 bg-gray-200 rounded">Prev</a>
        @endif
        @if($links['next'])
            <a href="#" wire:click.prevent="$set('page', {{ $meta['current_page'] + 1 }})"
               class="px-3 py-1 bg-gray-200 rounded">Next</a>
        @endif
    </div>
</div>

</flux:card>

{{-- 📱 Mobile Cards --}}
<div class="space-y-3 md:hidden">
@forelse($items as $row)
<flux:card class="p-4">
<div class="flex justify-between items-center">
<div class="font-semibold">{{ $row['regno'] }}</div>
<div class="space-x-1">
<button wire:click="openEdit({{ $row['id'] }})" class="px-2 py-1 bg-yellow-500 text-white rounded text-xs">Edit</button>
<button wire:click="delete({{ $row['id'] }})" class="px-2 py-1 bg-red-600 text-white rounded text-xs">Delete</button>
</div>
</div>

<div class="mt-2 text-sm space-y-1">
<div><span class="font-medium">Name:</span> {{ $row['first_name'] }} {{ $row['last_name'] }} {{ $row['other_names'] }}</div>
<div><span class="font-medium">Phone:</span> {{ $row['phone_no'] ?? '—' }}</div>
<div><span class="font-medium">Gender:</span> {{ $row['gender'] ?? '—' }}</div>
<div><span class="font-medium">JAMB No:</span> {{ $row['jamb_no'] ?? '—' }}</div>
<div><span class="font-medium">Score:</span> {{ $row['jamb_score'] ?? '—' }}</div>
<div><span class="font-medium">Faculty:</span> {{ $row['faculty_name'] ?? '—' }}</div>
<div><span class="font-medium">Dept:</span> {{ $row['department_name'] ?? '—' }}</div>
<div><span class="font-medium">Program:</span> {{ $row['program_name'] ?? '—' }}</div>
<div><span class="font-medium">Type:</span> {{ $row['program_type_name'] ?? '—' }}</div>
<div><span class="font-medium">Session:</span> {{ $row['acad_session_name'] ?? '—' }}</div>
<div><span class="font-medium">Entry:</span> {{ $row['entry_mode_name'] ?? '—' }}</div>
<div><span class="font-medium">Code:</span> {{ $row['screening_code'] ?? '—' }}</div>
</div>
</flux:card>
@empty
<div class="text-center text-zinc-500 py-6">No records</div>
@endforelse
</div>

{{-- ✍ Modal Form --}}
@if($showForm)
<div class="fixed inset-0 bg-black/30 flex items-center justify-center p-4 z-50">
<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-xl w-full max-w-3xl p-6 space-y-4">
<div class="text-lg font-semibold">
{{ $editId ? 'Edit Enrolled Student' : 'Add Enrolled Student' }}
</div>

<div class="grid md:grid-cols-2 gap-4 max-h-[70vh] overflow-y-auto pr-2">
<flux:input label="Regno" wire:model.live="form.regno" />
<flux:input label="First Name" wire:model.live="form.first_name" />
<flux:input label="Last Name" wire:model.live="form.last_name" />
<flux:input label="Other Names" wire:model.live="form.other_names" />
<flux:input label="Phone No" wire:model.live="form.phone_no" />

<flux:select label="Gender" wire:model.live="form.gender">
<option value="">-- Select --</option>
<option value="M">Male</option>
<option value="F">Female</option>
</flux:select>

<flux:input label="JAMB Score" type="number" wire:model.live="form.jamb_score" />

<flux:select label="Program Type" wire:model.live="form.program_type_id">
<option value="">-- Select --</option>
@foreach($programTypes as $pt)
<option value="{{ $pt['id'] }}">{{ $pt['name'] }}</option>
@endforeach
</flux:select>

<flux:select label="Faculty" wire:model.live="form.faculty_id">
<option value="">-- Select --</option>
@foreach($faculties as $f)
<option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
@endforeach
</flux:select>

<flux:select label="Department" wire:model.live="form.department_id">
<option value="">-- Select --</option>
@foreach($departments as $d)
<option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
@endforeach
</flux:select>

<flux:select label="Program" wire:model.live="form.program_id">
<option value="">-- Select --</option>
@foreach($programs as $p)
<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
@endforeach
</flux:select>

<flux:select label="Academic Session" wire:model.live="form.acad_session_id">
<option value="">-- Select --</option>
@foreach($acadSessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>

<flux:select label="Entry Mode" wire:model.live="form.entry_mode_id">
<option value="">-- Select --</option>
@foreach($entryModes as $m)
<option value="{{ $m['id'] }}">{{ $m['name'] }}</option>
@endforeach
</flux:select>


<flux:select label="Study Mode" wire:model.live="form.study_mode_id">
<option value="">-- Select --</option>
<option value="1">Full-Time</option>
<option value="2">Part-Time</option>
</flux:select>



</div>

<div class="flex justify-end gap-2 pt-2">
<flux:button variant="ghost" wire:click="$set('showForm', false)" class="cursor-pointer">Cancel</flux:button>
<flux:button color="primary" wire:click="save" class="cursor-pointer">Save</flux:button>
</div>
</div>
</div>
@endif

{{-- ❌ Confirm Delete Modal --}}
<flux:modal wire:model="confirmDelete">
<h3 class="text-lg font-semibold">Delete Student?</h3>
<p class="text-sm text-gray-500">This action cannot be undone.</p>

<div class="mt-4 flex justify-end gap-2">
<flux:button wire:click="$set('confirmDelete', false)" variant="ghost" class="cursor-pointer">Cancel</flux:button>
<flux:button wire:click="delete" color="destructive" class="cursor-pointer">Delete</flux:button>
</div>
</flux:modal>


{{-- 📁 Import CSV Modal --}}
<flux:modal wire:model="showImport">
    <h3 class="text-lg font-semibold">Import Enrolled Students</h3>
    <p class="text-sm text-gray-500">Upload a CSV file with student data (regno, name, faculty, etc).</p>

    <div class="mt-4">
        <input type="file" wire:model="csvFile" accept=".csv" class="w-full border rounded p-2">
    </div>

    <div class="mt-4 flex justify-end gap-2">
        <flux:button wire:click="$set('showImport', false)" variant="ghost" class="cursor-pointer">Cancel</flux:button>
        <flux:button wire:click="importCsv" color="primary" class="cursor-pointer">Upload</flux:button>
    </div>
</flux:modal>




</div>
