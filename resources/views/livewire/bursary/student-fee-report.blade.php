<div class="p-6 space-y-6 dark:bg-gray-900 dark:text-gray-100">
       <flux:heading size="xl" level="1">{{ __('Student Fee Payment Report') }}</flux:heading>
    <flux:separator />
<!-- Filters -->
<div class="grid grid-cols-4 gap-4 items-end">

<div>

<flux:select
wire:model.live.number="programTypeId"
label="Program Type"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Program Type -"
>
@foreach($programTypes as $type)
<flux:select.option value="{{ $type['id'] }}">
{{ $type['name'] }} @isset($type['code']) ({{ $type['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>

</div>

<div>
<flux:select
wire:model.live="facultyId"
label="Faculty"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Faculty -"
>
@foreach($faculties as $faculty)
<flux:select.option value="{{ $faculty['id'] }}">
{{ $faculty['name'] }} @isset($faculty['code']) ({{ $faculty['code'] }}) @endisset
</flux:select.option>
@endforeach
<flux:select.option value="all">All Faculties</flux:select.option>
</flux:select>
</div>

<div>
<flux:date-picker selectable-header wire:model="startDate" label="Start Date" />
</div>

<div>
<flux:date-picker selectable-header wire:model="endDate" label="End Date" />
</div>
</div>

<!-- Buttons -->
<div class="flex gap-2 mt-3">
<flux:button variant="primary" color="green" wire:click="loadReports" class="cursor-pointer" icon="arrow-path-rounded-square" size="sm">Load Reports</flux:button>

@if(!empty($reports))
<flux:button variant="primary" color="lime" wire:click="exportExcel" class="cursor-pointer" icon="clipboard-document-list" size="sm">Export to Excel</flux:button>
<flux:button variant="primary" color="emerald" wire:click="exportPdf" class="cursor-pointer" icon="document-text" size="sm">Export to PDF</flux:button>
@endif
</div>

<!-- Reports -->
@if($reports && count($reports))
<!-- Summary Section -->
<div class="bg-gray-50 border rounded p-4 flex justify-between items-center dark:bg-gray-800 dark:border-gray-700">
<div class="text-lg font-semibold text-gray-700 dark:text-gray-200">
Total Records: {{ number_format($totalRecords) }}
</div>
<div class="text-lg font-semibold text-green-600 dark:text-green-400">
Total Amount: ₦{{ number_format($totalAmount, 2) }}
</div>
</div>

<!-- Table -->
<div class="overflow-x-auto mt-4">
<table class="min-w-full border border-gray-300 dark:border-gray-700">
<thead class="bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 text-sm uppercase tracking-wider">
<tr>
<th class="px-3 py-2 text-left">#</th>
<th class="px-3 py-2 text-left">Reg No</th>
<th class="px-3 py-2 text-left">Fullname</th>
<th class="px-3 py-2 text-left">Faculty</th>
<th class="px-3 py-2 text-left">Department</th>
<th class="px-3 py-2 text-right">Amount</th>
<th class="px-3 py-2 text-center">Date</th>
</tr>
</thead>
<tbody class="text-sm divide-y dark:divide-gray-700">
@foreach($paginatedReports as $index => $r)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
<td class="px-3 py-2">{{ $index + 1 }}</td>
<td class="px-3 py-2">{{ $r['regno'] ?? '' }}</td>
<td class="px-3 py-2">{{ $r['fullname'] ?? '' }}</td>
<td class="px-3 py-2">{{ $r['faculty'] ?? '' }}</td>
<td class="px-3 py-2">{{ $r['department'] ?? '' }}</td>
<td class="px-3 py-2 text-right">
₦{{ number_format($r['amount'] ?? 0, 2) }}
</td>
<td class="px-3 py-2 text-center">
{{ \Carbon\Carbon::parse($r['trans_date'] ?? '')->format('d M Y') }}
</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- Pagination -->
<div class="mt-4">
{{ $paginatedReports->links() }}
</div>
@else
<div class="text-center text-gray-500 dark:text-gray-400">
No records found. Please apply filters and click “Load Reports”.
</div>
@endif
</div>
