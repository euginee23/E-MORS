<?php

use App\Enums\PermitStatus;
use App\Enums\UserRole;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;

/**
 * Create a vendor user that passes the vendor-approved middleware
 * (active permit status + assigned stall).
 */
function createApprovedVendor(): User
{
    $user = User::factory()->create(['role' => UserRole::Vendor]);
    $market = Market::create(['name' => 'Test Market', 'address' => '123 Test St']);
    $vendorProfile = Vendor::create([
        'market_id' => $market->id,
        'user_id' => $user->id,
        'business_name' => 'Test Business',
        'contact_name' => 'Test Contact',
        'permit_status' => PermitStatus::Active,
    ]);
    Stall::create([
        'market_id' => $market->id,
        'vendor_id' => $vendorProfile->id,
        'stall_number' => 'A1',
        'section' => 'A',
    ]);

    return $user;
}

// ─── Admin Route Access ───

test('admin can access admin pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    $this->get(route('vendors.index'))->assertOk();
    $this->get(route('stalls.index'))->assertOk();
    $this->get(route('collections.index'))->assertOk();
    $this->get(route('reports.index'))->assertOk();
    $this->get(route('users.index'))->assertOk();
});

test('vendor cannot access admin pages', function () {
    $vendor = createApprovedVendor();

    $this->actingAs($vendor);

    $this->get(route('vendors.index'))->assertForbidden();
    $this->get(route('stalls.index'))->assertForbidden();
    $this->get(route('collections.index'))->assertForbidden();
    $this->get(route('reports.index'))->assertForbidden();
    $this->get(route('users.index'))->assertForbidden();
});

test('collector cannot access admin pages', function () {
    $collector = User::factory()->create(['role' => UserRole::Collector]);

    $this->actingAs($collector);

    $this->get(route('vendors.index'))->assertForbidden();
    $this->get(route('stalls.index'))->assertForbidden();
    $this->get(route('collections.index'))->assertForbidden();
    $this->get(route('reports.index'))->assertForbidden();
    $this->get(route('users.index'))->assertForbidden();
});

// ─── Vendor Route Access ───

test('vendor can access vendor pages', function () {
    $vendor = createApprovedVendor();

    $this->actingAs($vendor);

    $this->get(route('vendor.stall'))->assertOk();
    $this->get(route('vendor.payments'))->assertOk();
    $this->get(route('vendor.profile'))->assertOk();
    $this->get(route('vendor.announcements'))->assertOk();
});

test('admin cannot access vendor pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    $this->get(route('vendor.stall'))->assertForbidden();
    $this->get(route('vendor.payments'))->assertForbidden();
    $this->get(route('vendor.profile'))->assertForbidden();
    $this->get(route('vendor.announcements'))->assertForbidden();
});

test('collector cannot access vendor pages', function () {
    $collector = User::factory()->create(['role' => UserRole::Collector]);

    $this->actingAs($collector);

    $this->get(route('vendor.stall'))->assertForbidden();
    $this->get(route('vendor.payments'))->assertForbidden();
    $this->get(route('vendor.profile'))->assertForbidden();
    $this->get(route('vendor.announcements'))->assertForbidden();
});

// ─── Collector Route Access ───

test('collector can access collector pages', function () {
    $collector = User::factory()->create(['role' => UserRole::Collector]);

    $this->actingAs($collector);

    $this->get(route('collector.summary'))->assertOk();
    $this->get(route('collector.collect'))->assertOk();
    $this->get(route('collector.collections'))->assertOk();
    $this->get(route('collector.vendors'))->assertOk();
});

test('admin cannot access collector pages', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin);

    $this->get(route('collector.summary'))->assertForbidden();
    $this->get(route('collector.collect'))->assertForbidden();
    $this->get(route('collector.collections'))->assertForbidden();
    $this->get(route('collector.vendors'))->assertForbidden();
});

test('vendor cannot access collector pages', function () {
    $vendor = createApprovedVendor();

    $this->actingAs($vendor);

    $this->get(route('collector.summary'))->assertForbidden();
    $this->get(route('collector.collect'))->assertForbidden();
    $this->get(route('collector.collections'))->assertForbidden();
    $this->get(route('collector.vendors'))->assertForbidden();
});

// ─── Guest Access ───

test('guests cannot access vendor pages', function () {
    $this->get(route('vendor.stall'))->assertRedirect(route('login'));
    $this->get(route('vendor.payments'))->assertRedirect(route('login'));
});

test('guests cannot access collector pages', function () {
    $this->get(route('collector.summary'))->assertRedirect(route('login'));
    $this->get(route('collector.collect'))->assertRedirect(route('login'));
});
