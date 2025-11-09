<div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow-lg space-y-8 transition">

    <!-- Form Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">
            {{ $editing ? 'Edit Fee Item' : 'Add New Fee Item' }}
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input
                type="text"
                wire:model="name"
                placeholder="Name"
                class="border dark:border-gray-700 rounded-lg p-2 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
            >

            <input
                type="text"
                wire:model="code"
                placeholder="Code (optional)"
                class="border dark:border-gray-700 rounded-lg p-2 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
            >

            <select
                wire:model="category_id"
                class="border dark:border-gray-700 rounded-lg p-2 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
            >
                <option value="">-- Select Category --</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex flex-wrap gap-6 items-center pt-2">
            <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="is_recurring" class="rounded">
                <span>Recurring</span>
            </label>

            <label class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
                <input type="checkbox" wire:model="is_active" class="rounded">
                <span>Active</span>
            </label>

            <div class="flex gap-3">
                @if ($editing)
                    <button
                        wire:click="updateFeeItem"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm cursor-pointer"
                    >
                        Update
                    </button>
                    <button
                        wire:click="resetForm"
                        class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg shadow-sm cursor-pointer"
                    >
                        Cancel
                    </button>
                @else
                    <button
                        wire:click="createFeeItem"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm cursor-pointer"
                    >
                        Save
                    </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Search & Controls -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-6 border-t border-gray-200 dark:border-gray-700 pt-4">
        <input
            type="text"
            wire:model.live="search"
            placeholder="Search fee items..."
            class="border dark:border-gray-700 rounded-lg p-2 w-full sm:w-1/3 dark:bg-gray-800 dark:text-gray-100 focus:ring-2 focus:ring-blue-500"
        >

        <button
            wire:click="$set('editing', false)"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg shadow-sm cursor-pointer"
        >
            + Add New
        </button>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Name</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Code</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Category</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Recurring</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Active</th>
                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($feeItems as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                        <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $item['name'] }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $item['code'] ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $item['category']['name'] ?? 'N/A' }}</td>
                        <td class="px-4 py-2">
                            <span class="{{ $item['is_recurring'] ? 'text-green-600 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $item['is_recurring'] ? 'Yes' : 'No' }}
                            </span>
                        </td>
                        <td class="px-4 py-2">
                            <span class="{{ $item['is_active'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $item['is_active'] ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-4 py-2 space-x-3">
                            <button
                                wire:click="editFeeItem({{ $item['id'] }})"
                                class="text-blue-600 dark:text-blue-400 hover:underline cursor-pointer"
                            >
                                Edit
                            </button>
                            <button
                                wire:click="deleteFeeItem({{ $item['id'] }})"
                                class="text-red-600 dark:text-red-400 hover:underline cursor-pointer"
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500 dark:text-gray-400">
                            No fee items found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6 flex flex-wrap justify-between items-center gap-3">
        <button
            wire:click="goToPage({{ $currentPage - 1 }})"
            @disabled(! $hasPreviousPage)
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg disabled:opacity-50 cursor-pointer"
        >
            ‹ Prev
        </button>

        <span class="text-sm text-gray-700 dark:text-gray-300">
            Page {{ $currentPage }} of {{ $lastPage }} — Total {{ $total }}
        </span>

        <button
            wire:click="goToPage({{ $currentPage + 1 }})"
            @disabled(! $hasNextPage)
            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg disabled:opacity-50 cursor-pointer"
        >
            Next ›
        </button>
    </div>
</div>
