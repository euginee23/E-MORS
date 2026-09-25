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

/**
 * Three collections spread across known dates so a custom range can be asserted
 * to include the middle one and exclude the two on either side.
 */
beforeEach(function () {
    $this->market = Market::create(['name' => 'Tukuran Market', 'address' => 'Tukuran Road']);

    $this->admin = User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->collector = User::factory()->create([
        'role' => UserRole::Collector,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->vendor = Vendor::create([
        'market_id' => $this->market->id,
        'business_name' => 'Gulayan',
        'contact_name' => 'Maria Santos',
        'permit_status' => PermitStatus::Active,
    ]);

    $this->stall = Stall::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_number' => 'A-01',
        'section' => 'A',
        'status' => StallStatus::Occupied,
    ]);

    $this->record = function (string $receipt, string $date, float $amount) {
        return Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->stall->id,
            'collector_id' => $this->collector->id,
            'receipt_number' => $receipt,
            'amount' => $amount,
            'payment_date' => $date,
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);
    };

    ($this->record)('RCP-BEFORE', '2026-05-01', 1000);
    ($this->record)('RCP-INSIDE', '2026-07-15', 2500);
    ($this->record)('RCP-AFTER', '2026-10-01', 4000);
});

// ─── Admin collections ───

test('admin collections narrows to a custom range', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07')
        ->assertSee('RCP-INSIDE')
        ->assertDontSee('RCP-BEFORE')
        ->assertDontSee('RCP-AFTER');
});

test('admin collection statistics recalculate for the selected range', function () {
    $component = Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07');

    // Only the one in-range collection, not the ₱7,500 all-time total.
    $component->assertSee('₱ 2,500')
        ->assertSee('Jun 9, 2026 – Sep 7, 2026')
        ->assertSee('1 transaction');
});

test('admin all time totals include every collection', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->assertSet('periodFilter', 'all')
        ->assertSee('₱ 7,500')
        ->assertSee('3 transactions');
});

test('a reversed admin range still returns the rows between the two dates', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-09-07')
        ->set('dateTo', '2026-06-09')
        ->assertSee('RCP-INSIDE')
        ->assertDontSee('RCP-AFTER');
});

test('clearing the admin custom range restores every collection', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07')
        ->assertDontSee('RCP-AFTER')
        ->call('clearCustomRange')
        ->assertSee('RCP-BEFORE')
        ->assertSee('RCP-AFTER');
});

test('a bad admin date is rejected rather than silently filtering', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::collections.index')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', 'not-a-date')
        ->assertHasErrors(['dateFrom' => 'date']);
});

// ─── Collector collections ───

test('collector my collections narrows to a custom range', function () {
    Livewire::actingAs($this->collector)
        ->test('pages::collector.collections')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07')
        ->assertSee('RCP-INSIDE')
        ->assertDontSee('RCP-BEFORE')
        ->assertDontSee('RCP-AFTER')
        // Summary cards follow the same window.
        ->assertSee('₱ 2,500')
        ->assertSee('Jun 9, 2026 – Sep 7, 2026');
});

test('collector totals cover the whole range, not just the current month', function () {
    Livewire::actingAs($this->collector)
        ->test('pages::collector.collections')
        ->set('periodFilter', 'custom')
        ->set('dateFrom', '2026-01-01')
        ->set('dateTo', '2026-12-31')
        ->assertSee('₱ 7,500');
});

// ─── Reports ───

test('reports recalculate for a custom range', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::reports.index')
        ->set('period', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07')
        ->assertSee('₱ 2,500')
        ->assertSee('Jun 9, 2026 – Sep 7, 2026')
        ->assertSee('RCP-INSIDE')
        ->assertDontSee('RCP-BEFORE')
        ->assertDontSee('RCP-AFTER');
});

test('an unfilled custom report range falls back to the month', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::reports.index')
        ->set('period', 'custom')
        ->assertSee('This Month');
});

test('presets keep resolving exactly as before', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::reports.index')
        ->set('period', 'year')
        ->assertSee('This Year')
        ->set('period', 'today')
        ->assertSee('Today');
});

test('a custom range report exports an xlsx', function () {
    Livewire::actingAs($this->admin)
        ->test('pages::reports.index')
        ->set('period', 'custom')
        ->set('dateFrom', '2026-06-09')
        ->set('dateTo', '2026-09-07')
        ->call('export')
        ->assertFileDownloaded();
});

test('reports show the trend, section, top vendor, and overdue panels', function () {
    ($this->record)('RCP-NOW', now()->toDateString(), 1750);

    Collection::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_id' => $this->stall->id,
        'collector_id' => $this->collector->id,
        'receipt_number' => 'RCP-LATE',
        'amount' => 900,
        'payment_date' => now()->subDays(5)->toDateString(),
        'payment_method' => 'cash',
        'status' => PaymentStatus::Overdue,
    ]);

    Livewire::actingAs($this->admin)
        ->test('pages::reports.index')
        ->assertSeeInOrder([
            'Monthly Revenue Trend',
            'Collection by Section', 'Section A', '100%',
            'Top Performing Vendors', 'Maria Santos', 'A-01', '₱ 1,750',
            'Overdue Payments', 'Maria Santos', '₱ 900',
            'Collection Ledger', 'RCP-NOW',
        ]);
});
