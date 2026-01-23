<?php

namespace App\Livewire\Students\FeeTransfer\Steps;

use Livewire\Component;

class TransferDetails extends Component
{
    public $amount;
    public $description;
    public array $student;

    public function mount(array $student)
{
    $this->student = $student;
   
}

    public function proceed()
    {
        $this->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:1000',
        ]);

        $this->dispatch('detailsCompleted', [
            'amount' => $this->amount,
            'description' => $this->description,
        ]);
    }

    public function render()
    {
        return view('livewire.students.fee-transfer.steps.transfer-details');
    }
}
