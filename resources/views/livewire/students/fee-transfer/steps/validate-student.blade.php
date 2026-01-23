<div class="bg-white dark:bg-neutral-900 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-800 p-6 space-y-8">

<!-- Page Header -->
<div>
<h1 class="text-2xl font-semibold text-neutral-900 dark:text-neutral-100">
Fee Transfer
</h1>
<p class="mt-1 text-sm text-neutral-600 dark:text-neutral-400">
Validate student before initiating fee transfer.
</p>
</div>

<!-- Section -->
<div class="space-y-6">

<div class="flex items-center gap-3">
<flux:icon.check-circle class="w-6 h-6 text-green-600 dark:text-green-400" />
<h2 class="text-lg font-medium text-neutral-900 dark:text-neutral-100">
Validate Student
</h2>
</div>

<flux:input
wire:model.defer="regno"
label="Registration Number"
placeholder="Enter student registration number"
required
/>


<flux:button
wire:click="validateStudent"
wire:loading.attr="disabled"
wire:target="validateStudent"
variant="primary"
icon="check"
class="cursor-pointer"
>
<span wire:loading.remove wire:target="validateStudent">
Validate Student
</span>
<span wire:loading wire:target="validateStudent">
Validating…
</span>
</flux:button>

</div>

</div>
