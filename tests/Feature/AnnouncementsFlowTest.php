<?php

use App\Enums\PermitStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;

function createMarket(string $name = 'Market A'): Market
{
    return Market::create([
        'name' => $name,
        'address' => $name . ' Address',
    ]);
}

function createAdminForMarket(Market $market): User
{
    return User::factory()->create([
        'role' => UserRole::Admin,
        'market_id' => $market->id,
    ]);
}

function createApprovedVendorForMarket(Market $market): User
{
    $user = User::factory()->create([
        'role' => UserRole::Vendor,
        'market_id' => $market->id,
    ]);

    $vendorProfile = Vendor::create([
        'market_id' => $market->id,
        'user_id' => $user->id,
        'business_name' => 'Vendor Biz ' . $user->id,
        'contact_name' => $user->name,
        'permit_status' => PermitStatus::Active,
    ]);

    Stall::create([
        'market_id' => $market->id,
        'vendor_id' => $vendorProfile->id,
        'stall_number' => 'S-' . $user->id,
        'section' => 'A',
    ]);

    return $user;
}

test('vendor announcements shows only published announcements from same market', function () {
    $marketA = createMarket('Market A');
    $marketB = createMarket('Market B');

    $adminA = createAdminForMarket($marketA);

    Announcement::create([
        'market_id' => $marketA->id,
        'author_id' => $adminA->id,
        'title' => 'Published A',
        'body' => 'Visible to Market A vendors.',
        'category' => 'general',
        'published_at' => now()->subMinute(),
    ]);

    Announcement::create([
        'market_id' => $marketA->id,
        'author_id' => $adminA->id,
        'title' => 'Draft A',
        'body' => 'Should not be visible.',
        'category' => 'general',
        'published_at' => null,
    ]);

    Announcement::create([
        'market_id' => $marketB->id,
        'author_id' => $adminA->id,
        'title' => 'Published B',
        'body' => 'Should not be visible to Market A vendors.',
        'category' => 'general',
        'published_at' => now()->subMinute(),
    ]);

    $vendorA = createApprovedVendorForMarket($marketA);

    $this->actingAs($vendorA)
        ->get(route('vendor.announcements'))
        ->assertOk()
        ->assertSee('Published A')
        ->assertDontSee('Draft A')
        ->assertDontSee('Published B');
});

test('admin create page can create announcement visible to vendor when published', function () {
    $market = createMarket();
    $admin = createAdminForMarket($market);

    $this->actingAs($admin)
        ->get(route('announcements.create'))
        ->assertOk();

    Announcement::create([
        'market_id' => $market->id,
        'author_id' => $admin->id,
        'title' => 'Admin Post',
        'body' => 'New notice for vendors',
        'category' => 'policy',
        'published_at' => now(),
    ]);

    $vendor = createApprovedVendorForMarket($market);

    $this->actingAs($vendor)
        ->get(route('vendor.announcements'))
        ->assertOk()
        ->assertSee('Admin Post');
});
