<?php

namespace Database\Seeders;

use App\Enums\StallStatus;
use App\Models\Market;
use App\Models\Stall;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class StallSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::where('name', 'Pagadian City Public Market')->first();

        if (! $market) {
            return;
        }

        $vendors = Vendor::where('market_id', $market->id)->get()->keyBy('contact_name');

        // Section A — Dry Goods & General Merchandise (15 stalls)
        // Section B — Fish & Seafood / Wet Section (15 stalls)
        // Section C — Meat Section (15 stalls)
        // Section D — Fruits & Vegetables (15 stalls)

        $assignments = [
            // Section A — Dry Goods vendors
            'A-01' => ['vendor' => 'Fernando Lucero', 'size' => '4x4m', 'rate' => 4500.00],
            'A-02' => ['vendor' => 'Conchita Abella', 'size' => '4x4m', 'rate' => 4500.00],
            'A-03' => ['vendor' => 'Rodolfo Saño', 'size' => '3x3m', 'rate' => 3000.00],
            'A-04' => ['vendor' => 'Gloria Dinorog', 'size' => '3x3m', 'rate' => 3000.00],
            'A-05' => ['vendor' => 'Marcelo Catadman', 'size' => '4x4m', 'rate' => 4500.00],
            'A-06' => ['vendor' => 'Rosalinda Taguiam', 'size' => '3x3m', 'rate' => 3000.00],
            // A-07 to A-12 unassigned occupied (other vendors not in system)
            // A-13 available, A-14 maintenance, A-15 available

            // Section B — Fish & Seafood vendors
            'B-01' => ['vendor' => 'Romeo Pangan', 'size' => '3x3m', 'rate' => 3500.00],
            'B-02' => ['vendor' => 'Nena Locopoc', 'size' => '3x3m', 'rate' => 3500.00],
            'B-03' => ['vendor' => 'Danilo Baguindoc', 'size' => '3x3m', 'rate' => 3500.00],
            'B-04' => ['vendor' => 'Teresita Oliguino', 'size' => '3x3m', 'rate' => 3500.00],
            'B-05' => ['vendor' => 'Leonardo Jaballa', 'size' => '4x4m', 'rate' => 5000.00],
            'B-06' => ['vendor' => 'Maricel Sumalpong', 'size' => '3x3m', 'rate' => 3500.00],

            // Section C — Meat vendors
            'C-01' => ['vendor' => 'Ernesto Daguplo', 'size' => '4x4m', 'rate' => 4000.00],
            'C-02' => ['vendor' => 'Carmen Villarosa', 'size' => '4x4m', 'rate' => 4000.00],
            'C-03' => ['vendor' => 'Juanito Maglinte', 'size' => '3x3m', 'rate' => 3500.00],
            'C-04' => ['vendor' => 'Mila Bagolcol', 'size' => '3x3m', 'rate' => 3500.00],
            'C-05' => ['vendor' => 'Artemio Tumamak', 'size' => '3x3m', 'rate' => 3500.00],

            // Section D — Fruits & Vegetables vendors
            'D-01' => ['vendor' => 'Erlinda Montaño', 'size' => '3x3m', 'rate' => 2500.00],
            'D-02' => ['vendor' => 'Nelson Calibod', 'size' => '4x4m', 'rate' => 3500.00],
            'D-03' => ['vendor' => 'Rowena Gamotin', 'size' => '3x3m', 'rate' => 2500.00],
            'D-04' => ['vendor' => 'Benjamin Jalipa', 'size' => '3x3m', 'rate' => 2500.00],
            'D-05' => ['vendor' => 'Lourdes Lupogan', 'size' => '3x3m', 'rate' => 2500.00],
            'D-06' => ['vendor' => 'Dolores Enriquez', 'size' => '3x3m', 'rate' => 2500.00],
            'D-07' => ['vendor' => 'Amelita Sumagaysay', 'size' => '3x3m', 'rate' => 2500.00],
        ];

        $maintenanceStalls = ['A-14', 'C-12'];
        $availableStalls = ['A-13', 'A-15', 'B-14', 'B-15', 'C-14', 'C-15', 'D-14', 'D-15'];

        $sections = [
            'A' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3000.00],
            'B' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'C' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 3500.00],
            'D' => ['count' => 15, 'defaultSize' => '3x3m', 'defaultRate' => 2500.00],
        ];

        foreach ($sections as $section => $config) {
            for ($i = 1; $i <= $config['count']; $i++) {
                $stallNumber = $section . '-' . str_pad($i, 2, '0', STR_PAD_LEFT);

                $vendorId = null;
                $size = $config['defaultSize'];
                $rate = $config['defaultRate'];

                if (isset($assignments[$stallNumber])) {
                    $assignment = $assignments[$stallNumber];
                    $vendorId = $vendors->get($assignment['vendor'])?->id;
                    $size = $assignment['size'];
                    $rate = $assignment['rate'];
                    $status = StallStatus::Occupied;
                } elseif (in_array($stallNumber, $maintenanceStalls)) {
                    $status = StallStatus::Maintenance;
                } elseif (in_array($stallNumber, $availableStalls)) {
                    $status = StallStatus::Available;
                } else {
                    // Remaining stalls are occupied by vendors not tracked in the system
                    $status = StallStatus::Occupied;
                }

                Stall::updateOrCreate(
                    [
                        'market_id' => $market->id,
                        'stall_number' => $stallNumber,
                    ],
                    [
                        'market_id' => $market->id,
                        'vendor_id' => $vendorId,
                        'stall_number' => $stallNumber,
                        'section' => $section,
                        'size' => $size,
                        'monthly_rate' => $rate,
                        'status' => $status,
                    ],
                );
            }
        }
    }
}
