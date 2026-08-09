<div class="space-y-6">

{{-- Filters --}}
<x-filters.academic-history :sessions="$sessions">

<x-slot:actions>

{{-- Reset --}}

<flux:button
variant="ghost"
icon="arrow-path"
wire:click="resetFilters"
>
Reset
</flux:button>

<flux:button
variant="primary"
class="cursor-pointer"
icon="document-arrow-down"
size="sm"
wire:click="exportPdf">
PDF
</flux:button>



</x-slot:actions>

</x-filters.academic-history>


<div class="grid grid-cols-2 gap-2 lg:grid-cols-2">

{{-- Sessions --}}

<flux:card>

<flux:heading size="sm">
Total Transactions
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
 {{ number_format($summary['transactions'] ?? 0) }}
</flux:heading>

<flux:text class="mt-1">
Transactions
</flux:text>

</flux:card>


<flux:card>

<flux:heading size="sm">
Total Amount
</flux:heading>

<flux:heading
size="xl"
class="mt-2"
>
 {{ number_format($summary['total_amount'] ?? 0, 2) }}
</flux:heading>

<flux:text class="mt-1">
Total Amount
</flux:text>

</flux:card>

</div>


{{-- Payment History --}}
<flux:card>

<div class="flex items-center justify-between mb-5">
  <!-- LEFT SIDE -->
  <div>
    <flux:heading size="lg">
      Payment History
    </flux:heading>

    <flux:text class="mt-1 text-zinc-500">
      Student payment transactions.
    </flux:text>
  </div>

  <!-- RIGHT SIDE -->
  <div class="flex items-center gap-6">
    <div class="text-right">
      <flux:text class="text-xs text-zinc-500">
        Transactions
      </flux:text>
      <div class="font-semibold">
       {{ number_format($summary['transactions'] ?? 0) }}
      </div>
    </div>

    <div class="text-right">
      <flux:text class="text-xs text-zinc-500">
        Total
      </flux:text>
      <div class="font-semibold">
        ₦{{ number_format($summary['total_amount'] ?? 0, 2) }}
      </div>
    </div>
  </div>
</div>



<div class="overflow-x-auto">

<table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

<thead>

<tr class="bg-zinc-50 dark:bg-zinc-800/50">

<th class="px-4 py-3 w-16 text-center">
#
</th>

<th class="px-4 py-3 text-left">
Tranx Reference#
</th>

<th class="px-4 py-3 text-left">
Payment Type
</th>

<th class="px-4 py-3 text-left">
Session
</th>

<th class="px-4 py-3 text-left">
Semester
</th>

<th class="px-4 py-3 text-center">
Level
</th>

<th class="px-4 py-3 text-right">
Amount
</th>

<th class="px-4 py-3 text-center">
Date Paid
</th>

</tr>

</thead>

<tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

@forelse($payments as $payment)

<tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

<td class="px-4 py-3 text-center text-zinc-500">
{{ $loop->iteration }}
</td>

<td class="px-4 py-3 font-medium">
{{ $payment['transaction_reference'] }}
</td>

<td class="px-4 py-3">

<flux:badge
color="{{ ($payment['type'] ?? '') === 'school_fee' ? 'green' : 'blue' }}">

{{ $payment['payment_type'] ?? 'N/A' }}

</flux:badge>

</td>

<td class="px-4 py-3">
{{ $payment['session'] }}
</td>

<td class="px-4 py-3">
{{ $payment['fee_period_id'] === 1 ? 'First Semester' : ($payment['fee_period_id'] === 2 ? 'Second Semester' : ($payment['fee_period_id'] === 3 ? 'Full Session' : '-')) }}
</td>

<td class="px-4 py-3 text-center">

{{ is_numeric($payment['level_id'])
? $payment['level_id'].'00'
: ($payment['level_id'] ?? '-') }}

</td>

<td class="px-4 py-3 text-right font-semibold">

₦{{ number_format($payment['amount'], 2) }}

</td>

<td class="px-4 py-3 text-center">

{{ $payment['payment_date']
? date('d M, Y', strtotime($payment['payment_date']))
: '-' }}

</td>

</tr>

@empty

<tr>

<td colspan="8" class="px-6 py-10">

<flux:callout variant="secondary">

No payment history found for the selected filters.

</flux:callout>

</td>

</tr>

@endforelse

</tbody>

</table>

</div>

{{-- Pagination --}}
@if(!empty($pagination) && ($pagination['last_page'] ?? 1) > 1)

<div class="flex items-center justify-between mt-6 border-t border-zinc-200 dark:border-zinc-700 pt-4">

<flux:text class="text-sm text-zinc-500">

Showing
{{ $pagination['from'] }}
-
{{ $pagination['to'] }}
of
{{ $pagination['total'] }}
transactions

</flux:text>

<div class="flex items-center gap-2">

<flux:button
variant="ghost"
size="sm"
class="cursor-pointer"
wire:click="previousPage"
:disabled="$pagination['current_page'] <= 1">

Previous

</flux:button>

<flux:badge>

Page {{ $pagination['current_page'] }}
of
{{ $pagination['last_page'] }}

</flux:badge>

<flux:button
variant="ghost"
size="sm"
class="cursor-pointer"
wire:click="nextPage"
:disabled="$pagination['current_page'] >= $pagination['last_page']">

Next

</flux:button>

</div>

</div>

@endif

</flux:card>

</div>
