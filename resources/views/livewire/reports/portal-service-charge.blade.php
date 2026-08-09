<div class="space-y-6">

{{-- =========================================================
HEADER
========================================================== --}}

<div>
<flux:heading size="xl">
Portal Service Charge Commission
</flux:heading>

<flux:text class="mt-1 text-zinc-500">
Commission based on students who registered courses.
</flux:text>
</div>


{{-- =========================================================
PERIOD FILTER
========================================================== --}}

<flux:card>

<div class="space-y-5">

{{-- Filter Header --}}

<div>
<flux:heading size="sm">
Report Period
</flux:heading>

<flux:text class="mt-1 text-sm text-zinc-500">
Select a reporting period or specify a custom date range.
</flux:text>
</div>


{{-- =================================================
QUICK PERIODS
================================================== --}}

<div class="flex flex-wrap gap-2">

<flux:button
wire:click="setPeriod('today')"
variant="{{ $period === 'today' ? 'primary' : 'ghost' }}"
wire:loading.attr="disabled"
class="cursor-pointer"
wire:target="setPeriod"
icon="calendar"
>
Today
</flux:button>


<flux:button
wire:click="setPeriod('week')"
variant="{{ $period === 'week' ? 'primary' : 'ghost' }}"
wire:loading.attr="disabled"
wire:target="setPeriod"
class="cursor-pointer"
icon="calendar"
>
This Week
</flux:button>


<flux:button
wire:click="setPeriod('month')"
variant="{{ $period === 'month' ? 'primary' : 'ghost' }}"
wire:loading.attr="disabled"
wire:target="setPeriod"
class="cursor-pointer"
icon="calendar-days"
>
This Month
</flux:button>


<flux:button
wire:click="setPeriod('custom')"
variant="{{ $period === 'custom' ? 'primary' : 'ghost' }}"
wire:loading.attr="disabled"
wire:target="setPeriod"
class="cursor-pointer"
icon="calendar-date-range"
>
Custom Range
</flux:button>

</div>


{{-- =================================================
DATE / BREAKDOWN FILTERS
================================================== --}}

<div class="grid gap-4 md:grid-cols-3">

<flux:input
type="date"
label="Start Date"
wire:model="startDate"
/>


<flux:input
type="date"
label="End Date"
wire:model="endDate"
/>


<flux:select
label="Breakdown"
wire:model="groupBy"
>

<flux:select.option value="day">
Daily
</flux:select.option>

<flux:select.option value="week">
Weekly
</flux:select.option>

<flux:select.option value="month">
Monthly
</flux:select.option>

</flux:select>

</div>


{{-- =================================================
ACTIONS
================================================== --}}

<div class="flex flex-wrap items-center gap-2">

<flux:button
variant="primary"
icon="arrow-path"
wire:click="fetchReport"
wire:loading.attr="disabled"
wire:target="fetchReport"
class="cursor-pointer"
>

<span wire:loading.remove wire:target="fetchReport">
Fetch Report
</span>

<span
wire:loading
wire:target="fetchReport"
class="flex items-center gap-2"
>
<flux:icon.arrow-path class="size-4 animate-spin" />
Loading...
</span>

</flux:button>


<flux:button
variant="ghost"
wire:click="resetReport"
wire:loading.attr="disabled"
wire:target="resetReport"
class="cursor-pointer"
>
Reset
</flux:button>

</div>

</div>

</flux:card>


{{-- =========================================================
VALIDATION
========================================================== --}}

@if($errors->any())

<flux:callout
variant="danger"
icon="exclamation-triangle"
>

<flux:callout.heading>
Unable to generate report
</flux:callout.heading>

<flux:callout.text>
{{ $errors->first() }}
</flux:callout.text>

</flux:callout>

@endif


{{-- =========================================================
LOADING STATE
========================================================== --}}

<div
wire:loading.flex
wire:target="fetchReport,setPeriod"
class="items-center justify-center"
>

<flux:card class="w-full">

<div class="flex items-center justify-center gap-3 py-8">

<flux:icon.arrow-path
class="size-5 animate-spin text-zinc-500"
/>

<flux:text class="text-zinc-500">
Loading commission report...
</flux:text>

</div>

</flux:card>

</div>


{{-- =========================================================
REPORT
========================================================== --}}

@if($hasReport)

<div
wire:loading.remove
wire:target="fetchReport,setPeriod"
class="space-y-6"
>

{{-- =================================================
REPORT HEADER
================================================== --}}

<div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">

<div>

<flux:heading size="lg">
Report Summary
</flux:heading>

<flux:text class="mt-1 text-zinc-500">

{{ $this->formattedStartDate }}

<span class="mx-1">—</span>

{{ $this->formattedEndDate }}

</flux:text>

</div>


<flux:badge
color="blue"
size="sm"
>
{{ ucfirst($groupBy) }} Breakdown
</flux:badge>

</div>


{{-- =================================================
SUMMARY CARDS
================================================== --}}

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">

{{-- Students Registered --}}

<flux:card>

<div class="flex items-start justify-between gap-4">

<div>

<flux:text class="text-sm text-zinc-500">
Students Registered
</flux:text>

<flux:heading
size="xl"
class="mt-2"
>
{{ number_format(
$summary['students'] ?? 0
) }}
</flux:heading>

</div>


<div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">

<flux:icon.users
class="size-5 text-blue-600 dark:text-blue-400"
/>

</div>

</div>

</flux:card>


{{-- Commission Per Student --}}

<flux:card>

<div class="flex items-start justify-between gap-4">

<div>

<flux:text class="text-sm text-zinc-500">
Commission Per Student
</flux:text>

<flux:heading
size="xl"
class="mt-2"
>
₦{{ number_format(
$summary['commission_per_student'] ?? 0,
2
) }}
</flux:heading>

</div>


<div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">

<flux:icon.banknotes
class="size-5 text-amber-600 dark:text-amber-400"
/>

</div>

</div>

</flux:card>


{{-- Total Commission --}}

<flux:card>

<div class="flex items-start justify-between gap-4">

<div>

<flux:text class="text-sm text-zinc-500">
Portal Commission
</flux:text>

<flux:heading
size="xl"
class="mt-2"
>
₦{{ number_format(
$summary['total_commission'] ?? 0,
2
) }}
</flux:heading>

</div>


<div class="rounded-lg bg-emerald-100 p-2 dark:bg-emerald-900/30">

<flux:icon.currency-dollar
class="size-5 text-emerald-600 dark:text-emerald-400"
/>

</div>

</div>

</flux:card>

</div>


{{-- =================================================
BREAKDOWN
================================================== --}}

@if(count($breakdown) > 0)

<flux:card class="overflow-hidden">

{{-- Breakdown Header --}}

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">

<div>

<flux:heading size="lg">
Commission Breakdown
</flux:heading>

<flux:text class="mt-1 text-zinc-500">
{{ ucfirst($groupBy) }}
student registration activity.
</flux:text>

</div>

</div>


{{-- =================================================
TABLE
================================================== --}}

<div class="mt-6 overflow-x-auto">

<table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">

<thead>

<tr class="text-left">

<th class="px-4 py-3 text-sm font-medium">
Period
</th>


@if($groupBy === 'week')

<th class="px-4 py-3 text-sm font-medium">
Start
</th>

<th class="px-4 py-3 text-sm font-medium">
End
</th>

@endif


<th class="px-4 py-3 text-right text-sm font-medium">
Students
</th>


<th class="px-4 py-3 text-right text-sm font-medium">
Commission
</th>

</tr>

</thead>


<tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">

@foreach($breakdown as $row)

<tr class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40">

{{-- Period --}}

<td class="px-4 py-3">

@if($groupBy === 'month')

<span class="font-medium">
{{ \Carbon\Carbon::parse(
$row['period'] . '-01'
)->format('F Y') }}
</span>

@elseif($groupBy === 'day')

<span class="font-medium">
{{ \Carbon\Carbon::parse(
$row['period']
)->format('d M Y') }}
</span>

@else

<span class="font-medium">
Week
</span>

@endif

</td>


{{-- Weekly Start --}}

@if($groupBy === 'week')

<td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">

{{ !empty($row['period_start'])
? \Carbon\Carbon::parse(
$row['period_start']
)->format('d M Y')
: '-' }}

</td>


{{-- Weekly End --}}

<td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">

{{ !empty($row['period_end'])
? \Carbon\Carbon::parse(
$row['period_end']
)->format('d M Y')
: '-' }}

</td>

@endif


{{-- Students --}}

<td class="px-4 py-3 text-right">

{{ number_format(
$row['students'] ?? 0
) }}

</td>


{{-- Commission --}}

<td class="px-4 py-3 text-right font-semibold">

₦{{ number_format(
$row['commission'] ?? 0,
2
) }}

</td>

</tr>

@endforeach

</tbody>


{{-- =================================================
TOTAL
================================================== --}}

<tfoot>

<tr class="bg-zinc-50 dark:bg-zinc-800/50">

<td
colspan="{{ $groupBy === 'week' ? 3 : 1 }}"
class="px-4 py-3 text-right font-semibold"
>
Period Total
</td>


<td class="px-4 py-3 text-right font-semibold">

{{ number_format(
$summary['students'] ?? 0
) }}

</td>


<td class="px-4 py-3 text-right font-semibold">

₦{{ number_format(
$summary['total_commission'] ?? 0,
2
) }}

</td>

</tr>

</tfoot>

</table>

</div>

</flux:card>

@endif


{{-- =================================================
NO ACTIVITY
================================================== --}}

@if(
($summary['students'] ?? 0) === 0 &&
count($breakdown) === 0
)

<flux:card>

<div class="py-12 text-center">

<flux:icon.chart-bar
class="mx-auto size-10 text-zinc-400"
/>

<flux:heading
size="sm"
class="mt-4"
>
No Course Registration Activity
</flux:heading>

<flux:text class="mt-2 text-zinc-500">
No students registered courses during the
selected reporting period.
</flux:text>

</div>

</flux:card>

@endif

</div>

@endif

</div>
