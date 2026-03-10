<?php
use App\Http\Controllers\Course\CourseBulkTemplateController;
use App\Http\Controllers\Export\AdmissionPaymentExportController;
use App\Http\Controllers\Export\CandidateExportController;
use App\Http\Controllers\Export\MatricRegisterExportController;
use App\Http\Controllers\Export\TutorialListExcelController;
use App\Http\Controllers\Export\TutorialListPdfController;
use App\Livewire\Admin\Users;
use App\Livewire\Admission;
use App\Livewire\Admissions\ChangeApplicationType;
use App\Livewire\Admissions\EnrolledStudents\Index;
use App\Livewire\AdmittedStudent; // Remove this line if the class does not exist
use App\Livewire\Bursary;
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
use App\Livewire\Students\FeeTransfer\Start;
use App\Livewire\Students\MarkAsScreened;
use App\Livewire\Students\MatricRegister;
use App\Livewire\Students\ResetStudentEmail;
use Illuminate\Support\Facades\Route;

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

Route::get('/bursary/fee-item', FeeItem::class)
->name('bursary.fee-item')
->middleware('role:bursary-manager|super-admin');

Route::get('/bursary/program-type-fee-item-amount', ProgramTypeFeeItemAmountManager::class)
->name('bursary.program-type-fee-item-amount')
->middleware('role:bursary-manager|super-admin');

Route::get('/bursary/consultant-school-fees-report', ConsultantSchoolFeesReport::class)
->name('bursary.consultant-school-fees-report')
->middleware('role:super-admin');

Route::get('/bursary/study-center-summary-report', StudyCenterSummaryReport::class)
->name('bursary.study-center-summary-report')
->middleware('role:super-admin');

Route::get('/registration/tutorial-list', TutorialList::class)
    ->name('registration.tutorial-list')
    ->middleware('role:student-manager|super-admin');

       Route::get('/students/matriculation-register', MatricRegister::class)->name('students.matriculation-register')
    ->middleware('role:student-manager|super-admin|admissions-manager|bursary-manager');

Route::get('/exports/tutorial-list/pdf',
    [TutorialListPdfController::class, 'generate']
   )->name('exports.tutorial-list.pdf');

Route::get(
    '/exports/tutorial-list/excel',
    [TutorialListExcelController::class, 'generate']
)->name('exports.tutorial-list.excel');

Route::prefix('exports/admissions')->group(function () {
Route::get('/payments/export/excel', [AdmissionPaymentExportController::class, 'exportExcel'])->name('exports.admissions.export.excel');
Route::get('/payments/export/pdf', [AdmissionPaymentExportController::class, 'exportPdf'])->name('exports.admissions.export.pdf');
});

Route::get('/exports/matric-register/pdf',[MatricRegisterExportController::class, 'pdf']
)->name('exports.matric-register.pdf');

Route::get('/exports/matric-register/excel', [MatricRegisterExportController::class, 'excel']
)->name('exports.matric-register.excel');

Route::get(
    '/courses/bulk/template',
    [CourseBulkTemplateController::class, 'download']
)->name('courses.bulk.template');

// User Management Routes
Route::get('/admin/users', Users::class)
->name('admin.users')
->middleware('role:super-admin');
Route::get('/admissions/change-application-type', ChangeApplicationType::class)
->name('admissions.change-application-type')
->middleware('role:admissions-manager|super-admin');

Route::get('/students/mark-screened', MarkAsScreened::class)->name('students.mark-screened')
->middleware('role:student-manager|super-admin|bursary-manager');
Route::get('/students/transfer-requests', TransferRequests::class)->name('students.transfer-requests')
->middleware('role:student-manager|super-admin');

Route::get('/students/fee-transfer',Start::class)->name('students.fee-transfer')
->middleware('role:student-manager|super-admin');

Route::get('/students/reset-email', ResetStudentEmail::class)->name('students.reset-email')
->middleware('role:student-manager|super-admin');

Route::get('/students/change-of-course', ChangeOfCourseIndex::class)->name('students.change-of-course')
->middleware('role:student-manager|super-admin|admissions-manager');

Route::get('/students/change-of-course/{regno}', ChangeOfCourseForm::class)->name('students.change-of-course.form')
->middleware('role:student-manager|super-admin|admissions-manager');

Route::get('/courses/manage-courses', ManageCourses::class)->name('courses.manage-courses')
->middleware('role:student-manager|super-admin');

Route::get('/courses/bulk-upload', BulkUploadCourses::class)->name('courses.bulk-upload')
->middleware('role:student-manager|super-admin');

Route::get('/exports/candidates.xlsx', [CandidateExportController::class, 'excel'])
->name('exports.candidates.excel')->middleware('export.filters');
Route::get('/exports/candidates.csv', [CandidateExportController::class, 'csv'])
->name('exports.candidates.csv')->middleware('export.filters');
Route::get('/exports/candidates.pdf', [CandidateExportController::class, 'pdf'])
->name('exports.candidates.pdf')->middleware('export.filters');


});

require __DIR__.'/auth.php';
