<div class="space-y-8 p-6">

{{-- Filter --}}
@if ($showFilters)
<div class="grid grid-cols-1 md:grid-cols-5 gap-4">

{{-- Program Type --}}
<flux:select wire:model.live="filters.program_type_id" searchable>
<option value="">-Program Type-</option>
@foreach($programTypes as $pt)
<option value="{{ $pt['id'] }}">{{ $pt['name'] }}</option>
@endforeach
</flux:select>


{{-- Faculty --}}
<flux:select wire:model.live="filters.faculty_id" searchable :disabled="empty($filters['program_type_id'] ?? null)">
<option value="">-Faculty-</option>
@foreach($faculties as $f)
<option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
@endforeach
</flux:select>

{{-- Department --}}
<flux:select wire:model.live="filters.department_id" searchable :disabled="empty($filters['faculty_id'] ?? null)"
>
<option value="">-Department-</option>
@foreach($departments as $d)
<option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
@endforeach
</flux:select>

{{-- Program --}}
<flux:select wire:model.live="filters.program_id" searchable :disabled="empty($filters['department_id'] ?? null)">
<option value="">-Program-</option>
@foreach($programs as $p)
<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
@endforeach
</flux:select>

{{-- Category --}}
<flux:select
{{-- label="Category" --}}
wire:model.defer="filters.category"
>
<option value="">Select category</option>
<option value="NBTE">NBTE</option>
<option value="Others">Others</option>
</flux:select>

</div>

<div>
<flux:button variant="primary" wire:click="loadFilteredCourses" class="cursor-pointer">
Load Courses
</flux:button>


<flux:button
variant="filled"
wire:click="exportCourses"
class="cursor-pointer"
:disabled="empty($courses)"
>
Export CSV
</flux:button>


</div>
@endif
{{-- filter --}}


{{-- Header / Search --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
<div class="flex items-center justify-between gap-2 w-full sm:w-auto">
<flux:input
wire:model.live.500ms="filters.search"
placeholder="Search course code or title…"
autofocus
/>

<flux:button
variant="primary"
class="cursor-pointer"
wire:click="toggleFilters"
>
{{ $showFilters ? 'Hide Filters' : 'Load Filters' }}
</flux:button>

<flux:link href="{{ route('courses.bulk-upload') }}">
Bulk Upload
</flux:link>

</div>

<flux:button
variant="primary"
wire:click="create"
class="bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white border-green-600 hover:border-green-700 dark:border-green-500 dark:hover:border-green-600 cursor-pointer flex items-center gap-2 px-4 py-2.5 transition-colors"
>
+ Add Course
</flux:button>
</div>

{{-- Courses Table --}}
<div class="bg-white dark:bg-neutral-900 rounded-xl border border-neutral-200 dark:border-neutral-800 shadow-sm dark:shadow-neutral-900/50 overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-sm">
<thead class="bg-neutral-50 dark:bg-neutral-800/50 border-b border-neutral-200 dark:border-neutral-700">
<tr>
<th class="px-4 py-3.5 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Code</th>
<th class="px-4 py-3.5 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Title</th>
<th class="px-4 py-3.5 text-center text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Level</th>
<th class="px-4 py-3.5 text-center text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Semester</th>
<th class="px-4 py-3.5 text-center text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Cat/Elective</th>
<th class="px-4 py-3.5 text-center text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Status</th>
<th class="px-4 py-3.5 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Program</th>
<th class="px-4 py-3.5 text-left text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Program Type</th>
<th class="px-4 py-3.5 text-right text-xs font-semibold text-neutral-700 dark:text-neutral-300 uppercase tracking-wider">Action</th>
</tr>
</thead>
<tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
@forelse($courses as $course)
<tr class="hover:bg-neutral-50/50 dark:hover:bg-neutral-800/50 transition-colors">
<td class="px-4 py-3.5 font-medium font-mono text-neutral-900 dark:text-neutral-100">
{{ $course['course_code'] }}
</td>
<td class="px-4 py-3.5">
<div class="text-neutral-900 dark:text-neutral-100">{{ $course['course_title'] }}</div>
<div class="text-xs text-neutral-500 dark:text-neutral-400">{{ $course['credit_hours'] }} credit hours</div>
</td>
<td class="px-4 py-3.5 text-center">
<span class="inline-flex items-center justify-center w-8 h-8 text-sm font-medium bg-neutral-100 dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 rounded-full">
{{ $course['level']['id'] ?? '-' }}
</span>
</td>
<td class="px-4 py-3.5 text-center font-medium text-neutral-700 dark:text-neutral-300">
{{ $course['semester'] }}
</td>
<td class="px-4 py-3.5 text-center text-neutral-700 dark:text-neutral-300">
{{ $course['category'] ?? '-' }} / {{ $course['elective'] ? 'Yes' : 'No' }}
</td>

{{-- In your Blade template --}}
<td class="px-4 py-3.5 text-center">
<flux:badge color="{{ $course['is_active'] ? 'emerald' : 'zinc' }}">
{{ $course['is_active'] ? 'Active' : 'Inactive' }}
</flux:badge>
</td>

<td class="px-4 py-3.5 text-neutral-700 dark:text-neutral-300">
{{ $course['program']['name'] ?? '-' }}
</td>

<td class="px-4 py-3.5">
<span class="inline-flex items-center px-3 py-1 text-xs font-medium bg-green-600 dark:bg-green-500 text-white rounded-full">
{{ $course['program_type']['name'] ?? '-' }}
</span>
</td>

<td class="px-4 py-3.5 text-right">
<div class="flex justify-end gap-2">
<flux:button
size="sm"
wire:click="edit({{ json_encode($course) }})"
class="border-neutral-300 dark:border-neutral-700 hover:border-green-600 dark:hover:border-green-500 hover:text-green-700 dark:hover:text-green-400 cursor-pointer bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300"
>
Edit
</flux:button>

<flux:button
size="sm"
variant="danger"
wire:click="deactivate({{ $course['id'] }})"
class="hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-400 hover:border-red-300 dark:hover:border-red-700 cursor-pointer"
>
Deactivate
</flux:button>
</div>
</td>
</tr>
@empty
<tr>
<td colspan="9" class="px-4 py-12 text-center">
<div class="flex flex-col items-center justify-center">
<svg class="w-12 h-12 mb-4 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
</svg>
<p class="text-lg font-medium text-neutral-600 dark:text-neutral-300">No courses found</p>
<p class="text-sm mt-1 text-neutral-500 dark:text-neutral-400">Try adjusting your search or add a new course</p>
</div>
</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- PAGINATION --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-2">
<div class="text-sm text-neutral-600 dark:text-neutral-400">
Page {{ $pagination['current_page'] ?? 1 }} of {{ $pagination['last_page'] ?? 1 }}
</div>

<div class="flex items-center gap-2">
<flux:button
size="sm"
wire:click="$set('page', {{ ($pagination['current_page'] ?? 1) - 1 }})"
class="border-neutral-300 dark:border-neutral-700 hover:border-green-600 dark:hover:border-green-500 hover:text-green-700 dark:hover:text-green-400 cursor-pointer bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300"
>
Previous
</flux:button>

<flux:button
size="sm"
wire:click="$set('page', {{ ($pagination['current_page'] ?? 1) + 1 }})"
class="border-neutral-300 dark:border-neutral-700 hover:border-green-600 dark:hover:border-green-500 hover:text-green-700 dark:hover:text-green-400 cursor-pointer bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300"
>
Next
</flux:button>
</div>
</div>

{{-- ADD / EDIT COURSE MODAL --}}
<flux:modal wire:model="showModal" class="max-w-3xl">
<div class="p-6 bg-white dark:bg-neutral-900 text-neutral-900 dark:text-neutral-100">
{{-- Title --}}
<div class="mb-6 pb-4 border-b border-neutral-200 dark:border-neutral-700">
<h2 class="text-xl font-semibold">
{{ $isEditing ? 'Edit Course' : 'Add New Course' }}
</h2>
<p class="text-sm text-neutral-600 dark:text-neutral-400 mt-1">
Fill in the course details and academic context.
</p>
</div>

<div class="space-y-6">
{{-- Course Info --}}
<div>
<flux:input
label="Course Title"
wire:model.defer="form.course_title"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
/>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<flux:input
label="Course Code"
wire:model.defer="form.course_code"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
/>

<flux:input
label="Credit Hours"
type="number"
wire:model.defer="form.credit_hours"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
/>

<flux:select
label="Semester"
wire:model.defer="form.semester"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">Select semester</option>
<option value="1">First</option>
<option value="2">Second</option>
</flux:select>

<flux:select
label="Category"
wire:model.defer="form.category"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">Select category</option>
<option value="NBTE">NBTE</option>
<option value="Others">Others</option>
</flux:select>
</div>


{{-- Academic Context --}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<flux:select
label="Program Type"
searchable
wire:model.live="form.program_type_id"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">Select program type</option>
@foreach($programTypes as $pt)
<option value="{{ $pt['id'] }}">{{ $pt['name'] }}</option>
@endforeach
</flux:select>

<flux:select
label="Faculty"
wire:model.live="faculty_id"
:disabled="empty($faculties)"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 disabled:bg-neutral-100 dark:disabled:bg-neutral-900 disabled:text-neutral-500 dark:disabled:text-neutral-600"
>
<option value="">Select faculty</option>
@foreach($faculties as $f)
<option value="{{ $f['id'] }}">{{ $f['name'] }}</option>
@endforeach
</flux:select>

<flux:select
label="Department"
wire:model.live="department_id"
:disabled="empty($departments)"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 disabled:bg-neutral-100 dark:disabled:bg-neutral-900 disabled:text-neutral-500 dark:disabled:text-neutral-600"
>
<option value="">Select department</option>
@foreach($departments as $d)
<option value="{{ $d['id'] }}">{{ $d['name'] }}</option>
@endforeach
</flux:select>

<flux:select
label="Program"
wire:model.defer="form.program_id"
:disabled="empty($programs)"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 disabled:bg-neutral-100 dark:disabled:bg-neutral-900 disabled:text-neutral-500 dark:disabled:text-neutral-600"
>
<option value="">Select program</option>
@foreach($programs as $p)
<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>
@endforeach
</flux:select>
</div>


{{-- Level & Sessions --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<flux:select
label="Level"
wire:model.defer="form.level_id"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">Select level</option>
@foreach($levels as $l)
<option value="{{ $l['id'] }}">{{ $l['name'] }}</option>
@endforeach
</flux:select>

<flux:select
label="Start Session"
wire:model.defer="form.start_session_id"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">Start session</option>
@foreach($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>

<flux:select
label="End Session"
wire:model.defer="form.end_session_id"
class="border-neutral-300 dark:border-neutral-700 focus:border-green-600 dark:focus:border-green-500 focus:ring-green-500/20 dark:focus:ring-green-500/30 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100"
>
<option value="">End session</option>
@foreach($sessions as $s)
<option value="{{ $s['id'] }}">{{ $s['name'] }}</option>
@endforeach
</flux:select>
</div>


{{-- Flags --}}
{{-- <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
 <flux:checkbox wire:model.defer="form.is_gst" label="GST" class="text-neutral-900 dark:text-neutral-100" />
<flux:checkbox wire:model.defer="form.is_ume" label="UME" class="text-neutral-900 dark:text-neutral-100" />
<flux:checkbox wire:model.defer="form.is_de" label="DE" class="text-neutral-900 dark:text-neutral-100" />
<flux:checkbox wire:model.defer="form.is_al" label="AL" class="text-neutral-900 dark:text-neutral-100" />
<flux:checkbox wire:model.defer="form.is_it" label="IT" class="text-neutral-900 dark:text-neutral-100" />
<flux:checkbox wire:model.defer="form.elective" label="Elective" class="text-neutral-900 dark:text-neutral-100" />
</div> --}}

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">

    @php
        $checkboxClass = 'h-4 w-4 rounded border-neutral-300 text-green-600
                          focus:ring-2 focus:ring-green-500
                          dark:border-neutral-600 dark:bg-neutral-800
                          dark:checked:bg-green-600 dark:focus:ring-green-600';
        $labelClass = 'flex items-center gap-2 text-sm
                       text-neutral-900 dark:text-neutral-100';
    @endphp

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.is_gst" class="{{ $checkboxClass }}">
        GNS
    </label>

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.is_ume" class="{{ $checkboxClass }}">
        UME
    </label>

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.is_de" class="{{ $checkboxClass }}">
        DE
    </label>

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.is_al" class="{{ $checkboxClass }}">
        AL
    </label>

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.is_it" class="{{ $checkboxClass }}">
        IT
    </label>

    <label class="{{ $labelClass }}">
        <input type="checkbox" wire:model.defer="form.elective" class="{{ $checkboxClass }}">
        Elective
    </label>

</div>


{{-- Actions --}}
<div class="pt-6 border-t border-neutral-200 dark:border-neutral-700 flex justify-end gap-3">
<flux:button
variant="ghost"
wire:click="$set('showModal', false)"
class="text-neutral-700 dark:text-neutral-300 hover:text-neutral-900 dark:hover:text-neutral-100 border-neutral-300 dark:border-neutral-700 hover:border-neutral-400 dark:hover:border-neutral-600 cursor-pointer px-4 py-2.5"
>
Cancel
</flux:button>

<flux:button
variant="primary"
wire:click="save"
class="bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 text-white border-green-600 hover:border-green-700 dark:border-green-500 dark:hover:border-green-600 cursor-pointer px-5 py-2.5"
>
{{ $isEditing ? 'Update Course' : 'Create Course' }}
</flux:button>
</div>
</div>
</div>
</flux:modal>
</div>
