<?php

namespace App\Livewire\Admin;

use Flux\Flux;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Notifications\UserSetPasswordNotification;

class Users extends Component
{
    use WithPagination;

    public $showCreateForm = false;
    public $editingUserId = null;

    public $name;
    public $email;

    // multiple roles bound to FluxUI listbox
    public $selectedRoles = [];

    protected $rules = [
        'name'          => 'required|string',
        'email'         => 'required|email|unique:users,email',
        'selectedRoles' => 'required|array|min:1',
    ];

    /** Create a new user and send them a password reset link */
    public function save()
    {
        $this->validate();

        // Create user with a dummy password (cannot be used)
        $user = User::create([
            'name'     => $this->name,
            'email'    => $this->email,
            'password' => Hash::make(Str::random(32)), // unusable until reset
            'email_verified_at' => now(), // 👈 staff are auto-verified
        ]);

        $user->assignRole($this->selectedRoles);

        // Send password reset link to user's email
        $token = Password::createToken($user);
        $user->sendPasswordResetNotification($token);


        $this->reset(['name','email','selectedRoles','showCreateForm']);
        Flux::toast('User created. A password setup email has been sent.', variant: 'success');
    }

    /** Edit user inline */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        $this->editingUserId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    /** Update user inline */
    public function update()
    {
        $user = User::findOrFail($this->editingUserId);

        $this->validate([
            'name'          => 'required|string',
            'email'         => 'required|email|unique:users,email,' . $user->id,
            'selectedRoles' => 'required|array|min:1',
        ]);

        $user->update([
            'name'  => $this->name,
            'email' => $this->email,
        ]);

        $user->syncRoles($this->selectedRoles);

        $this->reset(['editingUserId','name','email','selectedRoles']);
        Flux::toast('User updated successfully', variant: 'success');
    }

    /** Delete user */
    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        Flux::toast('User deleted successfully', variant: 'success');
    }

    public function render()
    {
        return view('livewire.admin.users', [
            'users'    => User::paginate(10),
            'allRoles' => Role::all(), // roles for the listbox
        ]);
    }
}
