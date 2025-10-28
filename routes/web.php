<?php

use App\Livewire\Bursary;
use App\Livewire\Student;
use App\Livewire\Admission;
use App\Livewire\Admin\Users;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Appearance;
use Illuminate\Support\Facades\Route;
use App\Livewire\Bursary\ApprovePayment;
use App\Livewire\Bursary\StudentFeeReport;
use App\Livewire\Bursary\ConfirmPaymentRef;
use App\Livewire\Bursary\OtherPaymentsReport;
use App\Livewire\Bursary\AdmissionPaymentReport;
use App\Livewire\Admissions\ChangeApplicationType;
use App\Livewire\Admissions\EnrolledStudents\Index;
use App\Http\Controllers\Export\CandidateExportController;
use App\Http\Controllers\Export\AdmissionPaymentExportController;
use App\Livewire\AdmittedStudent; // Remove this line if the class does not exist

Route::get('/', function () {
return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
->middleware(['auth', 'verified'])
->name('dashboard');

Route::middleware(['auth'])->group(function () {
Route::redirect('settings', 'settings/profile');
Route::get('settings/profile', Profile::class)->name('settings.profile');
Route::get('settings/password', Password::class)->name('settings.password');
Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
Route::get('/students/admitted', AdmittedStudent::class)->name('students.admitted');
// Remove or replace with a valid Livewire component if needed
Route::get('/admissions', Admission::class)->name('admissions')
->middleware('role:admissions-manager|super-admin');
Route::get('/students', Student::class)->name('students')
->middleware('role:student-manager|super-admin');

Route::get('/students/enrolled', Index::class)->name('students.enrolled')
    ->middleware('role:admissions-manager|super-admin');

     Route::get('/bursary/admission-payment-report', AdmissionPaymentReport::class)
     ->name('bursary.admission-payment-report')
     ->middleware('role:bursary-manager|super-admin');

     Route::get('/bursary/student-fee-report', StudentFeeReport::class)
     ->name('bursary.student-fee-report')->middleware('role:bursary-manager|super-admin');

     Route::get('/bursary/other-payments-report', OtherPaymentsReport::class)
     ->name('bursary.other-payments-report')
     ->middleware('role:bursary-manager|super-admin');

        Route::get('/bursary/approve-payment', ApprovePayment::class)
    ->name('bursary.approve-payment')
    ->middleware('role:bursary-manager|super-admin');

       Route::get('/bursary/confirm-payment-ref', ConfirmPaymentRef::class)
        ->name('bursary.confirm-payment-ref')
        ->middleware('role:bursary-manager|super-admin');

 Route::prefix('exports/admissions')->group(function () {
     Route::get('/payments/export/excel', [AdmissionPaymentExportController::class, 'exportExcel'])->name('exports.admissions.export.excel');
     Route::get('/payments/export/pdf', [AdmissionPaymentExportController::class, 'exportPdf'])->name('exports.admissions.export.pdf');
 });

// User Management Routes
Route::get('/admin/users', Users::class)
    ->name('admin.users')
    ->middleware('role:super-admin');
Route::get('/admissions/change-application-type', ChangeApplicationType::class)
->name('admissions.change-application-type')
->middleware('role:admissions-manager|super-admin');

 Route::get('/exports/candidates.xlsx', [CandidateExportController::class, 'excel'])
        ->name('exports.candidates.excel')->middleware('export.filters');
    Route::get('/exports/candidates.csv', [CandidateExportController::class, 'csv'])
        ->name('exports.candidates.csv')->middleware('export.filters');
    Route::get('/exports/candidates.pdf', [CandidateExportController::class, 'pdf'])
        ->name('exports.candidates.pdf')->middleware('export.filters');


});

require __DIR__.'/auth.php';
