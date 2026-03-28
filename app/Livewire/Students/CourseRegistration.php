<?php

namespace App\Livewire\Students;

use App\Services\Clients\AdmissionsPortalClient;
use App\Services\Clients\StudentPortalClient;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CourseRegistration extends Component
{
public $regno;
public $student = null;
public $error;

public $session;
public $semester;
public $level;

public $courses = [];
public $selectedCourses = [];

public $registeredCourses = [];

public $sessions = [];
public $levels = [];

public $includeLowerLevels = false;

public $selectedCredits = 0;

public $maxCreditLoad = 35;

public $step = 1;

public $coursesByLevel = [];

/*
|--------------------------------------------------------------------------
| Mount
|--------------------------------------------------------------------------
*/

public function mount()
{
$svc = app(AdmissionsPortalClient::class);
$this->sessions = $svc->getAcadSessions() ?? [];

$studentSvc = app(StudentPortalClient::class);
$this->levels = $studentSvc->getLevels() ?? [];
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

$this->reset([
'student',
'error',
'courses',
'selectedCourses',
'registeredCourses',
'selectedCredits'
]);

$res = $client->getStudentByRegno(
$this->regno,
['with' => 'details']
);

if (!isset($res['id']) && !isset($res['data'])) {
//$this->error = 'Student not found';
  Flux::toast(
            "Student not found",
            variant: 'danger',
            position: 'top-right',
            duration: 5000
        );
return;
}

$this->student = $res['data'] ?? $res;
$this->step = 2;
}


/*
|--------------------------------------------------------------------------
| Load Courses
|--------------------------------------------------------------------------
*/

public function loadCourses(StudentPortalClient $client)
{


    if (!$this->student) {
        return;
    }


    $this->validate([
        'session'  => 'required',
        'semester' => 'required',
        'level'    => 'required'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Validate Student Level
    |--------------------------------------------------------------------------
    */

    $check = $client->validateStudentLevel([
        'user_id' => $this->student['user_id'],
        'acad_session_id' => $this->session,
        'level_id' => $this->level
    ]);


    if (!($check['valid'] ?? false)) {

        $this->resetCourses();

        Flux::toast(
            $check['message'],
            variant: 'danger',
            position: 'top-right',
            duration: 5000
        );

        return;
    }



    /*
    |--------------------------------------------------------------------------
    | Validate Fee Payment
    |--------------------------------------------------------------------------
    */

    $payment = $client->checkStudentFeePayment([
        'regno' => $this->student['matric_no'],
        'acad_session_id' => $this->session,
        'semester' => $this->semester
    ]);

    //dd($payment, $this->student['matric_no'], $this->session, $this->semester);

    if (!($payment['paid'] ?? false)) {

        $this->resetCourses();

        Flux::toast(
            "Student has not paid fees for the selected session and semester.",
            variant: 'danger',
            position: 'top-right',
            duration: 5000
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Fetch Courses
    |--------------------------------------------------------------------------
    */

    $response = $client->getCoursesFilter([
        'program_id' => $this->student['program_id'],
        'semester' => $this->semester,
        'level_id' => $this->level,
        'include_lower_levels' => $this->includeLowerLevels ? 1 : 0
    ]);

    $this->courses = $response['data'] ?? $response ?? [];

    if (empty($this->courses)) {

        Flux::toast(
            "No courses found for the selected filters.",
            variant: 'warning',
            position: 'top-right'
        );

        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Group Courses By Level
    |--------------------------------------------------------------------------
    */

    $this->coursesByLevel = collect($this->courses)
        ->groupBy('level_id')
        ->sortKeys()
        ->toArray();

    /*
    |--------------------------------------------------------------------------
    | Load Already Registered Courses
    |--------------------------------------------------------------------------
    */

    $this->loadRegisteredCourses($client);

    $this->step = 3;
}

/*
|--------------------------------------------------------------------------
| Load Registered Courses
|--------------------------------------------------------------------------
*/

public function loadRegisteredCourses(StudentPortalClient $client)
{
if (!$this->student) {
return;
}

$this->registeredCourses = $client->getStudentCourseRegistration([
'user_id' => $this->student['user_id'],
'semester' => $this->semester,
'acad_session_id' => $this->session
]);

// Preselect registered courses
$this->selectedCourses = collect($this->registeredCourses)
->pluck('course_id')
->toArray();

$this->calculateCredits();
}


/*
|--------------------------------------------------------------------------
| Register Courses
|--------------------------------------------------------------------------
*/

public function registerCourses(StudentPortalClient $client)
{
if (!$this->student) {
return;
}

if (empty($this->selectedCourses)) {

Flux::toast(
"Please select courses",
variant: 'warning',
position: 'top-right',
duration: 4000
);

return;
}

if ($this->selectedCredits > $this->maxCreditLoad) {

Flux::toast(
"Maximum allowed credit load is {$this->maxCreditLoad}",
variant: 'danger',
position: 'top-right',
duration: 5000
);

return;
}

$resp = $client->registerStudentCourses([
'user_id' => $this->student['user_id'],
'level_id' => $this->level,
'semester' => $this->semester,
'acad_session_id' => $this->session,
'program_id' => $this->student['program_id'],
'courses' => $this->selectedCourses,
'registered_by' => Auth::user()->email
]);


if (empty($resp['message'])) {

Flux::toast(
"Course registration failed",
variant: 'danger',
position: 'top-right',
duration: 4000
);

return;
}

Flux::toast(
"Courses registered successfully",
variant: 'success',
position: 'top-right',
duration: 4000
);

$this->loadRegisteredCourses($client);
}


/*
|--------------------------------------------------------------------------
| Toggle Course Active / Inactive
|--------------------------------------------------------------------------
*/

public function toggleCourse(StudentPortalClient $client, $id)
{
$client->toggleStudentCourse($id);

Flux::toast(
"Course status updated",
variant: 'success',
position: 'top-right',
duration: 3000
);

$this->loadRegisteredCourses($client);
}


/*
|--------------------------------------------------------------------------
| Delete Course
|--------------------------------------------------------------------------
*/

public function deleteCourse(StudentPortalClient $client, $id)
{
$client->deleteStudentCourse($id);

Flux::toast(
"Course removed successfully",
variant: 'warning',
position: 'top-right',
duration: 3000
);

$this->loadRegisteredCourses($client);
}


/*
|--------------------------------------------------------------------------
| Calculate Selected Credits
|--------------------------------------------------------------------------
*/

public function calculateCredits()
{
$this->selectedCredits = collect($this->coursesByLevel)
    ->flatten(1)
    ->whereIn('id', $this->selectedCourses)
    ->sum('credit_hours');
}


/*
|--------------------------------------------------------------------------
| Watch Selected Courses
|--------------------------------------------------------------------------
*/

public function updatedSelectedCourses()
{
$this->calculateCredits();
}


/*
|--------------------------------------------------------------------------
| Dynamic Credit Limit by Level
|--------------------------------------------------------------------------
*/

public function updatedLevel()
{
$limits = [
1 => 35,
2 => 35,
3 => 35,
4 => 35
];

$this->maxCreditLoad = $limits[$this->level] ?? 35;
}


private function resetCourses()
{
    $this->courses = [];
    $this->registeredCourses = [];
    $this->selectedCourses = [];
    $this->coursesByLevel = [];
}

public function backToStep1()
{
    $this->step = 1;

}


/*
|--------------------------------------------------------------------------
| Render
|--------------------------------------------------------------------------
*/

public function render()
{
return view('livewire.students.course-registration');
}
}
