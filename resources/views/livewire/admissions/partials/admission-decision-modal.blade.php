<flux:modal
wire:model="showDecisionModal"
class="w-full max-w-xl"
>
@if ($processingCandidate)

<div class="space-y-6">

{{-- Header --}}
<div>

<flux:heading size="lg">
Admission Decision
</flux:heading>

<flux:text class="mt-1">
Process the admission decision for this candidate.
</flux:text>

</div>


{{-- Candidate --}}
<div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">

<div class="font-semibold text-zinc-900 dark:text-white">
{{ $processingCandidate['name'] ?? '—' }}
</div>

<div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-sm text-zinc-500">

<span>
{{ $processingCandidate['regno'] ?? '—' }}
</span>

@if (!empty($processingCandidate['application_type']))
<span>
{{ $processingCandidate['application_type'] }}
</span>
@endif

</div>

</div>


{{-- Academic information --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

<div>
<flux:text size="sm" class="text-zinc-500">
Course of Study
</flux:text>

<div class="mt-1 font-medium">
{{ $processingCandidate['program'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Programme Type
</flux:text>

<div class="mt-1 font-medium">
{{ $processingCandidate['program_type'] ?? '—' }}
</div>
</div>

</div>


{{-- Decision --}}
<flux:field>

<flux:label>
Admission Decision
<span class="text-red-500">*</span>
</flux:label>

<flux:radio.group
wire:model.live="admissionDecision"
>

<flux:radio
value="admit"
label="Admit"
description="Approve the candidate for admission."
/>

<flux:radio
value="reject"
label="Reject"
description="Reject the candidate from admission."
/>

</flux:radio.group>

@error('admissionDecision')
<flux:error>
{{ $message }}
</flux:error>
@enderror

</flux:field>


{{-- Reason --}}
@if ($admissionDecision === 'reject')
<flux:field>

<flux:label>
Decision Reason
<span class="text-red-500">*</span>
</flux:label>

<flux:textarea
wire:model="decisionReason"
rows="4"
placeholder="Enter the reason for this decision..."
/>

<flux:description>
A reason is required when rejecting a candidate.
</flux:description>


@error('decisionReason')
<flux:error>
{{ $message }}
</flux:error>
@enderror

</flux:field>
@endif

{{-- Warning for Admit --}}
@if ($admissionDecision === 'admit')

<div class="rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-900 dark:bg-green-950/30">

<div class="flex gap-3">

<flux:icon
name="information-circle"
class="mt-0.5 size-5 text-green-600"
/>

<div>

<div class="font-medium text-green-800 dark:text-green-300">
Confirm admission
</div>

<div class="mt-1 text-sm text-green-700 dark:text-green-400">
Confirming admission will approve this candidate
and commit the admission to the Student Portal.
</div>

</div>

</div>

</div>

@elseif ($admissionDecision === 'reject')

<div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950/30">

<div class="flex gap-3">

<flux:icon
name="exclamation-triangle"
class="mt-0.5 size-5 text-red-600"
/>

<div>

<div class="font-medium text-red-800 dark:text-red-300">
Confirm rejection
</div>

<div class="mt-1 text-sm text-red-700 dark:text-red-400">
This candidate will be marked as rejected.
</div>

</div>

</div>

</div>

@endif


{{-- Actions --}}
<div class="flex justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">

<flux:button
variant="ghost"
wire:click="closeDecisionModal"
:disabled="$processingDecision"
>
Cancel
</flux:button>

<flux:button
variant="primary"
wire:click="confirmAdmissionDecision"
wire:loading.attr="disabled"
icon="check-badge"
class="cursor-pointer"
:disabled="!$admissionDecision || $processingDecision"
>

<span
wire:loading.remove
wire:target="confirmAdmissionDecision"
class="cursor-pointer"
>
Confirm Decision
</span>

<span
wire:loading.flex
wire:target="confirmAdmissionDecision"
class="items-center gap-2"
>
Processing...
</span>


</flux:button>

</div>

</div>

@endif
</flux:modal>
