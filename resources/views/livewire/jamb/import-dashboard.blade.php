<div
    x-data="{
        uploading: false,
        progress: 0
    }"

    x-on:livewire-upload-start="
        uploading = true
    "

    x-on:livewire-upload-finish="
        uploading = false
    "

    x-on:livewire-upload-error="
        uploading = false
    "

    x-on:livewire-upload-progress="
        progress = $event.detail.progress
    "
    class="space-y-6"
>

    {{-- Loading Overlay --}}
    <div
        wire:loading.flex
        wire:target="analyze"
        class="fixed inset-0 z-50 items-center justify-center bg-black/60 backdrop-blur-sm"
    >
        <div
            class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4"
        >

            <div class="flex justify-center mb-5">

                <svg
                    class="animate-spin h-12 w-12 text-green-600"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    ></circle>

                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8v8H4z"
                    ></path>

                </svg>

            </div>

            <h3
                class="text-lg font-semibold text-center text-gray-900 dark:text-white"
            >
                Analyzing JAMB File
            </h3>

            <p
                class="mt-2 text-center text-sm text-gray-600 dark:text-gray-300"
            >
                Please wait...
                Large files may take 1 - 3 minutes.
            </p>

        </div>
    </div>

    {{-- Success Message --}}
    @if(session()->has('success'))

        <div
            class="
                bg-green-100
                dark:bg-green-900/30
                border
                border-green-300
                dark:border-green-700
                text-green-800
                dark:text-green-200
                rounded-xl
                p-4
            "
        >
            {{ session('success') }}
        </div>

    @endif

    {{-- Upload Card --}}
    <div
        class="
            bg-white
            dark:bg-gray-800
            rounded-xl
            shadow
            border
            border-gray-200
            dark:border-gray-700
            p-6
        "
    >

        <div class="flex items-center justify-between mb-4">

            <div>

                <h2
                    class="
                        text-2xl
                        font-bold
                        text-gray-900
                        dark:text-white
                    "
                >
                    JAMB Data Import
                </h2>

                <p
                    class="
                        text-sm
                        text-gray-500
                        dark:text-gray-400
                    "
                >
                    Upload and analyze JAMB admission data.
                </p>

            </div>

        </div>

        <div class="space-y-4">

          <form
    wire:submit.prevent="analyze"
    class="space-y-4"
>

    <input
        type="file"
        wire:model.live="file"
        accept=".xls,.xlsx"
        class="
            w-full
            rounded-lg
            border
            border-gray-300
            dark:border-gray-700
            dark:bg-gray-900
            dark:text-white
            p-3
        "
    >

    @error('file')
        <div
            class="
                text-red-600
                dark:text-red-400
                text-sm
            "
        >
            {{ $message }}
        </div>
    @enderror

    <div
        x-show="uploading"
        x-transition
        class="space-y-2"
    >

        <div
            class="
                w-full
                bg-gray-200
                dark:bg-gray-700
                rounded-full
                h-3
            "
        >
            <div
                class="
                    bg-green-600
                    h-3
                    rounded-full
                "
                :style="'width:' + progress + '%'"
            ></div>
        </div>

        <div
            class="
                text-sm
                text-gray-600
                dark:text-gray-300
            "
        >
            Uploading...
            <span x-text="progress"></span>%
        </div>

    </div>

    <button
        type="submit"
        x-bind:disabled="uploading"
        wire:loading.attr="disabled"
        wire:target="analyze"

        class="
            bg-green-600
            hover:bg-green-700
            disabled:opacity-50
            disabled:cursor-not-allowed
            text-white
            px-5
            py-3
            rounded-lg
            font-medium
            transition
            cursor-pointer
        "
    >

        <span
            wire:loading.remove
            wire:target="analyze"
        >
            Analyze File
        </span>

        <span
            wire:loading
            wire:target="analyze"
        >
            Analyzing...
        </span>

    </button>

</form>

        </div>

    </div>

   {{-- Analysis Results --}}
@if($analysisCompleted)

    <div
        class="
            bg-white
            dark:bg-gray-800
            rounded-xl
            shadow
            border
            border-gray-200
            dark:border-gray-700
            p-6
        "
    >

        <h3
            class="
                text-lg
                font-semibold
                text-gray-900
                dark:text-white
                mb-5
            "
        >
            Analysis Summary
        </h3>

        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-2
                xl:grid-cols-3
                gap-4
            "
        >

            {{-- Total Records --}}
            <div
                class="
                    bg-blue-50
                    dark:bg-blue-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Total Records
                </div>

                <div class="text-2xl font-bold text-blue-600">
                    {{ number_format($analysis['total_records']) }}
                </div>
            </div>

            {{-- Eligible Records --}}
            <div
                class="
                    bg-green-50
                    dark:bg-green-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Eligible Records
                </div>

                <div class="text-2xl font-bold text-green-600">
                    {{ number_format($analysis['eligible_records']) }}
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Direct Entry + UTME ≥ {{ config('admissions.minimum_jamb_score') }}
                </div>
            </div>

            {{-- Below Cutoff --}}
            <div
                class="
                    bg-red-50
                    dark:bg-red-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Below Cutoff
                </div>

                <div class="text-2xl font-bold text-red-600">
                    {{ number_format($analysis['below_cutoff_records']) }}
                </div>

                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    UTME below {{ config('admissions.minimum_jamb_score') }}
                </div>
            </div>

            {{-- Total Courses --}}
            <div
                class="
                    bg-indigo-50
                    dark:bg-indigo-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Total Courses
                </div>

                <div class="text-2xl font-bold text-indigo-600">
                    {{ number_format($analysis['total_courses']) }}
                </div>
            </div>

            {{-- Auto Matched --}}
            <div
                class="
                    bg-green-50
                    dark:bg-green-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Auto Matched
                </div>

                <div class="text-2xl font-bold text-green-600">
                    {{ number_format($analysis['auto_matched_count']) }}
                </div>
            </div>

            {{-- Manual Review --}}
            <div
                class="
                    bg-yellow-50
                    dark:bg-yellow-900/20
                    rounded-xl
                    p-4
                "
            >
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Manual Review
                </div>

                <div class="text-2xl font-bold text-yellow-600">
                    {{ number_format($analysis['manual_review_count']) }}
                </div>
            </div>

        </div>

        {{-- Cutoff Warning --}}
        @if($analysis['below_cutoff_records'] > 0)

            <div
                class="
                    mt-6
                    rounded-xl
                    border
                    border-amber-300
                    dark:border-amber-700
                    bg-amber-50
                    dark:bg-amber-900/20
                    p-4
                "
            >
                <div
                    class="
                        font-semibold
                        text-amber-800
                        dark:text-amber-300
                    "
                >
                    Admission Cutoff Warning
                </div>

                <div
                    class="
                        text-sm
                        text-amber-700
                        dark:text-amber-200
                        mt-1
                    "
                >
                    {{ number_format($analysis['below_cutoff_records']) }}
                    candidate(s) scored below
                    {{ config('admissions.minimum_jamb_score') }}
                    and will be skipped during import.
                </div>
            </div>

        @endif

    </div>

    {{-- Ready Banner --}}
    @if($analysis['manual_review_count'] === 0)

        <div
            class="
                bg-green-100
                dark:bg-green-900/30
                border
                border-green-300
                dark:border-green-700
                text-green-800
                dark:text-green-200
                rounded-xl
                p-4
            "
        >
            ✅ All courses have been mapped successfully.

            @if($analysis['below_cutoff_records'] > 0)

                {{ number_format($analysis['below_cutoff_records']) }}
                candidate(s) will be excluded during import due to the minimum JAMB score requirement.

            @else

                This file is ready for import.

            @endif

        </div>


          <button
                wire:click="import"
                wire:loading.attr="disabled"
                wire:target="import"
                class="
                    bg-green-600
                    hover:bg-green-700
                    text-white
                    px-6
                    py-3
                    rounded-lg
                    font-medium
                "
            >

                <span
                    wire:loading.remove
                    wire:target="import"
                >
                    Import JAMB Data
                </span>

                <span
                    wire:loading
                    wire:target="import"
                >
                    Importing...
                </span>

            </button>


            @if($importResult)

    <div
        class="
            bg-white
            dark:bg-gray-800
            rounded-xl
            shadow
            border
            border-gray-200
            dark:border-gray-700
            p-6
        "
    >

        <h3
            class="
                text-lg
                font-semibold
                mb-4
                text-gray-900
                dark:text-white
            "
        >
            Import Result
        </h3>

        <div
            class="
                grid
                grid-cols-1
                md:grid-cols-4
                gap-4
            "
        >

            <div>
                <div class="text-sm text-gray-500">
                    Total Records
                </div>

                <div class="text-2xl font-bold">
                    {{ number_format($importResult['total_records']) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Imported
                </div>

                <div
                    class="
                        text-2xl
                        font-bold
                        text-green-600
                    "
                >
                    {{ number_format($importResult['imported_records']) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Failed
                </div>

                <div
                    class="
                        text-2xl
                        font-bold
                        text-red-600
                    "
                >
                    {{ number_format($importResult['failed_records']) }}
                </div>
            </div>

            <div>
                <div class="text-sm text-gray-500">
                    Below Cutoff
                </div>

                <div
                    class="
                        text-2xl
                        font-bold
                        text-yellow-600
                    "
                >
                    {{ number_format($importResult['skipped_cutoff']) }}
                </div>
            </div>

        </div>

    </div>

@endif

    @endif



        {{-- Manual Mapping --}}
        @if(count($analysis['manual_review']) > 0)

            <div
                class="
                    bg-white
                    dark:bg-gray-800
                    rounded-xl
                    shadow
                    border
                    border-gray-200
                    dark:border-gray-700
                    p-6
                "
            >

                <h3
                    class="
                        text-lg
                        font-semibold
                        text-gray-900
                        dark:text-white
                        mb-4
                    "
                >
                    Manual Course Mapping
                </h3>

                <div class="space-y-4">

                    @foreach($analysis['manual_review'] as $course)

                        <div>

                            <label
                                class="
                                    block
                                    mb-2
                                    font-medium
                                    text-gray-700
                                    dark:text-gray-300
                                "
                            >
                                {{ $course['course'] }}
                            </label>

                            <select
                                wire:model="manualMappings.{{ $course['course'] }}"
                                class="
                                    w-full
                                    rounded-lg
                                    border
                                    border-gray-300
                                    dark:border-gray-700
                                    dark:bg-gray-900
                                    dark:text-white
                                    p-3
                                "
                            >

                                <option value="">
                                    Select Program
                                </option>

                                @foreach($programs as $program)

                                    <option
                                        value="{{ $program->id }}"
                                    >
                                        {{ $program->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                    @endforeach

                </div>

                <button
                    wire:click="saveMappings"
                    class="
                        mt-6
                        bg-green-600
                        hover:bg-green-700
                        text-white
                        px-5
                        py-3
                        rounded-lg
                        font-medium
                    "
                >
                    Save Mappings
                </button>

            </div>

        @endif

    @endif

</div>
