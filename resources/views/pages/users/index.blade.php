<x-layouts::app :title="__('Users & Access Control')">
    <div class="flex h-full w-full flex-1 flex-col gap-6">
        {{-- Page Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">{{ __('Users & Access Control') }}</flux:heading>
                <flux:subheading class="mt-1">{{ __('Manage user accounts, roles, and system access permissions.') }}</flux:subheading>
            </div>
            <flux:button icon="plus" variant="primary">
                {{ __('Add User') }}
            </flux:button>
        </div>

        {{-- Stats Row --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Total Users') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold">15</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Administrators') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-blue-600">3</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Collectors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-purple-600">5</flux:heading>
            </div>
            <div class="rounded-2xl border border-orange-100 bg-white/80 backdrop-blur-sm p-4 shadow-sm dark:border-zinc-700 dark:bg-zinc-900/80">
                <flux:text class="text-sm text-zinc-500">{{ __('Vendors') }}</flux:text>
                <flux:heading size="xl" class="mt-1 text-2xl font-bold text-emerald-600">7</flux:heading>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="flex-1">
                <flux:input icon="magnifying-glass" placeholder="{{ __('Search users by name or email...') }}" />
            </div>
            <flux:select class="sm:w-40" placeholder="{{ __('Role') }}">
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
                        @php
                            $users = [
                                ['name' => 'Admin User', 'email' => 'admin@emors.test', 'role' => 'Admin', 'status' => 'Active', 'twofa' => true, 'created' => 'Jan 1, 2026'],
                                ['name' => 'Collector User', 'email' => 'collector@emors.test', 'role' => 'Collector', 'status' => 'Active', 'twofa' => false, 'created' => 'Jan 1, 2026'],
                                ['name' => 'Vendor User', 'email' => 'vendor@emors.test', 'role' => 'Vendor', 'status' => 'Active', 'twofa' => false, 'created' => 'Jan 1, 2026'],
                                ['name' => 'Maria Santos', 'email' => 'maria@vendor.test', 'role' => 'Vendor', 'status' => 'Active', 'twofa' => false, 'created' => 'Feb 15, 2026'],
                                ['name' => 'Juan Cruz', 'email' => 'juan@vendor.test', 'role' => 'Vendor', 'status' => 'Active', 'twofa' => false, 'created' => 'Mar 1, 2026'],
                                ['name' => 'Jose Reyes', 'email' => 'jose@collector.test', 'role' => 'Collector', 'status' => 'Inactive', 'twofa' => false, 'created' => 'Jan 20, 2026'],
                            ];
                        @endphp
                        @foreach($users as $user)
                        <tr class="hover:bg-orange-50/50 dark:hover:bg-zinc-800/50">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <flux:avatar size="sm" :name="$user['name']" />
                                    <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $user['name'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $user['email'] }}</td>
                            <td class="px-6 py-3">
                                @php
                                    $roleColor = match($user['role']) {
                                        'Admin' => 'blue',
                                        'Collector' => 'purple',
                                        'Vendor' => 'lime',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge :color="$roleColor" size="sm">{{ $user['role'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                <flux:badge :color="$user['status'] === 'Active' ? 'lime' : 'zinc'" size="sm">{{ $user['status'] }}</flux:badge>
                            </td>
                            <td class="px-6 py-3">
                                @if($user['twofa'])
                                    <flux:icon.shield-check class="size-5 text-emerald-500" />
                                @else
                                    <flux:icon.shield-exclamation class="size-5 text-zinc-400" />
                                @endif
                            </td>
                            <td class="px-6 py-3 text-zinc-700 dark:text-zinc-300">{{ $user['created'] }}</td>
                            <td class="px-6 py-3">
                                <flux:dropdown>
                                    <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal" />
                                    <flux:menu>
                                        <flux:menu.item icon="eye">{{ __('View') }}</flux:menu.item>
                                        <flux:menu.item icon="pencil-square">{{ __('Edit') }}</flux:menu.item>
                                        <flux:menu.item icon="key">{{ __('Reset Password') }}</flux:menu.item>
                                        <flux:menu.separator />
                                        <flux:menu.item icon="trash" variant="danger">{{ __('Delete') }}</flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-orange-100 px-6 py-3 dark:border-neutral-700">
                <flux:text class="text-sm text-zinc-500">{{ __('Showing 1-6 of 15 users') }}</flux:text>
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
</x-layouts::app>
