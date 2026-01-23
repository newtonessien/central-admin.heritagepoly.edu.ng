<div class="space-y-8">

<div class="border-b pb-4 dark:border-gray-700">
<flux:heading size="lg" class="text-gray-800 dark:text-gray-200">Bulk Upload Courses</flux:heading>
<div class="mt-2 flex items-center gap-2">
@for($i = 1; $i <= 4; $i++)
<div class="flex items-center">
<div class="flex h-8 w-8 items-center justify-center rounded-full border-2
{{ $step >= $i ? 'border-primary-600 bg-primary-600 text-white dark:border-primary-500 dark:bg-primary-500' : 'border-gray-300 text-gray-400 dark:border-gray-600 dark:text-gray-500' }}">
{{ $i }}
</div>
@if($i < 4)
<div class="h-0.5 w-6 {{ $step > $i ? 'bg-primary-600 dark:bg-primary-500' : 'bg-gray-300 dark:bg-gray-600' }}"></div>
@endif
</div>
@endfor

</div>
<div class="mt-1 flex text-xs text-gray-500 dark:text-gray-400">
<span class="w-8 text-center">Context</span>
<span class="ml-6 w-8 text-center">Upload</span>
<span class="ml-6 w-8 text-center">Preview</span>
<span class="ml-6 w-8 text-center">Summary</span>
</div>
</div>

{{-- STEP 1: CONTEXT --}}
@if ($step === 1)
<div class="space-y-6">
<div>
<flux:heading size="sm" class="text-gray-700 dark:text-gray-300 mb-4">Step 1: Select Context</flux:heading>
<p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Select the program and session details for the courses you're uploading.
<flux:link
href="{{ route('courses.bulk.template') }}"
target="_blank">
Download Excel Template
</flux:link>
</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program Type</label>
<flux:select wire:model.live="program_type_id" searchable class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select Program Type</option>
@foreach($programTypes as $pt)
<option value="{{ $pt['id'] }}">{{ $pt['name'] }}</option>
@endforeach
</flux:select>
</div>

<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Faculty</label>
<flux:select wire:model.live="faculty_id" searchable :disabled="!$program_type_id" class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select Faculty</option>
@foreach($faculties as $f)
<option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
@endforeach
</flux:select>
</div>

<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Department</label>
<flux:select wire:model.live="department_id" searchable :disabled="!$faculty_id" class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select Department</option>
@foreach($departments as $d)
<option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
@endforeach
</flux:select>
</div>

<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Program</label>
<flux:select wire:model="program_id" searchable :disabled="!$department_id" class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select Program</option>
@foreach($programs as $p)
<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
@endforeach
</flux:select>
</div>

<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Session</label>
<flux:select wire:model="start_session_id" class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select Start Session</option>
@foreach($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>
</div>

<div class="space-y-2">
<label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Session</label>
<flux:select wire:model="end_session_id" class="w-full dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200">
<option value="">Select End Session</option>
@foreach($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>
</div>
</div>

<div class="pt-4 border-t dark:border-gray-700">
<flux:button variant="primary" wire:click="goToUploadStep" class="cursor-pointer">
Continue to Upload
</flux:button>
</div>
</div>
@endif

{{-- STEP 2: UPLOAD --}}
@if ($step === 2)
<div class="space-y-6">
<div>
<flux:heading size="sm" class="text-gray-700 dark:text-gray-300 mb-4">Step 2: Upload Excel File</flux:heading>
<p class="text-sm text-gray-600 dark:text-gray-400 mb-6">Upload your Excel or CSV file containing course data. <flux:link
  href="{{ route('courses.bulk.template') }}"
 target="_blank">
 Download Excel Template
</flux:link>
</p>
</div>

<div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-8 text-center hover:border-primary-500 dark:hover:border-primary-400 transition-colors bg-white dark:bg-gray-800">
<div class="max-w-md mx-auto">
<svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
</svg>
<div class="mt-4">
<flux:input
type="file"
wire:model="file"
accept=".xlsx,.csv"
class="block w-full text-sm text-gray-500 dark:text-gray-400
file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
file:text-sm file:font-semibold file:bg-primary-50 file:text-primary-700
dark:file:bg-primary-900/30 dark:file:text-primary-300
hover:file:bg-primary-100 dark:hover:file:bg-primary-900/50"
/>
</div>
<p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Supports .xlsx and .csv files</p>
</div>
</div>

<div class="flex gap-3 pt-4">
<flux:button variant="ghost" wire:click="$set('step', 1)" class="cursor-pointer">
Back
</flux:button>
<flux:button variant="primary" wire:click="parseFile" class="cursor-pointer">
Preview Upload
</flux:button>
</div>
</div>
@endif

{{-- STEP 3: PREVIEW --}}
@if ($step === 3)
<div class="space-y-6">
<div>
<flux:heading size="sm" class="text-gray-700 dark:text-gray-300 mb-2">Step 3: Preview Upload</flux:heading>
<p class="text-sm text-gray-600 dark:text-gray-400">{{ count($rows) }} rows detected. Please review before importing.</p>
</div>

{{-- Errors --}}
@if (!empty($errors))
<div class="border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20 p-4 rounded dark:border-red-400">
<div class="flex">
<svg class="w-5 h-5 text-red-400 dark:text-red-300 mr-3" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
</svg>
<div class="space-y-2">
<h4 class="text-sm font-semibold text-red-800 dark:text-red-200">Validation Errors</h4>
@foreach($errors as $err)
@if(is_array($err))
<div class="text-sm text-red-700 dark:text-red-300">
<span class="font-medium">Row {{ $err['row'] }}:</span>
{{ implode(', ', $err['errors']) }}
</div>
@else
<div class="text-sm text-red-700 dark:text-red-300">{{ $err }}</div>
@endif
@endforeach
</div>
</div>
</div>
@endif

{{-- Preview Table --}}
<div class="border rounded-lg overflow-hidden dark:border-gray-700 dark:bg-gray-800">
<div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b dark:border-gray-700">
<div class="flex items-center justify-between">
<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Course Preview</span>
<span class="text-xs text-gray-500 dark:text-gray-400">{{ count($rows) }} total rows</span>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-100 dark:bg-gray-900">
<tr>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">#</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Code</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Title</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sem</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
@foreach($rows as $r)
<tr class="{{ $r['valid'] ? 'hover:bg-gray-50 dark:hover:bg-gray-800/50' : 'bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30' }}">
<td class="px-4 py-3 text-sm {{ $r['valid'] ? 'text-gray-900 dark:text-gray-200' : 'text-red-700 dark:text-red-300' }}">{{ $r['row'] }}</td>
<td class="px-4 py-3 text-sm {{ $r['valid'] ? 'text-gray-900 dark:text-gray-200' : 'text-red-700 dark:text-red-300' }}">{{ $r['data']['course_code'] }}</td>
<td class="px-4 py-3 text-sm {{ $r['valid'] ? 'text-gray-900 dark:text-gray-200' : 'text-red-700 dark:text-red-300' }}">{{ $r['data']['course_title'] }}</td>
<td class="px-4 py-3 text-sm {{ $r['valid'] ? 'text-gray-900 dark:text-gray-200' : 'text-red-700 dark:text-red-300' }}">{{ $r['data']['level_id'] }}</td>
<td class="px-4 py-3 text-sm {{ $r['valid'] ? 'text-gray-900 dark:text-gray-200' : 'text-red-700 dark:text-red-300' }}">{{ $r['data']['semester'] }}</td>
<td class="px-4 py-3">
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $r['valid'] ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' }}">
@if($r['valid'])
<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
</svg>
OK
@else
<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
</svg>
Error
@endif
</span>
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>

<div class="flex gap-3 pt-4">
<flux:button variant="ghost" wire:click="$set('step', 2)" class="cursor-pointer">
Back
</flux:button>
<flux:button
variant="primary"
wire:click="importCourses"
:disabled="collect($rows)->where('valid', true)->isEmpty()"
class="cursor-pointer"
>
Confirm Import ({{ collect($rows)->where('valid', true)->count() }} valid rows)
</flux:button>
</div>
</div>
@endif

{{-- STEP 4: SUMMARY --}}
@if ($step === 4)
<div class="space-y-6">
<div>
<flux:heading size="sm" class="text-gray-700 dark:text-gray-300 mb-4">Import Summary</flux:heading>
<p class="text-sm text-gray-600 dark:text-gray-400">Import process completed. Below is the summary of the operation.</p>
</div>

<div class="border rounded-lg overflow-hidden dark:border-gray-700 dark:bg-gray-800">
<div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 border-b dark:border-gray-700">
<div class="flex items-center justify-between">
<span class="text-sm font-medium text-gray-700 dark:text-gray-300">Import Results</span>
<span class="text-xs text-gray-500 dark:text-gray-400">
{{ collect($rows)->where('import_status', 'success')->count() }} successful,
{{ collect($rows)->where('import_status', 'error')->count() }} failed
</span>
</div>
</div>
<div class="overflow-x-auto">
<table class="w-full">
<thead class="bg-gray-100 dark:bg-gray-900">
<tr>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Row</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Course Code</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
<th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
</tr>
</thead>
<tbody class="divide-y divide-gray-200 dark:divide-gray-700">
@foreach($rows as $r)
<tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
<td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $r['row'] }}</td>
<td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-200">{{ $r['data']['course_code'] }}</td>
<td class="px-4 py-3">
@if(($r['import_status'] ?? '') === 'error')
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
</svg>
Error
</span>
@else
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
<svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
</svg>
Success
</span>
@endif
</td>
<td class="px-4 py-3 text-sm {{ ($r['import_status'] ?? '') === 'error' ? 'text-red-600 dark:text-red-300' : 'text-green-600 dark:text-green-300' }}">
{{ $r['import_message'] ?? '-' }}
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
</div>

<div class="flex gap-3 pt-4">
<flux:button as="a" href="{{ route('courses.manage-courses') }}" variant="primary" class="cursor-pointer">
Back to Courses
</flux:button>
<flux:button variant="filled" wire:click="$refresh" class="cursor-pointer">
Upload Another File
</flux:button>
</div>
</div>
@endif

</div>
