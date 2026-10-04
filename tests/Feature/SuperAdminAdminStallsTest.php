<?php

use App\Enums\AdminStatus;
use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

test('super admin views every stall and its vendor for an admin\'s market', function () {
    $market = Market::create(['name' => 'San Pablo Public Market', 'address' => 'Poblacion']);

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $market->id,
        'status' => AdminStatus::Verified,
    ]);

    $flora = Vendor::create([
        'market_id' => $market->id,
        'business_name' => 'Flora dry goods',
        'contact_name' => 'Flora Esrael',
        'permit_status' => PermitStatus::Active,
    ]);

    Stall::create(['market_id' => $market->id, 'stall_number' => 'DRY-01', 'section' => 'DRY', 'status' => StallStatus::Available]);
    Stall::create(['market_id' => $market->id, 'stall_number' => 'DRY-02', 'section' => 'DRY', 'status' => StallStatus::Occupied, 'vendor_id' => $flora->id]);
    Stall::create(['market_id' => $market->id, 'stall_number' => 'WET-01', 'section' => 'WET', 'status' => StallStatus::Available]);

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    $component = Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.admins')
        ->call('openStallsModal', $admin->id)
        ->assertSet('showStallsModal', true)
        ->assertSee('Stall and Vendor Details')
        ->assertSee('San Pablo Public Market')
        ->assertSeeInOrder(['DRY-02', 'Flora Esrael'])
        ->assertSet('marketStallStats', ['stalls' => 3, 'occupied' => 1, 'vendors' => 1]);

    $component->set('stallsSection', 'WET');
    expect($component->get('marketStalls')->pluck('stall_number')->all())->toBe(['WET-01']);

    $component->set('stallsSection', 'all')->set('stallsSearch', 'Flora');
    expect($component->get('marketStalls')->pluck('stall_number')->all())->toBe(['DRY-02']);
});
