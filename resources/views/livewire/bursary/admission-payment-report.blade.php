<div class="p-6 space-y-6 dark:bg-gray-900 dark:text-gray-100">
       <flux:heading size="xl" level="1">{{ __('Admission Forms Payment Report') }}</flux:heading>
    <flux:separator />
<!-- Filters -->
<div class="grid grid-cols-4 gap-4 items-end">
<div>

<flux:select
wire:model.live.number="selectedApplicationType"
label="Application Type"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Application Type -"
>
@foreach($applicationTypes as $type)
<flux:select.option value="{{ $type['id'] }}">
{{ $type['name'] }} @isset($type['code']) ({{ $type['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>
</div>
<div>

<flux:select
wire:model.live.number="selectedFaculty"
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
<flux:date-picker selectable-header wire:model="startDate"   label="Start Date"/>
</div>

<div>
<flux:date-picker selectable-header wire:model="endDate"   label="End Date"/>
</div>
</div>

<!-- Buttons -->
<div class="flex gap-2 mt-2">
<flux:button variant="primary" color="green" wire:click="loadReports" class="cursor-pointer" icon="arrow-path-rounded-square" size="sm">Load Reports</flux:button>
@if($totalRecords > 0)
<flux:button variant="primary" wire:click="export('excel')" color="lime" icon="document-text" class="cursor-pointer" size="sm">Export to Excel</flux:button>
<flux:button variant="primary" color="teal" wire:click="export('pdf')" icon="arrow-down-tray" class="cursor-pointer" size="sm">Export to PDF</flux:button>
@endif
</div>

<!-- Loading -->
@if($loading)
<div class="text-center py-10 text-gray-500 dark:text-gray-400">Loading reports...</div>
@endif

<!-- Reports -->
@if(!$loading && $reports && count($reports))
<!-- Summary Section -->
<div class="bg-gray-50 border rounded p-4 flex justify-between items-center dark:bg-gray-800 dark:border-gray-700">
<div class="text-lg font-semibold text-gray-700 dark:text-gray-200">
Total Records: {{ number_format($totalRecords) }}
</div>
<div class="text-lg font-semibold text-green-600 dark:text-green-400">
Total Amount: ₦{{ number_format($totalAmount, 2) }}
</div>
</div>

<!-- Reports Table -->
<div class="mt-4 overflow-x-auto">
<table class="min-w-full border border-gray-200 dark:border-gray-700">
<thead class="bg-gray-100 text-sm text-gray-700 uppercase dark:bg-gray-800 dark:text-gray-300">
<tr>
<th class="px-3 py-2 text-left">Sn</th>
<th class="px-3 py-2 text-left">Reg No</th>
<th class="px-3 py-2 text-left">Full Name</th>
<th class="px-3 py-2 text-left">Faculty</th>
<th class="px-3 py-2 text-left">Department</th>
<th class="px-3 py-2 text-right">Amount</th>
<th class="px-3 py-2 text-center">Trans Date</th>
</tr>
</thead>
<tbody class="dark:divide-gray-700">
@foreach($this->paginatedReports as $report)
<tr class="border-t text-sm hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-700">
<td class="px-3 py-2">{{ ($loop->index + 1) + (($page - 1) * $perPage) }}</td>
<td class="px-3 py-2">{{ $report['regno'] }}</td>
<td class="px-3 py-2">{{ $report['fullname'] }}</td>
<td class="px-3 py-2">{{ $report['faculty'] }}</td>
<td class="px-3 py-2">{{ $report['department'] }}</td>
<td class="px-3 py-2 text-right">₦{{ number_format($report['amount'], 2) }}</td>
<td class="px-3 py-2 text-center">{{ \Carbon\Carbon::parse($report['trans_date'])->format('d M Y') }}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<!-- Pagination -->
<div class="flex justify-between mt-4">
<button wire:click="previousPage"
class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 cursor-pointer"
@disabled($page == 1)>
Prev
</button>
<span class="text-sm text-gray-600 dark:text-gray-400">Page {{ $page }} of {{ ceil($totalRecords / $perPage) }}</span>
<button wire:click="nextPage"
class="px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 cursor-pointer"
@disabled(($page * $perPage) >= $totalRecords)>
Next
</button>
</div>
@elseif(!$loading)
<div class="text-center text-gray-500 dark:text-gray-400">No records found for selected filters.</div>
@endif
</div>
