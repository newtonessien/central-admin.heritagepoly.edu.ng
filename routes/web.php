<?php

use App\Http\Controllers\Course\CourseBulkTemplateController;
use App\Http\Controllers\Export\AdmissionPaymentExportController;
use App\Http\Controllers\Export\CandidateExportController;
use App\Http\Controllers\Export\MatricRegisterExportController;
use App\Http\Controllers\Export\RegisteredCoursesPdfController;
use App\Http\Controllers\Export\TutorialListExcelController;
use App\Http\Controllers\Export\TutorialListPdfController;
use App\Livewire\Admin\Users;
use App\Livewire\Admission;
use App\Livewire\Admissions\ChangeApplicationType;
use App\Livewire\Admissions\EnrolledStudents\Index;
use App\Livewire\AdmittedStudent;
use App\Livewire\Bursary\AdmissionPaymentReport;
use App\Livewire\Bursary\ApprovePayment;
use App\Livewire\Bursary\ConfirmPaymentRef;
use App\Livewire\Bursary\ConsultantSchoolFeesReport;
use App\Livewire\Bursary\FeeItem;
use App\Livewire\Bursary\OtherPaymentsReport;
use App\Livewire\Bursary\ProgramTypeFeeItemAmountManager;
use App\Livewire\Bursary\StudentFeeReport;
use App\Livewire\Bursary\StudyCenterSummaryReport;
use App\Livewire\Courses\BulkUploadCourses;
use App\Livewire\Courses\ManageCourses;
use App\Livewire\Registration\TutorialList;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Student;
use App\Livewire\Students\AcademicRequests\TransferRequests;
use App\Livewire\Students\ChangeOfCourse\ChangeOfCourseForm;
use App\Livewire\Students\ChangeOfCourse\ChangeOfCourseIndex;
use App\Livewire\Students\CourseRegistration;
use App\Livewire\Students\FeeTransfer\Start;
use App\Livewire\Students\MarkAsScreened;
use App\Livewire\Students\MatricRegister;
use App\Livewire\Students\RegisteredCoursesReport;
use App\Livewire\Students\ResetStudentEmail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('welcome'))->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');


/*
|--------------------------------------------------------------------------
| Authenticated Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    Route::redirect('settings', 'settings/profile');

    Route::prefix('settings')->group(function () {
        Route::get('profile', Profile::class)->name('settings.profile');
        Route::get('password', Password::class)->name('settings.password');
        Route::get('appearance', Appearance::class)->name('settings.appearance');
    });


    /*
    |--------------------------------------------------------------------------
    | Admissions
    |--------------------------------------------------------------------------
    */

    Route::prefix('admissions')->middleware('role:admissions-manager|super-admin')->group(function () {

        Route::get('/', Admission::class)->name('admissions');

        Route::get('change-application-type', ChangeApplicationType::class)
            ->name('admissions.change-application-type');

        Route::get('enrolled-students', Index::class)
            ->name('students.enrolled');
    });


    /*
    |--------------------------------------------------------------------------
    | Students
    |--------------------------------------------------------------------------
    */

    Route::prefix('students')->group(function () {

        Route::get('/', Student::class)
            ->name('students')
            ->middleware('role:student-manager|super-admin');

        Route::get('admitted', AdmittedStudent::class)
            ->name('students.admitted');

        Route::get('matriculation-register', MatricRegister::class)
            ->name('students.matriculation-register')
            ->middleware('role:student-manager|super-admin|admissions-manager|bursary-manager');

        Route::get('mark-screened', MarkAsScreened::class)
            ->name('students.mark-screened')
            ->middleware('role:student-manager|super-admin|bursary-manager');

        Route::get('transfer-requests', TransferRequests::class)
            ->name('students.transfer-requests')
            ->middleware('role:student-manager|super-admin');

             Route::get('reset-email', ResetStudentEmail::class)
            ->name('students.reset-email')
            ->middleware('role:student-manager|super-admin');

        Route::get('change-of-course', ChangeOfCourseIndex::class)
            ->name('students.change-of-course')
            ->middleware('role:student-manager|super-admin|admissions-manager');

        Route::get('change-of-course/{regno}', ChangeOfCourseForm::class)
            ->name('students.change-of-course.form')
            ->middleware('role:student-manager|super-admin|admissions-manager');


        Route::get('course-registration', CourseRegistration::class)
            ->name('students.course-registration')
            ->middleware('role:student-manager|super-admin');

        Route::get('registered-courses', RegisteredCoursesReport::class)
            ->name('students.registered-courses')
            ->middleware('role:student-manager|super-admin');
    });


    /*
    |--------------------------------------------------------------------------
    | Bursary
    |--------------------------------------------------------------------------
    */

    Route::prefix('bursary')->middleware('role:bursary-manager|super-admin')->group(function () {

        Route::get('admission-payment-report', AdmissionPaymentReport::class)
            ->name('bursary.admission-payment-report');

        Route::get('student-fee-report', StudentFeeReport::class)
            ->name('bursary.student-fee-report');

        Route::get('other-payments-report', OtherPaymentsReport::class)
            ->name('bursary.other-payments-report');

        Route::get('approve-payment', ApprovePayment::class)
            ->name('bursary.approve-payment');

        Route::get('confirm-payment-ref', ConfirmPaymentRef::class)
            ->name('bursary.confirm-payment-ref');

        Route::get('fee-item', FeeItem::class)
            ->name('bursary.fee-item');

        Route::get('program-type-fee-item-amount', ProgramTypeFeeItemAmountManager::class)
            ->name('bursary.program-type-fee-item-amount');

        Route::get('fee-transfer', Start::class)
            ->name('students.fee-transfer');
    });

    Route::get(
        '/bursary/consultant-school-fees-report',
        ConsultantSchoolFeesReport::class
    )->name('bursary.consultant-school-fees-report')->middleware('role:super-admin');

    Route::get(
        '/bursary/study-center-summary-report',
        StudyCenterSummaryReport::class
    )->name('bursary.study-center-summary-report')->middleware('role:super-admin');


    /*
    |--------------------------------------------------------------------------
    | Courses
    |--------------------------------------------------------------------------
    */

    Route::prefix('courses')->middleware('role:student-manager|super-admin')->group(function () {

        Route::get('manage-courses', ManageCourses::class)
            ->name('courses.manage-courses');

        Route::get('bulk-upload', BulkUploadCourses::class)
            ->name('courses.bulk-upload');

        Route::get('bulk/template', [CourseBulkTemplateController::class, 'download'])
            ->name('courses.bulk.template');
    });


    /*
    |--------------------------------------------------------------------------
    | Registration
    |--------------------------------------------------------------------------
    */

    Route::get('/registration/tutorial-list', TutorialList::class)
        ->name('registration.tutorial-list')
        ->middleware('role:student-manager|super-admin');


    /*
    |--------------------------------------------------------------------------
    | Admin
    |--------------------------------------------------------------------------
    */

    Route::get('/admin/users', Users::class)
        ->name('admin.users')
        ->middleware('role:super-admin');


    /*
    |--------------------------------------------------------------------------
    | Exports
    |--------------------------------------------------------------------------
    */

    Route::prefix('exports')->group(function () {

        Route::get('tutorial-list/pdf', [TutorialListPdfController::class, 'generate'])
            ->name('exports.tutorial-list.pdf');

        Route::get('tutorial-list/excel', [TutorialListExcelController::class, 'generate'])
            ->name('exports.tutorial-list.excel');

        Route::get('matric-register/pdf', [MatricRegisterExportController::class, 'pdf'])
            ->name('exports.matric-register.pdf');

        Route::get('matric-register/excel', [MatricRegisterExportController::class, 'excel'])
            ->name('exports.matric-register.excel');

        Route::prefix('admissions')->group(function () {

            Route::get('payments/export/excel', [AdmissionPaymentExportController::class, 'exportExcel'])
                ->name('exports.admissions.export.excel');

            Route::get('payments/export/pdf', [AdmissionPaymentExportController::class, 'exportPdf'])
                ->name('exports.admissions.export.pdf');
        });

        Route::get('candidates.xlsx', [CandidateExportController::class, 'excel'])
            ->name('exports.candidates.excel')
            ->middleware('export.filters');

        Route::get('candidates.csv', [CandidateExportController::class, 'csv'])
            ->name('exports.candidates.csv')
            ->middleware('export.filters');

        Route::get('candidates.pdf', [CandidateExportController::class, 'pdf'])
            ->name('exports.candidates.pdf')
            ->middleware('export.filters');

      Route::get('/course-registration-report',
            [RegisteredCoursesPdfController::class,'courseRegistrationPdf'])
            ->name('exports.course-registration-report');
    });

});

require __DIR__.'/auth.php';
