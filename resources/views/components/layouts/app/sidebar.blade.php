<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
@include('partials.head')
</head>
<body class="min-h-screen bg-white dark:bg-zinc-800">
@persist('toast')<flux:toast/>@endpersist
<flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
<flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

<a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
<x-app-logo />
</a>

<flux:navlist variant="outline">
<flux:navlist.group :heading="__('Platform')" class="grid">
<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
</flux:navlist.group>

@role('admissions-manager|super-admin')
<flux:navlist.group heading="Admissions Management" expandable :expanded="false">
<flux:navlist.item icon="academic-cap" :href="route('admissions')" :current="request()->routeIs('admissions')" wire:navigate>{{ __('Applications') }}</flux:navlist.item>
<flux:navlist.item icon="user-group" :href="route('students.admitted')" :current="request()->routeIs('students.admitted')" wire:navigate>{{ __('Admitted Students') }}</flux:navlist.item>
<flux:navlist.item icon="arrow-path-rounded-square" :href="route('admissions.change-application-type')" :current="request()->routeIs('admissions.change-application-type')" wire:navigate>{{ __('Change App. Type') }}</flux:navlist.item>
<flux:navlist.item icon="book-open" :href="route('students.enrolled')" :current="request()->routeIs('students.enrolled')" wire:navigate>{{ __('Enrolled Students') }}</flux:navlist.item>
</flux:navlist.group>
@endrole

@role('student-manager|super-admin')
<flux:navlist.group heading="Student Management" expandable :expanded="false">
{{-- <flux:navlist.item icon="book-open" :href="route('students')" :current="request()->routeIs('students')" wire:navigate>{{ __('Students') }}</flux:navlist.item> --}}
<flux:navlist.item icon="envelope-open" :href="route('students.reset-email')" :current="request()->routeIs('students.reset-email')" wire:navigate>{{ __('Reset Email') }}</flux:navlist.item>
</flux:navlist.group>
@endrole

@role('student-manager|super-admin')
<flux:navlist.group heading="Student Management" expandable :expanded="false">
{{-- <flux:navlist.item icon="book-open" :href="route('students')" :current="request()->routeIs('students')" wire:navigate>{{ __('Students') }}</flux:navlist.item> --}}
<flux:navlist.item icon="envelope-open" :href="route('students.reset-email')" :current="request()->routeIs('students.reset-email')" wire:navigate>{{ __('Reset Email') }}</flux:navlist.item>
</flux:navlist.group>
@endrole

@role('bursary-manager|super-admin')
<flux:navlist.group heading="Bursary Management" expandable :expanded="false">
<flux:navlist.item icon="credit-card" :href="route('bursary.admission-payment-report')" :current="request()->routeIs('bursary.admission-payment-report')" wire:navigate>{{ __('Forms Payment') }}</flux:navlist.item>
<flux:navlist.item icon="currency-dollar" :href="route('bursary.student-fee-report')" :current="request()->routeIs('bursary.student-fee-report')" wire:navigate>{{ __('Fee Report') }}</flux:navlist.item>
<flux:navlist.item icon="chart-bar" :href="route('bursary.other-payments-report')" :current="request()->routeIs('bursary.other-payments-report')" wire:navigate>{{ __('Other Payments') }}</flux:navlist.item>
<flux:navlist.item icon="check-circle" :href="route('bursary.approve-payment')" :current="request()->routeIs('bursary.approve-payment')" wire:navigate>{{ __('Manage Payments') }}</flux:navlist.item>
<flux:navlist.item icon="document-magnifying-glass" :href="route('bursary.confirm-payment-ref')" :current="request()->routeIs('bursary.confirm-payment-ref')" wire:navigate>{{ __('Confirm TransRef') }}</flux:navlist.item>
<flux:navlist.item icon="circle-stack" :href="route('bursary.fee-item')" :current="request()->routeIs('bursary.fee-item')" wire:navigate>{{ __('Manage Fee Item') }}</flux:navlist.item>
<flux:navlist.item icon="chart-bar" :href="route('bursary.program-type-fee-item-amount')" :current="request()->routeIs('bursary.program-type-fee-item-amount')" wire:navigate>{{ __('Fee Template') }}</flux:navlist.item>
</flux:navlist.group>
@endrole

@role('super-admin')
<flux:navlist.group heading="Portal Commission" expandable :expanded="false">
<flux:navlist.item icon="presentation-chart-bar" :href="route('bursary.consultant-school-fees-report')" :current="request()->routeIs('bursary.consultant-school-fees-report')" wire:navigate>{{ __('Regular') }}</flux:navlist.item>
    <flux:navlist.item icon="circle-stack" :href="route('bursary.study-center-summary-report')" :current="request()->routeIs('bursary.study-center-summary-report')" wire:navigate>{{ __('e-Learning') }}</flux:navlist.item>
</flux:navlist.group>
@endrole

@role('super-admin')
<flux:navlist.group heading="Administration">
    <flux:navlist.item icon="users" :href="route('admin.users')" :current="request()->routeIs('admin.users')" wire:navigate>
        Manage Users
    </flux:navlist.item>

</flux:navlist.group>

@endrole



</flux:navlist>

<flux:spacer />

<flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
<flux:radio value="light" icon="sun" />
<flux:radio value="dark" icon="moon" />
<flux:radio value="system" icon="computer-desktop" />
</flux:radio.group>

<!-- Desktop User Menu -->
<flux:dropdown class="hidden lg:block" position="bottom" align="start">
<flux:profile
:name="auth()->user()->name"
:initials="auth()->user()->initials()"
icon:trailing="chevrons-up-down"
/>

<flux:menu class="w-[220px]">
<flux:menu.radio.group>
<div class="p-0 text-sm font-normal">
<div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
<span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
<span
class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
>
{{ auth()->user()->initials() }}
</span>
</span>

<div class="grid flex-1 text-start text-sm leading-tight">
<span class="truncate font-semibold">{{ auth()->user()->name }}</span>
<span class="truncate text-xs">{{ auth()->user()->email }}</span>
</div>
</div>
</div>
</flux:menu.radio.group>

<flux:menu.separator />

<flux:menu.radio.group>
<flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
</flux:menu.radio.group>

<flux:menu.separator />

<form method="POST" action="{{ route('logout') }}" class="w-full">
@csrf
<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
{{ __('Log Out') }}
</flux:menu.item>
</form>
</flux:menu>
</flux:dropdown>
</flux:sidebar>

<!-- Mobile User Menu -->
<flux:header class="lg:hidden">
<flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

<flux:spacer />

<flux:dropdown position="top" align="end">
<flux:profile
:initials="auth()->user()->initials()"
icon-trailing="chevron-down"
/>

<flux:menu>
<flux:menu.radio.group>
<div class="p-0 text-sm font-normal">
<div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
<span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
<span
class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
>
{{ auth()->user()->initials() }}
</span>
</span>

<div class="grid flex-1 text-start text-sm leading-tight">
<span class="truncate font-semibold">{{ auth()->user()->name }}</span>
<span class="truncate text-xs">{{ auth()->user()->email }}</span>
</div>
</div>
</div>
</flux:menu.radio.group>

<flux:menu.separator />

<flux:menu.radio.group>
<flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
</flux:menu.radio.group>

<flux:menu.separator />

<form method="POST" action="{{ route('logout') }}" class="w-full">
@csrf
<flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
{{ __('Log Out') }}
</flux:menu.item>
</form>
</flux:menu>
</flux:dropdown>
</flux:header>

{{ $slot }}


@fluxScripts
</body>
</html>
