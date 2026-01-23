<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
<!-- Header -->
<div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
<div class="flex items-center space-x-3">
<div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
<svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
</svg>
</div>
<div>
<h2 class="font-semibold text-gray-900 dark:text-white">Select New Program</h2>
<p class="text-xs text-gray-500 dark:text-gray-400">Choose the destination program for course transfer</p>
</div>
</div>
</div>

<!-- Form Content -->
<div class="p-6 space-y-6">
<!-- Program Type Selection -->
<div class="space-y-4">
@if ($changeType === 'inter_program_type')
<!-- Phase 2: Program Type Selection -->
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
New Program Type
<span class="text-red-500">*</span>
</label>
<div class="relative">
<select
wire:model="toProgramTypeId"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
    bg-white dark:bg-gray-700
    text-gray-900 dark:text-white
    focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
    focus:outline-none transition-all duration-200
    appearance-none"
>
<option value="" class="text-gray-400 dark:text-gray-500 py-2">
-- Select Program Type --
</option>
@foreach ($programTypes as $pt)
<option value="{{ $pt['id'] }}" class="py-2">
    {{ $pt['name'] }}
</option>
@endforeach
</select>
<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
<svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
</svg>
</div>
</div>
<p class="text-xs text-gray-500 dark:text-gray-400">
Inter-program transfer requires selecting a new program type
</p>
</div>
@else
<!-- Phase 1: Program Type Display (Read-only) -->
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Current Program Type
</label>
<div class="relative">
<input
type="text"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
    bg-gray-50 dark:bg-gray-700/50
    text-gray-900 dark:text-gray-300
    cursor-not-allowed"
value="{{ $student['program_type'] ?? 'Not specified' }}"
disabled
/>
<div class="absolute inset-y-0 right-0 flex items-center pr-3">
<svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
</svg>
</div>
</div>
<p class="text-xs text-gray-500 dark:text-gray-400">
Same program type transfer preserves current program classification
</p>
</div>
@endif
</div>

<!-- Faculty Selection -->
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Faculty
<span class="text-red-500">*</span>
</label>
<div class="relative">
<select
wire:model.live="facultyId"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
focus:outline-none transition-all duration-200
appearance-none"
{{ !$isEligible ? 'disabled' : '' }}
>
<option value="" class="text-gray-400 dark:text-gray-500 py-2">
-- Select Faculty --
</option>
@foreach ($faculties as $faculty)
<option value="{{ $faculty['id'] }}" class="py-2">
{{ $faculty['name'] }}
</option>
@endforeach
</select>
<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
<svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
</svg>
</div>
</div>
</div>

<!-- Department Selection (Conditional) -->
@if ($departments)
<div class="space-y-2 transition-all duration-300">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Department
<span class="text-red-500">*</span>
</label>
<div class="relative">
<select
wire:model.live="departmentId"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
focus:outline-none transition-all duration-200
appearance-none"
{{ !$isEligible ? 'disabled' : '' }}
>
<option value="" class="text-gray-400 dark:text-gray-500 py-2">
-- Select Department --
</option>
@foreach ($departments as $department)
<option value="{{ $department['id'] }}" class="py-2">
{{ $department['name'] }}
</option>
@endforeach
</select>
<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
<svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
</svg>
</div>
</div>
<p class="text-xs text-gray-500 dark:text-gray-400">
{{ count($departments) }} department(s) available
</p>
</div>
@endif

<!-- Program Selection (Conditional) -->
@if ($programs)
<div class="space-y-2 transition-all duration-300">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Program
<span class="text-red-500">*</span>
</label>
<div class="relative">
<select
wire:model="programId"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500
focus:outline-none transition-all duration-200
appearance-none"
{{ !$isEligible ? 'disabled' : '' }}
>
<option value="" class="text-gray-400 dark:text-gray-500 py-2">
-- Select Program --
</option>
@foreach ($programs as $program)
<option value="{{ $program['id'] }}" class="py-2">
{{ $program['name'] }}
</option>
@endforeach
</select>
<div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
<svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
</svg>
</div>
</div>
<p class="text-xs text-gray-500 dark:text-gray-400">
{{ count($programs) }} program(s) available in selected department
</p>
</div>
@endif

<!-- Selection Status Indicator -->
@if ($programId)
<div class="mt-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
<div class="flex items-center space-x-3">
<div class="p-1.5 bg-green-100 dark:bg-green-800 rounded-full">
<svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
</svg>
</div>
<div>
<p class="text-sm font-medium text-green-800 dark:text-green-300">Program selection complete</p>
<p class="text-xs text-green-700 dark:text-green-400 mt-1">Ready to proceed with course change application</p>
</div>
</div>
</div>
@elseif ($departmentId && !$programs)
<div class="mt-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
<div class="flex items-center space-x-3">
<div class="p-1.5 bg-yellow-100 dark:bg-yellow-800 rounded-full">
<svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.732 0L4.346 16.5c-.77.833.192 2.5 1.732 2.5z"/>
</svg>
</div>
<div>
<p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">No programs available</p>
<p class="text-xs text-yellow-700 dark:text-yellow-400 mt-1">Selected department doesn't have programs for the chosen criteria</p>
</div>
</div>
</div>
@endif
</div>

<!-- Footer -->
<div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-900/50">
<div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
<div class="flex items-center space-x-2">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
<span>All fields are required unless indicated otherwise</span>
</div>
<span class="hidden sm:inline">Step 2 of 3</span>
</div>
</div>
</div>
