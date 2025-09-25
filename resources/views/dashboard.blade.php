<x-layouts.app :title="__('Dashboard')">

    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-100">Central Admin Dashboard</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Quick access to all administrative modules</p>
        </div>
        <div>
            <a href="/settings/profile"
               class="inline-flex items-center rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700">
                Settings
            </a>
        </div>
    </div>

    <!-- Grid (6 modules, 2 rows) -->
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

        <!-- Admissions -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v7m0-7L3 9m9 5l9-5" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Admissions</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage admission processes</p>
            </div>
        </div>

        <!-- Student Portal -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9 9 0 1112 21a9 9 0 01-6.879-3.196z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Student Portal</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Control student records</p>
            </div>
        </div>

        <!-- Ebursary -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.657 0 3-.895 3-2s-1.343-2-3-2-3 .895-3 2 1.343 2 3 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Ebursary</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Manage finance & payments</p>
            </div>
        </div>

        <!-- Reports -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6m4 6v-4m4 4v-2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Reports</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">View analytics & metrics</p>
            </div>
        </div>

        <!-- Settings -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.983 2.021a1 1 0 011.034 0l1.342.774a1 1 0 01.45.902v1.548a7.96 7.96 0 012.01 1.165l1.463-.365a1 1 0 011.19.591l.774 1.342a1 1 0 01-.452 1.33l-1.262.727c.087.324.151.658.188 1.002h1.548a1 1 0 01.902.45l.774 1.342a1 1 0 01-.59 1.19l-1.464.365a7.96 7.96 0 01-1.165 2.01l.365 1.463a1 1 0 01-.59 1.19l-1.342.774a1 1 0 01-1.33-.452l-.727-1.262a7.96 7.96 0 01-1.002.188v1.548a1 1 0 01-.45.902l-1.342.774a1 1 0 01-1.19-.59l-.365-1.464a7.96 7.96 0 01-2.01-1.165l-1.463.365a1 1 0 01-1.19-.59l-.774-1.342a1 1 0 01.452-1.33l1.262-.727a7.96 7.96 0 01-.188-1.002H7.5a1 1 0 01-.902-.45l-.774-1.342a1 1 0 01.59-1.19l1.464-.365a7.96 7.96 0 011.165-2.01l-.365-1.463a1 1 0 01.59-1.19l1.342-.774z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Settings</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">System configurations</p>
            </div>
        </div>

        <!-- Admin Management -->
        <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700
                    flex flex-col items-center justify-center bg-white dark:bg-neutral-700 hover:shadow-lg transition">
            <div class="flex flex-col items-center text-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-600 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0-1.105-.895-2-2-2s-2 .895-2 2 .895 2 2 2 2-.895 2-2-.895-2-2-2-2 .895-2 2zm0 0v4m-4 4h8m-4-4v4" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-100">Admin Management</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Roles & permissions</p>
            </div>
        </div>

    </div>
</div>



</x-layouts.app>
