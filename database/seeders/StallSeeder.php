<?php

namespace Database\Seeders;

use App\Enums\StallStatus;
use App\Models\Market;
use App\Models\Stall;
use Illuminate\Database\Seeder;

class StallSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::where('name', 'Pagadian City Public Market')->first();

        if (! $market) {
            return;
        }

        $maintenanceStalls = ['A-07', 'B-05', 'C-10', 'D-03'];

        $sections = [
            'A' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3000.00],
            'B' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'C' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'D' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 2500.00],
        ];

        foreach ($sections as $section => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                $stallNumber = $section . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                $status = in_array($stallNumber, $maintenanceStalls)
                    ? StallStatus::Maintenance
                    : StallStatus::Available;

                Stall::updateOrCreate(
                    [
                        'market_id'    => $market->id,
                        'stall_number' => $stallNumber,
                    ],
                    [
                        'vendor_id'    => null,
                        'section'      => $section,
                        'size'         => $config['defaultSize'],
                        'monthly_rate' => $config['defaultRate'],
                        'status'       => $status,
                    ],
                );
            }
        }
    }
}
