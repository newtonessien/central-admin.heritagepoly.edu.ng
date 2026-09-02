<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserProgramType;
use App\Services\Clients\AdmissionsPortalClient;
use Flux\Flux;
use Livewire\Component;

class ProgramTypeAccess extends Component
{
public ?int $selectedUserId = null;

public array $users = [];

public array $programTypes = [];

public array $selectedProgramTypeIds = [];

public function mount(): void
{
$this->loadProgramTypes();
$this->loadUsers();

}

/*
|--------------------------------------------------------------------------
| Load Data
|--------------------------------------------------------------------------
*/

protected function loadUsers(): void
{
    $assigned = UserProgramType::query()
        ->get()
        ->groupBy('user_id');

    $programTypes = collect($this->programTypes)
        ->keyBy(fn ($programType) => (int) $programType['id']);

    $this->users = User::role('admissions-manager')
        ->orderBy('name')
        ->get()
        ->map(function (User $user) use ($assigned, $programTypes) {

            $programTypeIds = $assigned
                ->get($user->id, collect())
                ->pluck('program_type_id')
                ->map(fn ($id) => (int) $id)
                ->values();

            $programTypeNames = $programTypeIds
                ->map(fn ($id) => $programTypes->get($id)['name'] ?? null)
                ->filter()
                ->values()
                ->all();

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'program_type_ids' => $programTypeIds->all(),
                'program_type_names' => $programTypeNames,
            ];
        })
        ->values()
        ->toArray();
}

protected function loadProgramTypes(): void
{
$this->programTypes = app(AdmissionsPortalClient::class)
->getProgramTypes();

}

/*
|--------------------------------------------------------------------------
| User Selection
|--------------------------------------------------------------------------
*/

public function selectUser(int $userId): void
{
$this->selectedUserId = $userId;

$this->selectedProgramTypeIds = UserProgramType::query()
->where('user_id', $userId)
->pluck('program_type_id')
->map(fn ($id) => (string) $id)
->values()
->toArray();

Flux::modal('program-type-access')->show();
}

/*
|--------------------------------------------------------------------------
| Save Access
|--------------------------------------------------------------------------
*/

public function save(): void
{
if (!$this->selectedUserId) {
return;
}

$user = User::findOrFail($this->selectedUserId);

abort_unless(
$user->hasRole('admissions-manager'),
403
);

$programTypeIds = collect($this->selectedProgramTypeIds)
->flatten()
->filter(fn ($id) => is_numeric($id))
->map(fn ($id) => (int) $id)
->unique()
->values()
->all();

UserProgramType::query()
->where('user_id', $user->id)
->delete();

if ($programTypeIds) {
$now = now();

UserProgramType::insert(
collect($programTypeIds)
->map(fn (int $programTypeId) => [
'user_id' => $user->id,
'program_type_id' => $programTypeId,
'created_at' => $now,
'updated_at' => $now,
])
->all()
);
}

Flux::modal('program-type-access')->close();

$this->selectedUserId = null;
$this->selectedProgramTypeIds = [];

$this->loadUsers();

Flux::toast(
'Programme type access added successfully',
variant: 'success',
position: 'top-right',
duration: 4000
);


}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

public function getSelectedUserProperty(): ?User
{
if (!$this->selectedUserId) {
return null;
}

return User::find($this->selectedUserId);
}

public function getAssignedProgramTypeNamesProperty(): array
{
if (!$this->selectedUserId) {
return [];
}

return collect($this->programTypes)
->whereIn(
'id',
$this->selectedProgramTypeIds
)
->pluck('name')
->values()
->toArray();
}

public function render()
{
return view('livewire.admin.program-type-access');
}
}
