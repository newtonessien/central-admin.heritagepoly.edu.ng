<div>
<flux:card class="space-y-2">
<div class="flex items-center justify-between">
<flux:heading> 2024/2025 List of Candidates for Admissions</flux:heading>

<div class="flex items-center gap-2">
<flux:text size="sm" class="text-zinc-500" wire:loading.remove wire:target="filterCandidates,program_type_id,faculty_id,department_id,program_id">
@if($program_type_id && $faculty_id && $department_id && $program_id)
Ready to load candidates
@else
Select Program Type → Faculty → Department → Program
@endif
</flux:text>

<flux:button variant="subtle" icon="arrow-path" wire:click="resetFilters">
Reset
</flux:button>

  {{-- exports --}}
 @php
    // 1) keep your guard
    $hasAnyFilter = (
        !empty($program_type_id)
        || !empty($faculty_id)
        || !empty($department_id)
        || !empty($program_id)
        || in_array(($status ?? ''), ['pending','approved'], true)
        || (isset($localSearch) && trim((string)$localSearch) !== '')
    );

    // Ensure we can "collect" regardless of array/object
  $ptName = collect($programTypes)->firstWhere('id', (int) $program_type_id)['name'] ?? null;
  $fName  = collect($faculties)->firstWhere('id', (int) $faculty_id)['name'] ?? null;
  $dName  = collect($departments)->firstWhere('id', (int) $department_id)['name'] ?? null;
  $pName  = collect($programs)->firstWhere('id', (int) $program_id)['name'] ?? null;

    // 2) look up human names for the selected ids
    // $ptName = optional($ptColl->firstWhere('id', (int) $program_type_id))['name'] ?? null;
    // $fName  = optional($fColl->firstWhere('id', (int) $faculty_id))['name'] ?? null;
    // $dName  = optional($dColl->firstWhere('id', (int) $department_id))['name'] ?? null;
    // $pName  = optional($pColl->firstWhere('id', (int) $program_id))['name'] ?? null;

    // 3) shared params for all export routes
  $exportParams = array_filter([
    'status'             => $status,
    'program_type_id'    => $program_type_id,
    'faculty_id'         => $faculty_id,
    'department_id'      => $department_id,
    'program_id'         => $program_id,
    'q'                  => $localSearch,
    'program_type_name'  => $ptName,
    'faculty_name'       => $fName,
    'department_name'    => $dName,
    'program_name'       => $pName,
  ], fn($v) => $v !== null && $v !== '');
@endphp

{{-- Excel --}}
@if($hasAnyFilter)
  <a href="{{ route('exports.candidates.excel', $exportParams) }}"
     class="text-xs rounded-md border px-1 py-1 hover:bg-zinc-50">
    Export Excel
  </a>
@else
  <button type="button"
          class="rounded-md border px-1 py-1 text-xs text-zinc-400 cursor-not-allowed"
          title="Apply at least one filter or search before exporting">
    Export Excel
  </button>
@endif

{{-- CSV --}}
@if($hasAnyFilter)
  <a href="{{ route('exports.candidates.csv', $exportParams) }}"
     class="rounded-md border px-1 py-1 text-xs hover:bg-zinc-50">
    Export CSV
  </a>
@else
  <button type="button"
          class="rounded-md border px-1 py-1 text-xs text-zinc-400 cursor-not-allowed"
          title="Apply at least one filter or search before exporting">
    Export CSV
  </button>
@endif

{{-- PDF --}}
@if($hasAnyFilter)
  <a href="{{ route('exports.candidates.pdf', $exportParams) }}"
     class="rounded-md border px-1 py-1 text-xs hover:bg-zinc-50">
    Export PDF
  </a>
@else
  <button type="button"
          class="rounded-md border px-1 py-1 text-xs text-zinc-400 cursor-not-allowed"
          title="Apply at least one filter or search before exporting">
    Export PDF
  </button>
@endif


  {{-- exports --}}
<div class="w-40 md:w-50">
    <flux:input
      size="sm"
      placeholder="Filter by name or reg no…"
      icon="magnifying-glass"
      wire:model.live.debounce.300ms="localSearch"
      autocomplete="off"
    />
  </div>


</div>
</div>
@error('filters')
  <div class="mt-2 rounded-md border border-rose-200 bg-rose-50 px-3 py-2 text-sm text-rose-700">
    {{ $message }}
  </div>
@enderror

{{-- Filters --}}
<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
{{-- Program Type --}}
<flux:select
wire:model.live.number="program_type_id"
label="Program Type"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Program Type -"
>
@foreach($programTypes as $pt)
<flux:select.option value="{{ $pt['id'] }}">
{{ $pt['name'] }} @isset($pt['code']) ({{ $pt['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>

{{-- Faculty --}}
<flux:select
wire:model.live.number="faculty_id"
label="Faculty"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Faculty -"
>
@foreach($faculties as $f)
<flux:select.option value="{{ $f['id'] }}">
{{ $f['name'] }} @isset($f['code']) ({{ $f['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>

{{-- Department (depends on faculty) --}}
<div class="space-y-1">
<flux:select
wire:model.live.number="department_id"
label="Department"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Department -"
:disabled="!$faculty_id"
>
@foreach($departments as $d)
<flux:select.option value="{{ $d['id'] }}">
{{ $d['name'] }} @isset($d['code']) ({{ $d['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>

<flux:text size="xs" class="text-zinc-500" wire:loading.delay.shortest wire:target="faculty_id">
Loading departments…
</flux:text>
</div>

{{-- Program (depends on department + program type) --}}
<div class="space-y-1">
<flux:select
wire:model.live.number="program_id"
label="Program"
searchable
indicator="checkbox"
variant="listbox"
placeholder="- Select Program -"
:disabled="!$department_id"
>
@foreach($programs as $p)
<flux:select.option value="{{ $p['id'] }}">
{{ $p['name'] }} @isset($p['code']) ({{ $p['code'] }}) @endisset
</flux:select.option>
@endforeach
</flux:select>

<flux:text size="xs" class="text-zinc-500"
wire:loading.delay.shortest
wire:target="department_id,program_type_id">
Loading programs…
</flux:text>
</div>
</div>

{{-- Actions --}}
<div class="flex items-center gap-3 pt-2">
<flux:button
variant="primary"
icon="magnifying-glass"
wire:click="filterCandidates"
:disabled="!($program_type_id && $faculty_id && $department_id && $program_id)"
size="sm"
class="cursor-pointer"
>
<span wire:loading.remove wire:target="filterCandidates">Load {{ $status === 'approved' ? 'Approved' : ($status === 'pending' ? 'Pending' : 'All') }}</span>
<span wire:loading wire:target="filterCandidates">Loading…</span>
</flux:button>


@php
$filtered = $this->filteredCandidates;            // <- use computed list
$hasRows  = is_array($filtered) && count($filtered) > 0;
@endphp

{{-- Bulk actions toolbar --}}
@if($hasRows)
<div class="flex items-center gap-2 -mt-2">
<flux:button
variant="primary"
icon="check"
size="sm"
class="bg-emerald-600 hover:bg-emerald-700 text-white cursor-pointer"
wire:click="bulkApprove"
wire:target="bulkApprove"
wire:loading.attr="disabled"
:disabled="count($selected) === 0"
x-data
x-on:click.prevent="if ({{ count($selected) }} && !confirm('Approve selected candidates?')) { $event.stopImmediatePropagation(); }"
>
<span wire:loading.remove wire:target="bulkApprove">Approve Selected ({{ count($selected) }})</span>
<span wire:loading wire:target="bulkApprove">Working…</span>
</flux:button>

<flux:button
variant="subtle"
icon="arrow-uturn-left"
class="text-rose-600 hover:text-rose-700"
wire:click="bulkRevoke"
wire:target="bulkRevoke"
wire:loading.attr="disabled"
:disabled="count($selected) === 0"
x-data
x-on:click.prevent="if ({{ count($selected) }} && !confirm('Revoke selected admissions?')) { $event.stopImmediatePropagation(); }"
>
<span wire:loading.remove wire:target="bulkRevoke">Revoke Selected ({{ count($selected) }})</span>
<span wire:loading wire:target="bulkRevoke">Working…</span>
</flux:button>
</div>
@endif

</div>


{{-- Results --}}
@if($hasRows)

{{-- Tabs (Pending | Approved | All) --}}
<div class="flex items-center gap-2 border-b border-zinc-200 dark:border-zinc-800 pt-2">
@php
$active = fn ($v) => ($status === $v) || ($v === '' && $status === '');
@endphp

<button
type="button"
class="px-3 py-2 text-sm -mb-px border-b-2 transition
{{ $active('pending') ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-300' }}"
wire:click="$set('status','pending')"
>
Pending
</button>

<button
type="button"
class="px-3 py-2 text-sm -mb-px border-b-2 transition
{{ $active('approved') ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-300' }}"
wire:click="$set('status','approved')"
>
Approved
</button>

<button
type="button"
class="px-3 py-2 text-sm -mb-px border-b-2 transition
{{ $active('') ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-zinc-600 hover:text-zinc-900 dark:text-zinc-300' }}"
wire:click="$set('status','')"
>
All
</button>



</div>


{{-- Table section starts --}}
<flux:table>
    <flux:table.columns>

        <flux:table.column class="w-10">
            <input
                type="checkbox"
                class="h-4 w-4 rounded border-zinc-300"
                wire:model.live="selectAll"
                @disabled(! $hasRows)
                title="Select all visible rows"
            />
        </flux:table.column>

        <flux:table.column>Reg No</flux:table.column>
        <flux:table.column>Name</flux:table.column>
        <flux:table.column>Sex</flux:table.column>
        <flux:table.column>State</flux:table.column>
        <flux:table.column>O&#39;Level Details</flux:table.column>
        <flux:table.column>Credentials</flux:table.column>
        <flux:table.column class="w-44 text-right">Actions</flux:table.column>

       </flux:table.columns>

       {{-- <flux:table.columns>

    <flux:table.column class="w-8"></flux:table.column>

    <flux:table.column>Reg No</flux:table.column>
    <flux:table.column>Name</flux:table.column>
    <flux:table.column>Sex</flux:table.column>
    <flux:table.column>State</flux:table.column>
    <flux:table.column>O&#39;Level Details</flux:table.column>
    <flux:table.column>Credentials</flux:table.column>
    <flux:table.column class="w-44 text-right">Actions</flux:table.column>
</flux:table.columns> --}}


    <flux:table.rows>
        @foreach($filtered as $c) {{-- <- filtered list --}}
            @php
                //$cid = $c['id'] ?? $loop->index;  // needs real id from API for actions
                $cid = isset($c['id']) ? (int) $c['id'] : null;
                $isApproved = (($c['is_admitted'] ?? 0) == 1) || (strtolower((string)($c['status'] ?? '')) === 'approved');

                // Build O'Level display: "2015/WAEC : ENG=C4 | MAT=C6  ||  2017/NECO : PHY=C4 | ..."
                $olevel = collect($c['olevel_details'] ?? []);
                $olevelDisplay = $olevel->map(function ($sit) {
                    $title = trim(($sit['sitting'] ?? '') ?: (($sit['is_sitting'] ?? false) ? 'Second Sitting' : 'First Sitting'));
                    $subs = collect($sit['results'] ?? [])->map(function($r){
                        // prefer subject code if present, else subject name
                        $label = $r['code'] ?? $r['subject'] ?? '';
                        return $label . '=' . ($r['grade'] ?? '');
                    })->implode(' | ');
                    return trim($title . ' : ' . $subs);
                })->implode('  ||  ');
            @endphp

            <flux:table.row :key="$cid">
                {{-- Row checkbox --}}
                <flux:table.cell>
                    @if(isset($c['id']))
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded border-zinc-300"
                            value="{{ (int) $c['id'] }}"
                            wire:model.live="selected"
                        />
                    @endif
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    {{ $c['regno'] ?? '—' }}
                </flux:table.cell>

                <flux:table.cell>
                    {{-- API returns flat "name" now --}}
                    {{ $c['name'] ?? '—' }}
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    {{ $c['sex'] ?? '—' }}
                </flux:table.cell>

                <flux:table.cell class="whitespace-nowrap">
                    {{ $c['state'] ?? '—' }}
                </flux:table.cell>

            {{-- O'Level Details (preview + show more/less + modal + hover popover) --}}
<flux:table.cell class="relative">
    @php
        // $olevel and $olevelDisplay were already built just above in your row @php block
        $hasOlevel = (collect($olevel ?? [])->count() > 0) && !empty($olevelDisplay ?? null);
    @endphp

    <div x-data="{ expanded:false, open:false, hover:false }" class="space-y-1">
        {{-- Collapsible inline preview (2 lines by default) --}}
        {{-- <div :class="expanded ? '' : 'line-clamp-2'" class="text-sm">
            {{ $olevelDisplay ?: '—' }}
        </div> --}}

        @if($hasOlevel)
            <div class="flex items-center gap-3">
                {{-- Show more / less toggles --}}
                {{-- <button type="button"
                        class="text-xs underline text-emerald-700 hover:text-emerald-800"
                        x-show="!expanded"
                        @click="expanded = true">
                    Show more
                </button>
                <button type="button"
                        class="text-xs underline text-emerald-700 hover:text-emerald-800"
                        x-show="expanded"
                        @click="expanded = false">
                    Show less
                </button> --}}

                {{-- Modal trigger --}}
                <button type="button"
                        class="text-xs rounded-md border px-2 py-1 hover:bg-zinc-50"
                        @click="open = true">
                    View details
                </button>

                {{-- Optional hover popover (desktop) --}}
                <span class="hidden md:inline relative"
                      @mouseenter="hover = true" @mouseleave="hover = false" @focusin="hover = true" @focusout="hover = false">
                    <span class="text-xs underline cursor-help text-zinc-500">Hover to preview</span>
                    <div x-show="hover"
                         x-transition
                         class="absolute z-40 mt-2 w-[32rem] max-w-[90vw] rounded-md border bg-white p-3 text-xs shadow-lg dark:bg-zinc-900 dark:border-zinc-800"
                         style="left: 0;">
                        @foreach($olevel as $sit)
                            <div class="mb-2 last:mb-0">
                                <div class="font-medium">
                                    {{ ($sit['sitting'] ?? '') ?: (($sit['is_sitting'] ?? false) ? 'Second Sitting' : 'First Sitting') }}
                                </div>
                                <div class="mt-1 flex flex-wrap gap-1">
                                    @foreach(($sit['results'] ?? []) as $r)
                                        <span class="rounded border px-1.5 py-0.5">
                                            {{ $r['code'] ?? $r['subject'] ?? '' }}={{ $r['grade'] ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </span>
            </div>
        @endif

        {{-- Modal --}}
        <div x-show="open"
             x-transition.opacity
             class="fixed inset-0 z-50"
             aria-modal="true" role="dialog">
            <div class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div class="absolute inset-0 flex items-center justify-center p-4">
                <div class="w-full max-w-3xl rounded-2xl bg-white shadow-xl p-4 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold">
                            O’Level Details — {{ $c['name'] ?? '' }} ({{ $c['regno'] ?? '' }})
                        </div>
                        <button type="button"
                                class="text-sm rounded-md border px-2 py-1 hover:bg-zinc-50"
                                @click="open = false">
                            Close
                        </button>
                    </div>

                    <div class="mt-3 space-y-4">
                        @forelse($olevel as $sit)
                            <div class="rounded-md border p-3 dark:border-zinc-800">
                                <div class="font-medium">
                                    {{ ($sit['sitting'] ?? '') ?: (($sit['is_sitting'] ?? false) ? 'Second Sitting' : 'First Sitting') }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    @if(!empty($sit['exam_type']) || !empty($sit['exam_year']) || !empty($sit['school_name']))
                                        {{ $sit['exam_type'] ?? '' }} {{ $sit['exam_year'] ?? '' }}
                                        @if(!empty($sit['school_name'])) • {{ $sit['school_name'] }} @endif
                                    @endif
                                </div>
                                <div class="mt-2 flex flex-wrap gap-1">
                                    @foreach(($sit['results'] ?? []) as $r)
                                        <span class="rounded-md border px-2 py-1 text-xs dark:border-zinc-800">
                                            {{ $r['code'] ?? $r['subject'] ?? '' }} = {{ $r['grade'] ?? '' }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-zinc-500">No O’Level records.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</flux:table.cell>
                {{-- Credentials (PDF link) --}}

                <flux:table.cell class="whitespace-nowrap">
                    @if(!empty($c['credentials_pdf_url']))
                        <a
                            href="{{ $c['credentials_pdf_url'] }}"
                            target="_blank"
                            class="inline-flex items-center gap-1 rounded-md border px-2.5 py-1.5 text-xs hover:bg-zinc-50"
                        >
                            Download PDF
                        </a>
                    @else
                        <span class="text-zinc-400 text-xs">No file</span>
                    @endif
                </flux:table.cell>

                {{-- Actions --}}
                <flux:table.cell class="text-right">
                    @if($isApproved)
                        <div class="inline-flex items-center gap-2">
                            <flux:badge color="green">Approved</flux:badge>

                            <flux:button
                                size="xs"
                                variant="subtle"
                                icon="arrow-uturn-left"
                                class="text-rose-600 hover:text-rose-700"
                                wire:target="revoke"
                                wire:loading.attr="disabled"
                                x-data
                                x-on:click.prevent="if (confirm('Revoke this admission?')) { $wire.revoke({{ (int) $cid }}) }"
                            >
                                Revoke
                            </flux:button>
                        </div>
                    @else
                        <flux:button
                            size="xs"
                            variant="primary"
                            icon="check"
                            class="bg-emerald-600 hover:bg-emerald-700 text-white"
                            wire:target="approve"
                            wire:loading.attr="disabled"
                            x-data
                            x-on:click.prevent="if (confirm('Approve this candidate?')) { $wire.approve({{ (int) $cid }}) }"
                        >
                            <span wire:loading.remove wire:target="approve" class="cursor-pointer">Approve</span>
                            <span wire:loading wire:target="approve">Working…</span>
                        </flux:button>
                    @endif
                </flux:table.cell>
            </flux:table.row>
        @endforeach
    </flux:table.rows>
</flux:table>


{{-- table section end --}}

{{-- Pagination footer (unchanged; still shows server totals) --}}
<div class="flex items-center justify-between pt-4">
<flux:text size="sm" class="text-zinc-600">
@php
$from = ($page - 1) * $perPage + 1;
$to   = min($page * $perPage, $total);
if ($total === 0) { $from = 0; $to = 0; }
@endphp
Showing {{ $from }}–{{ $to }} of {{ $total }}
<span class="ml-2 text-zinc-400">
{{-- Optional: show how many after local filter --}}
@if($hasRows && count($filtered) !== count($candidates))
(filtered: {{ count($filtered) }} shown)
@endif
</span>
</flux:text>

<div class="flex items-center gap-2">
<flux:button variant="subtle" icon="chevron-left" wire:click="prevPage" :disabled="$page <= 1">
Prev
</flux:button>

<div class="flex items-center gap-1">
@php
$window = 2;
$start = max(1, $page - $window);
$end   = min($lastPage, $page + $window);
@endphp

@if($start > 1)
<button type="button" class="px-2 py-1 text-sm rounded hover:bg-zinc-100 dark:hover:bg-zinc-800"
wire:click="gotoPage(1)">1</button>
@if($start > 2)
<span class="px-1 text-sm text-zinc-400">…</span>
@endif
@endif

@for($p = $start; $p <= $end; $p++)
<button
type="button"
class="px-2 py-1 text-sm rounded {{ $p === $page ? 'bg-emerald-600 text-white' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
wire:click="gotoPage({{ $p }})"
>
{{ $p }}
</button>
@endfor

@if($end < $lastPage)
@if($end < $lastPage - 1)
<span class="px-1 text-sm text-zinc-400">…</span>
@endif
<button type="button" class="px-2 py-1 text-sm rounded hover:bg-zinc-100 dark:hover:bg-zinc-800"
wire:click="gotoPage({{ $lastPage }})">{{ $lastPage }}</button>
@endif
</div>

<flux:button variant="subtle" icon="chevron-right" wire:click="nextPage" :disabled="$page >= $lastPage">
Next
</flux:button>
</div>
</div>
@else
<flux:text class="text-zinc-600">
@if($program_type_id && $faculty_id && $department_id && $program_id)
No {{ $status === 'approved' ? 'approved' : ($status === 'pending' ? 'pending' : '') }} candidates found for selected filters.
@else
No candidates yet — complete your selections, then click “Load Candidates”.
@endif
</flux:text>
@endif
</flux:card>
</div>
