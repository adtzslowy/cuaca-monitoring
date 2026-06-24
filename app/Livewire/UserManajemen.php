<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Title('Manajemen User')]
class UserManajemen extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDelete = false;
    public bool $isEdit = false;

    public ?int $userId = null;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $passwordConfirm = '';
    public string $selectedRole = '';

    public string $search = '';
    public string $filterRole = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', $this->isEdit ? "unique:users,email,{$this->userId}" : 'unique:users,email'],
            'password' => $this->isEdit ? ['nullable', Password::min(8)] : ['required', Password::min(8)],
            'passwordConfirm' => $this->isEdit ? ['nullable', 'same:password'] : ['required', 'same:password'],
            'selectedRole' => ['required', 'exists:roles,name'],
        ];
    }

    protected array $messages = [
        'name.required'            => 'Nama wajib diisi.',
        'email.required'           => 'Email wajib diisi.',
        'email.unique'             => 'Email sudah digunakan.',
        'password.required'        => 'Password wajib diisi.',
        'passwordConfirm.same'     => 'Konfirmasi password tidak cocok.',
        'selectedRole.required'    => 'Role wajib dipilih.',
        'selectedRole.exists'      => 'Role tidak valid.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterRole(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->reset(['userId', 'name', 'email', 'password', 'passwordConfirm', 'selectedRole']);
        $this->resetErrorBag();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->passwordConfirm = '';
        $this->selectedRole = $user->roles->first()?->name ?? '';

        $this->resetErrorBag();
        $this->isEdit = true;
        $this->showModal = true;
    }


    public function save(): void
    {
        $this->validate();

        if ($this->isEdit) {
            $user = User::findOrFail($this->userId);

            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                ...(filled($this->password) ? ['password' => bcrypt($this->password)] : []),
            ]);
        } else {
            $user = User::create([
                'name'     => $this->name,
                'email'    => $this->email,
                'password' => bcrypt($this->password),
            ]);
        }

        $user->syncRoles([$this->selectedRole]);

        $message = $this->isEdit ? 'User berhasil diperbaharui.' : 'User berhasil ditambahkan.';

        $this->showModal = false;
        $this->reset(['userId', 'name', 'email', 'password', 'passwordConfirm', 'selectedRole']);

        session()->flash('success', $message);
    }

    public function confirmDelete(int $id): void
    {
        $this->userId = $id;
        $this->showDelete = true;
    }

    public function destroy(): void
    {
        $user = User::findOrFail($this->userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Tidak bisa menghapus akun sendiri.');
            $this->showDelete = false;
            return;
        }

        $user->delete();

        $this->showDelete = false;
        $this->userId     = null;

        session()->flash('success', 'User berhasil dihapus.');
    }

    public function render()
    {
        $roles = Role::where('name', '!=', 'super_admin')
            ->orderBy('name')
            ->pluck('name');

        $users = User::with('roles')
            ->whereDoesntHave('roles', fn($q) => $q->where('name', 'super_admin'))
            ->when($this->search, fn($q) =>
                $q->where(fn($q) =>
                    $q->where('name', 'like', "%{$this->search}%")
                      ->orWhere('email', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterRole, fn($q) =>
                $q->whereHas('roles', fn($q) => $q->where('name', $this->filterRole))
            )
            ->latest()
            ->paginate(10);

        return view('livewire.user-manajemen', compact('users', 'roles'));
    }
}
