<div class="max-w-xl mx-auto bg-white dark:bg-gray-900 shadow-md
rounded-lg p-6 space-y-6">

<!-- Header -->
<div class="border-b border-gray-200 dark:border-gray-700 pb-4">
    <h2 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">
        Student Email Reset
    </h2>

    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
        Lookup a student and update their email address securely.
    </p>

</div>


<!-- Success Alert -->
@if(session()->has('success'))
<div class="bg-green-50 dark:bg-green-900/20
border border-green-200 dark:border-green-700
text-green-700 dark:text-green-400
p-3 rounded-lg text-sm">
✔ {{ session('success') }}
</div>
@endif

<!-- Student Search -->
<div class="space-y-2">

<flux:input type="text" label="Registration Number/Matric Number"  wire:model.defer="regno" placeholder="Enter Registration Number or Matric Number"/>


<flux:button variant="primary" wire:click="findStudent" icon="magnifying-glass-circle" class="cursor-pointer">Get Student</flux:button>

</div>

<!-- Student Card -->
@if($student)
<div class="border border-gray-200 dark:border-gray-700
rounded-lg p-4
bg-gray-50 dark:bg-gray-800
space-y-4">

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

    <div class="bg-white dark:bg-gray-900
                border border-gray-200 dark:border-gray-700
                rounded-md px-3 py-2">

        <span class="text-xs uppercase tracking-wide
                    text-gray-500 dark:text-gray-400 block">
            Full Name
        </span>

        <p class="font-semibold text-gray-800 dark:text-white">
            {{ $student['name'] }}
        </p>

    </div>

    <div class="bg-white dark:bg-gray-900
                border border-gray-200 dark:border-gray-700
                rounded-md px-3 py-2">

        <span class="text-xs uppercase tracking-wide
                    text-gray-500 dark:text-gray-400 block">
            Current Email
        </span>

        <p class="font-semibold text-gray-800 dark:text-white break-all">
            {{ $student['email'] }}
        </p>

    </div>

</div>


<flux:separator />

<!-- New Email -->
<div class="space-y-2">
<flux:input type="text" label="New Email Address"  wire:model.defer="newEmail" placeholder="Enter new email address"/>
</div>


<flux:button variant="primary" color="lime" wire:click="resetEmail" icon="arrow-path" class="cursor-pointer">Reset Email & Sent Verification Link</flux:button>

</div>
@endif

</div>
