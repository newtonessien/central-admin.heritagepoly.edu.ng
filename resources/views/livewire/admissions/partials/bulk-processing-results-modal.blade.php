<flux:modal
wire:model="showBulkAdmissionResults"
class="md:w-[700px]"
>
<div class="space-y-6">

<div>
<flux:heading size="lg">
Admission Processing Results
</flux:heading>

<flux:text class="mt-1">
{{ $this->bulkProcessedCount }}
candidate(s) processed.
</flux:text>
</div>

{{-- Summary --}}

<div class="grid grid-cols-2 gap-4">

<div class="rounded-lg border p-4">
<flux:text>Successful</flux:text>

<div class="mt-1 text-2xl font-semibold">
{{ $this->bulkSuccessfulCount }}
</div>
</div>

<div class="rounded-lg border p-4">
<flux:text>Failed</flux:text>

<div class="mt-1 text-2xl font-semibold">
{{ $this->bulkFailedCount }}
</div>
</div>

</div>

{{-- Successful --}}

@if($this->bulkSuccessfulCount > 0)

<div>
<flux:heading size="sm">
Successfully Admitted
</flux:heading>

<div class="mt-3 space-y-2">

@foreach(
    $bulkAdmissionResults['successful']
    as $result
)

    <div class="flex justify-between rounded-lg border p-3">

        <span>
            {{ $result['regno'] ?? 'Unknown' }}
        </span>

        <flux:badge variant="solid" color="green">
            Admitted
        </flux:badge>

    </div>

@endforeach

</div>
</div>

@endif

{{-- Failed --}}

@if($this->bulkFailedCount > 0)

<div>
<flux:heading size="sm">
Failed
</flux:heading>

<div class="mt-3 space-y-2">

@foreach(
    $bulkAdmissionResults['failed']
    as $result
)

    <div class="rounded-lg border p-3">

        <div class="flex justify-between">

            <span class="font-medium">
                {{ $result['regno'] ?? 'Candidate #' . $result['id'] }}
            </span>

            <flux:badge variant="solid" color="red">
                Failed
            </flux:badge>

        </div>

        <flux:text class="mt-1">
            {{ $result['error'] }}
        </flux:text>

    </div>

@endforeach

</div>
</div>

@endif

<div class="flex justify-end">

<flux:button
variant="ghost"
wire:click="closeBulkAdmissionResults"
class="cursor-pointer"
>
Close
</flux:button>

</div>

</div>
</flux:modal>
