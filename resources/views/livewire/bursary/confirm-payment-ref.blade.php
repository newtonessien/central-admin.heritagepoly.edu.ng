<div class="bg-gradient-to-br from-gray-50 to-gray-100 dark:from-zinc-900 dark:to-zinc-800 p-6">
<div class="max-w-4xl mx-auto">
<!-- Header Section -->
<div class="mb-8">
<h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Payment Verification System</h1>
<p class="text-gray-600 dark:text-gray-400">Enter a transaction reference to retrieve payment details</p>
</div>

<!-- Search Section -->
<div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-6 mb-6">
<div class="flex flex-col md:flex-row md:items-end gap-4">
<div class="flex-1">
<label for="trans_refno" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
Transaction Reference
</label>
<flux:input
id="trans_refno"
wire:model.defer="trans_refno"
placeholder="Enter Transaction Reference"
class="w-full"
/>
</div>

<flux:button
wire:click="fetchPaymentByRef"
variant="primary"
class="w-full md:w-auto md:min-w-[140px] h-[42px] cursor-pointer"
icon="magnifying-glass"

>
Fetch Payment
</flux:button>
</div>

{{-- @error('trans_refno')
<p class="text-red-500 text-sm mt-2 flex items-center">
<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
</svg>
{{ $message }}
</p>
@enderror --}}
</div>

<!-- Payment Details -->
@if($payment)
<flux:card class="p-6 space-y-6 bg-white dark:bg-zinc-900 border border-gray-200 dark:border-zinc-700 rounded-xl shadow-sm">
<!-- Header with status -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200 dark:border-zinc-700">
<div>
<flux:heading size="lg" class="font-bold text-gray-900 dark:text-white">Payment Details</flux:heading>
<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Reference: {{ $payment['trans_refno'] }}</p>
</div>
<div class="flex items-center gap-3">
<span class="text-xs px-3 py-1.5 rounded-full font-medium
{{ $payment['is_paid'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' }}">
{{ $payment['is_paid'] ? 'Paid' : 'Unpaid' }}
</span>
<div class="h-8 w-px bg-gray-300 dark:bg-zinc-600"></div>
<div class="text-right">
<p class="text-xs text-gray-500 dark:text-gray-400">Confirmed</p>
<p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $payment['confirmed_count'] }} times</p>
</div>
</div>
</div>

<!-- Payment Information Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Student Information -->
<div class="space-y-4">
<h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Student Information</h3>
<div class="space-y-3">
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Registration No</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['regno'] }}</span>
</div>
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Full Name</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['student_name'] }}</span>
</div>
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Academic Session</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['academic_session'] }}</span>
</div>
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Program Type</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['program_type'] }}</span>
</div>
</div>
</div>

<!-- Payment Details -->
<div class="space-y-4">
<h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Payment Details</h3>
<div class="space-y-3">
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Amount</span>
<span class="font-bold text-lg text-gray-900 dark:text-white">₦{{ number_format($payment['amount'], 2) }}</span>
</div>
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Payment Date</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['payment_date'] }}</span>
</div>
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Payment Type</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['payment_type'] }}</span>
</div>

@if($payment['payment_type'] === 'School Fee')
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Fee</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['fee_name'] ?? '-' }}</span>
</div>
@elseif($payment['payment_type'] === 'Service')
<div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-zinc-800">
<span class="text-sm text-gray-600 dark:text-gray-400">Service</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $payment['service_name'] ?? '-' }}</span>
</div>
@endif
</div>
</div>
</div>

<!-- Actions -->
<div class="pt-4 border-t border-gray-200 dark:border-zinc-700 flex justify-end">
<div class="flex gap-3">
<flux:button variant="outline" size="sm" icon="printer">
Print Receipt
</flux:button>
<flux:button variant="primary" size="sm" icon="document-duplicate">
Export Details
</flux:button>
</div>
</div>
</flux:card>
@else
<!-- Empty State -->
<div class="bg-white dark:bg-zinc-900 rounded-xl shadow-sm border border-gray-200 dark:border-zinc-700 p-12 text-center">
<div class="max-w-md mx-auto">
<div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
<svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
</svg>
</div>
<h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No Payment Data</h3>
<p class="text-gray-500 dark:text-gray-400">Enter a transaction reference above to retrieve payment information.</p>
</div>
</div>
@endif
</div>
</div>
