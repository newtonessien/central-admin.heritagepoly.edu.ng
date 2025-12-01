<?php

namespace App\Livewire\Students;

use Flux\Flux;
use Livewire\Component;
use App\Services\Clients\StudentPortalClient;

class ResetStudentEmail extends Component
{
    public $regno;
    public $student = null;
    public $newEmail;
    public $error;

    public function findStudent(StudentPortalClient $client)
    {
           //add validation
        $this->validate([
            'regno' => 'required|string'
        ]);


        $this->reset(['student','error','newEmail']);

        $res = $client->getStudentByRegno($this->regno, ['with' => 'details']);

        if (!isset($res['id']) && !isset($res['data'])) {
            $this->error = 'Student not found';
            return;
        }

        // Normalize response
        $this->student = $res['data'] ?? $res;
    }

    public function resetEmail(StudentPortalClient $client)
    {
        $this->validate([
            'newEmail' => 'required|email'
        ]);

        $resp = $client->resetEmail(
            $this->student['user_id'],
            $this->newEmail
        );

        if (empty($resp['success'])) {
            $this->addError('newEmail', $resp['message'] ?? 'Reset failed');
            return;
        }

        //session()->flash('success', 'Email updated successfully.');
         Flux::toast('Email updated successfully and verification email sent.', variant: 'success', position: 'top-right', duration: 4000);

        // Reset state
        $this->reset(['regno','newEmail','student','error']);
    }

    public function render()
    {
        return view('livewire.students.reset-student-email');
    }
}

