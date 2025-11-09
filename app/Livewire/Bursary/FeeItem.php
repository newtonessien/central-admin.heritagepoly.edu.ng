<?php

namespace App\Livewire\Bursary;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Log;
use App\Services\Clients\StudentPortalClient;

class FeeItem extends Component
{
    public $search = '';
    public $name, $code, $category_id, $is_recurring = true, $is_active = true;
    public $fee_item_id;
    public $editing = false;
    public $categories = [];
    public $feeItems = [];

    public $currentPage = 1;
    public $perPage = 10;
    public $lastPage = 1;
    public $total = 0;

    /** @var StudentPortalClient */
    protected $client;

    public function mount(StudentPortalClient $client)
    {
        $this->client = $client;
        $this->loadCategories();
        $this->loadFeeItems();
    }

    public function hydrate(StudentPortalClient $client)
    {
        $this->client = $client;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetPage()
    {
        $this->currentPage = 1;
        $this->loadFeeItems();
    }

    public function loadFeeItems()
    {
        try {
            $params = [
                'search' => $this->search,
                'page' => $this->currentPage,
                'per_page' => $this->perPage,
            ];

            $response = $this->client->fetchFeeItems($params);

            $this->feeItems = $response['data'] ?? [];
            $this->currentPage = $response['meta']['current_page'] ?? 1;
            $this->lastPage = $response['meta']['last_page'] ?? 1;
            $this->total = $response['meta']['total'] ?? count($this->feeItems);

        } catch (\Throwable $e) {
            Log::error('Failed to load fee items', ['error' => $e->getMessage()]);
            $this->feeItems = [];
            //session()->flash('error', 'Unable to load fee items.');
            Flux::toast('Unable to load fee items', variant: 'warning', position: 'top-right', duration: 4000);

        }
    }

    public function loadCategories()
    {
        try {
            $resp = $this->client->fetchFeeItemCategories();
            $this->categories = $resp['data'] ?? $resp ?? [];
        } catch (\Throwable $e) {
            Log::error('Failed to load fee item categories', ['error' => $e->getMessage()]);
            $this->categories = [];
        }
    }

    public function createFeeItem()
    {
        $payload = [
            'name' => $this->name,
            'code' => $this->code,
            'category_id' => $this->category_id,
            'is_recurring' => (bool) $this->is_recurring,
            'is_active' => (bool) $this->is_active,
        ];

        try {
            $this->client->createFeeItem($payload);
           // session()->flash('success', 'Fee item created successfully.');
            Flux::toast('Fee item created successfully.', variant: 'success', position: 'top-right', duration: 4000);
            $this->resetForm();
            $this->loadFeeItems();
        } catch (\Throwable $e) {
            Log::error('Create Fee Item failed', ['error' => $e->getMessage()]);
            //session()->flash('error', 'Failed to create fee item: ' . $e->getMessage());
            Flux::toast('Failed to create fee item: ' . $e->getMessage(), variant: 'warning', position: 'top-right', duration: 4000);

        }
    }

    public function editFeeItem($id)
    {
        $item = collect($this->feeItems)->firstWhere('id', $id);
        if (! $item) return;

        $this->editing = true;
        $this->fee_item_id = $id;
        $this->name = $item['name'];
        $this->code = $item['code'];
        $this->category_id = $item['category_id'];
        $this->is_recurring = (bool) ($item['is_recurring'] ?? true);
        $this->is_active = (bool) ($item['is_active'] ?? true);
    }

    public function updateFeeItem()
    {
        try {
            $payload = [
                'name' => $this->name,
                'code' => $this->code,
                'category_id' => $this->category_id,
                'is_recurring' => (bool) $this->is_recurring,
                'is_active' => (bool) $this->is_active,
            ];

            $this->client->updateFeeItem($this->fee_item_id, $payload);
            //session()->flash('success', 'Fee item updated successfully.');
            Flux::toast('Fee item updated successfully', variant: 'success', position: 'top-right', duration: 4000);
            $this->resetForm();
            $this->loadFeeItems();

        } catch (\Throwable $e) {
            Log::error('Update Fee Item failed', ['error' => $e->getMessage()]);
           // session()->flash('error', 'Failed to update fee item: ' . $e->getMessage());
            Flux::toast('Failed to update fee item: ' . $e->getMessage(), variant: 'warning', position: 'top-right', duration: 4000);
               }
    }

    public function deleteFeeItem($id)
    {
        try {
            $this->client->deleteFeeItem($id);
            //session()->flash('success', 'Fee item deleted.');

            Flux::toast('Fee item deleted successfully', variant: 'success', position: 'top-right', duration: 4000);
            $this->loadFeeItems();
        } catch (\Throwable $e) {
            Log::error('Delete Fee Item failed', ['error' => $e->getMessage()]);
            //session()->flash('error', 'Failed to delete fee item: ' . $e->getMessage());
            Flux::toast('Failed to delete fee item: ' . $e->getMessage(), variant: 'warning', position: 'top-right', duration: 4000);

        }
    }

    public function resetForm()
    {
        $this->reset(['name', 'code', 'category_id', 'is_recurring', 'is_active', 'editing', 'fee_item_id']);
    }

    public function goToPage($page)
    {
        if ($page >= 1 && $page <= $this->lastPage) {
            $this->currentPage = $page;
            $this->loadFeeItems();
        }
    }

    public function getHasNextPageProperty()
    {
        return $this->currentPage < $this->lastPage;
    }

    public function getHasPreviousPageProperty()
    {
        return $this->currentPage > 1;
    }

    public function render()
    {
        return view('livewire.bursary.fee-items', [
            'feeItems' => $this->feeItems,
            'categories' => $this->categories,
            'currentPage' => $this->currentPage,
            'lastPage' => $this->lastPage,
            'total' => $this->total,
            'hasNextPage' => $this->hasNextPage,
            'hasPreviousPage' => $this->hasPreviousPage,
        ]);
    }
}
