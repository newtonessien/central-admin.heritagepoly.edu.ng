<?php

namespace App\Livewire\Students\AcademicRequests;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\Clients\StudentPortalClient;

class TransferRequests extends Component
{
    public array $requests = [];
    public ?array $selectedRequest = null;
    public bool $showApproveModal = false;

    public function mount()
    {
        $this->loadRequests();
    }

    protected function client(): StudentPortalClient
    {
        return app(StudentPortalClient::class);
    }

    protected function loadRequests(): void
    {
        $this->requests = $this->client()->getPendingTransfers();
        //dd($this->requests);

    }

    public function openApproveModal(int $id): void
    {

        $this->selectedRequest = $this->client()->getTransferRequest($id);
        $this->showApproveModal = true;
    }

    public function approve(): void
    {
        if (!$this->selectedRequest) {
            return;
        }

        $this->client()->approveTransfer(
            $this->selectedRequest['id'],
            Auth::user()->email
        );

        $this->showApproveModal = false;
        $this->selectedRequest = null;

        Flux::toast(
            'Transfer approved successfully.',
            variant: 'success'
        );

        $this->loadRequests();
    }

    public function render()
    {
        return view('livewire.students.academic-requests.transfer-requests');
    }
}
