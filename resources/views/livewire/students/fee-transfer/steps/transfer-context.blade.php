<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm space-y-6 border border-gray-200 dark:border-gray-700 transition-colors duration-150">

<div>
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Transfer Context</h2>
<p class="text-sm text-gray-600 dark:text-gray-400">Configure the academic context for this transfer</p>
</div>

<!-- Programme Details Card -->
<div class="rounded-lg border border-gray-200 dark:border-gray-700 p-4 bg-gray-50 dark:bg-gray-900/50">
<div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Programme Details</div>
<div class="text-base text-gray-900 dark:text-white">
{{ $student['program_type_name'] ?? '—' }} / {{ $student['program'] ?? '—' }}
</div>
</div>

<!-- Form Fields -->
<div class="space-y-4">
<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
Academic Session
</label>
<select
wire:model="acad_session_id"
class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500 transition-colors"
>
<option value="" class="text-gray-500">Select Academic Session</option>
@foreach ($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] ?? $s['label'] }}</option>
@endforeach
</select>
</div>

<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
Semester
</label>
<select
wire:model="semester"
class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500 transition-colors"
>
<option value="" class="text-gray-500">Select Semester</option>
<option value="1">First Semester</option>
<option value="2">Second Semester</option>
<option value="3">Full Session</option>
</select>
</div>

<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
Level
</label>
<select
wire:model="level_id"
class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500 transition-colors"
>
<option value="" class="text-gray-500">Select Level</option>
@foreach ($levels as $l)
<option value="{{ $l['id'] }}">{{ $l['name'] }}</option>
@endforeach
</select>
</div>

<div>
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
Payment Source
</label>
<select
wire:model="payment_source_type_id"
class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500 transition-colors"
>
<option value="" class="text-gray-500">Select Payment Source</option>
@foreach ($paymentSources as $ps)
<option value="{{ $ps['id'] }}">{{ $ps['name'] }}</option>
@endforeach
</select>
</div>
</div>

<!-- Error Message -->
@error('*')
<div class="p-3 rounded-md bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
</div>
@enderror

<!-- Action Button -->
<div class="pt-2">
<button
wire:click="proceed"
wire:loading.attr="disabled"
class="cursor-pointer w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
>
<span wire:loading.remove>Continue</span>
<span wire:loading>Processing...</span>
</button>
</div>

</div>
