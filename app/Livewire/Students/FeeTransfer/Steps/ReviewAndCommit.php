<?php

namespace App\Livewire\Students\FeeTransfer\Steps;

use Livewire\Component;
use App\Services\Clients\StudentPortalClient;
use Illuminate\Support\Facades\Auth;

class ReviewAndCommit extends Component
{
    public array $student;
    public array $context;
    public array $details;

    public function mount(array $student, array $context, array $details)
    {

        $this->student = $student;
        $this->context = $context;
        $this->details = $details;
    }



    public function commitData()
    {
        //dd('commit called');
        $this->dispatch('lock-ui');

        app(StudentPortalClient::class)->createFeeTransfer([
            'regno' => $this->student['regno'],
            'amount' => $this->details['amount'],
            'acad_session_id' => $this->context['acad_session_id'],
            'semester' => $this->context['semester'],
            'level_id' => $this->context['level_id'],
            //'program_type_id' => $this->student['program_type_id'],
            'payment_source_type_id' => $this->context['payment_source_type_id'],
            'description' => $this->details['description'],
            'approved_by_email' => Auth::user()->email,
        ]);

        $this->dispatch('transferCompleted');
    }

    public function render()
    {
        return view('livewire.students.fee-transfer.steps.review-and-commit');
    }
}

