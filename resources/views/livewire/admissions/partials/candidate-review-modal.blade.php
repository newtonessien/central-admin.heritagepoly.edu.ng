
{{-- ========================================================
MODAL: Candidate Review
========================================================= --}}

<flux:modal
wire:model="showReviewModal"
class="w-full max-w-5xl"
>
@if ($selectedCandidate)

<div class="space-y-6">

{{-- Header --}}
<div class="flex items-start justify-between gap-4">

<div>
<flux:heading size="lg">
Candidate Review
</flux:heading>

<flux:text class="mt-1">
{{ $selectedCandidate['name'] ?? 'Candidate' }}

@if (!empty($selectedCandidate['regno']))
<span class="text-zinc-500">
({{ $selectedCandidate['regno'] }})
</span>
@endif
</flux:text>
</div>

<flux:badge
:color="($selectedCandidate['is_admitted'] ?? false) ? 'green' : 'amber'"
>
{{ ($selectedCandidate['is_admitted'] ?? false) ? 'Admitted' : 'Pending' }}
</flux:badge>

</div>


{{-- Candidate information --}}
<div class="grid grid-cols-2 gap-4 md:grid-cols-4">

<div>
<flux:text size="sm" class="text-zinc-500">
Registration No.
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['regno'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Sex
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['sex'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
State
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['state'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Application Type
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['application_type'] ?? '—' }}
</div>
</div>

</div>


{{-- Academic information --}}
<div class="rounded-xl border border-zinc-200/20 bg-zinc-50/5 p-5">

<flux:heading size="sm">
Academic Information
</flux:heading>

<div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-3">

<div>
<flux:text size="sm" class="text-zinc-500">
Course of Study
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['program'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Programme Type
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['program_type'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Academic Session
</flux:text>

<div class="mt-1 font-medium">
{{ $this->sessionName ?? $selectedCandidate['acadSession'] ?? '—' }}
</div>
</div>

</div>

</div>


{{-- JAMB --}}
@if (
!empty($selectedCandidate['jamb_no']) ||
$selectedCandidate['jamb_score'] !== null
)

<div class="rounded-xl border border-zinc-200/20 bg-zinc-50/5 p-5">

<flux:heading size="sm">
JAMB / UTME
</flux:heading>

<div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-3">

<div>
<flux:text size="sm" class="text-zinc-500">
JAMB Number
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['jamb_no'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
JAMB Score
</flux:text>

<div class="mt-1 font-medium">
{{ $selectedCandidate['jamb_score'] ?? '—' }}
</div>
</div>

<div>
<flux:text size="sm" class="text-zinc-500">
Verification
</flux:text>

<div class="mt-1">
@if ($selectedCandidate['jamb_verified'] ?? false)
<flux:badge color="green">
Verified
</flux:badge>
@else
<flux:badge color="amber">
Not Verified
</flux:badge>
@endif
</div>
</div>

</div>

</div>

@endif


{{-- O'Level --}}
<div>

<flux:heading size="sm">
O'Level Results
</flux:heading>

<flux:text class="mt-1">
Examination details and subjects supplied by the candidate.
</flux:text>

<div class="mt-4 space-y-4">

@forelse ($selectedCandidate['olevel_details'] ?? [] as $result)

<div class="rounded-xl border border-zinc-200/20 p-5">

<div class="flex flex-wrap items-center gap-3">

<flux:badge>
{{ $result['sitting'] ?? 'Sitting' }}
</flux:badge>

@if (!empty($result['exam_type']))
<span class="font-medium">
{{ $result['exam_type'] }}
</span>
@endif

@if (!empty($result['exam_year']))
<span class="text-sm text-zinc-500">
{{ $result['exam_year'] }}
</span>
@endif

</div>

@if (!empty($result['school_name']))
<div class="mt-2 text-sm text-zinc-500">
{{ $result['school_name'] }}
</div>
@endif

@if (!empty($result['results']))

<div class="mt-4 flex flex-wrap gap-2">

@foreach ($result['results'] as $subject)

<div class="rounded-lg border border-zinc-200/20 px-3 py-2 text-sm">
<span class="font-medium">
{{ $subject['code'] ?? $subject['subject'] ?? '—' }}
</span>

<span class="mx-1 text-zinc-500">
=
</span>

<span>
{{ $subject['grade'] ?? '—' }}
</span>
</div>

@endforeach

</div>

@endif

</div>

@empty

<div class="rounded-xl border border-dashed border-zinc-200/20 p-6 text-center">

<flux:text>
No O'Level information supplied.
</flux:text>

</div>

@endforelse

</div>

</div>


{{-- Credentials --}}
@if (!empty($selectedCandidate['credentials_pdf_url']))

<div class="rounded-xl border border-zinc-200/20 p-5">

<div class="flex items-center justify-between gap-4">

<div>
<flux:heading size="sm">
Credentials
</flux:heading>

<flux:text class="mt-1">
Candidate's uploaded admission credentials.
</flux:text>
</div>

<flux:button
href="{{ $selectedCandidate['credentials_pdf_url'] }}"
target="_blank"
icon="arrow-down-tray"
variant="primary"
>
View Credentials
</flux:button>

</div>

</div>

@endif


{{-- Footer --}}
<div class="flex justify-end border-t border-zinc-200/20 pt-5">

<flux:button
variant="ghost"
class="cursor-pointer"
wire:click="closeReview"
>
Close
</flux:button>

</div>

</div>

@endif
</flux:modal>
