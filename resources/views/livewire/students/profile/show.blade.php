<div class="space-y-6">

{{-- =========================================================
HEADER
========================================================== --}}

<div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

<div>

<flux:breadcrumbs>

<flux:breadcrumbs.item
:href="route('students.search')"
>
Student Finder
</flux:breadcrumbs.item>

<flux:breadcrumbs.item>
{{ $student['regno'] }}
</flux:breadcrumbs.item>

</flux:breadcrumbs>


<flux:heading
size="xl"
class="mt-3"
>
Student 360° Profile History
</flux:heading>


<flux:text class="mt-2">
Comprehensive academic and personal information
for the selected student.
</flux:text>

</div>


<flux:button
:href="route('students.search')"
icon="arrow-left"
variant="ghost"
>
Back to Search
</flux:button>

</div>


{{-- =========================================================
HERO PROFILE
========================================================== --}}

<flux:card>

<div class="flex flex-col gap-6 lg:flex-row lg:items-center">

{{-- Passport --}}

<div class="shrink-0">

<div
class="size-32 overflow-hidden rounded-2xl
bg-zinc-100 dark:bg-zinc-800"
>

@if(!empty($student['user']['photo']))

<img
src="{{ file_url($student['user']['photo'], 'students') }}"
alt="{{ $student['name'] }}"
class="h-full w-full object-cover"
>

@else

<div class="flex h-full items-center justify-center">

<flux:icon.user
    class="size-16 text-zinc-400"
/>

</div>

@endif

</div>

</div>


{{-- Profile Information --}}

<div class="min-w-0 flex-1">

<flux:heading size="xl">
{{ $student['name'] }}
</flux:heading>


<flux:text class="mt-2 text-base">
{{ $student['program'] }}
</flux:text>


<div class="mt-4 flex flex-wrap gap-2">

<flux:badge color="blue">
{{ $student['regno'] }}
</flux:badge>


@if(!empty($student['matric_no']))

<flux:badge color="zinc">
{{ $student['matric_no'] }}
</flux:badge>

@endif


<flux:badge color="green">
{{ $student['program_type'] }}
</flux:badge>


<flux:badge color="zinc">
{{ $student['faculty'] }}
</flux:badge>

</div>


<div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

<div>

<flux:text class="text-xs text-zinc-500">
Department
</flux:text>

<div class="mt-1 font-medium">
{{ $student['department'] ?? '-' }}
</div>

</div>


<div>

<flux:text class="text-xs text-zinc-500">
Academic Session
</flux:text>

<div class="mt-1 font-medium">
{{ $student['acad_session'] ?? '-' }}
</div>

</div>


<div>

<flux:text class="text-xs text-zinc-500">
Gender
</flux:text>

<div class="mt-1 font-medium">
{{ $student['sex'] ?? '-' }}
</div>

</div>


<div>

<flux:text class="text-xs text-zinc-500">
JAMB Number
</flux:text>

<div class="mt-1 font-medium">
{{ $student['jamb_no'] ?? $student['user']['username'] }}
</div>

</div>

</div>

</div>

</div>

</flux:card>


{{-- =========================================================
NAVIGATION TABS
========================================================== --}}

<flux:card>

<flux:tabs wire:model.live="activeTab" variant="segmented">

<flux:tab name="overview" icon="book-open">
Overview
</flux:tab>

<flux:tab name="payments" icon="credit-card">
Payment History
</flux:tab>

<flux:tab name="registrations" icon="document">
Course Registration
</flux:tab>

<flux:tab name="results" icon="academic-cap">
Academic Results
</flux:tab>

</flux:tabs>

</flux:card>


{{-- =========================================================
TAB CONTENT
========================================================== --}}

<div class="relative">


{{-- =====================================================
TAB SWITCH LOADING STATE
====================================================== --}}

<div
wire:loading.flex
wire:target="activeTab"
class="absolute inset-0 z-30 min-h-48 items-center justify-center
rounded-xl
bg-white/70 dark:bg-zinc-900/70
backdrop-blur-sm"
>

<div class="flex flex-col items-center gap-3">

<flux:icon
name="arrow-path"
class="size-6 animate-spin text-zinc-500"
/>

<flux:text class="text-sm">
Loading...
</flux:text>

</div>

</div>


{{-- =====================================================
OVERVIEW
====================================================== --}}

@if($activeTab === 'overview')

<livewire:students.profile.overview
:student="$student"
:key="'overview-'.$student['regno']"
/>

@endif


{{-- =====================================================
PAYMENT HISTORY
====================================================== --}}

@if($activeTab === 'payments')

<livewire:students.profile.payment-history
:regno="$student['regno']"
:sessions="$sessions"
:key="'payments-'.$student['regno']"
/>

@endif


{{-- =====================================================
COURSE REGISTRATION HISTORY
====================================================== --}}

@if($activeTab === 'registrations')

<livewire:students.profile.course-registration-history
:regno="$student['regno']"
:sessions="$sessions"
:key="'registrations-'.$student['regno']"
/>

@endif


{{-- =====================================================
ACADEMIC RESULTS
====================================================== --}}

@if($activeTab === 'results')

<livewire:students.profile.results-history
:regno="$student['matric_no'] ?? $student['regno']"
:sessions="$sessions"
:key="'results-'.$student['matric_no'] ?? $student['regno']"
/>

@endif

</div>

</div>
