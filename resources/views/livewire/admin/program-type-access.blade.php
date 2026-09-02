<div class="space-y-6">

{{-- Header --}}
<div class="flex items-center justify-between">

<div>
<flux:heading size="xl">
Programme Type Access
</flux:heading>

<flux:text class="mt-1">
Assign admission officers to the programme types they are responsible for.
</flux:text>
</div>

</div>


{{-- Admission Officers --}}
<div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">

<div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">

<flux:heading size="lg">
Admission Officers
</flux:heading>

<flux:text class="mt-1">
Select an admission officer to manage their programme type access.
</flux:text>

</div>


<div class="divide-y divide-zinc-200 dark:divide-zinc-700">

@forelse($users as $user)

@php
$assignedCount = \App\Models\UserProgramType::query()
->where('user_id', $user['id'])
->count();
@endphp

<div class="flex items-center justify-between px-6 py-4">

<div class="min-w-0">

<div class="font-medium text-zinc-900 dark:text-white">
{{ $user['name'] }}
</div>

<div class="text-sm text-zinc-500">
{{ $user['email'] }}
</div>

@if(!empty($user['program_type_names']))

<div class="mt-2 flex flex-wrap gap-1.5">

@foreach($user['program_type_names'] as $programTypeName)

<flux:badge
size="sm"
color="lime"
>
{{ $programTypeName }}
</flux:badge>

@endforeach

</div>

@else

<div class="mt-2 text-xs text-zinc-500">
No programme type assigned
</div>

@endif

</div>


<div class="flex items-center gap-4">

@if($assignedCount > 0)

<flux:badge color="teal" size="sm">
{{ $assignedCount }}
{{ Str::plural('ProgramType(s)', $assignedCount) }}
</flux:badge>

@else

<flux:badge color="zinc">
No Access Assigned
</flux:badge>

@endif


<div class="flex shrink-0 items-center">

<flux:button
wire:click="selectUser({{ $user['id'] }})"
variant="primary"
color="green"
size="sm"
class="cursor-pointer"
icon="adjustments-horizontal"
>
Manage Access
</flux:button>

</div>

</div>

</div>

@empty

<div class="px-6 py-12 text-center">

<flux:icon name="users" class="mx-auto size-10 text-zinc-400" />

<flux:heading size="sm" class="mt-3">
No admission officers found
</flux:heading>

<flux:text class="mt-1">
Create an admission officer before assigning programme type access.
</flux:text>

</div>

@endforelse

</div>

</div>


{{-- Programme Type Access Modal --}}
<flux:modal
name="program-type-access"
class="md:w-[700px]"
>

<div class="space-y-6">

<div>

<flux:heading size="lg">
Programme Type Access
</flux:heading>

@if($this->selectedUser)

<flux:text class="mt-1">
{{ $this->selectedUser->name }}
</flux:text>

<flux:text class="text-sm">
Select the programme types this admission officer can process.
</flux:text>

@endif

</div>


{{-- Programme Types --}}
<div class="grid grid-cols-2 gap-3">

@forelse($programTypes as $programType)

<label
wire:key="program-type-{{ $programType['id'] }}"
class="flex cursor-pointer items-center gap-3 rounded-lg border border-zinc-200 p-3 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
>

{{-- <flux:checkbox
wire:model="selectedProgramTypeIds"
value="{{ (string) $programType['id'] }}"
/> --}}

<input
    type="checkbox"
    wire:model="selectedProgramTypeIds"
    value="{{ (string) $programType['id'] }}"
>

<div class="flex-1">

<div class="font-medium text-zinc-900 dark:text-white">
{{ $programType['name'] }}
</div>

</div>

</label>

@empty

<div class="rounded-lg border border-dashed border-zinc-300 p-6 text-center dark:border-zinc-700">

<flux:text>
No programme types available.
</flux:text>

</div>

@endforelse

</div>


{{-- Actions --}}
<div class="flex justify-end gap-2">

<flux:modal.close>

<flux:button variant="ghost">
Cancel
</flux:button>

</flux:modal.close>


<flux:button
wire:click="save"
variant="primary"
icon="check"
class="cursor-pointer"
wire:loading.attr="disabled"
>

<span wire:loading.remove wire:target="save">
Save Access
</span>

<span wire:loading wire:target="save">
Saving...
</span>

</flux:button>

</div>

</div>

</flux:modal>

</div>
