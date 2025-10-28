<div class="space-y-6">
<!-- Search Section -->
<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow">
<flux:heading size="lg" class="mb-4">Manage Student Payment</flux:heading>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<flux:input
wire:model.defer="regno"
label="Reg. No / Matric No"
placeholder="Enter student registration number"
/>

<div class="flex items-end">
<flux:button
wire:click="findStudent"
variant="primary"
class="w-full md:w-auto cursor-pointer"
icon="magnifying-glass-circle"
spinner="findStudent"
>
Search Student
</flux:button>
</div>
</div>

@if($loadingStudent)
<div class="text-amber-600 mt-2">Searching student...</div>
@endif

@if(!empty($student['regno']))
<!-- Student Details Card -->
<div class="mt-6 p-5 bg-white dark:bg-gray-900 rounded-xl shadow border border-gray-200 dark:border-gray-700">
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
<h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
{{ $student['name'] ?? 'Unknown Student' }}
</h3>
<span class="text-sm text-gray-500 dark:text-gray-400">
Reg No:
<span class="font-medium text-gray-800 dark:text-gray-200">
{{ $student['matric_no'] ?? $student['regno'] ?? '-' }}
</span>
</span>
</div>

<dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
<div class="flex flex-col">
<dt class="text-gray-500 dark:text-gray-400">Faculty</dt>
<dd class="font-medium text-gray-800 dark:text-gray-200">
{{ $student['faculty'] ?? '-' }}
</dd>
</div>

<div class="flex flex-col">
<dt class="text-gray-500 dark:text-gray-400">Department</dt>
<dd class="font-medium text-gray-800 dark:text-gray-200">
{{ $student['department'] ?? '-' }}
</dd>
</div>

<div class="flex flex-col">
<dt class="text-gray-500 dark:text-gray-400">Program Type</dt>
<dd class="font-medium text-gray-800 dark:text-gray-200">
{{ $student['program_type'] ?? '-' }}
</dd>
</div>
</dl>
</div>
@elseif($errorMessage)
<div class="mt-4 p-4 bg-red-50 dark:bg-red-900 text-red-700 dark:text-red-200 rounded">
{{ $errorMessage }}
</div>
@endif
</div>

<!-- Payment Approval Section -->
@if(!empty($student['regno']))
<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow space-y-4">
<flux:heading size="md">Manage New Payment</flux:heading>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<flux:select
wire:model.live.number="serviceId"
label="Service Type"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Service Type -"
>
@foreach($services as $service)
<flux:select.option value="{{ $service['id'] }}">
{{ $service['name'] }}
</flux:select.option>
@endforeach
</flux:select>

<flux:input
wire:model="amount"
type="number"
label="Amount (₦)"
placeholder="Enter amount"
/>

<flux:input
wire:model="notes"
label="Notes"
placeholder="Enter any notes"
/>
</div>

<div class="flex justify-end">
<flux:button
wire:click="approvePayment"
variant="primary"
class="cursor-pointer"
spinner="approvePayment"
icon="check-circle"
size="sm"
wire:attr.disabled="!($serviceId && $amount)"
>
Initiate Payment
</flux:button>
</div>
</div>

<!-- Existing Approved Payments Table -->
@if($loadingPayments)
<div class="text-blue-600 mt-3">Loading payments...</div>
@endif

@if(!empty($payments))

<flux:button wire:click="togglePayments" variant="primary" class="mt-4 cursor-pointer" size="sm" icon="{{ $showPayments ? 'eye-slash' : 'eye' }}">
    {{ $showPayments ? 'Hide Payments Update' : 'Show Payments Update' }}
</flux:button>

@if($showPayments)
<div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow space-y-4">
<flux:heading size="md">Existing Managed Payments</flux:heading>

<div class="overflow-x-auto">
<table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
<thead class="bg-gray-50 dark:bg-gray-800">
<tr>
<th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Service</th>
<th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Amount</th>
<th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Notes</th>
<th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Status</th>
<th class="px-4 py-2 text-left text-sm font-semibold text-gray-600 dark:text-gray-300">Actions</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
@forelse($paginatedPayments as $payment)
<tr class="{{ $loop->first ? 'bg-green-50 dark:bg-green-900/30' : '' }}">
<td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
{{ $payment['service_name'] ?? '-' }}
</td>
<td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
₦{{ number_format($payment['amount'], 2) }}
</td>
<td class="px-4 py-2 text-sm text-gray-800 dark:text-gray-200">
{{ $payment['notes'] ?? '-' }}
</td>
<td class="px-4 py-2">
@if(!empty($payment['is_paid']))
<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded">Paid</span>
@else
<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded">Unpaid</span>
@endif
</td>
<td class="px-4 py-2 text-sm flex space-x-2">
@if(empty($payment['is_paid']))
<flux:button
wire:click="editPayment({{ $payment['id'] }})"
size="sm"
class="cursor-pointer"
variant="primary"
icon="pencil"
>
Edit
</flux:button>

<flux:button
wire:click="deletePayment({{ $payment['id'] }})"
size="sm"
class="cursor-pointer"
variant="danger"
icon="trash"
>
Delete
</flux:button>
@else
<span class="text-gray-400 text-xs">Locked</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="5" class="text-center py-3 text-gray-500 dark:text-gray-400">
No managed payments found.
</td>
</tr>
@endforelse
</tbody>
</table>

<!-- Total Summary -->
<div class="text-right mt-3 font-semibold text-gray-700 dark:text-gray-300">
Total: ₦{{ number_format(collect($payments)->sum('amount'), 2) }}
</div>

<div class="mt-4">
      {{ $paginatedPayments->links() }}
</div>

</div>

</div>
@endif
@endif
@endif

<!-- Success Message -->
@if($successMessage)
<div class="p-4 bg-green-50 dark:bg-green-900 text-green-700 dark:text-green-200 rounded">
{{ $successMessage }}
</div>
@endif


<!-- Inline Edit Payment Modal -->
@if($editingPayment)

<!-- Toggle Edit Payment Modal Section -->

<div
class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 dark:bg-black/60 backdrop-blur-sm transition"
>
<div class="bg-white dark:bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-md p-6 space-y-6 border border-zinc-200 dark:border-zinc-700">

<!-- Header -->
<div class="flex justify-between items-center border-b border-zinc-200 dark:border-zinc-700 pb-2">
<h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Edit Payment</h2>
<button wire:click="$set('editingPayment', null)" class="text-zinc-500 hover:text-zinc-800 dark:hover:text-zinc-300 transition">
✕
</button>
</div>

<!-- Form Fields -->
<div class="space-y-4">
<!-- Service -->
<div>

<flux:select
wire:model.live.number="editingPayment.service_id"
label="Service Type"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Service Type -"
>
@foreach($services as $service)
<flux:select.option value="{{ $service['id'] }}">
{{ $service['name'] }}
</flux:select.option>
@endforeach
</flux:select>

</div>

<!-- Amount -->
<div>
<flux:input
wire:model="editingPayment.amount"
type="number"
label="Amount (₦)"
placeholder="Enter amount"
/>
</div>

<!-- Notes -->
<div>
<flux:input
wire:model="editingPayment.notes"
label="Notes"
placeholder="Enter any notes"
/>
</div>

</div>

<!-- Footer -->
<div class="flex justify-end space-x-2 pt-4">

<flux:button
wire:click="$set('editingPayment', null)"
variant="danger"
class="w-full md:w-auto cursor-pointer"
icon="x-circle"
spinner="editingPayment"
>
Discard Changes
</flux:button>


<flux:button
wire:click="updatePayment"
variant="primary"
class="w-full md:w-auto cursor-pointer"
icon="pencil-square"
spinner="updatePayment"
>
Save Changes
</flux:button>


</div>

</div>
</div>
@endif




</div>
