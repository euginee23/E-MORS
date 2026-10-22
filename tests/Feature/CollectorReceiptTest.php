<?php

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
    $this->market = Market::create(['name' => 'San Pablo Market', 'address' => 'San Pablo Road']);
    $this->collector = User::factory()->create(['role' => UserRole::Collector, 'market_id' => $this->market->id]);

    $this->vendor = Vendor::create([
        'market_id' => $this->market->id,
        'business_name' => 'Fish Stall',
        'contact_name' => 'Flora Esrael',
        'permit_status' => PermitStatus::Active,
    ]);

    $this->stall = Stall::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_number' => 'WET-01',
        'section' => 'WET',
        'status' => StallStatus::Occupied,
        'monthly_rate' => 3000,
    ]);
});

function recordPayment(string $method, string $reference = '')
{
    return Livewire::actingAs(test()->collector)
        ->test('pages::collector.collect')
        ->set('formVendorId', test()->vendor->id)
        ->set('formAmount', '3000')
        ->set('formPaymentMethod', $method)
        ->set('formReferenceNumber', $reference)
        ->call('save');
}

test('a gcash payment requires a reference number', function () {
    recordPayment('gcash')->assertHasErrors(['formReferenceNumber' => 'required_unless']);

    expect(Collection::count())->toBe(0);
});

test('a gcash reference is stored and shown in the collector and vendor tables', function () {
    recordPayment('gcash', '1012345678901')->assertHasNoErrors();

    expect(Collection::first()->reference_number)->toBe('1012345678901');

    Livewire::actingAs($this->collector)
        ->test('pages::collector.collections')
        ->assertSeeInOrder(['Reference No.', 'Gcash', '1012345678901'])
        ->set('search', '345678')
        ->assertSee('Flora Esrael');
});

test('a cash payment stores no reference even if one was typed', function () {
    recordPayment('cash', 'LEFTOVER')->assertHasNoErrors();

    expect(Collection::first()->reference_number)->toBeNull();
});

test('the print page shows only the receipt for the collector market', function () {
    recordPayment('bank_transfer', 'BDO-778899')->assertHasNoErrors();
    $collection = Collection::first();

    $this->actingAs($this->collector)
        ->get(route('collector.receipts.print', $collection))
        ->assertOk()
        ->assertSee($collection->receipt_number)
        ->assertSee('BDO-778899')
        ->assertSee('Flora Esrael')
        ->assertDontSee('Collection Details')
        ->assertDontSee('Record Collection');
});

test('a collector cannot print a receipt from another market', function () {
    $other = Market::create(['name' => 'Other Market', 'address' => 'Elsewhere']);
    $otherCollector = User::factory()->create(['role' => UserRole::Collector, 'market_id' => $other->id]);

    $collection = Collection::create([
        'market_id' => $this->market->id,
        'vendor_id' => $this->vendor->id,
        'stall_id' => $this->stall->id,
        'collector_id' => $this->collector->id,
        'receipt_number' => 'RCP-PRIVATE',
        'amount' => 3000,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'cash',
        'status' => PaymentStatus::Paid,
    ]);

    $this->actingAs($otherCollector)
        ->get(route('collector.receipts.print', $collection))
        ->assertNotFound();
});

test('a second market can record its first collection even when another market already used the same receipt number', function () {
    recordPayment('cash')->assertHasNoErrors();

    $otherMarket = Market::create(['name' => 'Tiguma Market', 'address' => 'Tiguma Road']);
    $otherCollector = User::factory()->create(['role' => UserRole::Collector, 'market_id' => $otherMarket->id]);
    $otherVendor = Vendor::create([
        'market_id' => $otherMarket->id,
        'business_name' => 'Dry Goods',
        'contact_name' => 'Merilyn Boniel',
        'permit_status' => PermitStatus::Active,
    ]);
    Stall::create([
        'market_id' => $otherMarket->id,
        'vendor_id' => $otherVendor->id,
        'stall_number' => 'A-01',
        'section' => 'DRY',
        'status' => StallStatus::Occupied,
        'monthly_rate' => 3000,
    ]);

    Livewire::actingAs($otherCollector)
        ->test('pages::collector.collect')
        ->set('formVendorId', $otherVendor->id)
        ->set('formAmount', '3000')
        ->set('formPaymentMethod', 'gcash')
        ->set('formReferenceNumber', '654532334562')
        ->call('save')
        ->assertHasNoErrors();

    $receipts = Collection::orderBy('id')->pluck('receipt_number', 'market_id');

    expect(Collection::count())->toBe(2)
        ->and($receipts[$this->market->id])->toBe($receipts[$otherMarket->id]);
});
