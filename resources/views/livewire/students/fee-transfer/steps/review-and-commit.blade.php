<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm space-y-6 border border-gray-200 dark:border-gray-700">

<div>
<h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Review & Commit</h2>
<p class="text-sm text-gray-600 dark:text-gray-400">Confirm the transfer details before proceeding</p>
</div>

<div class="bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
<div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Transfer Summary</div>
<ul class="space-y-3 text-sm">
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Registration No:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $student['regno'] }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Amount:</span>
<span class="font-medium text-gray-900 dark:text-white">₦{{ number_format($details['amount'], 2) }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Academic Session:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $context['acad_session_name'] }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Semester:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $context['semester'] == 1 ? 'First' : 'Second' }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Level:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $context['level_name'] }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Program Type/Program:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $student['program_type_name'] }} /{{ $student['program'] }}</span>
</li>
<li class="flex justify-between">
<span class="text-gray-600 dark:text-gray-400">Payment Source:</span>
<span class="font-medium text-gray-900 dark:text-white">{{ $context['payment_source_type_name'] }}</span>
</li>
</ul>
</div>

<div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
<div class="flex items-start">
<svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
</svg>
<div class="text-sm text-yellow-800 dark:text-yellow-300">
<strong class="font-medium">Important:</strong> This action unlocks registration for the selected academic session and cannot be undone.
</div>
</div>
</div>

<button
wire:click="commitData"
wire:loading.attr="disabled"
wire:target="commitData"
class="cursor-pointer w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
>
<span wire:loading.remove wire:target="commitData">Confirm Transfer</span>
<span wire:loading wire:target="commitData">Processing...</span>
</button>

</div>
