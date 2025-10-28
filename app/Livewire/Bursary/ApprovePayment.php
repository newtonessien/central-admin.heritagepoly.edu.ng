<?php

namespace App\Livewire\Bursary;

use Flux\Flux;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Services\Clients\StudentPortalClient;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;


class ApprovePayment extends Component
{

use WithPagination;
 // 👇 Add this line — prevents “$page does not exist”
    public int $page = 1;
// form inputs
public ?string $regno = null;
public ?int $serviceId = null;
public ?float $amount = null;
public ?string $notes = null;

// lookups & student
public array $services = [];
public ?array $student = null;

protected $paginationTheme = 'tailwind'; // Optional
protected $queryString = ['page' => ['except' => 1]];

public $showPayments = false; // default to hidden

// approved payments list
public array $payments = [];

// UI state
public bool $loadingPayments = false;
public bool $loadingStudent = false;
public ?string $errorMessage = null;
public ?string $successMessage = null;

// edit mode
public ?int $editingPaymentId = null;
public ?array $editingPayment = null;

public function mount()
{
$this->loadServices();
 $this->page = session('approve_payment_page', 1);
}

protected function getPortal(): StudentPortalClient
{
return app(StudentPortalClient::class);
}

protected function loadServices(): void
{
try {
$portal = $this->getPortal();
$this->services = $portal->getServices() ?? [];
} catch (\Throwable $e) {
$this->services = [];
$this->errorMessage = 'Unable to load services: ' . $e->getMessage();
Flux::toast($this->errorMessage, variant: 'warning', position: 'top-right');
}
}

public function findStudent(): void
{

$this->validateOnly('regno', ['regno' => 'required|string|max:255']);

// reset previous state
$this->reset(['student', 'payments', 'errorMessage', 'successMessage']);
$this->resetPage(); // reset to page 1 each time
$this->loadingStudent = true;
$this->loadingPayments = false;


try {
$portal = $this->getPortal();

// fetch student (with details)
$response = $portal->getStudentByRegNo($this->regno, ['with' => 'details']);

if (!empty($response['student'])) {
$this->student = $response['student'];
} elseif (!empty($response['data'])) {
$this->student = $response['data'];
} else {
$this->errorMessage = $response['message'] ?? 'Student not found.';
Flux::toast($this->errorMessage, variant: 'warning', position: 'top-right');
return;
}

// now fetch approved payments (show payments loading state)
$this->loadingPayments = true;
$rawPayments = $portal->getApprovedPaymentsByRegno($this->regno);

//dd($rawPayments);

// Defensive normalization: always produce an array of arrays
if (!is_array($rawPayments)) {
// if the client returned full response shape like ['data' => [...]]
if (is_array($rawPayments) && array_key_exists('data', $rawPayments) && is_array($rawPayments['data'])) {
$rawPayments = $rawPayments['data'];
} else {
$rawPayments = [];
}
}

$this->payments = collect($rawPayments)
    ->filter(fn($p) => is_array($p)) // remove invalid entries
    ->map(function ($p) {
        // normalize fields used by Blade
        return [
            ...$p,
            'service_name' => $p['service']['name'] ?? ($p['service_name'] ?? '-'),
            'amount' => $p['amount'] ?? 0,
            'notes' => $p['notes'] ?? '',
            'is_paid' => $p['is_paid'] ?? 0,
        ];
    })
    ->values()
    ->toArray(); // ✅ convert to array here (Livewire safe)



if (empty($this->payments)) {
Flux::toast('No managed payments found for this student.', variant: 'info', position: 'top-right');
}
} catch (\Throwable $e) {
$this->errorMessage = 'Error contacting Student Portal: ' . $e->getMessage();
Flux::toast($this->errorMessage, variant: 'danger', position: 'top-right');
} finally {
$this->loadingStudent = false;
$this->loadingPayments = false;
}
}


public function approvePayment(): void
{
$this->validate([
'regno'     => 'required|string|max:255',
'serviceId' => 'required|integer',
'amount'    => 'required|numeric|min:0.01',
'notes'     => 'required|string|max:100',
]);

if (!$this->student) {
Flux::toast('Search for a student first.', variant: 'warning', position: 'top-right');
return;
}

$payload = [
'user_id' => $this->student['id'] ?? null,
'regno' => $this->student['matric_no'] ?? $this->regno,
'service_id' => $this->serviceId,
'amount' => $this->amount,
'notes' => $this->notes,
'initiated_by' => Auth::user()->email ?? 'system',
];

try {
$portal = $this->getPortal();
$response = $portal->createApprovedPayment($payload);

if (!empty($response['success'])) {
$this->successMessage = $response['message'] ?? 'Payment Created successfully.';
Flux::toast($this->successMessage, variant: 'success', position: 'top-right');
$this->reset(['serviceId', 'amount', 'notes']);
$this->findStudent(); // refresh payments
$this->showPayments = true; // show payments after approval
} else {
$msg = $response['message'] ?? 'Error creating payment.';
Flux::toast($msg, variant: 'warning', position: 'top-right');
}
} catch (\Throwable $e) {
$msg = 'Error contacting Student Portal: ' . $e->getMessage();
Flux::toast($msg, variant: 'danger', position: 'top-right');
}
}

/**
 * Begin editing an unpaid approved payment.
 */

public function editPayment($paymentId)
{
$payment = collect($this->payments)->firstWhere('id', $paymentId);

if (!$payment) {
Flux::toast('Payment not found.', variant: 'warning', position: 'top-right');
return;
}

$this->editingPayment = [
'id' => $payment['id'],
'service_id' => $payment['service_id'] ?? null,
'amount' => $payment['amount'] ?? 0,
'notes' => $payment['notes'] ?? '',
];


}


/**
* Save changes to an unpaid payment.
*/
public function updatePayment(): void
{
   // validate inputs
    $this->validate([
        'editingPayment.amount' => 'required|numeric|min:0.01',
        'editingPayment.notes' => 'required|string|max:255',
        'editingPayment.service_id' => 'required|integer',
    ]);

    if (!$this->editingPayment || empty($this->editingPayment['id'])) {
        return;
    }

    try {
        $portal = $this->getPortal();
        $res = $portal->updateApprovedPayment($this->editingPayment['id'], [
            'amount' => $this->editingPayment['amount'],
            'notes' => $this->editingPayment['notes'] ?? '',
            'service_id' => $this->editingPayment['service_id'] ?? null,
            'updated_by' => Auth::user()->email,
        ]);

        if (!empty($res['success'])) {
            Flux::toast('Payment updated successfully.', variant: 'success', position: 'top-right');
            $this->findStudent();
            $this->editingPayment = null;
        } else {
            Flux::toast($res['message'] ?? 'Update failed.', variant: 'warning', position: 'top-right');
        }
    } catch (\Throwable $e) {
        Flux::toast('Error updating payment: ' . $e->getMessage(), variant: 'danger', position: 'top-right');
    }
}


/**
* Delete an unpaid payment.
*/
public function deletePayment(int $id): void
{
$record = collect($this->payments)->firstWhere('id', $id);
if (! $record || !empty($record['is_paid'])) {
Flux::toast('Paid or invalid records cannot be deleted.', variant: 'info', position: 'top-right');
return;
}

try {
$portal = $this->getPortal();
$res = $portal->deleteApprovedPayment($id);

if (!empty($res['success'])) {
Flux::toast('Payment deleted successfully.', variant: 'success', position: 'top-right');
$this->findStudent();
} else {
Flux::toast($res['message'] ?? 'Unable to delete payment.', variant: 'warning', position: 'top-right');
}
} catch (\Throwable $e) {
Flux::toast('Error deleting payment: ' . $e->getMessage(), variant: 'danger', position: 'top-right');
}
}

public function getPaginatedPaymentsProperty()
{
    $items = collect($this->payments);
    $perPage = 3;
    $page = $this->getPage() ?? 1; // ← use Livewire’s built-in page tracking

    return new LengthAwarePaginator(
        $items->forPage($page, $perPage)->values(),
        $items->count(),
        $perPage,
        $page,
        ['path' => request()->url(), 'query' => []]
    );
}

public function updatingPage($page)
    {
        // 👇 Optional: store current page in session so even hard refresh remembers
        session(['approve_payment_page' => $page]);
    }



public function togglePayments()
{
    $this->showPayments = !$this->showPayments;
}


public function render()
{
return view('livewire.bursary.approve-payment',[
            'paginatedPayments' => $this->paginatedPayments,
        ]);
}
}
