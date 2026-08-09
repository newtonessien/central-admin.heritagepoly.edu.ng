<div class="flex min-h-[75vh] items-center justify-center px-4">
<div class="w-full max-w-5xl space-y-8">

{{-- Header --}}
<div class="text-center">
<flux:heading size="xl">
Student 360° Profile Finder
</flux:heading>

<flux:text class="mt-3 max-w-2xl mx-auto">
Search for a student using their Registration Number to access the complete
<span class="font-medium">Student 360° Profile</span>.
</flux:text>
</div>

{{-- Search Card --}}
<flux:card class="mx-auto max-w-2xl">
<div class="space-y-6">

<flux:input
wire:model.defer="regno"
wire:keydown.enter="findStudent"
label="Registration Number"
placeholder="e.g. CSC/2023/00125"
autocomplete="off"
icon="magnifying-glass"
/>

@error('regno')
<div class="text-sm text-red-600">
{{ $message }}
</div>
@enderror

<div class="flex justify-center gap-3">
<flux:button
wire:click="findStudent"
variant="primary"
class="cursor-pointer"
icon="magnifying-glass">
Find Student
</flux:button>

<flux:button
wire:click="resetSearch"
class="cursor-pointer"
variant="ghost">
Reset
</flux:button>
</div>

</div>
</flux:card>

{{-- Features --}}
<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">

<flux:card>
<div class="flex flex-col items-center text-center space-y-3">
<div class="rounded-full bg-blue-100 dark:bg-blue-900/30 p-3">
<flux:icon.user class="size-6 text-blue-600" />
</div>

<flux:heading size="sm">
Student Overview
</flux:heading>

<flux:text size="sm">
Personal information, programme, faculty, department and status.
</flux:text>
</div>
</flux:card>


<flux:card>
<div class="flex flex-col items-center text-center space-y-3">
<div class="rounded-full bg-emerald-100 dark:bg-emerald-900/30 p-3">
<flux:icon.arrow-path-rounded-square class="size-6 text-emerald-600" />
</div>

<flux:heading size="sm">
Payment History
</flux:heading>

<flux:text size="sm">
View all payment transactions, receipts and history of payments made by the student.
</flux:text>
</div>
</flux:card>

<flux:card>
<div class="flex flex-col items-center text-center space-y-3">
<div class="rounded-full bg-emerald-100 dark:bg-emerald-900/30 p-3">
<flux:icon.academic-cap class="size-6 text-emerald-600" />
</div>

<flux:heading size="sm">
Course Registration History
</flux:heading>

<flux:text size="sm">
Browse all registered courses and semester enrolments.
</flux:text>
</div>
</flux:card>



<flux:card>
<div class="flex flex-col items-center text-center space-y-3">
<div class="rounded-full bg-violet-100 dark:bg-violet-900/30 p-3">
<flux:icon.chart-bar-square class="size-6 text-violet-600" />
</div>

<flux:heading size="sm">
Academic Results
</flux:heading>

<flux:text size="sm">
See the student's academic performance and results history.
</flux:text>
</div>
</flux:card>

</div>

</div>
</div>
