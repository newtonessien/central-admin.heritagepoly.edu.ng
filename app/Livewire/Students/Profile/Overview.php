<?php

namespace App\Livewire\Students\Profile;

use Livewire\Component;

class Overview extends Component
{
    public array $student = [];

    public function render()
    {
        return view('livewire.students.profile.overview');
    }
}
