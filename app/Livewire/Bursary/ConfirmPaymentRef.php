<?php
namespace App\Livewire\Bursary;

use Flux\Flux;
use Livewire\Component;
use App\Services\Clients\StudentPortalClient;

class ConfirmPaymentRef extends Component
{
    public $trans_refno;
    public $payment;

    protected $rules = [
        'trans_refno' => 'required|string'
    ];

    public function fetchPaymentByRef(StudentPortalClient $client)
    {
        $this->validate();

        $this->payment = $client->getPaymentByTransRef($this->trans_refno);

        if (!$this->payment) {
           // $this->addError('trans_refno', 'Payment not found or unable to fetch from student portal.');
            Flux::toast('Payment Reference not found or unable to fetch from student portal', variant: 'warning', position: 'top-right', duration: 4000);
             return;
        }




    }

    public function render()
    {
        return view('livewire.bursary.confirm-payment-ref');
    }
}

