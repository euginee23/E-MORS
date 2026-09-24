<?php

use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

function seedMarketWithCounts(string $name, int $stalls, int $vendors): Market
{
    $market = Market::create(['name' => $name, 'address' => "{$name} Road"]);

    for ($i = 1; $i <= $vendors; $i++) {
        Vendor::create([
            'market_id' => $market->id,
            'business_name' => "{$name} Biz {$i}",
            'contact_name' => "Contact {$i}",
            'permit_status' => PermitStatus::Active,
        ]);
    }

    for ($i = 1; $i <= $stalls; $i++) {
        Stall::create([
            'market_id' => $market->id,
            'stall_number' => 'S-'.$i,
            'section' => 'A',
            'status' => StallStatus::Available,
        ]);
    }

    return $market;
}

test('super admin sees every market with its stall and vendor counts', function () {
    $tukuran = seedMarketWithCounts('Tukuran Market', stalls: 5, vendors: 3);
    seedMarketWithCounts('Pagadian Market', stalls: 2, vendors: 1);

    User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $tukuran->id,
        'name' => 'Juan Cruz',
    ]);

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.markets')
        ->assertSee('Tukuran Market')
        ->assertSee('Pagadian Market')
        ->assertSee('Juan Cruz')
        // Network totals across both markets.
        ->assertSet('totalStalls', 7)
        ->assertSet('totalVendors', 4);
});

test('the detail modal reports the summary for one market', function () {
    $tukuran = seedMarketWithCounts('Tukuran Market', stalls: 5, vendors: 3);
    seedMarketWithCounts('Pagadian Market', stalls: 2, vendors: 1);

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.markets')
        ->call('viewMarket', $tukuran->id)
        ->assertSet('showDetailModal', true)
        ->assertSet('viewingMarketId', $tukuran->id)
        // Only this market's figures, not the network's 7 stalls / 4 vendors.
        ->assertSee('5 Stalls')
        ->assertSee('3 Vendors');
});

test('search narrows the market list', function () {
    seedMarketWithCounts('Tukuran Market', stalls: 1, vendors: 1);
    seedMarketWithCounts('Pagadian Market', stalls: 1, vendors: 1);

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.markets')
        ->set('search', 'Tukuran')
        ->assertSee('Tukuran Market')
        ->assertDontSee('Pagadian Market');
});

test('a soft deleted vendor stops counting towards its market', function () {
    $market = seedMarketWithCounts('Tukuran Market', stalls: 2, vendors: 3);
    Vendor::where('market_id', $market->id)->first()->delete();

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.markets')
        ->assertSet('totalVendors', 2);
});

test('only the super admin can reach the markets page', function () {
    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);
    $this->actingAs($superAdmin)->get(route('super-admin.markets.index'))->assertOk();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('super-admin.markets.index'))->assertForbidden();

    $this->actingAs(User::factory()->create(['role' => UserRole::Collector]))
        ->get(route('super-admin.markets.index'))->assertForbidden();

    auth()->logout();
    $this->get(route('super-admin.markets.index'))->assertRedirect(route('login'));
});

test('the admin accounts list shows each admin market stall and vendor counts', function () {
    $tukuran = seedMarketWithCounts('Tukuran Market', stalls: 50, vendors: 47);

    $admin = User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $tukuran->id,
        'name' => 'Juan Cruz',
    ]);

    $superAdmin = User::factory()->create(['role' => UserRole::SuperAdmin, 'market_id' => null]);

    Livewire::actingAs($superAdmin)
        ->test('pages::super-admin.admins')
        ->assertSeeInOrder(['Juan Cruz', 'Tukuran Market', '50', '47'])
        ->call('openViewModal', $admin->id)
        ->assertSeeInOrder(['Stalls', '50', 'Vendors', '47']);
});
