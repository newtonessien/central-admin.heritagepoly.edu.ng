<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Fee Item Templates</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Manage base fee amounts by program type, faculty, department, level, and academic session
            </p>
        </div>
        <button
            type="button"
            wire:click="createNew"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
            Add New Template
        </button>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow p-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Filter Templates</h2>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
            <!-- Program Type Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Program Type
                </label>
                <select
                    wire:model.live="filters.program_type_id"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Program Types</option>
                    @foreach($programTypes as $programType)
                        @if(isset($programType['id']) && isset($programType['name']))
                            <option value="{{ $programType['id'] }}">{{ $programType['name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Faculty Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Faculty
                </label>
                <select
                    wire:model.live="filters.faculty_id"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Faculties</option>
                    @foreach($faculties as $faculty)
                        @if(isset($faculty['id']) && isset($faculty['name']))
                            <option value="{{ $faculty['id'] }}">{{ $faculty['name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Department
                </label>
                <select
                    wire:model.live="filters.department_id"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @if(empty($filters['faculty_id'])) disabled @endif
                >
                    <option value="">All Departments</option>
                    @foreach($departments as $department)
                        @if(isset($department['id']) && isset($department['name']))
                            <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                        @endif
                    @endforeach
                </select>
                @if(empty($filters['faculty_id']))
                    <p class="text-xs text-gray-500 mt-1">Select faculty first</p>
                @endif
            </div>

            <!-- Level Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Level
                </label>
                <select
                    wire:model.live="filters.level_id"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Levels</option>
                    @foreach($levels as $level)
                        @if(isset($level['id']) && isset($level['name']))
                            <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Academic Session Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Academic Session
                </label>
                <select
                    wire:model.live="filters.acad_session_id"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option value="">All Sessions</option>
                    @foreach($acadSessions as $session)
                        @if(isset($session['id']) && isset($session['name']))
                            <option value="{{ $session['id'] }}">{{ $session['name'] }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Clear Filters -->
            <div class="flex items-end">
                <button
                    type="button"
                    wire:click="clearAllFilters"
                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                    Clear Filters
                </button>
            </div>
        </div>
    </div>

    <!-- Show filter status indicator -->
    @if($this->hasActiveFilters())
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2 text-sm text-blue-700 dark:text-blue-300">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                <span>Filters applied</span>
            </div>
            <button
                wire:click="clearAllFilters"
                class="text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 underline"
            >
                Clear all filters
            </button>
        </div>
    </div>
    @endif

    <!-- Records Table -->
    <div class="bg-white dark:bg-zinc-900 rounded-xl shadow">
        <div class="p-4 border-b border-gray-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                    @if($this->hasActiveFilters())
                        Filtered Fee Templates
                    @else
                        All Fee Templates
                    @endif
                    <span class="text-sm font-normal text-gray-500 ml-2">
                        ({{ count($records) }} shown)
                    </span>
                </h2>
                @if($this->shouldShowPagination())
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Page {{ $currentPage }} of {{ $lastPage }}
                        @if($this->hasActiveFilters())
                            <span class="text-blue-600">(filtered)</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Program Type
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Fee Item
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Faculty
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Department
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Level
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Acad Session
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            1st Semester
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            2nd Semester
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-zinc-900 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($records as $record)
                        <tr class="hover:bg-gray-50 dark:hover:bg-zinc-800">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $record['program_type']['name'] ?? 'All' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record['fee_item']['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record['faculty']['name'] ?? 'All' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record['department']['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record['level']['name'] ?? 'All' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                {{ $record['acad_session']['name'] ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                ₦{{ number_format($record['first_semester_amount'] ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900 dark:text-white">
                                ₦{{ number_format($record['second_semester_amount'] ?? 0, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <button
                                    type="button"
                                    wire:click="edit({{ $record['id'] ?? 0 }})"
                                    class="px-3 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    Edit
                                </button>
                                <button
                                    type="button"
                                    wire:click="delete({{ $record['id'] ?? 0 }})"
                                    wire:confirm="Are you sure you want to delete this fee template?"
                                    class="px-3 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    No fee templates found.
                                    @if(array_filter($filters))
                                        Try adjusting your filters or
                                    @endif
                                    <button
                                        type="button"
                                        wire:click="createNew"
                                        class="mt-2 text-blue-600 hover:text-blue-700 focus:outline-none"
                                    >
                                        create a new template
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination - Show whenever we have multiple pages (with or without filters) -->
        @if($this->shouldShowPagination())
        <div class="bg-white dark:bg-zinc-900 px-6 py-4 border-t border-gray-200 dark:border-zinc-700">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing <span class="font-medium">{{ $from }}</span> to <span class="font-medium">{{ $to }}</span> of
                    <span class="font-medium">{{ $total }}</span> results
                    @if($this->hasActiveFilters())
                        <span class="text-blue-600 dark:text-blue-400">(filtered)</span>
                    @endif
                </div>
                <div class="flex items-center space-x-2">
                    <!-- Previous Page -->
                    <button
                        wire:click="previousPage"
                        @if($currentPage <= 1) disabled @endif
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Previous
                    </button>

                    <!-- Page Numbers -->
                    @php
                        // Show limited page numbers for better UX
                        $startPage = max(1, $currentPage - 2);
                        $endPage = min($lastPage, $currentPage + 2);
                    @endphp

                    @if($startPage > 1)
                        <button
                            wire:click="gotoPage(1)"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-700"
                        >
                            1
                        </button>
                        @if($startPage > 2)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                    @endif

                    @for($page = $startPage; $page <= $endPage; $page++)
                        @if($page == $currentPage)
                            <span class="px-3 py-1 text-sm bg-blue-600 text-white rounded">
                                {{ $page }}
                            </span>
                        @else
                            <button
                                wire:click="gotoPage({{ $page }})"
                                class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-700"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endfor

                    @if($endPage < $lastPage)
                        @if($endPage < $lastPage - 1)
                            <span class="px-2 text-gray-500">...</span>
                        @endif
                        <button
                            wire:click="gotoPage({{ $lastPage }})"
                            class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-700"
                        >
                            {{ $lastPage }}
                        </button>
                    @endif

                    <!-- Next Page -->
                    <button
                        wire:click="nextPage"
                        @if($currentPage >= $lastPage) disabled @endif
                        class="px-3 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-zinc-700 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Create/Edit Modal -->
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm">
            <div class="bg-white dark:bg-zinc-900 rounded-xl shadow-2xl w-full max-w-4xl mx-4 max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b border-gray-200 dark:border-zinc-700">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ $form['id'] ? 'Edit Fee Template' : 'Create New Fee Template' }}
                    </h2>
                </div>

                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <!-- Program Type (Required) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Program Type *
                            </label>
                            <select
                                wire:model="form.program_type_id"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Program Type</option>
                                @foreach($programTypes as $programType)
                                    @if(isset($programType['id']) && isset($programType['name']))
                                        <option value="{{ $programType['id'] }}">{{ $programType['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Fee Item (Required) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Fee Item *
                            </label>
                            <select
                                wire:model="form.fee_item_id"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Fee Item</option>
                                @foreach($feeItems as $feeItem)
                                    @if(isset($feeItem['id']) && isset($feeItem['name']))
                                        <option value="{{ $feeItem['id'] }}">{{ $feeItem['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Faculty (Required) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Faculty *
                            </label>
                            <select
                                wire:model.live="form.faculty_id"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Faculty</option>
                                @foreach($faculties as $faculty)
                                    @if(isset($faculty['id']) && isset($faculty['name']))
                                        <option value="{{ $faculty['id'] }}">{{ $faculty['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Department (Optional) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Department (Optional)
                            </label>
                            <select
                                wire:model="form.department_id"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                @if(empty($form['faculty_id'])) disabled @endif
                            >
                                <option value="">All Departments</option>
                                @foreach($departments as $department)
                                    @if(isset($department['id']) && isset($department['name']))
                                        <option value="{{ $department['id'] }}">{{ $department['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                            @if(empty($form['faculty_id']))
                                <p class="text-xs text-gray-500 mt-1">Select faculty first</p>
                            @endif
                        </div>



                        <!-- Level (Required) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Level *
                            </label>
                            <select
                                wire:model="form.level_id"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Level</option>
                                @foreach($levels as $level)
                                    @if(isset($level['id']) && isset($level['name']))
                                        <option value="{{ $level['id'] }}">{{ $level['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- Academic Session (Required) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Academic Session *
                            </label>
                            <select
                                wire:model="form.acad_session_id"
                                required
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Select Session</option>
                                @foreach($acadSessions as $session)
                                    @if(isset($session['id']) && isset($session['name']))
                                        <option value="{{ $session['id'] }}">{{ $session['name'] }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <!-- First Semester Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                First Semester Amount
                            </label>
                            <input
                                type="number"
                                wire:model="form.first_semester_amount"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <!-- Second Semester Amount -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                Second Semester Amount
                            </label>
                            <input
                                type="number"
                                wire:model="form.second_semester_amount"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2 bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-zinc-700">
                        <button
                            type="button"
                            wire:click="$set('showForm', false)"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            {{ $form['id'] ? 'Update Template' : 'Create Template' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
