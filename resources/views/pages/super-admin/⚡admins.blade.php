<?php

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use App\Mail\AdminAccountApproved;
use App\Mail\AdminAccountRejected;
use App\Models\AdminVerificationLog;
use App\Models\Market;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public string $statusFilter = 'all';

    // View credentials modal
    public bool $showViewModal = false;
    public ?int $viewingAdminId = null;

    // Reject modal
    public bool $showRejectModal = false;
    public ?int $rejectingAdminId = null;
    public string $rejectingAdminName = '';
    public string $rejectReason = '';

    // Create/Edit form
    public bool $showModal = false;
    public ?int $editingAdminId = null;
    public string $formName = '';
    public string $formEmail = '';
    public ?int $formMarketId = null;
    public string $formPassword = '';
    public string $formPasswordConfirmation = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function admins()
    {
        return User::where('role', UserRole::Admin)
            ->with('market')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) =>
                $q2->where('name', 'like', '%' . $this->search . '%')
                   ->orWhere('email', 'like', '%' . $this->search . '%')
                   ->orWhereHas('market', fn ($m) => $m->where('name', 'like', '%' . $this->search . '%'))
            ))
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    #[Computed]
    public function markets()
    {
        return Market::orderBy('name')->get();
    }

    #[Computed]
    public function pendingCount(): int
    {
        return User::where('role', UserRole::Admin)->where('status', AdminStatus::Pending)->count();
    }

    #[Computed]
    public function verifiedCount(): int
    {
        return User::where('role', UserRole::Admin)->where('status', AdminStatus::Verified)->count();
    }

    #[Computed]
    public function rejectedCount(): int
    {
        return User::where('role', UserRole::Admin)->where('status', AdminStatus::Rejected)->count();
    }

    #[Computed]
    public function totalCount(): int
    {
        return User::where('role', UserRole::Admin)->count();
    }

    #[Computed]
    public function viewingAdmin(): ?User
    {
        return $this->viewingAdminId
            ? User::where('role', UserRole::Admin)->with('market')->find($this->viewingAdminId)
            : null;
    }

    public function openViewModal(int $adminId): void
    {
        $this->viewingAdminId = $adminId;
        $this->showViewModal = true;
    }

    public function downloadCredential(int $adminId, int $index)
    {
        $admin = User::where('role', UserRole::Admin)->findOrFail($adminId);
        $path = $admin->credential_paths[$index] ?? null;

        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function approve(int $adminId): void
    {
        $admin = User::where('role', UserRole::Admin)->findOrFail($adminId);

        $admin->update([
            'status' => AdminStatus::Verified,
            'is_active' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => null,
        ]);

        AdminVerificationLog::create([
            'admin_id' => $admin->id,
            'performed_by' => Auth::id(),
            'action' => 'approved',
        ]);

        Mail::to($admin->email)->send(new AdminAccountApproved($admin));

        $this->showViewModal = false;
        $this->dispatch('toast', message: "{$admin->name} has been verified and can now log in.", type: 'success');
        $this->clearCache();
    }

    public function openRejectModal(int $adminId): void
    {
        $admin = User::where('role', UserRole::Admin)->findOrFail($adminId);
        $this->rejectingAdminId = $admin->id;
        $this->rejectingAdminName = $admin->name;
        $this->rejectReason = '';
        $this->showViewModal = false;
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->validate([
            'rejectReason' => ['required', 'string', 'min:5', 'max:1000'],
        ], [
            'rejectReason.required' => 'Please provide a reason for rejecting this account.',
        ]);

        $admin = User::where('role', UserRole::Admin)->findOrFail($this->rejectingAdminId);

        $admin->update([
            'status' => AdminStatus::Rejected,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'rejection_reason' => $this->rejectReason,
        ]);

        AdminVerificationLog::create([
            'admin_id' => $admin->id,
            'performed_by' => Auth::id(),
            'action' => 'rejected',
            'reason' => $this->rejectReason,
        ]);

        Mail::to($admin->email)->send(new AdminAccountRejected($admin));

        $this->showRejectModal = false;
        $this->rejectingAdminId = null;
        $this->rejectingAdminName = '';
        $this->rejectReason = '';

        $this->dispatch('toast', message: 'Admin registration rejected.', type: 'success');
        $this->clearCache();
    }

    public function toggleActive(int $adminId): void
    {
        $admin = User::where('role', UserRole::Admin)->where('status', AdminStatus::Verified)->findOrFail($adminId);

        $admin->update(['is_active' => ! $admin->is_active]);

        AdminVerificationLog::create([
            'admin_id' => $admin->id,
            'performed_by' => Auth::id(),
            'action' => $admin->is_active ? 'activated' : 'deactivated',
        ]);

        $this->dispatch('toast', message: $admin->name . ' has been ' . ($admin->is_active ? 'activated' : 'deactivated') . '.', type: 'success');
        $this->clearCache();
    }

    public function deleteAdmin(int $adminId): void
    {
        $admin = User::where('role', UserRole::Admin)->findOrFail($adminId);
        $name = $admin->name;

        if ($admin->valid_id_path) {
            Storage::disk('local')->delete($admin->valid_id_path);
        }

        if ($admin->live_photo_path) {
            Storage::disk('local')->delete($admin->live_photo_path);
        }

        foreach ($admin->credential_paths ?? [] as $path) {
            Storage::disk('local')->delete($path);
        }

        $admin->delete();

        $this->showViewModal = false;
        $this->dispatch('toast', message: "{$name} has been deleted.", type: 'success');
        $this->clearCache();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEditModal(int $adminId): void
    {
        $admin = User::where('role', UserRole::Admin)->findOrFail($adminId);
        $this->editingAdminId = $admin->id;
        $this->formName = $admin->name;
        $this->formEmail = $admin->email;
        $this->formMarketId = $admin->market_id;
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
                $this->editingAdminId
                    ? Rule::unique(User::class, 'email')->ignore($this->editingAdminId)
                    : Rule::unique(User::class, 'email'),
            ],
            'formMarketId' => ['required', Rule::exists(Market::class, 'id')],
        ];

        if (! $this->editingAdminId) {
            $rules['formPassword'] = ['required', 'string', 'min:8', 'same:formPasswordConfirmation'];
        } elseif ($this->formPassword !== '') {
            $rules['formPassword'] = ['string', 'min:8', 'same:formPasswordConfirmation'];
        }

        $this->validate($rules, [
            'formName.required' => 'Name is required.',
            'formEmail.required' => 'Email is required.',
            'formMarketId.required' => 'Please select a municipality/market.',
            'formPassword.same' => 'Passwords do not match.',
        ]);

        if ($this->editingAdminId) {
            $admin = User::where('role', UserRole::Admin)->findOrFail($this->editingAdminId);
            $admin->name = $this->formName;
            $admin->email = $this->formEmail;
            $admin->market_id = $this->formMarketId;

            if ($this->formPassword !== '') {
                $admin->password = Hash::make($this->formPassword);
            }

            $admin->save();
            $this->dispatch('toast', message: 'Admin account updated successfully.', type: 'success');
        } else {
            $admin = User::create([
                'name' => $this->formName,
                'email' => $this->formEmail,
                'password' => Hash::make($this->formPassword),
                'role' => UserRole::Admin,
                'market_id' => $this->formMarketId,
                'status' => AdminStatus::Verified,
                'is_active' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
            ]);

            AdminVerificationLog::create([
                'admin_id' => $admin->id,
                'performed_by' => Auth::id(),
                'action' => 'approved',
                'reason' => 'Created directly by Super Admin.',
            ]);

            $this->dispatch('toast', message: 'Admin account created successfully.', type: 'success');
        }

        $this->showModal = false;
        $this->resetForm();
        $this->clearCache();
    }

    private function resetForm(): void
    {
        $this->editingAdminId = null;
        $this->formName = '';
        $this->formEmail = '';
        $this->formMarketId = null;
        $this->formPassword = '';
        $this->formPasswordConfirmation = '';
        $this->resetValidation();
    }

    private function clearCache(): void
    {
        unset($this->admins, $this->pendingCount, $this->verifiedCount, $this->rejectedCount, $this->totalCount, $this->viewingAdmin);
    }

    public function render()
    {
        return $this->view()->title(__('Admin Accounts'));
    }
}; ?>

<div>
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Admin Accounts') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Verify, approve, and manage municipality admin accounts across every market.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary" wire:click="openCreateModal">
                {{ __('Create Admin') }}
            </flux:button>
        </div>

        {{-- Stats --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Admins') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">{{ $this->totalCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Pending Verification') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-amber-600">{{ $this->pendingCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Verified') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">{{ $this->verifiedCount }}</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Rejected') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-red-600">{{ $this->rejectedCount }}</flux:heading>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="{{ __('Search by name, email, or municipality…') }}" />
            </div>
            <flux:select wire:model.live="statusFilter" class="sm:w-48">
                <flux:select.option value="all">{{ __('All Statuses') }}</flux:select.option>
                <flux:select.option value="pending">{{ __('Pending Verification') }}</flux:select.option>
                <flux:select.option value="verified">{{ __('Verified') }}</flux:select.option>
                <flux:select.option value="rejected">{{ __('Rejected') }}</flux:select.option>
            </flux:select>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-orange-100 text-left dark:border-zinc-700">
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Admin') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Municipality') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Status') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Active') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Registered') }}</th>
                            <th class="px-6 py-3 font-medium text-zinc-500 dark:text-zinc-400">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-orange-100 dark:divide-zinc-700">
                        @forelse($this->admins as $admin)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50" wire:key="admin-{{ $admin->id }}">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$admin->name" />
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-zinc-100">{{ $admin->name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $admin->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $admin->market?->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$admin->status->color()" size="sm">{{ $admin->status->label() }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                @if($admin->status === \App\Enums\AdminStatus::Verified)
                                <flux:badge :color="$admin->is_active ? 'lime' : 'zinc'" size="sm">
                                    {{ $admin->is_active ? 'Active' : 'Deactivated' }}
                                </flux:badge>
                                @else
                                <span class="text-zinc-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-zinc-500 dark:text-zinc-400">{{ $admin->created_at->format('M j, Y') }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye" wire:click="openViewModal({{ $admin->id }})">{{ __('View Details') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil-square" wire:click="openEditModal({{ $admin->id }})">{{ __('Edit') }}</flux:menu.item>
                                        @if($admin->status === \App\Enums\AdminStatus::Verified)
                                        <flux:menu.item icon="{{ $admin->is_active ? 'no-symbol' : 'check-circle' }}" x-on:click="$dispatch('open-confirm', { title: '{{ $admin->is_active ? 'Deactivate' : 'Activate' }} Admin', message: '{{ $admin->is_active ? 'Deactivate' : 'Activate' }} {{ $admin->name }}? {{ $admin->is_active ? 'They will be signed out and unable to log in.' : 'They will regain access to the system.' }}', confirm: '{{ $admin->is_active ? 'Deactivate' : 'Activate' }}', variant: '{{ $admin->is_active ? 'danger' : 'primary' }}', onConfirm: () => $wire.toggleActive({{ $admin->id }}) })">
                                            {{ $admin->is_active ? __('Deactivate') : __('Activate') }}
                                        </flux:menu.item>
                                        @endif
                                        @if($admin->status === \App\Enums\AdminStatus::Pending)
                                        <flux:menu.separator />
                                        <flux:menu.item icon="check" x-on:click="$dispatch('open-confirm', { title: 'Approve Admin', message: 'Approve {{ $admin->name }} for {{ $admin->market?->name }}?', confirm: 'Approve', variant: 'primary', onConfirm: () => $wire.approve({{ $admin->id }}) })">{{ __('Approve') }}</flux:menu.item>
                                        <flux:menu.item icon="x-mark" variant="danger" wire:click="openRejectModal({{ $admin->id }})">{{ __('Reject') }}</flux:menu.item>
                                        @endif
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger" x-on:click="$dispatch('open-confirm', { title: 'Delete Admin', message: 'Permanently delete {{ $admin->name }}? This removes their account and all submitted documents. This cannot be undone.', confirm: 'Delete', variant: 'danger', onConfirm: () => $wire.deleteAdmin({{ $admin->id }}) })">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center gap-3 text-zinc-400">
                                    <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <p class="text-sm">{{ __('No admin accounts found.') }}</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-zinc-700">
                {{ $this->admins->links() }}
            </div>
        </div>
    </div>

    {{-- View Details Modal --}}
    <flux:modal wire:model="showViewModal" class="w-full max-w-3xl p-0! max-h-[90vh] overflow-hidden">
        @if($this->viewingAdmin)
        <div class="flex flex-col max-h-[90vh]">
            {{-- Header --}}
            <div class="flex items-start gap-4 p-6 pb-5 border-b border-zinc-100 dark:border-zinc-700">
                <flux:avatar size="lg" :name="$this->viewingAdmin->name" class="shrink-0" />
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <flux:heading size="lg" class="truncate">{{ $this->viewingAdmin->name }}</flux:heading>
                        <flux:badge :color="$this->viewingAdmin->status->color()" size="sm">{{ $this->viewingAdmin->status->label() }}</flux:badge>
                    </div>
                    <flux:subheading class="mt-0.5">{{ __('Review the identity documents submitted for :market.', ['market' => $this->viewingAdmin->market?->name ?? __('this account')]) }}</flux:subheading>
                </div>
            </div>

            {{-- Scrollable body --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-8">
                {{-- Info grid --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-xl border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Email') }}</p>
                        <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100 break-all">{{ $this->viewingAdmin->email }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Contact Number') }}</p>
                        <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->viewingAdmin->contact_number ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Municipality') }}</p>
                        <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->viewingAdmin->market?->name ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl border border-zinc-100 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800/50 px-4 py-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-zinc-400">{{ __('Registered') }}</p>
                        <p class="mt-0.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $this->viewingAdmin->created_at->format('M j, Y g:i A') }}</p>
                    </div>
                </div>

                {{-- Identity photos --}}
                <div>
                    <flux:text class="text-sm font-semibold mb-3">{{ __('Identity Verification') }}</flux:text>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">{{ __('Valid ID') }}</p>
                            @if($this->viewingAdmin->valid_id_path)
                            <a href="{{ route('super-admin.admins.photo', [$this->viewingAdmin->id, 'valid_id']) }}" target="_blank" rel="noopener" class="group relative block overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <img src="{{ route('super-admin.admins.photo', [$this->viewingAdmin->id, 'valid_id']) }}" alt="{{ __('Valid ID') }}" class="w-full aspect-4/3 object-cover transition-transform duration-200 group-hover:scale-105" />
                                <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/30 transition-colors">
                                    <flux:icon.magnifying-glass-plus class="size-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </a>
                            @else
                            <div class="w-full aspect-4/3 flex flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-600 text-zinc-400">
                                <flux:icon.identification class="size-6" />
                                <span class="text-xs">{{ __('Not submitted') }}</span>
                            </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1.5">{{ __('Live Photo') }}</p>
                            @if($this->viewingAdmin->live_photo_path)
                            <a href="{{ route('super-admin.admins.photo', [$this->viewingAdmin->id, 'live_photo']) }}" target="_blank" rel="noopener" class="group relative block overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                                <img src="{{ route('super-admin.admins.photo', [$this->viewingAdmin->id, 'live_photo']) }}" alt="{{ __('Live Photo') }}" class="w-full aspect-4/3 object-cover transition-transform duration-200 group-hover:scale-105" />
                                <div class="absolute inset-0 flex items-center justify-center bg-black/0 group-hover:bg-black/30 transition-colors">
                                    <flux:icon.magnifying-glass-plus class="size-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" />
                                </div>
                            </a>
                            @else
                            <div class="w-full aspect-4/3 flex flex-col items-center justify-center gap-1.5 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-600 text-zinc-400">
                                <flux:icon.camera class="size-6" />
                                <span class="text-xs">{{ __('Not submitted') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Submitted documents --}}
                <div>
                    <flux:text class="text-sm font-semibold mb-3">{{ __('Submitted Documents') }}</flux:text>
                    @if(!empty($this->viewingAdmin->credential_paths))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($this->viewingAdmin->credential_paths as $index => $path)
                        <a href="#" wire:click.prevent="downloadCredential({{ $this->viewingAdmin->id }}, {{ $index }})" class="flex items-center gap-3 rounded-xl border border-zinc-200 dark:border-zinc-700 px-3.5 py-3 text-sm text-zinc-700 dark:text-zinc-300 hover:border-orange-300 dark:hover:border-orange-700 hover:bg-orange-50 dark:hover:bg-zinc-800 transition-colors">
                            <span class="flex size-9 items-center justify-center rounded-lg bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 shrink-0">
                                <flux:icon.paper-clip class="size-4" />
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block font-medium truncate">{{ __('Document') }} {{ $index + 1 }}</span>
                                <span class="block text-xs text-zinc-400">{{ __('Click to download') }}</span>
                            </span>
                        </a>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-zinc-400">{{ __('No documents were submitted.') }}</p>
                    @endif
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex justify-end gap-3 px-6 py-4 border-t border-zinc-100 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
                @if($this->viewingAdmin->status === \App\Enums\AdminStatus::Pending)
                <flux:button variant="ghost" wire:click="$set('showViewModal', false)">{{ __('Close') }}</flux:button>
                <flux:button variant="danger" wire:click="openRejectModal({{ $this->viewingAdmin->id }})">{{ __('Reject') }}</flux:button>
                <flux:button variant="primary" wire:click="approve({{ $this->viewingAdmin->id }})" wire:loading.attr="disabled" wire:target="approve">{{ __('Approve') }}</flux:button>
                @else
                <flux:button variant="primary" wire:click="$set('showViewModal', false)">{{ __('Close') }}</flux:button>
                @endif
            </div>
        </div>
        @endif
    </flux:modal>

    {{-- Reject Modal --}}
    <flux:modal wire:model="showRejectModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Reject Admin Account') }}</flux:heading>
                <flux:subheading>{{ __('Explain why :name\'s registration is being rejected. They will receive this by email.', ['name' => $rejectingAdminName]) }}</flux:subheading>
            </div>

            <form wire:submit="reject" class="space-y-4">
                <flux:textarea wire:model="rejectReason" :label="__('Rejection Reason')" rows="4" placeholder="{{ __('e.g. Submitted ID does not match the name on the account…') }}" />
                @error('rejectReason') <p class="text-xs text-red-500">{{ $message }}</p> @enderror

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showRejectModal', false)" wire:loading.attr="disabled" wire:target="reject">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="danger" type="submit" wire:loading.attr="disabled" wire:target="reject">{{ __('Reject Account') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>

    {{-- Create/Edit Modal --}}
    <flux:modal wire:model="showModal" class="max-w-lg">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ $editingAdminId ? __('Edit Admin') : __('Create Admin') }}</flux:heading>
                <flux:subheading>{{ $editingAdminId ? __('Update admin account details.') : __('Directly create a pre-verified admin account for a municipality.') }}</flux:subheading>
            </div>

            <form wire:submit="save" class="space-y-4">
                <flux:input wire:model="formName" :label="__('Full Name')" type="text" required />
                <flux:input wire:model="formEmail" :label="__('Email Address')" type="email" required />
                <flux:select wire:model="formMarketId" :label="__('Municipality / Market')">
                    <flux:select.option value="">{{ __('Select a municipality…') }}</flux:select.option>
                    @foreach($this->markets as $market)
                    <flux:select.option :value="$market->id">{{ $market->name }}</flux:select.option>
                    @endforeach
                </flux:select>
                <div class="grid grid-cols-2 gap-4">
                    <flux:input wire:model="formPassword" :label="$editingAdminId ? __('New Password (leave blank to keep)') : __('Password')" type="password" :required="!$editingAdminId" />
                    <flux:input wire:model="formPasswordConfirmation" :label="__('Confirm Password')" type="password" :required="!$editingAdminId" />
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <flux:button variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button variant="primary" type="submit">{{ $editingAdminId ? __('Update Admin') : __('Create Admin') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
