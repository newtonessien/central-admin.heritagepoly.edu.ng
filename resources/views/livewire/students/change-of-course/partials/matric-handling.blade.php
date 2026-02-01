<div class="rounded-xl border border-neutral-200 dark:border-neutral-800 bg-white dark:bg-neutral-900 p-5 space-y-4">

<div>
<h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">
Matriculation Number Handling
</h3>
<p class="text-xs text-neutral-600 dark:text-neutral-400 mt-1">
This student already has a matriculation number. Select how the system should proceed.
</p>
</div>

<div class="space-y-3">

<label class="flex gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-800
hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer">
<input type="radio" wire:model="matricAction" value="assign_new" class="mt-1" />
<div>
<div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
Assign new matric number
</div>
<div class="text-xs text-neutral-600 dark:text-neutral-400">
Recommended. A new matric number will be generated in the new department.
</div>
</div>
</label>

<label class="flex gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-800
hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer">
<input type="radio" wire:model="matricAction" value="reset" class="mt-1" />
<div>
<div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
Reset matric number
</div>
<div class="text-xs text-neutral-600 dark:text-neutral-400">
Student will regenerate a matric number later.
</div>
</div>
</label>

<label class="flex gap-3 p-3 rounded-lg border border-neutral-200 dark:border-neutral-800
hover:bg-neutral-50 dark:hover:bg-neutral-800/50 cursor-pointer">
<input type="radio" wire:model="matricAction" value="keep" class="mt-1" />
<div>
<div class="text-sm font-medium text-neutral-900 dark:text-neutral-100">
Keep existing matric number
</div>
<div class="text-xs text-neutral-600 dark:text-neutral-400">
No changes will be made.
</div>
</div>
</label>

</div>
</div>
