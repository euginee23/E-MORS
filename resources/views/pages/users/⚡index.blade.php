<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $roleFilter = 'all';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingUserId = null;
    public string $formName = '';
    public string $formEmail = '';
    public string $formPassword = '';
    public string $formPasswordConfirmation = '';
    public string $formRole = 'collector';

    // Delete
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;
    public string $deletingUserName = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoleFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function marketId(): ?int
    {
        return Auth::user()->market_id;
    }

    #[Computed]
    public function users()
    {
        return User::where('market_id', $this->marketId)
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('email', 'like', '%' . $this->search . '%')
            ))
            ->when($this->roleFilter !== 'all', fn ($q) => $q->where('role', $this->roleFilter))
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }

    #[Computed]
    public function totalUsers(): int
    {
        return User::where('market_id', $this->marketId)->count();
    }

    #[Computed]
    public function adminCount(): int
    {
        return User::where('market_id', $this->marketId)->where('role', UserRole::Admin)->count();
    }

    #[Computed]
    public function collectorCount(): int
    {
        return User::where('market_id', $this->marketId)->where('role', UserRole::Collector)->count();
    }

    #[Computed]
    public function vendorCount(): int
    {
        return User::where('market_id', $this->marketId)->where('role', UserRole::Vendor)->count();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $userId): void
    {
        $user = User::where('market_id', $this->marketId)->findOrFail($userId);
        $this->editingUserId = $user->id;
        $this->formName = $user->name;
        $this->formEmail = $user->email;
        $this->formRole = $user->role->value;
        $this->formPassword = '';
        $this->formPasswordConfirmation = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'formName' => ['required', 'string', 'max:255'],
            'formEmail' => [
                'required', 'string', 'email', 'max:255',
                $this->editingUserId
                    ? Rule::unique(User::class, 'email')->ignore($this->editingUserId)
                    : Rule::unique(User::class, 'email'),
            ],
            'formRole' => ['required', Rule::in(array_column(UserRole::cases(), 'value'))],
        ];

        if (! $this->editingUserId) {
            $rules['formPassword'] = ['required', 'string', 'min:8', 'same:formPasswordConfirmation'];
        } elseif ($this->formPassword !== '') {
            $rules['formPassword'] = ['string', 'min:8', 'same:formPasswordConfirmation'];
        }

        $this->validate($rules, [
            'formName.required' => 'Name is required.',
            'formEmail.required' => 'Email is required.',
            'formPassword.same' => 'Passwords do not match.',
        ]);

        if ($this->editingUserId) {
            $user = User::where('market_id', $this->marketId)->findOrFail($this->editingUserId);
            $user->name = $this->formName;
            $user->email = $this->formEmail;
            $user->role = $this->formRole;

            if ($this->formPassword !== '') {
                $user->password = Hash::make($this->formPassword);
            }

            $user->save();
            session()->flash('message', 'User updated successfully.');
        } else {
            User::create([
                'name' => $this->formName,
                'email' => $this->formEmail,
                'password' => Hash::make($this->formPassword),
                'role' => $this->formRole,
                'market_id' => $this->marketId,
            ]);
            session()->flash('message', 'User created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
        unset($this->users, $this->totalUsers, $this->adminCount, $this->collectorCount, $this->vendorCount);
    }

    public function confirmDelete(int $userId): void
    {
        $user = User::where('market_id', $this->marketId)->findOrFail($userId);
        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;
        $this->showDeleteModal = true;
    }

    public function deleteUser(): void
    {
        if ($this->deletingUserId === Auth::id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->showDeleteModal = false;
            return;
        }

        $user = User::where('market_id', $this->marketId)->findOrFail($this->deletingUserId);
        $user->delete();

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->deletingUserName = '';
        session()->flash('message', 'User deleted successfully.');
        unset($this->users, $this->totalUsers, $this->adminCount, $this->collectorCount, $this->vendorCount);
    }

    public function resetPassword(int $userId): void
    {
        $user = User::where('market_id', $this->marketId)->findOrFail($userId);
        $tempPassword = 'password123';
        $user->password = Hash::make($tempPassword);
        $user->save();
        session()->flash('message', "Password for {$user->name} has been reset to: {$tempPassword}");
    }

    private function resetForm(): void
    {
        $this->editingUserId = null;
        $this->formName = '';
        $this->formEmail = '';
        $this->formPassword = '';
        $this->formPasswordConfirmation = '';
        $this->formRole = 'collector';
        $this->resetValidation();
    }

    public function render()
    {
        return $this->view()->title(__('Users & Access Control'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Flash Messages --}}
        @if(session('message'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300">
                {{ session('message') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300">
                {{ session('error') }}
            </div>
        @endif

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Users & Access Control') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage user accounts, roles, and system access permissions.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Add User') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Users') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalUsers }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Administrators') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-blue-600">{{ $this->adminCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collectors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-purple-600">{{ $this->collectorCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Vendors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->vendorCount }}</flux:heading>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search users by name or email...') }}" />
            </div>
            <flux:select wire:model.live="roleFilter" class="sm:w-40">
                <flux:select.option value="all">{{ __('All Roles') }}</flux:select.option>
                <flux:select.option value="admin">{{ __('Admin') }}</flux:select.option>
                <flux:select.option value="collector">{{ __('Collector') }}</flux:select.option>
                <flux:select.option value="vendor">{{ __('Vendor') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Users Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('User') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Email') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Role') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('2FA') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Created') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->users as $user)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="user-{{ $user->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$user->name" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $user->email }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $roleColor = match($user->role) {
                                        \App\Enums\UserRole::Admin => 'blue',
                                        \App\Enums\UserRole::Collector => 'purple',
                                        \App\Enums\UserRole::Vendor => 'lime',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$roleColor" size="sm">{{ $user->role->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$user->email_verified_at ? 'lime' : 'zinc'" size="sm">
                                    {{ $user->email_verified_at ? 'Active' : 'Unverified' }}
                                </flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                @if($user->two_factor_confirmed_at)
                                    <flux:icon.shield-check class="size-5 text-emerald-500" />
                                @else
                                    <flux:icon.shield-exclamation class="size-5 text-zinc-400" />
                                @endif
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $user->id }})">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.item icon="key" wire:click="resetPassword({{ $user->id }})" wire:confirm="Reset password for {{ $user->name }}?">{{ __('Reset Password') }}</flux:menu.item>
                                        @if($user->id !== auth()->id())
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" wire:click="confirmDelete({{ $user->id }})">{{ __('Delete') }}</flux:menu.item>
                                        @endif
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-zinc-500">
                                {{ __('No users found.') }}
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                {{ $this->users->links() }}
            </div>
        </div>

        {{-- Role Permissions Overview --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="border-b border-orange-100 px-6 py-4 dark:border-neutral-700">
                <flux:heading size="lg">{{ __('Role Permissions Overview') }}</flux:heading>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Permission') }}</th>
                            <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Admin') }}</th>
                            <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Collector') }}</th>
                            <th class="px-6 py-3 text-center font-medium text-zinc-500 dark:text-zinc-400">{{ __('Vendor') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @php
                            $permissions = [
                                ['name' => 'View Dashboard', 'admin' => true, 'collector' => true, 'vendor' => true],
                                ['name' => 'Manage Vendors', 'admin' => true, 'collector' => false, 'vendor' => false],
                                ['name' => 'View Vendors', 'admin' => true, 'collector' => true, 'vendor' => false],
                                ['name' => 'Manage Stalls', 'admin' => true, 'collector' => false, 'vendor' => false],
                                ['name' => 'View Stalls', 'admin' => true, 'collector' => true, 'vendor' => true],
                                ['name' => 'Record Collections', 'admin' => true, 'collector' => true, 'vendor' => false],
                                ['name' => 'View Collections', 'admin' => true, 'collector' => true, 'vendor' => true],
                                ['name' => 'View Reports', 'admin' => true, 'collector' => true, 'vendor' => false],
                                ['name' => 'Manage Users', 'admin' => true, 'collector' => false, 'vendor' => false],
                            ];
                        @endphp
                        @foreach($permissions as $perm)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $perm['name'] }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($perm['admin'])
                                    <flux:icon.check class="mx-auto size-5 text-emerald-500" />
                                @else
                                    <flux:icon.x-mark class="mx-auto size-5 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($perm['collector'])
                                    <flux:icon.check class="mx-auto size-5 text-emerald-500" />
                                @else
                                    <flux:icon.x-mark class="mx-auto size-5 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if($perm['vendor'])
                                    <flux:icon.check class="mx-auto size-5 text-emerald-500" />
                                @else
                                    <flux:icon.x-mark class="mx-auto size-5 text-zinc-300 dark:text-zinc-600" />
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingUserId ? __('Edit User') : __('Add User') }}</flux:heading>
                <flux:subheading>{{ $editingUserId ? __('Update user account details.') : __('Create a new user account.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formName" :label="__('Full Name')" type="text" required />
                    <flux:input wire:model="formEmail" :label="__('Email Address')" type="email" required />
                </div>
                <flux:select wire:model="formRole" :label="__('Role')">
                    <flux:select.option value="admin">{{ __('Administrator') }}</flux:select.option>
                    <flux:select.option value="collector">{{ __('Collector') }}</flux:select.option>
                    <flux:select.option value="vendor">{{ __('Vendor') }}</flux:select.option>
                </flux:select>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formPassword" :label="$editingUserId ? __('New Password (leave blank to keep)') : __('Password')" type="password" :required="!$editingUserId" />
                    <flux:input wire:model="formPasswordConfirmation" :label="__('Confirm Password')" type="password" :required="!$editingUserId" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingUserId ? __('Update User') : __('Create User') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Delete Confirmation Modal --}}
    <flux:modal wire:model="showDeleteModal" class="max-w-sm">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Delete User') }}</flux:heading>
                <flux:subheading>{{ __('Are you sure you want to delete :name? This action cannot be undone.', ['name' => $deletingUserName]) }}</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button variant="ghost" wire:click="$set('showDeleteModal', false)">{{ __('Cancel') }}</flux:button>
                <flux:button variant="danger" wire:click="deleteUser">{{ __('Delete') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
