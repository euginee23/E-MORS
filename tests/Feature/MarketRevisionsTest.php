<?php

namespace Tests\Feature;

use App\Enums\AdminStatus;
use App\Enums\PaymentStatus;
use App\Enums\RentalStatus;
use App\Enums\UserRole;
use App\Models\Collection;
use App\Models\Market;
use App\Models\Stall;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketRevisionsTest extends TestCase
{
    use RefreshDatabase;

    private Market $market;

    private Vendor $vendor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->market = Market::create(['name' => 'Test Market', 'address' => 'Test Address']);

        $vendorUser = User::factory()->create([
            'role' => UserRole::Vendor,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);

        $this->vendor = Vendor::create([
            'market_id' => $this->market->id,
            'user_id' => $vendorUser->id,
            'business_name' => 'Cadiz Gulayan',
            'contact_name' => 'Maria Santos',
            'contact_phone' => '09171234567',
            'product_type' => 'Vegetables',
            'permit_status' => 'active',
        ]);

        // Three stalls with different rental terms: expired, expiring soon, active.
        $terms = [
            ['A-01', now()->subDays(20)],
            ['A-02', now()->addDays(14)],
            ['A-03', now()->addYear()],
        ];

        foreach ($terms as [$number, $expiry]) {
            Stall::create([
                'market_id' => $this->market->id,
                'vendor_id' => $this->vendor->id,
                'stall_number' => $number,
                'section' => 'A',
                'size' => '3x3m',
                'monthly_rate' => 3000,
                'status' => 'occupied',
                'rent_start' => now()->subYear(),
                'rent_expiry' => $expiry,
            ]);
        }
    }

    private function admin(): User
    {
        return User::factory()->create([
            'role' => UserRole::Admin,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
            'is_active' => true,
        ]);
    }

    public function test_admin_pages_render(): void
    {
        $admin = $this->admin();

        foreach (['dashboard', 'vendors.index', 'stalls.index', 'collections.index', 'reports.index', 'announcements.index', 'announcements.create'] as $route) {
            $this->actingAs($admin)->get(route($route))->assertOk();
        }
    }

    public function test_vendors_table_shows_stall_status_and_expiry(): void
    {
        $this->actingAs($this->admin())
            ->get(route('vendors.index'))
            ->assertOk()
            ->assertSee('Stall Status')
            ->assertSee('Stall Expiry')
            // Permit status stays out of the table, but permit expiry is shown so an
            // edit from the modal is visible. Assert on the column header markup.
            ->assertDontSee('>Permit Status</th>', false)
            ->assertSee('>Permit Expiry</th>', false)
            // Worst-of rollup across the three stalls.
            ->assertSee('Expired')
            ->assertSee('A-01, A-02, A-03');
    }

    public function test_admin_collections_page_is_read_only(): void
    {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-TEST-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        $this->actingAs($this->admin())
            ->get(route('collections.index'))
            ->assertOk()
            ->assertSee('RCP-TEST-0001')
            ->assertDontSee('Record Payment');
    }

    public function test_announcements_index_has_no_quick_create(): void
    {
        $this->actingAs($this->admin())
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertDontSee('Quick Create')
            ->assertSee('Create Announcement');
    }

    public function test_vendor_sees_every_rented_stall(): void
    {
        $vendorUser = $this->vendor->user;

        $this->actingAs($vendorUser)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Stalls Rented')
            ->assertSee('My Stalls')
            ->assertSee('A-01')
            ->assertSee('A-02')
            ->assertSee('A-03')
            ->assertSee('Vegetables')          // category
            ->assertSee('9,000');              // total monthly rent across 3 stalls

        $this->actingAs($vendorUser)
            ->get(route('vendor.stall'))
            ->assertOk()
            ->assertSee('A-01')
            ->assertSee('A-02')
            ->assertSee('A-03')
            ->assertSee('Expiring Soon');
    }

    public function test_landing_page_has_no_market_layout_designer(): void
    {
        $this->get('/')->assertOk()->assertDontSee('Market Layout');
    }

    public function test_stall_assignment_list_keeps_vendors_that_already_rent(): void
    {
        // The vendor already holds three stalls and must still be selectable.
        $this->actingAs($this->admin())
            ->get(route('stalls.index'))
            ->assertOk()
            ->assertSee('Maria Santos')
            ->assertSee('3 stalls');
    }

    public function test_vendor_view_modal_shows_profile_and_all_stalls(): void
    {
        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::vendors.index')
            ->call('openViewModal', $this->vendor->id)
            ->assertSet('showViewModal', true)
            ->assertSee('Maria Santos')
            ->assertSee('Vegetables')          // category
            ->assertSee('Stalls Rented')
            ->assertSee('A-01')
            ->assertSee('A-02')
            ->assertSee('A-03')
            ->assertSee('9,000.00');           // total monthly rent
    }

    public function test_vendor_can_be_assigned_an_additional_stall(): void
    {
        $spare = Stall::create([
            'market_id' => $this->market->id,
            'stall_number' => 'B-09',
            'section' => 'B',
            'size' => '3x3m',
            'monthly_rate' => 2500,
            'status' => 'available',
        ]);

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::vendors.index')
            ->call('openAssignModal', $this->vendor->id)
            ->set('selectedStallId', $spare->id)
            ->set('assignRentStart', now()->toDateString())
            ->set('assignRentExpiry', now()->addYear()->toDateString())
            ->call('assignStall')
            ->assertHasNoErrors();

        $this->assertSame(4, $this->vendor->fresh()->stalls()->count());
        $this->assertSame('occupied', $spare->fresh()->status->value);
        $this->assertNotNull($spare->fresh()->rent_expiry);
    }

    public function test_stall_number_is_capped_at_twelve_characters(): void
    {
        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('openCreateModal')
            ->set('formStallNumber', str_repeat('X', 13))
            ->set('formSection', 'C')
            ->set('formSize', '3x3m')
            ->set('formMonthlyRate', '1000')
            ->call('save')
            ->assertHasErrors(['formStallNumber']);

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('openCreateModal')
            ->set('formStallNumber', str_repeat('X', 12))
            ->set('formSection', 'C')
            ->set('formSize', '3x3m')
            ->set('formMonthlyRate', '1000')
            ->call('save')
            ->assertHasNoErrors();
    }

    public function test_collector_must_pick_a_stall_for_a_multi_stall_vendor(): void
    {
        $collector = User::factory()->create([
            'role' => UserRole::Collector,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);

        $component = \Livewire\Livewire::actingAs($collector)
            ->test('pages::collector.collect')
            ->set('formVendorId', $this->vendor->id);

        // Three stalls means no auto-fill; the collector has to choose.
        $component->assertSet('formStallId', null);

        $component->set('formAmount', '3000')
            ->call('save')
            ->assertHasErrors(['formStallId']);

        $target = $this->vendor->stalls->firstWhere('stall_number', 'A-02');

        $component->set('formStallId', $target->id)
            ->assertSet('formAmount', '3000.00')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('collections', [
            'vendor_id' => $this->vendor->id,
            'stall_id' => $target->id,
        ]);
    }

    public function test_report_exports_an_xlsx_workbook(): void
    {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-XLSX-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        $response = \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::reports.index')
            ->set('period', 'year')
            ->call('export')
            ->assertFileDownloaded();

        $this->assertTrue(true, 'export streamed without error');
    }

    public function test_collector_pages_render_with_multi_stall_vendors(): void
    {
        $collector = User::factory()->create([
            'role' => UserRole::Collector,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);

        foreach (['collector.summary', 'collector.collect', 'collector.collections', 'collector.vendors'] as $route) {
            $this->actingAs($collector)->get(route($route))->assertOk();
        }

        // The assigned-vendors list must show every stall, not just the first.
        $this->actingAs($collector)
            ->get(route('collector.vendors'))
            ->assertOk()
            ->assertSee('A-01, A-02, A-03')
            ->assertSee('9,000');
    }

    public function test_vendors_table_does_not_n_plus_one_on_stalls(): void
    {
        // Several vendors, each with stalls, to expose per-row lazy loading.
        for ($i = 0; $i < 5; $i++) {
            $v = Vendor::create([
                'market_id' => $this->market->id,
                'business_name' => "Biz {$i}",
                'contact_name' => "Vendor {$i}",
                'permit_status' => 'active',
            ]);
            foreach (['X', 'Y'] as $suffix) {
                Stall::create([
                    'market_id' => $this->market->id,
                    'vendor_id' => $v->id,
                    'stall_number' => "{$suffix}-{$i}",
                    'section' => $suffix,
                    'size' => '3x3m',
                    'monthly_rate' => 1000,
                    'status' => 'occupied',
                    'rent_expiry' => now()->addMonths(6),
                ]);
            }
        }

        $queries = 0;
        \Illuminate\Support\Facades\DB::listen(function () use (&$queries) {
            $queries++;
        });

        $this->actingAs($this->admin())->get(route('vendors.index'))->assertOk();

        // 6 vendors x (stalls + rollup) would blow past this if the relation were lazy loaded.
        $this->assertLessThan(30, $queries, "vendors.index ran {$queries} queries — likely an N+1");
    }

    public function test_vendor_dashboard_loads_stalls_once(): void
    {
        $queries = 0;
        \Illuminate\Support\Facades\DB::listen(function ($q) use (&$queries) {
            if (str_contains($q->sql, 'from "stalls"')) {
                $queries++;
            }
        });

        $this->actingAs($this->vendor->user)->get(route('dashboard'))->assertOk();

        $this->assertLessThanOrEqual(2, $queries, "dashboard ran {$queries} stall queries");
    }

    private function stall(array $overrides = []): Stall
    {
        return Stall::create(array_merge([
            'market_id' => $this->market->id,
            'stall_number' => 'Z-'.fake()->unique()->numberBetween(10, 99),
            'section' => 'Z',
            'size' => '3x3m',
            'monthly_rate' => 1000,
            'status' => 'available',
        ], $overrides));
    }

    public function test_rental_status_boundaries(): void
    {
        $unassigned = $this->stall(['vendor_id' => null, 'rent_expiry' => now()->subDay()]);
        $this->assertSame(RentalStatus::Unassigned, $unassigned->rental_status, 'no vendor means no rental term');

        $noTerm = $this->stall(['vendor_id' => $this->vendor->id, 'rent_expiry' => null]);
        $this->assertSame(RentalStatus::Active, $noTerm->rental_status, 'open-ended rental counts as active');

        $today = $this->stall(['vendor_id' => $this->vendor->id, 'rent_expiry' => now()]);
        $this->assertSame(RentalStatus::Expiring, $today->rental_status, 'expiring today is not yet expired');

        $yesterday = $this->stall(['vendor_id' => $this->vendor->id, 'rent_expiry' => now()->subDay()]);
        $this->assertSame(RentalStatus::Expired, $yesterday->rental_status);

        $edge = $this->stall(['vendor_id' => $this->vendor->id, 'rent_expiry' => now()->addDays(30)]);
        $this->assertSame(RentalStatus::Expiring, $edge->rental_status, 'day 30 is still the warning window');

        $beyond = $this->stall(['vendor_id' => $this->vendor->id, 'rent_expiry' => now()->addDays(31)]);
        $this->assertSame(RentalStatus::Active, $beyond->rental_status, 'day 31 is outside the warning window');
    }

    public function test_vendor_with_no_stalls_rolls_up_to_unassigned(): void
    {
        $bare = Vendor::create([
            'market_id' => $this->market->id,
            'business_name' => 'No Stall Co',
            'contact_name' => 'Empty Vendor',
            'permit_status' => 'pending',
        ]);

        $this->assertSame(RentalStatus::Unassigned, $bare->stallRentalStatus());
        $this->assertNull($bare->soonestRentExpiry());
        $this->assertSame(0.0, $bare->totalMonthlyRent());

        $user = User::factory()->create([
            'role' => UserRole::Vendor,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);
        $bare->update(['user_id' => $user->id, 'permit_status' => 'active']);

        // EnsureVendorIsApproved holds a stall-less vendor on the waiting screen,
        // so the empty states in the vendor pages are a defensive fallback only.
        $this->actingAs($user)->get(route('vendor.stall'))->assertRedirect(route('vendor.pending'));
        $this->actingAs($user)->get(route('vendor.pending'))->assertOk();
    }

    public function test_vendor_pages_survive_losing_every_stall(): void
    {
        // Render the pages while the vendor still qualifies, then strip the stalls
        // to prove the singular-to-plural rewrite left no unguarded access behind.
        $vendorUser = $this->vendor->user;
        $this->actingAs($vendorUser)->get(route('vendor.stall'))->assertOk();

        Stall::where('vendor_id', $this->vendor->id)->update(['vendor_id' => null]);

        $component = \Livewire\Livewire::actingAs($vendorUser)->test('pages::vendor.stall');
        $component->assertOk()->assertSee('No Stall Assigned');

        \Livewire\Livewire::actingAs($vendorUser)
            ->test('pages::vendor.profile')
            ->assertOk()
            ->assertSee('Unassigned');

        \Livewire\Livewire::actingAs($vendorUser)
            ->test('pages::vendor.payments')
            ->assertOk();
    }

    public function test_rental_status_filter_matches_the_rollup(): void
    {
        // The seeded vendor has an expired stall, so it must appear under "expired" only.
        foreach ([
            RentalStatus::Expired->value => true,
            RentalStatus::Expiring->value => false,
            RentalStatus::Active->value => false,
            RentalStatus::Unassigned->value => false,
        ] as $filter => $shouldAppear) {
            $component = \Livewire\Livewire::actingAs($this->admin())
                ->test('pages::vendors.index')
                ->set('statusFilter', $filter);

            $shouldAppear
                ? $component->assertSee('Maria Santos')
                : $component->assertDontSee('Maria Santos');
        }
    }

    public function test_unassigning_a_vendor_clears_the_rental_term(): void
    {
        $stall = $this->vendor->stalls->first();
        $this->assertNotNull($stall->rent_expiry);

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('unassignVendor', $stall->id);

        $stall->refresh();
        $this->assertNull($stall->vendor_id);
        $this->assertNull($stall->rent_start);
        $this->assertNull($stall->rent_expiry);
        $this->assertSame('available', $stall->status->value);
        $this->assertSame(RentalStatus::Unassigned, $stall->rental_status);
    }

    public function test_deleting_a_vendor_frees_every_stall(): void
    {
        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::vendors.index')
            ->call('deleteVendor', $this->vendor->id);

        $this->assertSame(0, Stall::where('vendor_id', $this->vendor->id)->count());
        $this->assertSame(3, Stall::whereNull('vendor_id')->where('status', 'available')->count());
        $this->assertSame(0, Stall::whereNotNull('rent_expiry')->count());
    }

    public function test_editing_a_stall_to_unassign_clears_the_rental_term(): void
    {
        $stall = $this->vendor->stalls->first();

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('openEditModal', $stall->id)
            ->set('formVendorId', null)
            ->call('save')
            ->assertHasNoErrors();

        $stall->refresh();
        $this->assertNull($stall->vendor_id);
        $this->assertNull($stall->rent_expiry);
    }

    public function test_export_handles_a_period_with_no_collections(): void
    {
        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::reports.index')
            ->set('period', 'today')
            ->call('export')
            ->assertFileDownloaded();

        $this->actingAs($this->admin())
            ->get(route('reports.index'))
            ->assertOk()
            ->assertSee('No collections recorded for this period.');
    }

    public function test_admin_cannot_invoke_removed_collection_write_methods(): void
    {
        // Hiding the buttons is not enough — the Livewire actions must be gone,
        // otherwise a crafted request could still record or delete a payment.
        $collection = Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-GUARD-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        foreach (['save', 'openCreateModal', 'openEditModal', 'deleteCollection'] as $method) {
            try {
                \Livewire\Livewire::actingAs($this->admin())
                    ->test('pages::collections.index')
                    ->call($method, $collection->id);
                $this->fail("collections.index still exposes {$method}()");
            } catch (\Throwable $e) {
                $this->assertStringContainsString($method, $e->getMessage());
            }
        }

        $this->assertDatabaseHas('collections', ['receipt_number' => 'RCP-GUARD-0001']);
        $this->assertSame(1, Collection::count(), 'no collection was created or removed');
    }

    public function test_collector_cannot_bill_a_stall_that_is_not_the_vendors(): void
    {
        $other = Vendor::create([
            'market_id' => $this->market->id,
            'business_name' => 'Other Biz',
            'contact_name' => 'Other Vendor',
            'permit_status' => 'active',
        ]);
        $otherStall = Stall::create([
            'market_id' => $this->market->id,
            'vendor_id' => $other->id,
            'stall_number' => 'C-01',
            'section' => 'C',
            'size' => '3x3m',
            'monthly_rate' => 500,
            'status' => 'occupied',
        ]);

        $collector = User::factory()->create([
            'role' => UserRole::Collector,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);

        // Point the form at our vendor but hand it another vendor's stall id.
        try {
            \Livewire\Livewire::actingAs($collector)
                ->test('pages::collector.collect')
                ->set('formVendorId', $this->vendor->id)
                ->set('formStallId', $otherStall->id)
                ->set('formAmount', '500')
                ->call('save');
            $this->fail('a mismatched stall id was accepted');
        } catch (\Throwable $e) {
            // abort_unless(..., 422)
        }

        $this->assertSame(0, Collection::count(), 'no collection should have been written');
    }

    public function test_assigning_a_vendor_on_the_stalls_page_seeds_a_rental_term(): void
    {
        $spare = $this->stall();

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('openEditModal', $spare->id)
            ->set('formVendorId', $this->vendor->id)
            ->call('save')
            ->assertHasNoErrors();

        $spare->refresh();
        $this->assertSame('occupied', $spare->status->value);
        $this->assertNotNull($spare->rent_start, 'assigning from the stalls page must seed a term');
        $this->assertNotNull($spare->rent_expiry);
        $this->assertSame(RentalStatus::Active, $spare->rental_status);
    }

    public function test_rent_expiry_before_rent_start_is_rejected_with_a_readable_message(): void
    {
        $spare = $this->stall();

        $component = \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::stalls.index')
            ->call('openEditModal', $spare->id)
            ->set('formVendorId', $this->vendor->id)
            ->set('formRentStart', now()->addYear()->toDateString())
            ->set('formRentExpiry', now()->toDateString())
            ->call('save')
            ->assertHasErrors(['formRentExpiry']);

        $message = $component->errors()->first('formRentExpiry');
        $this->assertStringContainsString('rent expiry', $message);
        $this->assertStringNotContainsString('formRent', $message, 'internal field names must not leak to users');
    }

    public function test_export_does_not_leave_temp_files_behind(): void
    {
        $pattern = sys_get_temp_dir().'/emors_report_*';
        foreach (glob($pattern) ?: [] as $stale) {
            @unlink($stale);
        }

        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-TMP-0001',
            'amount' => 1500,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        // Livewire drains the stream to build the download payload, which is what
        // triggers the cleanup in the streamDownload callback.
        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::reports.index')
            ->set('period', 'year')
            ->call('export')
            ->assertFileDownloaded();

        $this->assertSame([], glob($pattern) ?: [], 'export left a temp file behind');
    }

    public function test_export_cleans_up_when_the_workbook_cannot_be_written(): void
    {
        $pattern = sys_get_temp_dir().'/emors_report_*';
        foreach (glob($pattern) ?: [] as $stale) {
            @unlink($stale);
        }

        $exporter = new \App\Actions\Reports\ExportCollectionReport(
            marketId: $this->market->id,
            start: now()->startOfYear(),
            end: now()->endOfYear(),
            periodLabel: 'This Year',
        );

        // An unwritable destination must surface as an error, not a silent empty file.
        $this->expectException(\Throwable::class);

        try {
            $exporter->writeTo('/proc/definitely-not-writable/report.xlsx');
        } finally {
            $this->assertSame([], glob($pattern) ?: [], 'a failed write must not strand a temp file');
        }
    }

    public function test_approving_an_application_seeds_a_rental_term(): void
    {
        $applicantUser = User::factory()->create([
            'role' => UserRole::Vendor,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);
        $applicant = Vendor::create([
            'market_id' => $this->market->id,
            'user_id' => $applicantUser->id,
            'business_name' => 'Applicant Biz',
            'contact_name' => 'Applicant Vendor',
            'product_type' => 'Fish',
            'permit_status' => 'pending',
        ]);
        $free = $this->stall();

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::applications.index')
            ->call('openAssignModal', $applicant->id)
            ->set('selectedStallId', $free->id)
            ->call('assignStall')
            ->assertHasNoErrors();

        $free->refresh();
        $this->assertSame($applicant->id, $free->vendor_id);
        $this->assertNotNull($free->rent_expiry, 'approved applicants must get a trackable rental term');
        $this->assertSame(RentalStatus::Active, $free->rental_status);
        $this->assertSame('active', $applicant->fresh()->permit_status->value);
    }

    // ─── Announcements: long text must wrap, not overflow ───

    public function test_long_announcement_text_is_set_up_to_wrap(): void
    {
        $admin = $this->admin();
        $runOn = str_repeat('asjghbfjasxasnnnnn', 40); // one unbroken 720-char word

        \App\Models\Announcement::create([
            'market_id' => $this->market->id,
            'author_id' => $admin->id,
            'title' => $runOn,
            'body' => $runOn,
            'category' => 'general',
            'published_at' => now(),
        ]);

        $html = $this->actingAs($this->vendor->user)
            ->get(route('vendor.announcements'))
            ->assertOk()
            ->getContent();

        // The card must be able to shrink, and the long word must be breakable.
        $this->assertStringContainsString('min-w-0', $html, 'flex child cannot shrink without min-w-0');
        $this->assertStringContainsString('wrap-break-word', $html, 'long words would overflow the card');
        $this->assertStringContainsString('whitespace-pre-line', $html, 'admin line breaks should survive');
    }

    public function test_announcement_wrapping_classes_are_compiled_into_the_stylesheet(): void
    {
        $css = '';
        foreach (glob(public_path('build/assets/*.css')) ?: [] as $file) {
            $css .= file_get_contents($file);
        }

        if ($css === '') {
            $this->markTestSkipped('assets not built');
        }

        // A class that never reaches the stylesheet fixes nothing on screen.
        $this->assertStringContainsString('overflow-wrap:break-word', $css);
        $this->assertStringContainsString('white-space:pre-line', $css);
    }

    // ─── Collector: receipt, email, and the vendor picker ───

    private function collector(): User
    {
        return User::factory()->create([
            'role' => UserRole::Collector,
            'market_id' => $this->market->id,
            'status' => AdminStatus::Verified,
        ]);
    }

    public function test_recording_a_payment_emails_the_vendor_a_receipt(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $target = $this->vendor->stalls->firstWhere('stall_number', 'A-02');

        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->set('formVendorId', $this->vendor->id)
            ->set('formStallId', $target->id)
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('lastEmailedTo', $this->vendor->user->email);

        \Illuminate\Support\Facades\Mail::assertSent(
            \App\Mail\PaymentReceipt::class,
            fn ($mail) => $mail->hasTo($this->vendor->user->email)
                && $mail->collection->receipt_number !== null
        );
    }

    public function test_a_vendor_without_an_email_still_gets_the_payment_recorded(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $walkIn = Vendor::create([
            'market_id' => $this->market->id,
            'business_name' => 'Walk In Biz',
            'contact_name' => 'Walk In Vendor',
            'permit_status' => 'active',
        ]);
        $stall = $this->stall(['vendor_id' => $walkIn->id, 'status' => 'occupied']);

        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->set('formVendorId', $walkIn->id)
            ->set('formStallId', $stall->id)
            ->set('formAmount', '750')
            ->call('save')
            ->assertHasNoErrors()
            ->assertSet('lastEmailedTo', null);

        // The money is what matters; a missing address must not block the collection.
        $this->assertDatabaseHas('collections', ['vendor_id' => $walkIn->id, 'amount' => 750]);
        \Illuminate\Support\Facades\Mail::assertNothingSent();
    }

    public function test_the_collector_sees_a_printable_receipt_after_collecting(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $target = $this->vendor->stalls->firstWhere('stall_number', 'A-01');

        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->set('formVendorId', $this->vendor->id)
            ->set('formStallId', $target->id)
            ->call('save')
            ->assertSee('Payment Recorded')
            ->assertSee('Print Receipt')
            ->assertSee('Maria Santos')
            ->assertSee('A-01')
            ->assertSeeHtml('window.print()');
    }

    public function test_admin_can_view_a_receipt_but_cannot_print_or_email_it(): void
    {
        $collection = Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-VIEW-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        \Livewire\Livewire::actingAs($this->admin())
            ->test('pages::collections.index')
            ->call('viewReceipt', $collection->id)
            ->assertSee('RCP-VIEW-0001')          // can view
            ->assertDontSee('Print Receipt')       // cannot print
            ->assertDontSeeHtml('window.print()')
            ->assertDontSee('Resend Email');
    }

    public function test_a_vendor_collected_today_drops_out_of_the_picker(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $other = Vendor::create([
            'market_id' => $this->market->id,
            'business_name' => 'Second Biz',
            'contact_name' => 'Juan Cruz',
            'permit_status' => 'active',
        ]);
        $this->stall(['vendor_id' => $other->id, 'status' => 'occupied']);

        $component = \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect');

        $component->assertSee('Maria Santos')->assertSee('Juan Cruz');

        // Collect from Maria, then confirm she leaves the list for the next collector.
        $component->set('formVendorId', $this->vendor->id)
            ->set('formStallId', $this->vendor->stalls->first()->id)
            ->call('save')
            ->assertHasNoErrors();

        $fresh = \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect');

        $fresh->assertDontSee('Maria Santos')
            ->assertSee('Juan Cruz')
            ->assertSee('already collected today');

        // The toggle brings her back, flagged as paid.
        $fresh->set('showCollectedToday', true)
            ->assertSee('Maria Santos')
            ->assertSee('PAID');
    }

    public function test_hiding_collected_vendors_again_clears_a_stale_selection(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-STALE-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->set('showCollectedToday', true)
            ->set('formVendorId', $this->vendor->id)
            ->assertSet('formVendorId', $this->vendor->id)
            ->set('showCollectedToday', false)
            // She is hidden again, so she must not stay silently selected.
            ->assertSet('formVendorId', null);
    }

    public function test_yesterdays_collection_does_not_hide_a_vendor_today(): void
    {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-YEST-0001',
            'amount' => 3000,
            'payment_date' => now()->subDay(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Paid,
        ]);

        // "ma activate rag balik nig kaugma" — the list resets the next day.
        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->assertSee('Maria Santos');
    }

    public function test_an_unpaid_collection_does_not_hide_a_vendor(): void
    {
        Collection::create([
            'market_id' => $this->market->id,
            'vendor_id' => $this->vendor->id,
            'stall_id' => $this->vendor->stalls->first()->id,
            'receipt_number' => 'RCP-PEND-0001',
            'amount' => 3000,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => PaymentStatus::Pending,
        ]);

        // Only a paid collection counts as collected.
        \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->assertSee('Maria Santos');
    }

    public function test_the_collector_can_resend_the_receipt_email(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $target = $this->vendor->stalls->first();

        $component = \Livewire\Livewire::actingAs($this->collector())
            ->test('pages::collector.collect')
            ->set('formVendorId', $this->vendor->id)
            ->set('formStallId', $target->id)
            ->call('save');

        \Illuminate\Support\Facades\Mail::assertSentCount(1);

        $component->call('resendReceiptEmail')
            ->assertSet('lastEmailedTo', $this->vendor->user->email);

        \Illuminate\Support\Facades\Mail::assertSentCount(2);
    }
}
