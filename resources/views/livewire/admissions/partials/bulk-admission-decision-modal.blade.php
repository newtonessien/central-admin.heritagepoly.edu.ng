<flux:modal
    wire:model="showBulkAdmissionConfirm"
    class="md:w-[500px]"
>
    <div class="space-y-6">

        <div>
            <flux:heading size="lg">
                Confirm Admission
            </flux:heading>

            <flux:text class="mt-2">
                You are about to admit
                <strong>
                    {{ $this->selectedCandidateCount }}
                </strong>
                candidate(s).
            </flux:text>
        </div>

        <div class="rounded-lg border border-amber-500/30 bg-amber-500/10 p-4">
            <flux:text>
                Each candidate will first be migrated to the Student Portal.
                The admission will only be committed in the Admissions Portal
                after the Student Portal migration succeeds.
            </flux:text>
        </div>

        <div class="flex justify-end gap-3">

            <flux:button
                variant="ghost"
                wire:click="cancelBulkAdmission"
                class="cursor-pointer"
            >
                Cancel
            </flux:button>

            <flux:button
                variant="primary"
                wire:click="confirmBulkAdmission"
                class="cursor-pointer"
                icon="check-badge"
                 >
               <span wire:loading.remove wire:target="confirmBulkAdmission">
               Confirm Admission
                </span>

                <span
                    wire:loading.flex
                    wire:target="confirmBulkAdmission"
                    class="items-center gap-2"
                >
                    Admitting...
                </span>

            </flux:button>

        </div>

    </div>
</flux:modal>
