<div class="max-w-xl mx-auto mt-10">
    <flux:card>
        <div class="p-6 space-y-6">
            <h2 class="text-lg font-bold">Change Candidate Application Type</h2>

            <form wire:submit.prevent="update" class="space-y-4">
                <!-- Candidate RegNo -->
                <flux:input
                    label="Candidate RegNo"
                    placeholder="Enter candidate RegNo"
                    wire:model.defer="regno"
                    class="w-full"
                />

                <!-- Application Type -->
                <flux:select
                    label="New Application Type"
                    wire:model.defer="application_type_id"
                    placeholder="-Select New application type-"
                    class="w-full"
                    variant="listbox" searchable indicator="checkbox"
                >
                {{-- <flux:select.option value="">-Select Application Type-</flux:select.option> --}}
                    @foreach($applicationTypes as $type)
                        <flux:select.option value="{{ $type['id'] }}">
                            {{ $type['name'] }}
                        </flux:select.option>
                    @endforeach
                </flux:select>



                <!-- Submit -->
                <div class="flex justify-end">
                    <flux:button type="submit" variant="primary" spinner class="cursor-pointer">
                        Update Application Type
                    </flux:button>
                </div>
            </form>
        </div>
    </flux:card>
</div>
