<flux:card>
<flux:heading size="lg">Intra-University Transfer Requests</flux:heading>

<flux:table class="mt-4">

{{-- COLUMNS --}}
<flux:table.columns>
<flux:table.column>Reg No</flux:table.column>
<flux:table.column>Student</flux:table.column>
<flux:table.column>From → To Programme</flux:table.column>
<flux:table.column>Session</flux:table.column>
<flux:table.column>Action</flux:table.column>
</flux:table.columns>

{{-- ROWS --}}
<flux:table.rows>
@forelse($requests as $req)
<flux:table.row>
<flux:table.cell>
{{ $req['regno'] }}
</flux:table.cell>

<flux:table.cell>
{{ ucwords(strtolower($req['student_name'])) }}
</flux:table.cell>

<flux:table.cell>
<div class="text-sm">
{{ $req['from_program'] }}
<span class="mx-1 text-neutral-400">→</span>
<strong>{{ $req['to_program'] }}</strong>
</div>
</flux:table.cell>

<flux:table.cell>
{{ $req['session'] }}
</flux:table.cell>

<flux:table.cell>
<flux:button
size="sm"
class="cursor-pointer"
wire:click="openApproveModal({{ $req['id'] }})"
>
Review
</flux:button>
</flux:table.cell>
</flux:table.row>
@empty
<flux:table.row>
<flux:table.cell colspan="5">
<flux:callout variant="info">
No pending transfer requests.
</flux:callout>
</flux:table.cell>
</flux:table.row>
@endforelse
</flux:table.rows>

</flux:table>




<flux:modal wire:model="showApproveModal">
<flux:heading size="md" class="border-b border-neutral-200 dark:border-neutral-700 pb-3 mb-4">
Approve Transfer Request
</flux:heading>

@if($selectedRequest)
<div class="space-y-6">
<!-- Transfer Direction Card -->
<div class="bg-neutral-50 dark:bg-neutral-800/50 rounded-lg p-4 border border-neutral-200 dark:border-neutral-700">
<div class="flex items-center justify-between">
<div class="text-center flex-1">
<div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">From</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['from_program_type'] }}</div>
</div>
<div class="mx-4">
<flux:icon name="arrow-right" class="h-5 w-5 text-neutral-400 dark:text-neutral-500" />
</div>
<div class="text-center flex-1">
<div class="text-xs text-neutral-500 dark:text-neutral-400 mb-1">To</div>
<div class="font-semibold text-primary-600 dark:text-primary-400">{{ $selectedRequest['to_program_type'] }}</div>
</div>
</div>
</div>

<!-- Student Information -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-1">
<div class="text-xs text-neutral-500 dark:text-neutral-400">Student Name</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">{{ ucwords(strtolower($selectedRequest['student_name'])) }}</div>
</div>
<div class="space-y-1">
<div class="text-xs text-neutral-500 dark:text-neutral-400">Registration Number</div>
<div class="font-mono font-medium text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['regno'] }}</div>
</div>
</div>

<!-- Programme Details -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<!-- Current Programme -->
<div class="border border-neutral-200 dark:border-neutral-700 rounded-lg p-4 bg-white dark:bg-neutral-800/30">
<div class="flex items-center gap-2 mb-3">
<flux:icon name="building-library" class="h-4 w-4 text-neutral-400 dark:text-neutral-500" />
<div class="font-medium text-sm text-neutral-900 dark:text-neutral-100">Current Programme</div>
</div>
<div class="space-y-2 text-sm">
<div>
<div class="text-xs text-neutral-500 dark:text-neutral-400">Programme</div>
<div class="text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['from_program'] }}</div>
</div>
<div>
<div class="text-xs text-neutral-500 dark:text-neutral-400">Department</div>
<div class="text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['from_department'] }}</div>
</div>
<div>
<div class="text-xs text-neutral-500 dark:text-neutral-400">Faculty</div>
<div class="text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['from_faculty'] }}</div>
</div>
</div>
</div>

<!-- Proposed Programme -->
<div class="border-2 border-primary-100 dark:border-primary-900/30 rounded-lg p-4 bg-primary-50 dark:bg-primary-900/10">
<div class="flex items-center gap-2 mb-3">
<flux:icon name="arrow-trending-up" class="h-4 w-4 text-primary-500 dark:text-primary-400" />
<div class="font-medium text-sm text-primary-700 dark:text-primary-300">Proposed Programme</div>
</div>
<div class="space-y-2 text-sm">
<div>
<div class="text-xs text-primary-600 dark:text-primary-400">Programme</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['to_program'] }}</div>
</div>
<div>
<div class="text-xs text-primary-600 dark:text-primary-400">Department</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['to_department'] }}</div>
</div>
<div>
<div class="text-xs text-primary-600 dark:text-primary-400">Faculty</div>
<div class="font-medium text-neutral-900 dark:text-neutral-100">{{ $selectedRequest['to_faculty'] }}</div>
</div>
</div>
</div>
</div>

<!-- Warning -->
<flux:callout variant="warning" class="mt-2">
<div class="flex items-start gap-3">
<flux:icon name="exclamation-triangle" class="h-5 w-5 flex-shrink-0" />
<div class="flex-1">
<div class="font-medium mb-2 dark:text-amber-100">Important: Approval Consequences</div>
<ul class="space-y-1.5 text-sm">
<li class="flex items-start gap-2">
<div class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400 mt-1.5 flex-shrink-0"></div>
<span class="text-neutral-900 dark:text-neutral-100">Previous course registrations will be deactivated</span>
</li>
<li class="flex items-start gap-2">
<div class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400 mt-1.5 flex-shrink-0"></div>
<span class="text-neutral-900 dark:text-neutral-100">Payment records will be archived for reference</span>
</li>
<li class="flex items-start gap-2">
<div class="h-1.5 w-1.5 rounded-full bg-amber-500 dark:bg-amber-400 mt-1.5 flex-shrink-0"></div>
<span class="text-neutral-900 dark:text-neutral-100">Student must complete fresh course registration in new programme</span>
</li>
</ul>
</div>
</div>
</flux:callout>
</div>
@else
<!-- Loading State -->
<div class="py-8 text-center">
<flux:icon name="arrow-path" class="h-8 w-8 text-neutral-400 dark:text-neutral-500 animate-spin mx-auto mb-3" />
<div class="text-neutral-500 dark:text-neutral-400">Loading transfer details...</div>
</div>
@endif

<!-- Actions -->
<div class="flex justify-end gap-3 pt-6 mt-6 border-t border-neutral-200 dark:border-neutral-700">
<flux:button
variant="ghost"
wire:click="$set('showApproveModal', false)"
class="cursor-pointer px-5 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800"
>
Cancel
</flux:button>

<flux:button
variant="danger"
wire:click="approve"
wire:loading.attr="disabled"
:disabled="!$selectedRequest"
class="cursor-pointer px-6 bg-red-600 hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-800 text-white dark:text-red-50"
wire:loading.class="opacity-70 cursor-not-allowed"
>
<div class="flex items-center gap-2">
<flux:icon
name="check-circle"
class="h-4 w-4"
wire:loading.remove
/>
<flux:icon
name="arrow-path"
class="h-4 w-4 animate-spin"
wire:loading
/>
<span wire:loading.remove>Approve Transfer</span>
<span wire:loading>Processing...</span>
</div>
</flux:button>
</div>
</flux:modal>



</flux:card>


