<flux:modal
    name="delete-enrolled-student-confirmation"
    class="max-w-lg"
>

    <div class="space-y-6">

        <div>

            <flux:heading size="lg">
                Delete Enrolled Student
            </flux:heading>

            <flux:text class="mt-1">
                This action will remove the student from exceptional enrollment.
            </flux:text>

        </div>


        @if ($deletingStudent)

            <div class="rounded-lg border p-4">

                <flux:text size="sm" class="text-zinc-500">
                    Student
                </flux:text>

                <flux:text class="mt-1 font-medium">
                    {{ $deletingStudent['last_name'] ?? '—' }},  {{ $deletingStudent['first_name'] ?? '—' }} {{ $deletingStudent['middle_name'] ?? '' }}
                </flux:text>

                <flux:text class="mt-1">
                    {{ $deletingStudent['regno'] ?? '—' }}
                </flux:text>

            </div>


            <div class="rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-900 dark:bg-red-950">

                <div class="flex items-start gap-3">

                    <flux:icon
                        name="exclamation-triangle"
                        class="mt-0.5 text-red-600 dark:text-red-400"
                    />

                    <flux:text class="text-red-800 dark:text-red-200">
                        This will permanently remove this exceptional
                        enrollment record from the Student Portal.
                    </flux:text>

                </div>

            </div>

        @endif


        <div class="flex justify-end gap-3">

            <flux:button
                type="button"
                variant="ghost"
                x-on:click="$flux.modal('delete-enrolled-student-confirmation').close()"
                :disabled="$processingDelete"
            >
                Cancel
            </flux:button>

            <flux:button
                type="button"
                variant="danger"
                wire:click="deleteStudent"
                icon="trash"
                class="cursor-pointer"
                :disabled="$processingDelete"
            >

                @if ($processingDelete)
                    Deleting...
                @else
                Delete Student
                @endif

            </flux:button>

        </div>

    </div>

</flux:modal>
