<div class="space-y-6">

{{-- Heading --}}
<flux:heading size="xl" level="1">Users</flux:heading>
<flux:subheading size="lg" class="mb-6">Manage all your Users</flux:subheading>
<flux:separator variant="subtle" />

{{-- Create User Toggle --}}
<div>
@if(!$showCreateForm)
<flux:button wire:click="$set('showCreateForm', true)" variant="primary" icon="folder-plus" class="cursor-pointer">
Create User
</flux:button>
@endif

@if($showCreateForm)
<flux:card class="mt-4">
<form wire:submit.prevent="save" class="grid grid-cols-1 md:grid-cols-2 gap-4">
<flux:input label="Full Name" wire:model="name" placeholder="Enter full name" />
<flux:input type="email" label="Email" wire:model="email" placeholder="Enter email" />

{{-- Multi-role select for Create --}}
<flux:select
label="Roles"
wire:model="selectedRoles"
variant="listbox"
multiple
placeholder="Choose roles..."
class="w-full"
>
@foreach ($allRoles as $role)
<flux:select.option value="{{ $role->name }}">
{{ ucfirst($role->name) }}
</flux:select.option>
@endforeach
</flux:select>
<flux:input type="password" label="Password"  placeholder="Password setup email will be sent!" disabled/>

<div class="md:col-span-2 flex justify-end space-x-2">
<flux:button type="button" variant="ghost" wire:click="$set('showCreateForm', false)" class="cursor-pointer">
Cancel
</flux:button>
<flux:button type="submit" variant="primary" icon="plus" class="cursor-pointer">
Save User
</flux:button>
</div>
</form>
</flux:card>
@endif
</div>

{{-- Users List --}}
<flux:card>
<flux:heading size="lg">Users List</flux:heading>

<div class="mt-4 space-y-2">
@forelse ($users as $user)
<div class="p-3 rounded bg-gray-50 dark:bg-gray-800">
{{-- User Row --}}
<div class="flex items-center justify-between">
<div>
<div class="font-medium">{{ $user->name }}</div>
<div class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
<div class="mt-1 space-x-1">
@foreach ($user->roles as $role)
<flux:badge size="sm">{{ ucfirst($role->name) }}</flux:badge>
@endforeach
</div>
</div>
<div class="flex space-x-2">
<flux:button size="xs" variant="primary" wire:click="edit({{ $user->id }})" icon="pencil-square" class="cursor-pointer">
Edit
</flux:button>
<flux:button size="xs" variant="danger" wire:click="delete({{ $user->id }})" icon="trash" class="cursor-pointer">
Delete
</flux:button>
</div>
</div>

{{-- Inline Edit Form --}}
@if($editingUserId === $user->id)
<div class="mt-4">
<form wire:submit.prevent="update" class="grid grid-cols-1 md:grid-cols-2 gap-4">
<flux:input label="Full Name" wire:model="name" placeholder="Enter full name" />
<flux:input type="email" label="Email" wire:model="email" placeholder="Enter email" />

{{-- Multi-role select for Edit --}}
<flux:select
label="Roles"
wire:model="selectedRoles"
variant="listbox"
multiple
placeholder="Choose roles..."
class="w-full"
>
@foreach ($allRoles as $role)
<flux:select.option value="{{ $role->name }}">
{{ ucfirst($role->name) }}
</flux:select.option>
@endforeach
</flux:select>

<div class="md:col-span-2 flex justify-end space-x-2">
<flux:button type="button" variant="ghost" wire:click="$set('editingUserId', null)">
Cancel
</flux:button>
<flux:button type="submit" variant="primary" icon="check">
Update
</flux:button>
</div>
</form>
</div>
@endif
</div>
@empty
<flux:text>No users found.</flux:text>
@endforelse
</div>

<div class="mt-4">
{{ $users->links() }}
</div>
</flux:card>

</div>
