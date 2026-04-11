<?php

use App\Enums\PermitStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;

function createVendorWithState(PermitStatus $permitStatus, bool $assignStall = false): User
{
    $market = Market::create([
        'name' => 'Middleware Test Market',
        'address' => 'Test Address',
    ]);

    $user = User::factory()->create([
        'role' => UserRole::Vendor,
        'market_id' => $market->id,
    ]);

    $vendor = Vendor::create([
        'market_id' => $market->id,
        'user_id' => $user->id,
        'business_name' => 'Test Business',
        'contact_name' => 'Test Contact',
        'permit_status' => $permitStatus,
    ]);

    if ($assignStall) {
        Stall::create([
            'market_id' => $market->id,
            'vendor_id' => $vendor->id,
            'stall_number' => 'A-101',
            'section' => 'A',
            'status' => 'occupied',
        ]);
    }

    return $user;
}

test('pending vendor without assigned stall is redirected to pending page', function () {
    $vendorUser = createVendorWithState(PermitStatus::Pending, false);

    $this->actingAs($vendorUser)
        ->get(route('dashboard'))
        ->assertRedirect(route('vendor.pending'));
});

test('active vendor with assigned stall can access dashboard', function () {
    $vendorUser = createVendorWithState(PermitStatus::Active, true);

    $this->actingAs($vendorUser)
        ->get(route('dashboard'))
        ->assertOk();
});

test('vendor with assigned stall is normalized to active and can access dashboard', function () {
    $vendorUser = createVendorWithState(PermitStatus::Pending, true);

    $this->actingAs($vendorUser)
        ->get(route('dashboard'))
        ->assertOk();

    expect($vendorUser->vendor->fresh()->permit_status)->toBe(PermitStatus::Active);
});
