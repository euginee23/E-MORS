<?php

use App\Enums\PermitStatus;
use App\Enums\StallStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Livewire\Livewire;

function sectionTestMarket(): array
{
    $market = Market::create(['name' => 'Section Market', 'address' => 'Section Road']);
    $admin = User::factory()->create(['role' => UserRole::Admin, 'market_id' => $market->id]);

    Stall::create([
        'market_id' => $market->id,
        'stall_number' => 'DRY-01',
        'section' => 'DRY',
        'status' => StallStatus::Available,
    ]);

    return [$market, $admin];
}

test('a section can be renamed to a 12 letter name and its stall numbers follow', function () {
    [$market, $admin] = sectionTestMarket();

    Livewire::actingAs($admin)
        ->test('pages::stalls.index')
        ->call('startEditSection', 'DRY')
        ->set('editingSectionName', 'VEGETABLESAB')
        ->call('saveSection')
        ->assertHasNoErrors();

    expect(Stall::where('market_id', $market->id)->first())
        ->section->toBe('VEGETABLESAB')
        ->stall_number->toBe('VEGETABLESAB-01');
});

test('a section name longer than 12 letters is rejected', function () {
    [, $admin] = sectionTestMarket();

    Livewire::actingAs($admin)
        ->test('pages::stalls.index')
        ->call('startEditSection', 'DRY')
        ->set('editingSectionName', 'VEGETABLESABC')
        ->call('saveSection')
        ->assertHasErrors(['editingSectionName' => 'max']);

    Livewire::actingAs($admin)
        ->test('pages::stalls.index')
        ->set('newSectionLetter', 'VEGETABLESABC')
        ->call('createSection')
        ->assertHasErrors(['newSectionLetter' => 'max']);
});

test('a stall can be added under a 12 letter section', function () {
    [$market, $admin] = sectionTestMarket();

    Livewire::actingAs($admin)
        ->test('pages::stalls.index')
        ->set('newSectionLetter', 'VEGETABLESAB')
        ->call('createSection')
        ->assertSet('inlineStallNumber', 'VEGETABLESAB-01')
        ->call('quickSaveStall')
        ->assertHasNoErrors();

    expect(Stall::where('market_id', $market->id)->where('stall_number', 'VEGETABLESAB-01')->exists())->toBeTrue();
});

test('choosing the placeholder in the collector vendor and stall selects does not crash', function () {
    $market = Market::create(['name' => 'Collect Market', 'address' => 'Collect Road']);
    $collector = User::factory()->create(['role' => UserRole::Collector, 'market_id' => $market->id]);

    $vendor = Vendor::create([
        'market_id' => $market->id,
        'business_name' => 'Two Stall Biz',
        'contact_name' => 'Ana Reyes',
        'permit_status' => PermitStatus::Active,
    ]);

    foreach (['A-01', 'A-02'] as $number) {
        Stall::create([
            'market_id' => $market->id,
            'vendor_id' => $vendor->id,
            'stall_number' => $number,
            'section' => 'A',
            'status' => StallStatus::Occupied,
            'monthly_rate' => 1000,
        ]);
    }

    Livewire::actingAs($collector)
        ->test('pages::collector.collect')
        ->set('formVendorId', (string) $vendor->id)
        ->assertSet('formVendorId', $vendor->id)
        ->set('formStallId', '')
        ->assertSet('formStallId', null)
        ->set('formVendorId', '')
        ->assertSet('formVendorId', null);
});
