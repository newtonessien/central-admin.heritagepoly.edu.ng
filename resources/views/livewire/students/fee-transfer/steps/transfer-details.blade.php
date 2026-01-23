<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm space-y-6 border border-gray-200 dark:border-gray-700">

    <div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Transfer Details</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">Enter the transfer amount and description</p>
    </div>

    <div class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Amount
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 dark:text-gray-400">₦</span>
                </div>
                <input
                    type="number"
                    wire:model.defer="amount"
                    class="pl-9 w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500"
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                />
            </div>
            @error('amount')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Description
            </label>
            <textarea
                wire:model.defer="description"
                rows="3"
                class="w-full px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-green-500 dark:focus:ring-green-500 focus:border-green-500 dark:focus:border-green-500"
                placeholder="Enter description (e.g., Reason for transfer/Reference number)"
            ></textarea>
        </div>
    </div>

    <button
        wire:click="proceed"
        wire:loading.attr="disabled"
        class="cursor-pointer w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 text-white font-medium rounded-md transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <span wire:loading.remove>Continue</span>
        <span wire:loading>Processing...</span>
    </button>

</div>
