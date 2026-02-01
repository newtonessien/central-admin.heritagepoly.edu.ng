<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
<!-- Card Header -->
<div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
<div class="flex items-center justify-between">
<div class="flex items-center space-x-3">
<div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
<svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
</svg>
</div>
<div>
<h2 class="font-semibold text-gray-900 dark:text-white">Student Summary</h2>
<p class="text-xs text-gray-500 dark:text-gray-400">Current academic information</p>
</div>
</div>
<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300">
Active
</span>
</div>
</div>

<!-- Student Information Grid -->
<div class="p-6">
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
<!-- Personal Information -->
<div class="space-y-4">
<div>
<h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 flex items-center">
<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
</svg>
Personal Details
</h3>
<div class="space-y-3">
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Full Name</p>
<p class="text-sm font-medium text-gray-900 dark:text-white">
{{ $student['name'] ?? 'Not available' }}
</p>
</div>
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Registration Number</p>
<p class="text-sm font-medium text-gray-900 dark:text-white font-mono">
{{ $student['matric_no'] ?? $student['regno'] }}
</p>
</div>
</div>
</div>
</div>

<!-- Academic Information -->
<div class="space-y-4">
<div>
<h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-3 flex items-center">
<svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
</svg>
Academic Information
</h3>
<div class="space-y-3">
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Program Type</p>
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
{{ $student['program_type'] ?? 'Not specified' }}
</span>
</div>
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Faculty</p>
<p class="text-sm text-gray-900 dark:text-white">
{{ $student['faculty'] ?? 'Not available' }}
</p>
</div>
</div>
</div>
</div>
</div>

<!-- Divider -->
<div class="my-6 border-t border-gray-200 dark:border-gray-700"></div>

<!-- Department & Program Details -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
<div class="space-y-3">
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Department</p>
<div class="flex items-center space-x-2">
<div class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg">
<svg class="w-4 h-4 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
</svg>
</div>
<p class="text-sm text-gray-900 dark:text-white">
{{ $student['department'] ?? 'Not available' }}
</p>
</div>
</div>
</div>

<div class="space-y-3">
<div>
<p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Program</p>
<div class="flex items-center space-x-2">
<div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
</svg>
</div>
<p class="text-sm font-medium text-gray-900 dark:text-white">
{{ $student['program'] ?? 'Not available' }}
</p>
</div>
</div>
</div>
</div>
</div>

<!-- Footer -->
<div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-900/50">
<div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
<div class="flex items-center space-x-1">
<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
</svg>
<span>Last updated: Just now</span>
</div>
<span>ID: {{ $student['matric_no'] ?? $student['regno'] }}</span>
</div>
</div>
</div>
