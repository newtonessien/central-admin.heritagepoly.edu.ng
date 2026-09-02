{{-- ========================================================= --}}
{{-- EDIT ENROLLED STUDENT --}}
{{-- ========================================================= --}}

<flux:modal
name="edit-enrolled-student"
class="max-w-4xl"
>

<div class="space-y-6">

{{-- Header --}}
<div>

<flux:heading size="lg">
Edit Enrolled Student
</flux:heading>

<flux:text class="mt-1">
Update the student's academic enrollment details.
</flux:text>

</div>


@if ($editingStudentId)

{{-- ================================================= --}}
{{-- Student Identity --}}
{{-- ================================================= --}}

<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $editingStudent['regno'] ?? '—' }}
</flux:text>

</div>


{{-- ================================================= --}}
{{-- Academic Placement --}}
{{-- ================================================= --}}

<div>

<flux:heading size="sm">
Academic Placement
</flux:heading>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

{{-- Programme Type --}}
<flux:select
wire:model.live="editForm.program_type_id"
label="Programme Type"
placeholder="Select programme type"
:disabled="$processingEdit"
>

@foreach ($programTypes as $type)

<flux:select.option
value="{{ $type['id'] }}"
>
{{ $type['name'] }}
</flux:select.option>

@endforeach

</flux:select>


{{-- Academic Session --}}
<flux:select
wire:model="editForm.acad_session_id"
label="Academic Session"
placeholder="Select academic session"
:disabled="$processingEdit"
>

@foreach ($academicSessions as $session)

<flux:select.option
value="{{ $session['id'] }}"
>
{{ $session['name'] }}
</flux:select.option>

@endforeach

</flux:select>


{{-- Faculty --}}
<flux:select
wire:model.live="editForm.faculty_id"
label="Faculty"
placeholder="Select faculty"
:disabled="$processingEdit"
>

@foreach ($faculties as $faculty)

<flux:select.option
value="{{ $faculty['id'] }}"
>
{{ $faculty['name'] }}
</flux:select.option>

@endforeach

</flux:select>


{{-- Department --}}
<flux:select
wire:model.live="editForm.department_id"
label="Department"
placeholder="Select department"
:disabled="$processingEdit || !$editForm['faculty_id']"
>

@foreach ($departments as $department)

<flux:select.option
value="{{ $department['id'] }}"
>
{{ $department['name'] }}
</flux:select.option>

@endforeach

</flux:select>


{{-- Programme --}}
<div class="md:col-span-2">

<flux:select
wire:model="editForm.program_id"
label="Programme"
placeholder="Select programme"
:disabled="$processingEdit || !$editForm['department_id']"
>

@foreach ($programs as $program)

<flux:select.option
value="{{ $program['id'] }}"
>
{{ $program['name'] }}
</flux:select.option>

@endforeach

</flux:select>

</div>


{{-- Entry Mode --}}
<flux:select
wire:model="editForm.entry_mode_id"
label="Entry Mode"
placeholder="Select entry mode"
:disabled="$processingEdit"
>

@foreach ($entryModes as $mode)

<flux:select.option
value="{{ $mode['value'] ?? $mode['id'] }}"
>
{{ $mode['label'] ?? $mode['name'] }}
</flux:select.option>

@endforeach

</flux:select>


{{-- Study Mode --}}
<flux:select
wire:model="editForm.study_mode_id"
label="Study Mode"
placeholder="Select study mode"
:disabled="$processingEdit"
>

<flux:select.option value="1">
Full-Time
</flux:select.option>

<flux:select.option value="2">
Part-Time
</flux:select.option>

</flux:select>

</div>

</div>


{{-- ================================================= --}}
{{-- JAMB - UG ONLY --}}
{{-- ================================================= --}}

@if ((int) ($editForm['program_type_id'] ?? 0) === 1)

<div>

<flux:heading size="sm">
JAMB Information
</flux:heading>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">

<flux:input
wire:model="editForm.jamb_no"
label="JAMB No."
placeholder="JAMB registration number"
autocomplete="off"
maxlength="50"
:disabled="$processingEdit"
/>

<flux:input
wire:model="editForm.jamb_score"
label="JAMB Score"
type="number"
placeholder="0 – 400"
min="0"
max="400"
:disabled="$processingEdit"
/>

</div>

</div>

@endif


{{-- ================================================= --}}
{{-- Actions --}}
{{-- ================================================= --}}

<div class="flex justify-end gap-3 border-t pt-5">

<flux:button
type="button"
variant="ghost"
wire:click="closeEdit"
:disabled="$processingEdit"
>
Cancel
</flux:button>

<flux:button
type="button"
variant="primary"
icon="check"
class="cursor-pointer"
wire:click="updateStudent"
:disabled="$processingEdit"
>

@if ($processingEdit)
Saving...
@else
Save Changes
@endif
</flux:button>

</div>

@endif

</div>

</flux:modal>
