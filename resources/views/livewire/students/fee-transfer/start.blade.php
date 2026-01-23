<div class="max-w-4xl mx-auto space-y-6">

    <div wire:loading.delay class="text-sm text-neutral-500 dark:text-neutral-400">
    Loading next step…
</div>


    <!-- Step Indicator -->
    <div class="flex gap-2 text-sm">
        @foreach ([1=>'Student',2=>'Context',3=>'Details',4=>'Review'] as $i => $label)
            <span class="px-3 py-1 rounded-full
                {{ $step === $i ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700' }}">
                {{ $label }}
            </span>
        @endforeach
    </div>

        @if ($step > 1)
    <button
        wire:click="goToStep({{ $step - 1 }})"
        class="text-sm text-gray-600 dark:text-gray-300 cursor-pointer hover:underline mb-4 inline-block"
    >
        ← Back
    </button>
@endif

    @if ($completed)
    <div class="bg-green-50 dark:bg-green-900/30 p-6 rounded-xl text-center">
        <h2 class="text-lg font-semibold text-green-800 dark:text-green-200">
            Fee Transfer Successful
        </h2>
        <p class="text-sm text-green-700 dark:text-green-300 mt-2">
            Registration has been unlocked for the selected session.
        </p>

        <a href="{{ route('students.fee-transfer') }}"
           class="inline-block mt-4 text-blue-600 dark:text-blue-400">
            Start another transfer
        </a>
    </div>
@else

    <!-- Step Content -->

    @if ($step === 1)
    <livewire:students.fee-transfer.steps.validate-student
        wire:key="fee-transfer-step-1"
    />
@elseif ($step === 2)
    <livewire:students.fee-transfer.steps.transfer-context
        :student="$student"
        wire:key="fee-transfer-step-2"
    />
@elseif ($step === 3)
    <livewire:students.fee-transfer.steps.transfer-details
        :student="$student"
        :context="$context"
        wire:key="fee-transfer-step-3"
    />
@elseif ($step === 4)
    <livewire:students.fee-transfer.steps.review-and-commit
        :student="$student"
        :context="$context"
        :details="$details"
        wire:key="fee-transfer-step-4"
    />
@endif


@endif
</div>
