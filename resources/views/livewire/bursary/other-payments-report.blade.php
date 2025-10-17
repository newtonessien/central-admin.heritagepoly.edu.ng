<div class="space-y-6">
       <flux:heading size="xl" level="1">{{ __('General Payment Report') }}</flux:heading>
    <flux:separator />

    <!-- ================= FILTERS ================= -->
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">

        <!-- Program Type -->
        <flux:select wire:model="programTypeId" label="Program Type" searchable
indicator="checkbox"
variant="listbox" placeholder="- Select Program Type -">
            <flux:select.option value="all">All Program Types</flux:select.option>
            @foreach($programTypes as $type)
                <flux:select.option value="{{ $type['id'] }}">{{ $type['name'] }}</flux:select.option>
            @endforeach
        </flux:select>



        <!-- Faculty -->
        <flux:select wire:model="facultyId" label="Faculty" searchable
indicator="checkbox"
variant="listbox" placeholder="- Select Faculty -">
            <flux:select.option value="all">All Faculties</flux:select.option>
            @foreach($faculties as $faculty)
                <flux:select.option value="{{ $faculty['id'] }}">{{ $faculty['name'] }}</flux:select.option>
            @endforeach
        </flux:select>

        <!-- Service Type -->
        <flux:select wire:model="serviceId" label="Service Type" searchable
indicator="checkbox"
variant="listbox" placeholder="- Select Service Type -">
            <flux:select.option value="all">All Services</flux:select.option>
            @foreach($services as $service)
                <flux:select.option value="{{ $service['id'] }}">{{ $service['name'] }}</flux:select.option>
            @endforeach
        </flux:select>

        <!-- Start Date -->
        <div>
            <flux:date-picker wire:model="startDate" label="Start Date"/>
        </div>

        <!-- End Date -->
        <div>
            <flux:date-picker wire:model="endDate" label="End Date"/>
        </div>
    </div>

    <!-- Load Reports Button -->
<div class="flex gap-2 mt-3">
<flux:button variant="primary" color="green" wire:click="loadReports" class="cursor-pointer" icon="arrow-path-rounded-square" size="sm">Load Reports</flux:button>

  @if($paginatedReports->count() > 0)
<flux:button variant="primary" color="lime" wire:click="exportExcel" class="cursor-pointer" icon="clipboard-document-list" size="sm">Export to Excel</flux:button>
<flux:button variant="primary" color="emerald" wire:click="exportPdf" class="cursor-pointer" icon="document-text" size="sm">Export to PDF</flux:button>
@endif
</div>



    <!-- ================= REPORTS ================= -->
    <div wire:loading.class="opacity-50 pointer-events-none">

        @if($paginatedReports->count() > 0)

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
            <div class="overflow-x-auto mt-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-100 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">#</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Reg No</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Fullname</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Faculty</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Department</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Service</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Amount (₦)</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">Transaction Date</th>
                        </tr>
                    </thead>

                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($paginatedReports as $index => $row)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $loop->iteration + ($paginatedReports->currentPage() - 1) * $paginatedReports->perPage() }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['regno'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['fullname'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['faculty'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['department'] ?? '' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">{{ $row['service'] ?? '' }}</td>
                                <td class="px-4 py-2 text-right text-sm text-gray-700 dark:text-gray-300">
                                    ₦{{ number_format($row['amount'] ?? 0, 2) }}
                                </td>
                                <td class="px-4 py-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ isset($row['trans_date']) ? \Carbon\Carbon::parse($row['trans_date'])->format('d M, Y') : '' }}
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
            <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                No records found for the selected filters.
            </div>
        @endif
    </div>

</div>
