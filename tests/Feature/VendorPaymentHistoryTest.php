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
use Illuminate\Support\Carbon;
use Livewire\Livewire;

/**
 * Flora rents two stalls from September 2026. DRY-02 is paid in full for
 * September and October; SPACE-03 is paid for September and half of October.
 */
beforeEach(function () {
    Carbon::setTestNow('2026-10-15');

    $this->market = Market::create(['name' => 'San Pablo Market', 'address' => 'Poblacion']);

    $this->collector = User::factory()->create([
        'role' => UserRole::Collector,
        'market_id' => $this->market->id,
        'status' => AdminStatus::Verified,
    ]);

    $this->user = User::factory()->create([
        'role' => UserRole::Vendor,
        'market_id' => $this->market->id,
    ]);

    $this->vendor = Vendor::create([
        'market_id' => $this->market->id,
        'user_id' => $this->user->id,
        'business_name' => 'Flora dry goods',
        'contact_name' => 'Flora Esrael',
        'permit_status' => PermitStatus::Active,
    ]);

    $stall = fn (string $number, string $section, int $rate) => Stall::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_number' => $number,
        'section' => $section,
        'monthly_rate' => $rate,
        'status' => StallStatus::Occupied,
        'rent_start' => '2026-09-01',
        'rent_expiry' => '2027-09-01',
    ]);

    $this->dry = $stall('DRY-02', 'DRY', 2500);
    $this->space = $stall('SPACE-03', 'SPACE', 3500);

    $pay = fn (Stall $stall, string $receipt, string $date, int $amount) => Collection::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_id' => $stall->id,
        'collector_id' => $this->collector->id,
        'receipt_number' => $receipt,
        'amount' => $amount,
        'payment_date' => $date,
        'status' => PaymentStatus::Paid,
    ]);

    $pay($this->dry, 'RCP-DRY-SEP', '2026-09-25', 2500);
    $pay($this->dry, 'RCP-DRY-OCT', '2026-10-02', 2500);
    $pay($this->space, 'RCP-SPACE-SEP', '2026-09-25', 3500);
    $pay($this->space, 'RCP-SPACE-OCT', '2026-10-03', 1750);
});

afterEach(fn () => Carbon::setTestNow());

test('an unpaid balance row is generated for a short month', function () {
    Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->assertSee('RCP-DRY-OCT')
        ->assertSee('Balance for October 2026')
        ->assertSee('₱1,750')
        ->assertSet('statusFilter', 'all');
});

test('monitoring totals compare paid against what was due', function () {
    $summary = Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->get('summary');

    // Due: 2 stalls × 2 months = ₱12,000. Paid ₱10,250, unpaid ₱1,750.
    expect($summary)->toMatchArray([
        'paidCount' => 4,
        'paidAmount' => 10250.0,
        'unpaidCount' => 1,
        'unpaidAmount' => 1750.0,
        'due' => 12000.0,
        'collected' => 85,
    ]);
});

test('the stall filter narrows rows and totals to one stall', function () {
    Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->set('stallFilter', (string) $this->dry->id)
        ->assertSee('RCP-DRY-SEP')
        ->assertDontSee('RCP-SPACE-SEP')
        ->assertDontSee('Balance for October 2026')
        ->assertSet('summary.collected', 100);
});

test('the status filter shows only unpaid rows', function () {
    Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->set('statusFilter', 'unpaid')
        ->assertDontSee('RCP-DRY-OCT')
        ->assertSee('Balance for October 2026');
});

test('the period filter bounds both paid and unpaid rows', function () {
    Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->set('periodFilter', 'last_month')
        ->assertSee('RCP-DRY-SEP')
        ->assertDontSee('RCP-DRY-OCT')
        ->assertDontSee('Balance for October 2026');
});

test('the dashboard marks each stall paid or unpaid for the current month', function () {
    $this->actingAs($this->user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Rental Payment')
        ->assertSee('1 Paid')
        ->assertSee('1 Unpaid')
        ->assertSee('Partial')
        ->assertSee('₱1,750 balance');
});

test('payment history pages through merged records', function () {
    foreach (range(1, 12) as $day) {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->dry->id,
            'collector_id' => $this->collector->id,
            'receipt_number' => sprintf('RCP-EXTRA-%02d', $day),
            'amount' => 10,
            'payment_date' => sprintf('2026-08-%02d', $day),
            'status' => PaymentStatus::Paid,
        ]);
    }

    Livewire::actingAs($this->user)
        ->test('pages::vendor.payments')
        ->assertSee('RCP-DRY-OCT')
        ->assertDontSee('RCP-EXTRA-01')
        ->call('gotoPage', 2)
        ->assertSee('RCP-EXTRA-01')
        ->assertDontSee('RCP-DRY-OCT')
        ->assertSeeHtml('wire:click="previousPage');
});
