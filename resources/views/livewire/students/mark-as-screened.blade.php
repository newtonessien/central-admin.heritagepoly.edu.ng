<div class="max-w-xl mx-auto space-y-6">

<!-- Page Header -->
<div>
<h1 class="text-lg font-semibold text-neutral-900 dark:text-neutral-100">
Mark Student as Screened
</h1>
<p class="text-sm text-neutral-600 dark:text-neutral-400">
Use this action for students previously screened but who deferred admission.
</p>
</div>

<!-- Main Card -->
<flux:card class="space-y-6">

<!-- Reg No Input -->
<flux:input
label="Student Registration Number"
wire:model.defer="regno"
placeholder="Enter registration number"
/>

<div class="flex justify-end">
<flux:button
variant="primary"
wire:click="validateStudent"
wire:loading.attr="disabled"
class="cursor-pointer"
icon="check-badge"
>
Validate Student
</flux:button>
</div>

<!-- Student Preview -->
@if($student)
<flux:separator />

<div class="space-y-4">

<!-- Student Identity -->
<div class="rounded-lg bg-neutral-50 dark:bg-neutral-900 p-4 space-y-1">
<div class="font-medium text-neutral-900 dark:text-neutral-100">
{{ $student['name'] ?? '—' }}
</div>
<div class="text-sm text-neutral-600 dark:text-neutral-400">
{{ $student['regno'] ?? '' }}
</div>

<div class="text-sm text-neutral-600 dark:text-neutral-400">
{{ $student['program'] ?? '' }} / {{ $student['program_type'] ?? '' }}
</div>
</div>

<!-- Screening Status -->
<div class="flex items-center justify-between">
<div class="text-sm text-neutral-600 dark:text-neutral-400">
 <flux:badge variant="solid" color="lime">Screening Status</flux:badge>
</div>

@if(($student['is_screen'] ?? false))
<flux:badge variant="solid" color="teal">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
</svg> Screened
</flux:badge>
@else
<flux:badge variant="solid" color="red">
Not Screened
</flux:badge>
@endif
</div>

<!-- Action -->
@if(! ($student['is_screen'] ?? false))
    <flux:separator />
<div class="flex justify-end pt-2">

<flux:button
wire:click="markScreened"
wire:loading.attr="disabled"
variant="primary"
class="cursor-pointer"
icon="shield-check"
>
<span wire:loading.remove>
Mark as Screened
</span>
<span wire:loading>
Processing…
</span>
</flux:button>
</div>
@endif

</div>
@endif

</flux:card>

<!-- Result Message -->
@if($message)

{{ $message }}

@endif

</div>
