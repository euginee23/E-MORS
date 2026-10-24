<?php

use App\Enums\AdminStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\User;

function marketWithAdmin(string $name, AdminStatus $status): Market
{
    $market = Market::create(['name' => $name, 'address' => "{$name} Road"]);

    User::factory()->create([
        'role' => UserRole::Admin,
        'status' => $status,
        'market_id' => $market->id,
    ]);

    return $market;
}

test('home page only lists markets whose admin is verified', function () {
    marketWithAdmin('Verified Market', AdminStatus::Verified);
    marketWithAdmin('Pending Market', AdminStatus::Pending);
    Market::create(['name' => 'Orphan Market', 'address' => 'Nowhere']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Verified Market')
        ->assertDontSee('Pending Market')
        ->assertDontSee('Orphan Market');
});

test('vendor application is open only for markets with a verified admin', function () {
    $open = marketWithAdmin('Verified Market', AdminStatus::Verified);
    $pending = marketWithAdmin('Pending Market', AdminStatus::Pending);

    $this->get(route('vendor.apply', $open))->assertOk();
    $this->get(route('vendor.apply', $pending))->assertNotFound();
});
