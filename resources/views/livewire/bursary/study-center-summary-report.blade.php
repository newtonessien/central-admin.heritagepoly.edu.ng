<div class="p-6 space-y-6 bg-white dark:bg-gray-900 rounded-lg shadow-md transition" x-data="{ showDetails: false }">

    <h3 class="text-xl font-bold text-gray-800 dark:text-gray-100">
        Study Centre Commission Summary
    </h3>

    {{-- FILTERS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="text-gray-600 dark:text-gray-300">Study Centre</label>
            <select wire:model="study_center_id"
                    class="w-full border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 p-2 rounded">
                <option value="">All Centres</option>

                @foreach($centers as $center)
                    <option value="{{ $center['id'] }}">
                        {{ $center['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-gray-600 dark:text-gray-300">Start Date</label>
            <input type="date"
                   wire:model="start_date"
                   class="w-full border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 p-2 rounded">
        </div>

        <div>
            <label class="text-gray-600 dark:text-gray-300">End Date</label>
            <input type="date"
                   wire:model="end_date"
                   class="w-full border dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 p-2 rounded">
        </div>
    </div>

    {{-- ACTION BUTTONS --}}
    <div class="flex space-x-3">
        <button wire:click="fetchReport"
                class="bg-teal-600 hover:bg-teal-700 text-white px-4 py-2 rounded shadow cursor-pointer">
            Load Report
        </button>

        @if(!empty($report))
            <button wire:click="exportExcel"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow cursor-pointer">
                Export to Excel
            </button>
        @endif
    </div>


     {{-- ========================= --}}
{{--   TOTAL COMMISSION SUMMARY --}}
{{-- ========================= --}}
@if(!empty($report['combined']))
    <div class="mt-10">
    <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
        Total Commission Summary (Combined School Fees + Admissions)
    </h4>

    <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="border p-3 dark:border-gray-700 text-left">Centre</th>
                    <th class="border p-3 dark:border-gray-700 text-center">Transactions</th>
                    <th class="border p-3 dark:border-gray-700 text-right">Total Amount</th>
                    <th class="border p-3 dark:border-gray-700 text-right">Commission(50%)</th>
                    <th class="border p-3 dark:border-gray-700 text-right">Net Total</th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-gray-700">
                @foreach($summaryData as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                        <td class="border p-3 dark:border-gray-700">{{ $row['centre_name'] }}</td>
                        <td class="border p-3 text-center dark:border-gray-700">{{ $row['transactions'] }}</td>
                        <td class="border p-3 text-right dark:border-gray-700">₦{{ number_format($row['total_amount'], 2) }}</td>
                        <td class="border p-3 text-right dark:border-gray-700">₦{{ number_format($row['commission'], 2) }}</td>
                        <td class="border p-3 text-right dark:border-gray-700">₦{{ number_format($row['net_total'], 2) }}</td>
                    </tr>
                @endforeach

                {{-- GRAND TOTAL ROW --}}
                <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                    <td class="border p-3 dark:border-gray-600">TOTAL</td>
                    <td class="border p-3 text-center dark:border-gray-600">
                        {{ collect($summaryData)->sum('transactions') }}
                    </td>
                    <td class="border p-3 text-right dark:border-gray-600">
                        ₦{{ number_format(collect($summaryData)->sum('total_amount'), 2) }}
                    </td>
                    <td class="border p-3 text-right dark:border-gray-600">
                        ₦{{ number_format(collect($summaryData)->sum('commission'), 2) }}
                    </td>
                    <td class="border p-3 text-right dark:border-gray-600">
                        ₦{{ number_format(collect($summaryData)->sum('net_total'), 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

       {{-- Toggle Button for Detailed Tables --}}
            <div class="mt-3">
                <button @click="showDetails = !showDetails"
                        class="bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200 px-3 py-1 rounded shadow">
                    <span x-text="showDetails ? 'Hide Detailed Breakdown' : 'Show Detailed Breakdown'"></span>
                </button>
            </div>
</div>

@endif



    {{-- ========================= --}}
    {{--     SCHOOL FEES TABLE     --}}
    {{-- ========================= --}}

        <div x-show="showDetails" x-transition class="space-y-10 mt-6">
    @if(!empty($report['school_fees']))
        <div class="mt-10">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                School Fees Commission Summary
            </h4>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border p-3 dark:border-gray-700 text-left">Centre</th>
                            <th class="border p-3 dark:border-gray-700 text-center">Transactions</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Total Amount</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Centre Commission (50%)</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y dark:divide-gray-700">
                    @foreach($report['school_fees']['centers'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="border p-3 dark:border-gray-700">
                                {{ $row['study_center_name'] }}
                            </td>
                            <td class="border p-3 text-center dark:border-gray-700">
                                {{ $row['transactions'] }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['total_amount'], 2) }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['center_commission'], 2) }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['center_commission'], 2) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- CONSULTANT --}}
                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                        <td class="border p-3 dark:border-gray-600">Portal (N2K/Fee)</td>
                        <td class="border p-3 text-center dark:border-gray-600">
                            {{ $report['school_fees']['grand_totals']['transactions'] ?? 0 }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['school_fees']['grand_totals']['total_amount'] ?? 0, 2) }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['school_fees']['grand_totals']['total_consultant_commission'] ?? 0, 2) }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['school_fees']['grand_totals']['total_consultant_commission'] ?? 0, 2) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif



    {{-- ========================= --}}
    {{--     ADMISSIONS TABLE     --}}
    {{-- ========================= --}}
    @if(!empty($report['admissions']))
        <div class="mt-10">
            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                Admissions Form Commission Summary
            </h4>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 dark:border-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-100 dark:bg-gray-800">
                        <tr>
                            <th class="border p-3 dark:border-gray-700 text-left">Centre</th>
                            <th class="border p-3 dark:border-gray-700 text-center">Transactions</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Total Amount</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Centre Commission (50%)</th>
                            <th class="border p-3 dark:border-gray-700 text-right">Total</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y dark:divide-gray-700">
                    @foreach($report['admissions']['centers'] ?? [] as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="border p-3 dark:border-gray-700">
                                {{ $row['study_center_name'] }}
                            </td>
                            <td class="border p-3 text-center dark:border-gray-700">
                                {{ $row['transactions'] }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['total_amount'], 2) }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['center_commission'], 2) }}
                            </td>
                            <td class="border p-3 text-right dark:border-gray-700">
                                ₦{{ number_format($row['center_commission'], 2) }}
                            </td>
                        </tr>
                    @endforeach

                    {{-- ADMISSIONS TOTAL ROW --}}
                    <tr class="bg-gray-200 dark:bg-gray-700 font-bold">
                        <td class="border p-3 dark:border-gray-600">Admissions Total</td>
                        <td class="border p-3 text-center dark:border-gray-600">
                            {{ $report['admissions']['grand_totals']['transactions'] ?? 0 }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['admissions']['grand_totals']['total_amount'] ?? 0, 2) }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['admissions']['grand_totals']['total_center_commission'] ?? 0, 2) }}
                        </td>
                        <td class="border p-3 text-right dark:border-gray-600">
                            ₦{{ number_format($report['admissions']['grand_totals']['total_center_commission'] ?? 0, 2) }}
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

    @endif
        </div>





</div>
