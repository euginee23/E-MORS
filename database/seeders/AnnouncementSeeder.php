<?php

namespace Database\Seeders;

use App\Enums\AnnouncementCategory;
use App\Models\Announcement;
use App\Models\Market;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::where('name', 'Pagadian City Public Market')->first();

        if (! $market) {
            return;
        }

        $admin = User::where('email', 'admin@emors.test')->first();

        if (! $admin) {
            return;
        }

        $announcements = [
            [
                'title' => 'Market Maintenance Schedule — April 2026',
                'body' => 'Please be informed that the market will undergo general cleaning and maintenance on April 12, 2026 (Sunday). The market will be closed from 8:00 PM Saturday to 4:00 AM Monday. All vendors are requested to secure their stalls and remove perishable items. Thank you for your cooperation.',
                'category' => AnnouncementCategory::Maintenance,
                'published_at' => Carbon::now()->subDays(2),
            ],
            [
                'title' => 'Updated Collection Hours Starting May 2026',
                'body' => 'Effective May 1, 2026, the daily collection schedule will be adjusted. Collectors will visit stalls between 6:00 AM and 10:00 AM. Please ensure your payment is ready during these hours to avoid delays. Late payments will incur a 5% surcharge.',
                'category' => AnnouncementCategory::Policy,
                'published_at' => Carbon::now()->subDays(4),
            ],
            [
                'title' => 'Fire Safety Inspection — April 20, 2026',
                'body' => 'The Bureau of Fire Protection will conduct a routine fire safety inspection on April 20, 2026. All vendors must ensure fire extinguishers are accessible and electrical connections are in proper condition. Non-compliant stalls may face temporary closure.',
                'category' => AnnouncementCategory::Safety,
                'published_at' => Carbon::now()->subDays(6),
            ],
            [
                'title' => 'Holiday Schedule — Araw ng Kagitingan',
                'body' => 'The market will observe regular hours during the Araw ng Kagitingan holiday on April 9, 2026. However, collection will be suspended on that day. Payments for that day will be collected the following business day.',
                'category' => AnnouncementCategory::Holiday,
                'published_at' => Carbon::now()->subDays(10),
            ],
            [
                'title' => 'Year-End Financial Report Available',
                'body' => 'The 2025 year-end financial summary for all vendors is now available. You may request a copy of your individual payment summary at the market administration office. Office hours: Monday to Friday, 8:00 AM to 5:00 PM.',
                'category' => AnnouncementCategory::General,
                'published_at' => Carbon::now()->subDays(20),
            ],
        ];

        foreach ($announcements as $data) {
            Announcement::updateOrCreate(
                [
                    'market_id' => $market->id,
                    'title' => $data['title'],
                ],
                array_merge($data, [
                    'market_id' => $market->id,
                    'author_id' => $admin->id,
                ]),
            );
        }
    }
}
