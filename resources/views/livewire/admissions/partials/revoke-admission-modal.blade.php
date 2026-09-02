<flux:modal
    name="revoke-admission"
    class="md:w-[32rem]"
    wire:model="showRevokeModal"
>
    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-start gap-3">

            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-red-500/10">
                <flux:icon
                    name="arrow-uturn-left"
                    class="size-5 text-red-500"
                />
            </div>

            <div>
                <flux:heading size="lg">
                    Revoke Admission
                </flux:heading>

                <flux:text class="mt-1">
                    Confirm that you want to revoke this candidate's admission.
                </flux:text>
            </div>

        </div>


        {{-- Candidate --}}
        @if ($revokingCandidate)

            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-900/50">

                <div class="space-y-3">

                    <div>
                        <flux:text size="sm" class="text-zinc-500">
                            Candidate
                        </flux:text>

                        <flux:heading size="sm">
                            {{ $revokingCandidate['name'] ?? '—' }}
                        </flux:heading>
                    </div>


                    <div class="grid grid-cols-2 gap-4">

                        <div>
                            <flux:text size="sm" class="text-zinc-500">
                                RegNo
                            </flux:text>

                            <flux:text>
                                {{ $revokingCandidate['regno'] ?? '—' }}
                            </flux:text>
                        </div>


                        <div>
                            <flux:text size="sm" class="text-zinc-500">
                                Programme
                            </flux:text>

                            <flux:text>
                                {{ $revokingCandidate['program'] ?? '—' }}
                            </flux:text>
                        </div>

                    </div>


                    <div>
                        <flux:text size="sm" class="text-zinc-500">
                            Application Type
                        </flux:text>

                        <flux:text>
                            {{ $revokingCandidate['application_type'] ?? '—' }}
                        </flux:text>
                    </div>

                </div>

            </div>

        @endif


        {{-- Warning --}}
        <div class="rounded-lg border border-red-500/20 bg-red-500/5 p-4">

            <div class="flex gap-3">

                <flux:icon
                    name="exclamation-triangle"
                    class="mt-0.5 size-5 shrink-0 text-red-500"
                />

                <div class="space-y-1">

                    <flux:heading size="sm">
                        This action cannot be undone automatically.
                    </flux:heading>

                    <flux:text size="sm">
                        The student's admission record will be removed from
                        the Student Portal. The candidate will then be marked
                        as no longer admitted in the Admissions Portal.
                    </flux:text>

                </div>

            </div>

        </div>


        {{-- Reason --}}
        <flux:field>

            <flux:label>
                Reason for Revocation
                <span class="text-red-500">*</span>
            </flux:label>

            <flux:textarea
                wire:model="revokeReason"
                rows="4"
                placeholder="Enter the reason for revoking this admission..."
                :disabled="$processingRevoke"
            />

            <flux:error name="revokeReason" />

        </flux:field>


        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">

            <flux:button
                type="button"
                variant="ghost"
                wire:click="closeRevokeModal"
                :disabled="$processingRevoke"
            >
                Cancel
            </flux:button>


            <flux:button
                type="button"
                variant="danger"
                icon="arrow-uturn-left"
                class="cursor-pointer"
                wire:click="revokeAdmission"
                wire:loading.attr="disabled"
                wire:target="revokeAdmission"
                :disabled="$processingRevoke"
            >

                <span wire:loading.remove wire:target="revokeAdmission">
                Confirm Revoke
                </span>

                <span
                    wire:loading.flex
                    wire:target="revokeAdmission"
                    class="items-center gap-2"
                >
                    Revoking...
                </span>

            </flux:button>

        </div>

    </div>
</flux:modal>
