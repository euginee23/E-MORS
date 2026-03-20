<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Models\Collection;
use App\Models\Market;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::where('name', 'Pagadian City Public Market')->first();

        if (! $market) {
            return;
        }

        $mario = User::where('email', 'mario@emors.test')->first();
        $analiza = User::where('email', 'analiza@emors.test')->first();

        $vendors = Vendor::where('market_id', $market->id)
            ->with('stall')
            ->get()
            ->filter(fn ($v) => $v->stall !== null);

        // Daily stall fees by section (Philippine public market rates)
        $dailyRates = [
            'A' => 150.00, // Dry Goods
            'B' => 120.00, // Fish & Seafood
            'C' => 130.00, // Meat
            'D' => 100.00, // Fruits & Vegetables
        ];

        // Collectors alternate by section:
        // Mario handles Sections A & C, Analiza handles Sections B & D
        $collectorBySection = [
            'A' => $mario,
            'B' => $analiza,
            'C' => $mario,
            'D' => $analiza,
        ];

        // Generate collections for the last 30 calendar days (weekdays only = ~22 market days)
        // "Today" is whenever the seeder runs
        $today = Carbon::today();
        $receiptCounter = 1;
        $marketDays = [];

        for ($d = 30; $d >= 0; $d--) {
            $date = $today->copy()->subDays($d);
            // Market is open Mon-Sat (skip Sundays)
            if ($date->isSunday()) {
                continue;
            }
            $marketDays[] = $date;
        }

        // Vendors who occasionally miss payments (realistic patterns)
        $irregularVendors = [
            'Teresita Oliguino',   // expired permit — stopped paying ~2 weeks ago
            'Dolores Enriquez',    // expired permit — sporadic
            'Rosalinda Taguiam',   // pending permit — started recently
            'Mila Bagolcol',       // pending permit — started recently
        ];

        foreach ($marketDays as $date) {
            $isToday = $date->isToday();
            $daysAgo = $today->diffInDays($date);

            foreach ($vendors as $vendor) {
                $section = $vendor->stall->section;
                $amount = $dailyRates[$section] ?? 100.00;
                $collector = $collectorBySection[$section] ?? $mario;

                // Determine if this vendor pays on this date
                if ($vendor->contact_name === 'Teresita Oliguino') {
                    // Expired permit: paid until ~14 days ago, then stopped
                    if ($daysAgo < 14) {
                        continue;
                    }
                } elseif ($vendor->contact_name === 'Dolores Enriquez') {
                    // Expired permit: sporadic — pays ~3 days a week
                    if ($date->dayOfWeek % 2 === 0 && ! $isToday) {
                        continue;
                    }
                } elseif ($vendor->contact_name === 'Rosalinda Taguiam') {
                    // Pending permit: only started paying ~7 days ago
                    if ($daysAgo > 7) {
                        continue;
                    }
                } elseif ($vendor->contact_name === 'Mila Bagolcol') {
                    // Pending permit: only started paying ~5 days ago
                    if ($daysAgo > 5) {
                        continue;
                    }
                }

                // Payment status logic
                if ($isToday) {
                    // Today: some paid already (morning collection), some still pending
                    // Vendors in sections A & B already collected (morning round), C & D pending (afternoon)
                    $status = in_array($section, ['A', 'B'])
                        ? PaymentStatus::Paid
                        : PaymentStatus::Pending;
                } elseif (in_array($vendor->contact_name, ['Dolores Enriquez']) && $daysAgo <= 3) {
                    // Recent unpaid days for expired-permit vendor
                    $status = PaymentStatus::Overdue;
                } else {
                    $status = PaymentStatus::Paid;
                }

                // Payment method: mostly cash, occasional GCash
                $paymentMethod = 'cash';
                if ($vendor->contact_name === 'Fernando Lucero' && $date->dayOfWeek === Carbon::FRIDAY) {
                    $paymentMethod = 'gcash'; // Fernando pays via GCash on Fridays
                } elseif ($vendor->contact_name === 'Conchita Abella' && $daysAgo <= 5) {
                    $paymentMethod = 'gcash'; // Conchita recently switched to GCash
                }

                $collectorId = $status === PaymentStatus::Paid ? $collector?->id : null;

                $receiptNumber = sprintf('RCP-%d-%04d', $date->year, $receiptCounter);

                Collection::create([
                    'market_id' => $market->id,
                    'vendor_id' => $vendor->id,
                    'stall_id' => $vendor->stall->id,
                    'collector_id' => $collectorId,
                    'receipt_number' => $receiptNumber,
                    'amount' => $amount,
                    'payment_date' => $date->toDateString(),
                    'payment_method' => $paymentMethod,
                    'status' => $status,
                    'notes' => null,
                    'created_at' => $date->copy()->setTime(
                        in_array($section, ['A', 'B']) ? 7 : 13,
                        rand(0, 59),
                        rand(0, 59)
                    ),
                    'updated_at' => $date->copy()->setTime(
                        in_array($section, ['A', 'B']) ? 7 : 13,
                        rand(0, 59),
                        rand(0, 59)
                    ),
                ]);

                $receiptCounter++;
            }
        }
    }
}
