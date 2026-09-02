
{{-- ========================================================= --}}
{{-- VIEW STUDENT --}}
{{-- ========================================================= --}}

<flux:modal
name="enrolled-student-view"
class="max-w-3xl"
wire:model="showViewModal"
>

@if ($viewingStudent)

<div class="space-y-6">

<div class="flex items-start justify-between gap-4">

<div>

<flux:heading size="lg">
Student Details
</flux:heading>

<flux:text class="mt-1">
{{ $viewingStudent['regno'] ?? '—' }}
</flux:text>

</div>

</div>


{{-- Student --}}
<div class="rounded-lg border p-5">

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

<div>
<flux:text size="sm" class="text-zinc-500">
Student Name
</flux:text>

<flux:text class="mt-1 font-medium">
{{ trim(
($viewingStudent['first_name'] ?? '') . ' ' .
($viewingStudent['last_name'] ?? '') . ' ' .
($viewingStudent['other_names'] ?? '')
) ?: '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['regno'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Phone Number
</flux:text>

<flux:text class="mt-1">
{{ $viewingStudent['phone_no'] ?? '—' }}
</flux:text>
</div>


<div>
<flux:text size="sm" class="text-zinc-500">
Gender
</flux:text>

<flux:text class="mt-1">
{{ $viewingStudent['gender'] ?? '—' }}
</flux:text>
</div>

</div>

</div>


{{-- Academic --}}
<div>

<flux:heading size="sm">
Academic Placement
</flux:heading>

<div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">

<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Programme Type
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['program_type_name'] ?? '—' }}
</flux:text>

</div>


<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Academic Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['acad_session_name'] ?? '—' }}
</flux:text>

</div>


<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Faculty
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['faculty_name'] ?? '—' }}
</flux:text>

</div>


<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Department
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['department_name'] ?? '—' }}
</flux:text>

</div>


<div class="rounded-lg border p-4 sm:col-span-2">

<flux:text size="sm" class="text-zinc-500">
Programme
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $viewingStudent['program_name'] ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- Screening --}}
<div class="rounded-lg border p-4">

<flux:text size="sm" class="text-zinc-500">
Screening Code
</flux:text>

<flux:text class="mt-1 font-mono font-medium">
{{ $viewingStudent['screening_code'] ?? '—' }}
</flux:text>

</div>


<div class="flex justify-end">

<flux:button
type="button"
variant="ghost"
wire:click="closeViewModal"
>
Close
</flux:button>

</div>

</div>

@endif

</flux:modal>
