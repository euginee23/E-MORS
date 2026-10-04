<?php

use App\Enums\AdminStatus;
use App\Enums\PaymentStatus;
use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Enums\UserRole;
use App\Models\Collection;
use App\Models\Market;
use App\Models\Stall;
use App\Models\StallAssignment;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    Carbon::setTestNow('2026-10-15');

    $this->market = Market::create(['name' => 'San Pablo Market', 'address' => 'Poblacion']);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->collector = User::factory()->create([
        'role' => UserRole::Collector,
        'name' => 'Ana Reyes',
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->makeVendor = fn (string $name, string $business) => Vendor::create([
        'market_id' => $this->market->id,
        'business_name' => $business,
        'contact_name' => $name,
        'permit_status' => PermitStatus::Active,
    ]);

    $this->stall = Stall::create([
        'market_id' => $this->market->id,
        'stall_number' => 'DRY-02',
        'section' => 'DRY',
        'size' => '3x3m',
        'monthly_rate' => 2500,
        'status' => StallStatus::Available,
    ]);
});

afterEach(fn () => Carbon::setTestNow());

test('assigning and reassigning a stall records the renter history', function () {
    $maria = ($this->makeVendor)('Maria Santos', 'Santos variety store');
    $flora = ($this->makeVendor)('Flora Esrael', 'Flora dry goods');

    $this->stall->update(['vendor_id' => $maria->id, 'status' => 'occupied', 'rent_start' => '2024-06-01']);
    expect(StallAssignment::where('stall_id', $this->stall->id)->count())->toBe(1);

    $this->stall->update(['vendor_id' => $flora->id, 'rent_start' => '2026-01-06']);

    $history = StallAssignment::where('stall_id', $this->stall->id)->orderBy('id')->get();
    expect($history)->toHaveCount(2)
        ->and($history[0]->vendor_id)->toBe($maria->id)
        ->and($history[0]->end_date)->not->toBeNull()
        ->and($history[1]->vendor_id)->toBe($flora->id)
        ->and($history[1]->end_date)->toBeNull();

    $this->stall->update(['vendor_id' => null, 'status' => 'available', 'rent_start' => null]);
    expect(StallAssignment::whereNull('end_date')->count())->toBe(0);

    Livewire::actingAs($this->admin)
        ->test('pages::stalls.show', ['stall' => $this->stall])
        ->call('setTab', 'renters')
        ->assertSee('Stall Renters History', false)
        ->assertSeeInOrder(['Assignment Timeline', 'Flora Esrael', 'Maria Santos'])
        ->assertSee('Ended');
});

test('collection history marks a short month as partial and shows the balance', function () {
    $flora = ($this->makeVendor)('Flora Esrael', 'Flora dry goods');
    $this->stall->update(['vendor_id' => $flora->id, 'status' => 'occupied', 'rent_start' => '2026-09-01']);

    foreach ([['OR-1', '2026-09-03', 2500], ['OR-2', '2026-10-04', 1250]] as [$receipt, $date, $amount]) {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $flora->id,
            'stall_id' => $this->stall->id,
            'collector_id' => $this->collector->id,
            'receipt_number' => $receipt,
            'amount' => $amount,
            'payment_date' => $date,
            'status' => PaymentStatus::Paid,
        ]);
    }

    Livewire::actingAs($this->admin)
        ->test('pages::stalls.show', ['stall' => $this->stall])
        ->set('tab', 'collections')
        ->assertSee('OR-1')
        ->assertSee('Partial')
        ->assertSee('Balance due ₱1,250 for October 2026')
        ->assertSee('₱3,750'); // collected this year
});

test('the details tab shows the current renter and section snapshot', function () {
    $flora = ($this->makeVendor)('Flora Esrael', 'Flora dry goods');
    $this->stall->update(['vendor_id' => $flora->id, 'status' => 'occupied']);

    $this->actingAs($this->admin)
        ->get(route('stalls.show', $this->stall))
        ->assertOk()
        ->assertSee('DRY-02')
        ->assertSee('Current Renter')
        ->assertSee('Flora Esrael')
        ->assertSee('Stall Snapshot');
});

test('a stall from another market is not found', function () {
    $other = Market::create(['name' => 'Elsewhere', 'address' => 'Far']);
    $stall = Stall::create([
        'market_id' => $other->id,
        'stall_number' => 'X-01',
        'section' => 'X',
        'status' => StallStatus::Available,
    ]);

    $this->actingAs($this->admin)
        ->get(route('stalls.show', $stall))
        ->assertNotFound();
});
