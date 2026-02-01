<div class="max-w-5xl mx-auto px-4 md:px-0 space-y-10">

<!-- STEP INDICATOR -->
<div class="flex items-center justify-between">
@foreach ([1 => 'Student', 2 => 'Matric', 3 => 'Programme', 4 => 'Confirm'] as $i => $label)
<div class="flex items-center gap-2">
<div
class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium
{{ $step >= $i
? 'bg-green-600 text-white'
: 'bg-neutral-200 dark:bg-neutral-700 text-neutral-500' }}"
>
{{ $i }}
</div>
<span class="text-sm {{ $step >= $i ? 'text-neutral-900 dark:text-neutral-100' : 'text-neutral-500' }}">
{{ $label }}
</span>
</div>

@if ($i < 4)
<div class="flex-1 h-px mx-3 bg-neutral-200 dark:bg-neutral-700"></div>
@endif
@endforeach
</div>

<!-- STEP 1 -->
@if ($step === 1)
<div class="space-y-6">
@include('livewire.students.change-of-course.partials.eligibility-banner')

@includeWhen(
$student,
'livewire.students.change-of-course.partials.student-summary-card'
)

<div class="flex justify-end">
<button wire:click="nextStep"
class="cursor-pointer px-5 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700">
Continue
</button>
</div>
</div>
@endif

<!-- STEP 2 -->
@if ($step === 2)
<div class="space-y-6">

@if ($this->studentHasMatric())
@include('livewire.students.change-of-course.partials.matric-handling')
@else
<p class="text-sm text-neutral-600 dark:text-neutral-400">
This student does not have a matric number.
</p>
@endif

<div class="flex justify-between">
<button wire:click="prevStep" class="cursor-pointer text-sm text-neutral-600 hover:underline">
Back
</button>

<button wire:click="nextStep"
class="cursor-pointer px-5 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700">
Continue
</button>
</div>
</div>
@endif

<!-- STEP 3 -->
@if ($step === 3)
<div class="space-y-6">

<div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
<label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
Change Type <span class="text-red-500">*</span>
</label>

<select wire:model.live="changeType"
class="w-full mt-2 rounded-lg border border-neutral-300 dark:border-neutral-600
bg-white dark:bg-neutral-800 px-4 py-2.5">
<option value="same_program_type">Same Program Type</option>
<option value="inter_program_type">Inter Program Type</option>
</select>
</div>

@includeWhen(
$isEligible,
'livewire.students.change-of-course.partials.program-selector'
)

<div class="flex justify-between">
<button wire:click="prevStep" class="cursor-pointer text-sm text-neutral-600 hover:underline">
Back
</button>

<button wire:click="nextStep"
class="cursor-pointer px-5 py-2.5 rounded-lg bg-green-600 text-white hover:bg-green-700">
Review
</button>
</div>
</div>
@endif

<!-- STEP 4 -->
@if ($step === 4)
<div class="space-y-6">

<!-- Action Summary -->
<div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-neutral-50 dark:bg-neutral-900 p-5">
<p class="text-sm text-neutral-700 dark:text-neutral-300">
You are about to apply a
<strong>{{ Str::headline(str_replace('_', ' ', $changeType)) }} </strong>
for this student.
</p>
</div>

<!-- Programme Diff -->
<div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
<h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-4">
Programme Change Summary
</h3>

<div class="divide-y divide-neutral-200 dark:divide-neutral-800">
@foreach ($this->programmeDiff as $label => $values)
<div class="py-3 grid grid-cols-3 gap-4 text-sm">
<div class="text-neutral-600 dark:text-neutral-400">
{{ $label }}
</div>

<div class="text-neutral-500 line-through">
{{ $values['from'] }}
</div>

<div class="text-green-700 dark:text-green-400 font-medium">
{{ $values['to'] }}
</div>
</div>
@endforeach
</div>
</div>

<!-- Matric Action -->
@if ($this->studentHasMatric())
<div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5">
<h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-2">
Matriculation Handling
</h3>

<p class="text-sm text-neutral-600 dark:text-neutral-400">
{{
match ($matricAction) {
'assign_new' => 'A new matric number will be generated.',
'reset'      => 'The existing matric number will be reset.',
'keep'       => 'The current matric number will be retained.',
default      => '—',
}
}}
</p>
</div>
@endif

<!-- Audit Preview -->
<div class="rounded-xl border border-dashed border-neutral-300 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-900 p-5">
<h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100 mb-3">
Audit Information
</h3>

<div class="grid grid-cols-2 gap-4 text-sm text-neutral-600 dark:text-neutral-400">
<div>
<div class="text-xs uppercase tracking-wide">Performed by</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">
{{ auth()->user()->email }}
</div>
</div>

<div>
<div class="text-xs uppercase tracking-wide">Action time</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">
{{ now()->format('d M Y, H:i') }}
</div>
</div>
</div>
</div>

<!-- Reason -->
<div class="space-y-2">
<label class="text-sm font-medium text-neutral-700 dark:text-neutral-300">
Reason (optional)
</label>
<textarea
wire:model.defer="reason"
rows="4"
class="w-full rounded-lg border border-neutral-300 dark:border-neutral-600
bg-white dark:bg-neutral-800 px-4 py-3"
></textarea>
</div>

<!-- Actions -->
<div class="flex justify-between pt-2">
<button wire:click="prevStep" class="cursor-pointer text-sm text-neutral-600 hover:underline">
Back
</button>

<button
wire:click="submit"
wire:loading.attr="disabled"
class="cursor-pointer px-6 py-3 rounded-lg bg-green-600 text-white hover:bg-green-700
disabled:opacity-50"
>
Apply Change of Course
</button>
</div>

</div>
@endif


</div>
