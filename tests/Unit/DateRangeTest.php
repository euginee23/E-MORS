<?php

namespace Tests\Unit;

use App\Support\DateRange;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-09-20 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_all_time_has_no_bound(): void
    {
        $this->assertNull(DateRange::resolve('all'));
        $this->assertNull(DateRange::resolve('anything-unknown'));
    }

    public function test_each_preset_resolves_to_the_expected_window(): void
    {
        $cases = [
            'today' => ['2026-09-20', '2026-09-20', 'Today'],
            'week' => ['2026-09-14', '2026-09-20', 'This Week'],
            'month' => ['2026-09-01', '2026-09-30', 'This Month'],
            'last_month' => ['2026-08-01', '2026-08-31', 'Last Month'],
            'quarter' => ['2026-07-01', '2026-09-30', 'This Quarter'],
            'year' => ['2026-01-01', '2026-12-31', 'This Year'],
        ];

        foreach ($cases as $period => [$start, $end, $label]) {
            $range = DateRange::resolve($period);

            $this->assertNotNull($range, "{$period} should resolve");
            $this->assertSame($start, $range->start->toDateString(), "{$period} start");
            $this->assertSame($end, $range->end->toDateString(), "{$period} end");
            $this->assertSame($label, $range->label, "{$period} label");
        }
    }

    public function test_a_custom_range_spans_both_dates_inclusively(): void
    {
        $range = DateRange::resolve('custom', '2026-06-09', '2026-09-07');

        $this->assertSame('2026-06-09', $range->start->toDateString());
        $this->assertSame('2026-09-07', $range->end->toDateString());
        $this->assertSame('Jun 9, 2026 – Sep 7, 2026', $range->label);
    }

    public function test_a_blank_custom_range_leaves_the_query_unbounded(): void
    {
        $this->assertNull(DateRange::resolve('custom'));
        $this->assertNull(DateRange::resolve('custom', '', ''));
    }

    public function test_a_half_filled_custom_range_falls_back_to_the_supplied_side(): void
    {
        $fromOnly = DateRange::resolve('custom', '2026-06-09', null);
        $this->assertSame('2026-06-09', $fromOnly->start->toDateString());
        $this->assertSame('2026-06-09', $fromOnly->end->toDateString());

        $toOnly = DateRange::resolve('custom', null, '2026-09-07');
        $this->assertSame('2026-09-07', $toOnly->start->toDateString());
        $this->assertSame('2026-09-07', $toOnly->end->toDateString());
    }

    public function test_a_reversed_custom_range_is_swapped_rather_than_returning_nothing(): void
    {
        $range = DateRange::resolve('custom', '2026-09-07', '2026-06-09');

        $this->assertSame('2026-06-09', $range->start->toDateString());
        $this->assertSame('2026-09-07', $range->end->toDateString());
    }

    public function test_unparseable_input_is_ignored(): void
    {
        $this->assertNull(DateRange::resolve('custom', 'not-a-date', 'also-not-a-date'));
    }
}
