<?php

namespace Database\Seeders;

use App\Enums\PermitStatus;
use App\Models\Market;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $market = Market::where('name', 'Pagadian City Public Market')->first();

        if (! $market) {
            return;
        }

        $vendorUser = User::where('email', 'fernando@emors.test')->first();

        $vendors = [
            // Section A — Dry Goods & General Merchandise
            ['business_name' => 'Lucero General Merchandise', 'contact_name' => 'Fernando Lucero', 'contact_phone' => '09176543210', 'permit_number' => 'PGD-2026-0001', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31', 'user_id' => $vendorUser?->id],
            ['business_name' => 'Abella Rice & Grains Trading', 'contact_name' => 'Conchita Abella', 'contact_phone' => '09185551234', 'permit_number' => 'PGD-2026-0002', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Saño Dried Fish & Goods', 'contact_name' => 'Rodolfo Saño', 'contact_phone' => '09197654321', 'permit_number' => 'PGD-2026-0003', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Dinorog Spices & Condiments', 'contact_name' => 'Gloria Dinorog', 'contact_phone' => '09209876543', 'permit_number' => 'PGD-2026-0004', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-06-30'],
            ['business_name' => 'Catadman Clothing & Textiles', 'contact_name' => 'Marcelo Catadman', 'contact_phone' => '09171112233', 'permit_number' => 'PGD-2026-0005', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Taguiam Household Supplies', 'contact_name' => 'Rosalinda Taguiam', 'contact_phone' => '09183334455', 'permit_number' => 'PGD-2026-0006', 'permit_status' => PermitStatus::Pending, 'permit_expiry' => '2026-03-15'],

            // Section B — Fish & Seafood (Wet Section)
            ['business_name' => 'Pangan Fresh Fish', 'contact_name' => 'Romeo Pangan', 'contact_phone' => '09195556677', 'permit_number' => 'PGD-2026-0007', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Locopoc Seafood', 'contact_name' => 'Nena Locopoc', 'contact_phone' => '09207778899', 'permit_number' => 'PGD-2026-0008', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-09-30'],
            ['business_name' => 'Baguindoc Dried & Smoked Fish', 'contact_name' => 'Danilo Baguindoc', 'contact_phone' => '09172223344', 'permit_number' => 'PGD-2026-0009', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Oliguino Crabs & Shellfish', 'contact_name' => 'Teresita Oliguino', 'contact_phone' => '09184445566', 'permit_number' => 'PGD-2026-0010', 'permit_status' => PermitStatus::Expired, 'permit_expiry' => '2026-02-28'],
            ['business_name' => 'Jaballa Fish Vendors', 'contact_name' => 'Leonardo Jaballa', 'contact_phone' => '09196667788', 'permit_number' => 'PGD-2026-0011', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Sumalpong Fresh Catch', 'contact_name' => 'Maricel Sumalpong', 'contact_phone' => '09208889900', 'permit_number' => 'PGD-2026-0012', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],

            // Section C — Meat Section
            ['business_name' => 'Daguplo Pork & Lechon', 'contact_name' => 'Ernesto Daguplo', 'contact_phone' => '09171234001', 'permit_number' => 'PGD-2026-0013', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Villarosa Meat Shop', 'contact_name' => 'Carmen Villarosa', 'contact_phone' => '09181234002', 'permit_number' => 'PGD-2026-0014', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Maglinte Beef & Carabao', 'contact_name' => 'Juanito Maglinte', 'contact_phone' => '09191234003', 'permit_number' => 'PGD-2026-0015', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-11-30'],
            ['business_name' => 'Bagolcol Chicken & Poultry', 'contact_name' => 'Mila Bagolcol', 'contact_phone' => '09201234004', 'permit_number' => 'PGD-2026-0016', 'permit_status' => PermitStatus::Pending, 'permit_expiry' => '2026-03-31'],
            ['business_name' => 'Tumamak Native Chicken', 'contact_name' => 'Artemio Tumamak', 'contact_phone' => '09171234005', 'permit_number' => 'PGD-2026-0017', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],

            // Section D — Fruits & Vegetables
            ['business_name' => 'Montaño Vegetables', 'contact_name' => 'Erlinda Montaño', 'contact_phone' => '09181234006', 'permit_number' => 'PGD-2026-0018', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Calibod Fruits Trading', 'contact_name' => 'Nelson Calibod', 'contact_phone' => '09191234007', 'permit_number' => 'PGD-2026-0019', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Gamotin Root Crops & Produce', 'contact_name' => 'Rowena Gamotin', 'contact_phone' => '09201234008', 'permit_number' => 'PGD-2026-0020', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-08-31'],
            ['business_name' => 'Jalipa Coconut & Coco Products', 'contact_name' => 'Benjamin Jalipa', 'contact_phone' => '09171234009', 'permit_number' => 'PGD-2026-0021', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Lupogan Banana & Plantain', 'contact_name' => 'Lourdes Lupogan', 'contact_phone' => '09181234010', 'permit_number' => 'PGD-2026-0022', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
            ['business_name' => 'Enriquez Herbs & Organic', 'contact_name' => 'Dolores Enriquez', 'contact_phone' => '09191234011', 'permit_number' => 'PGD-2026-0023', 'permit_status' => PermitStatus::Expired, 'permit_expiry' => '2026-01-31'],
            ['business_name' => 'Sumagaysay Flowers & Plants', 'contact_name' => 'Amelita Sumagaysay', 'contact_phone' => '09201234012', 'permit_number' => 'PGD-2026-0024', 'permit_status' => PermitStatus::Active, 'permit_expiry' => '2026-12-31'],
        ];

        foreach ($vendors as $vendorData) {
            Vendor::updateOrCreate(
                [
                    'market_id' => $market->id,
                    'contact_name' => $vendorData['contact_name'],
                ],
                array_merge($vendorData, ['market_id' => $market->id]),
            );
        }
    }
}
