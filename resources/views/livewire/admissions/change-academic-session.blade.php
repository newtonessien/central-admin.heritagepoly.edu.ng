<div class="space-y-6">

{{-- ============================================================= --}}
{{-- Page Header --}}
{{-- ============================================================= --}}

<div>
<flux:heading size="xl">
Change Academic Session
</flux:heading>

<flux:text class="mt-1">
Change the intended admission session for a candidate.
</flux:text>
</div>


{{-- ============================================================= --}}
{{-- Search Candidate --}}
{{-- ============================================================= --}}

<flux:card>

    <div class="space-y-4">

        <div>
            <flux:heading size="sm">
                Find Candidate
            </flux:heading>

            <flux:text size="sm" class="mt-1">
                Enter the candidate's registration number.
            </flux:text>
        </div>


        <div class="flex flex-col gap-3 sm:flex-row">

            <div class="flex-1">

                <flux:input
                    wire:model="regno"
                    placeholder="Enter Registration Number"
                    autocomplete="off"
                    wire:keydown.enter="findCandidate"
                    :disabled="$loadingCandidate || $processing"
                />

                @error('regno')
                    <flux:text
                        size="sm"
                        class="mt-1 text-red-600"
                    >
                        {{ $message }}
                    </flux:text>
                @enderror

            </div>


            <flux:button
                type="button"
                variant="primary"
                wire:click="findCandidate"
                wire:loading.attr="disabled"
                wire:target="findCandidate"
                icon="magnifying-glass"
                class="cursor-pointer"
            >

                <span
                    wire:loading.remove
                    wire:target="findCandidate"
                    class="flex items-center gap-2"
                >
                    Find Candidate
                </span>


                <span
                    wire:loading.flex
                    wire:target="findCandidate"
                    class="items-center gap-2"
                >
                    <flux:icon
                        name="arrow-path"
                        class="size-4 animate-spin"
                    />

                    Searching...
                </span>

            </flux:button>

        </div>


        {{-- Error --}}
        @if ($errorMessage)

            <div
                class="rounded-lg border border-red-200 bg-red-50 p-3
                       dark:border-red-900/50 dark:bg-red-950/30"
            >

                <div class="flex items-start gap-2">

                    <flux:icon
                        name="exclamation-circle"
                        class="mt-0.5 size-5 text-red-600"
                    />

                    <flux:text class="text-red-700 dark:text-red-300">
                        {{ $errorMessage }}
                    </flux:text>

                </div>

            </div>

        @endif

    </div>

</flux:card>


{{-- ============================================================= --}}
{{-- Error }}
{{-- ============================================================= --}}

@if ($errorMessage)

<div
class="rounded-lg border border-red-200 bg-red-50 p-4
dark:border-red-900 dark:bg-red-950"
>

<div class="flex items-start gap-3">

<flux:icon
name="exclamation-triangle"
class="mt-0.5 text-red-600 dark:text-red-400"
/>

<flux:text class="text-red-700 dark:text-red-300">
{{ $errorMessage }}
</flux:text>

</div>

</div>

@endif


{{-- ============================================================= --}}
{{-- Candidate --}}
{{-- ============================================================= --}}

@if ($candidate)

<flux:card class="space-y-6">

{{-- ------------------------------------------------------- --}}
{{-- Candidate Header --}}
{{-- ------------------------------------------------------- --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

<div>

<flux:heading size="lg">
{{ $this->candidateName() ?: 'Candidate' }}
</flux:heading>

<flux:text class="mt-1">
{{ $candidate['jamb_no'] ?? $candidate['regno'] }}
</flux:text>

</div>

<flux:button
type="button"
variant="ghost"
wire:click="resetCandidate"
:disabled="$processing"
>
Clear
</flux:button>

</div>


{{-- ------------------------------------------------------- --}}
{{-- Candidate Information --}}
{{-- ------------------------------------------------------- --}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">

{{-- Registration Number --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Registration Number
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['jamb_no'] ?? $candidate['regno'] }}
</flux:text>

</div>


{{-- Programme Type --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Programme Type
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['program_type'] ?? '—' }}
</flux:text>

</div>


{{-- Current Academic Session --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Current Academic Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['acadSession'] ?? '—' }}
</flux:text>

</div>


{{-- Status --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Status
</flux:text>

<div class="mt-1">

@if (($candidate['is_admitted'] ?? false))

<flux:badge
color="green"
icon="check-circle"
>
Admitted
</flux:badge>

@elseif (($candidate['status'] ?? null) === 'rejected')

<flux:badge
color="red"
icon="x-circle"
>
Rejected
</flux:badge>

@else

<flux:badge
color="amber"
icon="clock"
>
Pending
</flux:badge>

@endif

</div>

</div>

</div>


{{-- ------------------------------------------------------- --}}
{{-- Current Placement --}}
{{-- ------------------------------------------------------- --}}

<div>

<flux:heading size="sm">
Current Placement
</flux:heading>

<flux:text class="mt-1">
The candidate's existing academic placement.
</flux:text>


<div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-3">

{{-- Faculty --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Faculty
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['faculty'] ?? '—' }}
</flux:text>

</div>


{{-- Department --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Department
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['department'] ?? '—' }}
</flux:text>

</div>


{{-- Programme --}}
<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Programme
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['program'] ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- ------------------------------------------------------- --}}
{{-- Change Academic Session --}}
{{-- ------------------------------------------------------- --}}

<div class="border-t pt-6">

<flux:heading size="sm">
Change Academic Session
</flux:heading>

<flux:text class="mt-1">
Select the academic session under which this candidate
should be admitted.
</flux:text>


{{-- New Session --}}
<div class="mt-4 max-w-xl">

<flux:select
wire:model.live="acadSessionId"
label="New Academic Session"
placeholder="Select academic session"
:disabled="$processing"
>

@foreach ($academicSessions as $session)

<flux:select.option
value="{{ $session['id'] }}"
>
{{ $session['name'] }}
</flux:select.option>

@endforeach

</flux:select>

</div>


{{-- Action --}}
<div class="mt-4">

<flux:button
type="button"
variant="primary"
wire:click="confirmChange"
class="cursor-pointer"
icon="arrow-path"
:disabled="$processing || !$acadSessionId"
>
Change Academic Session
</flux:button>

</div>

</div>

</flux:card>

@endif


{{-- ============================================================= --}}
{{-- Confirmation Modal --}}
{{-- ============================================================= --}}

<flux:modal
name="change-academic-session-confirmation"
class="max-w-lg"
>

<div class="space-y-6">

{{-- ------------------------------------------------------- --}}
{{-- Header --}}
{{-- ------------------------------------------------------- --}}

<div>

<flux:heading size="lg">
Confirm Academic Session Change
</flux:heading>

<flux:text class="mt-1">
Please verify the change before committing it.
</flux:text>

</div>


@if ($candidate)

{{-- --------------------------------------------------- --}}
{{-- Candidate --}}
{{-- --------------------------------------------------- --}}

<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Candidate
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $this->candidateName() ?: '—' }}
</flux:text>

<flux:text
size="sm"
class="mt-1"
>
{{ $candidate['regno'] ?? '—' }}
</flux:text>

</div>


{{-- --------------------------------------------------- --}}
{{-- Session Change --}}
{{-- --------------------------------------------------- --}}

<div class="rounded-lg border p-4">

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

{{-- Current Session --}}
<div>

<flux:text
size="sm"
class="text-zinc-500"
>
Current Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $candidate['acadSession'] ?? '—' }}
</flux:text>

</div>


{{-- New Session --}}
<div>

<flux:text
size="sm"
class="text-zinc-500"
>
New Session
</flux:text>

<flux:text class="mt-1 font-medium">
{{ $this->selectedSessionName() ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- --------------------------------------------------- --}}
{{-- Placement --}}
{{-- --------------------------------------------------- --}}

<div class="rounded-lg border p-4">

<flux:text
size="sm"
class="text-zinc-500"
>
Academic Placement
</flux:text>

<div class="mt-3 space-y-2">

<div class="flex justify-between gap-4">

<flux:text size="sm">
Faculty
</flux:text>

<flux:text
size="sm"
class="text-right font-medium"
>
{{ $candidate['faculty'] ?? '—' }}
</flux:text>

</div>


<div class="flex justify-between gap-4">

<flux:text size="sm">
Department
</flux:text>

<flux:text
size="sm"
class="text-right font-medium"
>
{{ $candidate['department'] ?? '—' }}
</flux:text>

</div>


<div class="flex justify-between gap-4">

<flux:text size="sm">
Programme
</flux:text>

<flux:text
size="sm"
class="text-right font-medium"
>
{{ $candidate['program'] ?? '—' }}
</flux:text>

</div>

</div>

</div>


{{-- --------------------------------------------------- --}}
{{-- Warning --}}
{{-- --------------------------------------------------- --}}

<div
class="rounded-lg border border-amber-200 bg-amber-50 p-4
dark:border-amber-900 dark:bg-amber-950"
>

<div class="flex items-start gap-3">

<flux:icon
name="exclamation-triangle"
class="mt-0.5 text-amber-600
dark:text-amber-400"
/>

<flux:text
class="text-amber-800 dark:text-amber-200"
>
This will change the candidate's intended
admission academic session in the Admissions
Portal.

The candidate's faculty, department and
programme will not be changed.
</flux:text>

</div>

</div>

@endif


{{-- ------------------------------------------------------- --}}
{{-- Actions --}}
{{-- ------------------------------------------------------- --}}

<div class="flex justify-end gap-3">

<flux:button
type="button"
variant="ghost"
x-on:click="$flux.modal('change-academic-session-confirmation').close()"
:disabled="$processing"
>
Cancel
</flux:button>


<flux:button
type="button"
variant="primary"
wire:click="changeAcademicSession"
icon="check"
class="cursor-pointer"
:disabled="$processing"
>

@if ($processing)
Processing...
@else

Confirm Change

@endif

</flux:button>

</div>

</div>

</flux:modal>


{{-- ============================================================= --}}
{{-- Processing --}}
{{-- ============================================================= --}}

@if ($processing)

<flux:card>

<div class="flex items-center gap-3">

<flux:icon
name="arrow-path"
class="animate-spin"
/>

<div>

<flux:heading size="sm">
Changing academic session...
</flux:heading>

<flux:text>
Please wait while the Admissions Portal
commits the change.
</flux:text>

</div>

</div>

</flux:card>

@endif

</div>
