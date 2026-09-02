{{-- ========================================================= --}}
{{-- CONFIRMATION MODAL --}}
{{-- ========================================================= --}}

<flux:modal
name="exceptional-enrollment-confirmation"
class="max-w-2xl"
wire:model="showConfirmation"
>

<div class="space-y-6">

<div>

<flux:heading size="lg">
Confirm Exceptional Enrollment
</flux:heading>

<flux:text class="mt-1">
Review the student's information before creating the enrollment.
</flux:text>

</div>


@if ($showConfirmation)

<div class="rounded-lg border p-5">

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

<div>

<flux:text size="sm" class="text-zinc-500">
Student
</flux:text>

<flux:text class="mt-1 font-medium">
{{ trim(
($form['first_name'] ?? '') . ' ' .
($form['last_name'] ?? '') . ' ' .
($form['other_names'] ?? '')
) }}
</flux:text>

</div>


<div>

<flux:text size="sm" class="text-zinc-500">
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $form['regno'] }}
</flux:text>

</div>


<div>

<flux:text size="sm" class="text-zinc-500">
Programme
</flux:text>

<flux:text class="mt-1 font-medium">

{{
collect($programs)
->firstWhere('id', (int) $form['program_id'])['name']
?? '—'
}}

</flux:text>

</div>


<div>

<flux:text size="sm" class="text-zinc-500">
Academic Session
</flux:text>

<flux:text class="mt-1 font-medium">

{{
collect($academicSessions)
->firstWhere('id', (int) $form['acad_session_id'])['name']
?? '—'
}}

</flux:text>

</div>

</div>

</div>


<div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-900 dark:bg-amber-950">

<div class="flex items-start gap-3">

<flux:icon
name="exclamation-triangle"
class="mt-0.5 text-amber-600 dark:text-amber-400"
/>

<flux:text class="text-amber-800 dark:text-amber-200">

This is an exceptional enrollment.
The registration number was screened against
the Admissions Portal before this enrollment was allowed.

</flux:text>

</div>

</div>

@endif


<div class="flex justify-end gap-3">

<flux:button
type="button"
variant="ghost"
wire:click="$set('showConfirmation', false)"
:disabled="$processing"
>
Cancel
</flux:button>


<flux:button
type="button"
variant="primary"
wire:click="enroll"
icon="check"
class="cursor-pointer"
:disabled="$processing"
>

@if ($processing)
Enrolling...
@else
Confirm Enrollment
@endif

</flux:button>

</div>

</div>

</flux:modal>
