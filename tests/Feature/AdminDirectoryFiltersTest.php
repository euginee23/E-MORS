<?php

use App\Enums\AdminStatus;
use App\Enums\PaymentStatus;
use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Enums\UserRole;
use App\Models\Collection;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

beforeEach(function () {
    $this->market = Market::create(['name' => 'San Pablo Market', 'address' => 'Poblacion']);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->flora = Vendor::create([
        'market_id' => $this->market->id,
        'business_name' => 'Flora dry goods',
        'contact_name' => 'Flora Esrael',
        'permit_status' => PermitStatus::Active,
    ]);

    $this->idle = Vendor::create([
        'market_id' => $this->market->id,
        'business_name' => 'Idle Store',
        'contact_name' => 'Pedro Idle',
        'permit_status' => PermitStatus::Pending,
    ]);

    $stall = fn (array $attributes) => Stall::create($attributes + [
        'market_id' => $this->market->id,
        'size' => '3x3m',
        'monthly_rate' => 2500,
        'status' => StallStatus::Available,
    ]);

    $stall(['stall_number' => 'DRY-01', 'section' => 'DRY']);
    $stall(['stall_number' => 'DRY-02', 'section' => 'DRY', 'vendor_id' => $this->flora->id, 'status' => StallStatus::Occupied]);
    $stall(['stall_number' => 'DRY-03', 'section' => 'DRY', 'status' => StallStatus::Maintenance]);
    $stall(['stall_number' => 'WET-01', 'section' => 'WET', 'size' => '4x3m', 'monthly_rate' => 4000]);
});

test('the stall directory filters by status, size, assignment and rate', function () {
    $component = Livewire::actingAs($this->admin)->test('pages::stalls.index');

    $numbers = fn () => $component->get('stalls')->pluck('stall_number')->all();

    $component->set('sectionFilter', 'DRY')->set('statusFilter', 'maintenance');
    expect($numbers())->toBe(['DRY-03']);

    $component->call('clearFilters')->set('assignmentFilter', 'assigned');
    expect($numbers())->toBe(['DRY-02']);

    $component->call('clearFilters')->set('sizeFilter', '4x3m');
    expect($numbers())->toBe(['WET-01']);

    $component->call('clearFilters')->set('rateFilter', '4000');
    expect($numbers())->toBe(['WET-01']);

    $component->set('rateFilter', 'all')->set('sectionFilter', 'DRY')
        ->assertSee('Section: DRY')
        ->assertSee('3 stalls');
});

test('the stall directory opens the edit modal from the details page link', function () {
    $stall = Stall::where('stall_number', 'DRY-02')->first();

    $this->actingAs($this->admin)
        ->get(route('stalls.index', ['edit' => $stall->id]))
        ->assertOk()
        ->assertSee('Edit Stall');
});

test('vendor management filters by permit status, assignment and section', function () {
    $component = Livewire::actingAs($this->admin)->test('pages::vendors.index');
    $names = fn () => $component->get('vendors')->pluck('contact_name')->all();

    $component->set('permitFilter', 'pending');
    expect($names())->toBe(['Pedro Idle']);

    $component->call('clearFilters')->set('assignmentFilter', 'assigned');
    expect($names())->toBe(['Flora Esrael']);

    $component->call('clearFilters')->set('sectionFilter', 'WET');
    expect($names())->toBe([]);

    $component->set('sectionFilter', 'DRY')
        ->assertSee('Section: DRY')
        ->assertSee('1 vendor');
});

test('the collector detail modal narrows collections to the chosen dates', function () {
    $collector = User::factory()->create([
        'role' => UserRole::Collector,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $stall = Stall::where('stall_number', 'DRY-02')->first();

    foreach ([['RCP-JUL', '2026-07-10', 2500], ['RCP-SEP', '2026-09-10', 1500]] as [$receipt, $date, $amount]) {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->flora->id,
            'stall_id' => $stall->id,
            'collector_id' => $collector->id,
            'receipt_number' => $receipt,
            'amount' => $amount,
            'payment_date' => $date,
            'status' => PaymentStatus::Paid,
        ]);
    }

    $component = Livewire::actingAs($this->admin)
        ->test('pages::collectors.index')
        ->call('viewDetails', $collector->id)
        ->assertSet('collectorRangeStats.amount', 4000.0)
        ->set('detailFrom', '2026-09-01')
        ->set('detailTo', '2026-09-30')
        ->assertSet('periodFilter', 'custom')
        ->assertSet('collectorRangeStats.amount', 1500.0)
        ->assertSet('collectorRangeStats.count', 1);

    expect($component->get('collectorRecentCollections')->pluck('receipt_number')->all())->toBe(['RCP-SEP']);
});
