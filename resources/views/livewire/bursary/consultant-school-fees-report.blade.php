<div class="p-4">

    <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">
        Portal Commission Report
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">Start Date</label>
            <input type="date" wire:model="start_date"
                   class="w-full border rounded p-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:ring focus:ring-blue-400 focus:outline-none">
        </div>

        <div>
            <label class="block mb-1 text-gray-700 dark:text-gray-200">End Date</label>
            <input type="date" wire:model="end_date"
                   class="w-full border rounded p-2 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-100 focus:ring focus:ring-blue-400 focus:outline-none">
        </div>

        <div class="flex items-end">
            <button wire:click="fetchReport"
                    class="w-full bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white font-semibold py-2 px-4 rounded shadow-md transition cursor-pointer">
                Fetch Report
            </button>
        </div>
    </div>

    @if($report)
        <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-800 rounded shadow">
            <h4 class="font-semibold text-lg mb-3 text-gray-800 dark:text-gray-100">Summary</h4>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-700 p-4 rounded shadow flex justify-between items-center">
                    <span class="font-medium text-gray-700 dark:text-gray-200">Total Transactions</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100">{{ $report['total_transactions'] ?? 0 }}</span>
                </div>
                <div class="bg-white dark:bg-gray-700 p-4 rounded shadow flex justify-between items-center">
                    <span class="font-medium text-gray-700 dark:text-gray-200">Portal Commission (N2K/Fee)</span>
                    <span class="font-bold text-gray-900 dark:text-gray-100">
                        ₦{{ number_format($report['consultant_commission'] ?? 0, 2) }}
                    </span>
                </div>
            </div>

            {{-- Optional Export Button --}}
            <div class="mt-4 flex justify-end">
                <button
                        class="bg-teal-600 hover:bg-teal-700 dark:bg-teal-500 dark:hover:bg-teal-600 text-white font-semibold py-2 px-4 rounded shadow-md transition">
                    Report for the Period - {{ date('d-M-Y', strtotime($start_date)) }} to {{ date('d-M-Y', strtotime($end_date)) }}
                </button>
            </div>
        </div>
    @endif
</div>
