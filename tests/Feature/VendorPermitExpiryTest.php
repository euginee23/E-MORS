<?php

use App\Enums\PermitStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('a permit expiry edited by the admin shows in the vendor list, the profile, and the vendor stall page', function () {
    $market = Market::create(['name' => 'San Pablo Market', 'address' => 'San Pablo Road']);

    $admin = User::factory()->create(['role' => UserRole::Admin, 'market_id' => $market->id]);
    $vendorUser = User::factory()->create(['role' => UserRole::Vendor, 'market_id' => $market->id]);

    $vendor = Vendor::create([
        'market_id' => $market->id,
        'user_id' => $vendorUser->id,
        'business_name' => 'Fish Stall',
        'contact_name' => 'Mira Samrano',
        'permit_status' => PermitStatus::Active,
    ]);

    Stall::create([
        'market_id' => $market->id,
        'vendor_id' => $vendor->id,
        'stall_number' => 'DRY-02',
        'section' => 'DRY',
        'status' => 'occupied',
        'rent_expiry' => '2027-09-20',
    ]);

    Livewire::actingAs($admin)
        ->test('pages::vendors.index')
        ->call('openEditModal', $vendor->id)
        ->set('formPermitExpiry', '2026-12-17')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSee('Dec 17, 2026')
        ->assertSee('Sep 20, 2027')
        ->call('openViewModal', $vendor->id)
        ->assertSeeInOrder(['Permit Expiry', 'Dec 17, 2026']);

    Livewire::actingAs($vendorUser)
        ->test('pages::vendor.stall')
        ->assertSeeInOrder(['Business Permit', 'Permit Expiry', 'Dec 17, 2026']);
});
