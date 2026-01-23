<div class="max-w-xl mx-auto p-6 space-y-8">
<!-- Header Section -->
<div class="space-y-2">
<h1 class="text-2xl font-bold text-gray-900 dark:text-white">Change of Course</h1>
<p class="text-sm text-gray-600 dark:text-gray-400">Enter student registration details to proceed with course modification</p>
</div>

<!-- Form Section -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
<form wire:submit.prevent="fetchStudent" class="space-y-6">
<!-- Input Field -->
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
Registration Number
<span class="text-red-500">*</span>
</label>
<div class="relative">
<input
type="text"
wire:model.defer="regno"
class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg
bg-white dark:bg-gray-700
text-gray-900 dark:text-white
focus:ring-2 focus:ring-green-500 focus:border-green-500
focus:outline-none transition-all duration-200
placeholder:text-gray-400 dark:placeholder:text-gray-500"
placeholder="Enter Registration Number"
aria-label="Student registration number"
/>
@error('regno')
<div class="absolute right-3 top-3">
<svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
</svg>
</div>
@enderror
</div>

<!-- Error Message -->
@error('regno')
<div class="flex items-start space-x-2 mt-1">
<svg class="w-4 h-4 text-red-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
</svg>
<p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
</div>
@enderror
</div>

<!-- Submit Button -->
<div class="pt-2">
<button
type="submit"
class="w-full px-5 py-3 bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600
text-white font-medium rounded-lg
focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2
transition-all duration-200
disabled:opacity-50 disabled:cursor-not-allowed
flex items-center justify-center space-x-2 cursor-pointer"
wire:loading.attr="disabled"
>
<span wire:loading.remove>Verify</span>
<span wire:loading>
<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
</svg>
</span>
</button>
</div>
</form>
</div>

<!-- Optional: Additional Info -->
<div class="text-center text-sm text-gray-500 dark:text-gray-400">
<p>Ensure the registration number matches the institution's format</p>
</div>
</div>
