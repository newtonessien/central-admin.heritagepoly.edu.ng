<flux:card>

    <flux:heading size="lg">

        Student Overview

    </flux:heading>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mt-6">

        <div>
            <div class="text-sm text-zinc-500">
                Registration Number
            </div>

            <div class="font-semibold">
                {{ $student['regno'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Matric Number
            </div>

            <div class="font-semibold">
                {{ $student['matric_no'] ?: '-' }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Gender
            </div>

            <div class="font-semibold">
                {{ $student['sex'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Faculty
            </div>

            <div class="font-semibold">
                {{ $student['faculty'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Department
            </div>

            <div class="font-semibold">
                {{ $student['department'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Programme
            </div>

            <div class="font-semibold">
                {{ $student['program'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Programme Type
            </div>

            <div class="font-semibold">
                {{ $student['program_type'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Academic Session
            </div>

            <div class="font-semibold">
                {{ $student['acad_session'] }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                Email
            </div>

            <div class="font-semibold">
                {{ $student['email'] ?: '-' }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                JAMB Number
            </div>

            <div class="font-semibold">
                {{ $student['jamb_no'] ?: '-' }}
            </div>
        </div>

        <div>
            <div class="text-sm text-zinc-500">
                JAMB Score
            </div>

            <div class="font-semibold">
                {{ $student['jamb_score'] ?: '-' }}
            </div>
        </div>

    </div>

</flux:card>
