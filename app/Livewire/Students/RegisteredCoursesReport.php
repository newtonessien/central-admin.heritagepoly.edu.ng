<?php

namespace App\Livewire\Students;

use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Flux\Flux;
use Livewire\Component;

class RegisteredCoursesReport extends Component
{
    public $regno;
    public $semester;
    public $session;

    public $student = null;
    public $courses = [];

    public $sessions = [];

    public $level;

    public $sessionSummary = [];

    public function mount()
    {
        $svc = app(AdmissionsPortalClient::class);
        //$this->sessions = $svc->getAcadSessions() ?? [];
        $this->sessions = collect($svc->getAcadSessions())
        ->pluck('name','id')
        ->all();


    }

    /*
    |--------------------------------------------------------------------------
    | Find Student
    |--------------------------------------------------------------------------
    */

    public function findStudent(StudentPortalClient $client)
    {
        $this->validate([
            'regno' => 'required|string'
        ]);

        $this->reset(['student','courses','semester','session']);

        $res = $client->getStudentByRegno(
            $this->regno,
            ['with' => 'details']
        );

        if (!isset($res['id']) && !isset($res['data'])) {

            Flux::toast(
                "Student not found",
                variant: 'danger',
                position: 'top-right',
                duration: 4000
            );

            return;
        }

        $this->student = $res['data'] ?? $res;
    }


    /*
    |--------------------------------------------------------------------------
    | Load Registered Courses
    |--------------------------------------------------------------------------
    */

    public function search(StudentPortalClient $client)
{
    if (!$this->student) {

        Flux::toast(
            "Load student first",
            variant: 'warning'
        );

        return;
    }

    $this->validate([
        'semester' => 'required',
        'session' => 'required'
    ]);

    $resp = $client->getRegisteredCourses([
        'regno' => $this->student['matric_no'],
        'semester' => $this->semester,
        'acad_session_id' => $this->session
    ]);

    $courses = collect($resp['courses'] ?? []);

    $this->level = $resp['student']['level'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | Group Courses: Session → Semester
    |--------------------------------------------------------------------------
    */

    $grouped = $courses
    ->groupBy('acad_session_id')
    ->map(function ($sessionCourses) {

        $first = $sessionCourses
            ->where('semester',1)
            ->sum('credit_hours');

        $second = $sessionCourses
            ->where('semester',2)
            ->sum('credit_hours');

        return [
            'courses' => $sessionCourses->groupBy('semester')->toArray(),
            'first_sem' => $first,
            'second_sem' => $second,
            'total' => $first + $second
        ];
    });

//$this->courses = $grouped->pluck('courses')->toArray();
$this->courses = $grouped->map(function ($s) {
    return $s['courses'];
})->toArray();
$this->sessionSummary = $grouped->map(function($s){
    return [
        'first_sem' => $s['first_sem'],
        'second_sem' => $s['second_sem'],
        'total' => $s['total']
    ];
})->toArray();

    // $this->courses = $courses
    //     ->groupBy('acad_session_id')
    //     ->map(function ($sessionCourses) {

    //         return $sessionCourses->groupBy('semester');

    //     })
    //     ->toArray();

    if ($courses->isEmpty()) {

        Flux::toast(
            "No course registration found",
            variant: 'warning'
        );
    }
}

    /*
    |--------------------------------------------------------------------------
    | Reset Form
    |--------------------------------------------------------------------------
    */

    public function clear()
    {
        $this->reset([
            'regno',
            'semester',
            'session',
            'student',
            'courses'
        ]);
    }

public function downloadPdf()
{
    if (!$this->student) {

        Flux::toast(
            "Load student first",
            variant: 'warning'
        );

        return;
    }

    $params = [
        'regno' => $this->student['matric_no'],
        'semester' => $this->semester ?? 0,
        'acad_session_id' => $this->session ?? 0
    ];

    return redirect()->route(
        'exports.course-registration-report',
        $params
    );
}



    public function render()
    {
        return view('livewire.students.registered-courses-report');
    }
}
