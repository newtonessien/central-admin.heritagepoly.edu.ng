<?php

namespace App\Livewire\Students\ChangeOfCourse;

use Livewire\Component;

class ChangeOfCourseIndex extends Component
{
    public string $regno = '';

    protected $rules = [
        'regno' => 'required|string|min:3',
    ];

    public function fetchStudent()
    {
        $this->validate();

        return redirect()->route(
            'students.change-of-course.form',
            ['regno' => $this->regno]
        );
    }

    public function render()
    {
        return view('livewire.students.change-of-course.index');
    }
}
