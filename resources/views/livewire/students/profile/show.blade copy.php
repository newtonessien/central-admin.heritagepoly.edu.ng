<div class="space-y-8">

{{-- Header --}}
<div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

<div>

<flux:breadcrumbs>

<flux:breadcrumbs.item
:href="route('students.search')">
Student Finder
</flux:breadcrumbs.item>

<flux:breadcrumbs.item>
{{ $student['regno'] }}
</flux:breadcrumbs.item>

</flux:breadcrumbs>

<flux:heading size="xl" class="mt-3">
Student 360° Profile
</flux:heading>

<flux:text class="mt-2">
Comprehensive academic and personal information for the selected student.
</flux:text>

</div>

<flux:button
:href="route('students.search')"
icon="arrow-left"
variant="ghost">
Back to Search
</flux:button>

</div>


{{-- Hero Profile --}}
<flux:card>

<div class="flex flex-col gap-8 lg:flex-row">

{{-- Passport --}}
<div class="flex justify-center lg:block">

<div class="h-40 w-40 overflow-hidden rounded-2xl border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-800 shadow-sm">

@if(!empty($student['user']['photo']))
<img
src="{{ student_portal_url($student['user']['photo']) }}"
class="h-full w-full object-cover">
@else
<div class="flex h-full items-center justify-center">
<flux:icon.user class="size-20 text-zinc-400"/>
</div>
@endif

</div>

</div>

{{-- Profile --}}
<div class="flex-1">

<div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">

<div>

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

<flux:badge color="green">
{{ $student['program_type'] }}
</flux:badge>

<flux:badge color="zinc">
{{ $student['faculty'] }}
</flux:badge>

</div>

</div>

</div>

<div class="mt-8 grid gap-6 md:grid-cols-2 xl:grid-cols-3">

<div>
<div class="text-sm text-zinc-500">
Gender
</div>

<div class="mt-1 font-semibold">
{{ $student['sex'] == 'M' ? 'Male' : 'Female' }}
</div>
</div>

<div>
<div class="text-sm text-zinc-500">
Phone Number
</div>

<div class="mt-1 font-semibold">
{{ $student['user']['phone_no'] ?: 'Not Available' }}
</div>
</div>

<div>
<div class="text-sm text-zinc-500">
Email Address
</div>

<div class="mt-1 font-semibold">
{{ $student['email'] ?: 'Not Available' }}
</div>
</div>



</div>

</div>

</div>

</flux:card>


{{-- Quick Access --}}
<div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">



<flux:card class="text-center">

<flux:icon.credit-card class="mx-auto size-10 text-amber-600"/>

<flux:heading size="sm" class="mt-4">
Payment History
</flux:heading>

  <flux:text class="mt-2">
        {{ number_format($paymentSummary['transactions'] ?? 0) }}
        Transactions,
        Total:
        ₦{{ number_format($paymentSummary['total_amount'] ?? 0, 2) }}
    </flux:text>

</flux:card>

<flux:card class="text-center">

<flux:icon.clipboard-document-list class="mx-auto size-10 text-blue-600"/>

<flux:heading size="sm" class="mt-4">
Course Registration
</flux:heading>

<flux:text class="mt-2">

        Sessions: {{ $registrationSummary['sessions'] ?? 0 }},
        Semesters: {{ $registrationSummary['semesters'] ?? 0 }},
        Courses: {{ $registrationSummary['courses'] ?? 0 }}.

</flux:text>

</flux:card>

<flux:card class="text-center">

<flux:icon.academic-cap class="mx-auto size-10 text-violet-600"/>

<flux:heading size="sm" class="mt-4">
Academic Results
</flux:heading>

<flux:text class="mt-2">
4 Sessions, 4 Semesters, 3.5 CGPA.
</flux:text>

</flux:card>

<flux:card class="text-center">

<flux:icon.book-open class="mx-auto size-10 text-emerald-600"/>

<flux:heading size="sm" class="mt-4">
Academic Standing
</flux:heading>

<flux:text class="mt-2">
Standing: Good
</flux:text>

</flux:card>



</div>


{{-- Tabs --}}
<flux:card>

<flux:tabs wire:model.live="activeTab">

<flux:tab name="overview">
Overview
</flux:tab>

<flux:tab name="payments">
Payment History
</flux:tab>

<flux:tab name="registrations">
Course Registration
</flux:tab>

<flux:tab name="results">
Academic Results
</flux:tab>

</flux:tabs>

</flux:card>


{{-- Content --}}
<div>

@switch($activeTab)

@case('overview')

<livewire:students.profile.overview
:student="$student"
:key="'overview-'.$student['regno']"/>

@break

@case('payments')

    <livewire:students.profile.payment-history
        :regno="$student['regno']"
          :sessions="$sessions"
        :key="'payments-'.$student['regno']" />

    @break

@case('registrations')

    <livewire:students.profile.course-registration-history
        :regno="$student['regno']"
         :sessions="$sessions"
        :key="'registrations-'.$student['regno']" />

    @break

@case('results')

    <livewire:students.profile.results-history
        :regno="$student['regno']"
         :sessions="$sessions"
        :key="'results-'.$student['regno']" />

    @break

@endswitch

</div>

</div>
