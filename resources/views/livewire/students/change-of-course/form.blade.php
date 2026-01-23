<div class="max-w-4xl mx-auto space-y-8 p-4 md:p-0">
<!-- Header Section -->
<div class="space-y-2">
<h1 class="text-2xl font-bold text-gray-900 dark:text-white">
Change of Course of Study
</h1>
<p class="text-sm text-gray-600 dark:text-gray-400">
Manage student program transfers and course modifications
</p>
</div>

<!-- Eligibility Banner -->
@include('livewire.students.change-of-course.partials.eligibility-banner')

<!-- Student Summary Card -->
@includeWhen($student,
'livewire.students.change-of-course.partials.student-summary-card'
)

<!-- Change Type Selection -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Change Type
<span class="text-red-500">*</span>
</label>

<select
wire:model.live="changeType"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-blue-500 focus:border-blue-500
focus:outline-none transition-all duration-200"
>
<option value="same_program_type" class="py-2">
Same Program Type
</option>
<option value="inter_program_type" class="py-2">
Inter Program Type
</option>
</select>

<p class="text-xs text-gray-500 dark:text-gray-400 pt-2">
<span class="font-medium">Inter-program type change</span> affects program type,
entry mode, study mode and school division.
</p>
</div>
</div>

<!-- Program Selector -->
@includeWhen($isEligible,
'livewire.students.change-of-course.partials.program-selector'
)

<!-- Confirmation Section -->
@if ($isEligible)
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
<!-- Reason Textarea -->
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Reason for Change (Optional)
</label>
<textarea
wire:model.defer="reason"
rows="4"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-blue-500 focus:border-blue-500
focus:outline-none transition-all duration-200
placeholder:text-gray-400 dark:placeholder:text-gray-500"
placeholder="Provide additional context or explanation for this change..."
></textarea>
<p class="text-xs text-gray-500 dark:text-gray-400">
This information will be recorded in the change history
</p>
</div>

<!-- Action Button -->
<div class="pt-2">
<button
wire:click="submit"
wire:loading.attr="disabled"
wire:target="submit"
class="w-full px-5 py-3 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600
text-white font-medium rounded-lg
focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
transition-all duration-200
disabled:opacity-50 disabled:cursor-not-allowed
flex items-center justify-center space-x-2 cursor-pointer"
>
<span wire:loading.remove class="flex items-center space-x-2">
<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
<span>Apply Change of Course</span>
</span>
<span wire:loading class="flex items-center space-x-2">
<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
<span>Processing...</span>
</span>
</button>

<p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
This action will update the student's academic record
</p>
</div>
</div>
@endif

<!-- Additional Guidance -->
@if (!$isEligible && $student)
<div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
<div class="flex items-start space-x-3">
<svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
</svg>
<div class="text-sm text-blue-800 dark:text-blue-300">
<p class="font-medium">Review Required</p>
<p>Please check the eligibility requirements above to proceed with the course change.</p>
</div>
</div>
</div>
@endif
</div>
