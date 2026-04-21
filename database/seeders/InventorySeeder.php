<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\PurityLevel;
use App\Models\StockLocation;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Create Product Categories
        $categories = [
            ['name' => 'Gold Rings', 'code' => 'GR', 'icon' => 'fas fa-ring'],
            ['name' => 'Silver Jewelry', 'code' => 'SJ', 'icon' => 'fas fa-ring'],
            ['name' => 'Diamonds', 'code' => 'DM', 'icon' => 'fas fa-gem'],
            ['name' => 'Gemstones', 'code' => 'GM', 'icon' => 'fas fa-gem'],
            ['name' => 'Pendants', 'code' => 'PN', 'icon' => 'fas fa-locket'],
            ['name' => 'Necklaces', 'code' => 'NK', 'icon' => 'fas fa-circle'],
            ['name' => 'Bracelets', 'code' => 'BR', 'icon' => 'fas fa-circle'],
            ['name' => 'Earrings', 'code' => 'ER', 'icon' => 'fas fa-earlybirds'],
            ['name' => 'Bangles', 'code' => 'BG', 'icon' => 'fas fa-circle'],
            ['name' => 'Chains', 'code' => 'CH', 'icon' => 'fas fa-link'],
        ];

        foreach ($categories as $category) {
            ProductCategory::firstOrCreate(
                ['code' => $category['code']],
                ['name' => $category['name'], 'icon' => $category['icon'], 'is_active' => true]
            );
        }

        // Create Purity Levels
        $purities = [
            ['name' => '24 Karat', 'karat' => 24, 'percentage' => 99.9],
            ['name' => '22 Karat', 'karat' => 22, 'percentage' => 91.6],
            ['name' => '21 Karat', 'karat' => 21, 'percentage' => 87.5],
            ['name' => '20 Karat', 'karat' => 20, 'percentage' => 83.3],
            ['name' => '18 Karat', 'karat' => 18, 'percentage' => 75.0],
            ['name' => '14 Karat', 'karat' => 14, 'percentage' => 58.5],
            ['name' => '10 Karat', 'karat' => 10, 'percentage' => 41.7],
            ['name' => 'Silver 925', 'karat' => 0, 'percentage' => 92.5],
        ];

        foreach ($purities as $purity) {
            PurityLevel::firstOrCreate(
                ['karat' => $purity['karat']],
                ['name' => $purity['name'], 'percentage' => $purity['percentage'], 'is_active' => true]
            );
        }

        // Create Stock Locations (if Branch exists)
        if (class_exists(\App\Models\Branch::class)) {
            $branches = \App\Models\Branch::first();

            if ($branches) {
                $locations = [
                    ['name' => 'Main Shop', 'code' => 'SHOP-001', 'type' => 'shop', 'branch_id' => $branches->id],
                    ['name' => 'Warehouse', 'code' => 'WH-001', 'type' => 'warehouse', 'branch_id' => $branches->id],
                    ['name' => 'Locker 1', 'code' => 'LOC-001', 'type' => 'locker', 'branch_id' => $branches->id],
                    ['name' => 'Safe Box', 'code' => 'SAFE-001', 'type' => 'safe', 'branch_id' => $branches->id],
                ];

                foreach ($locations as $location) {
                    StockLocation::firstOrCreate(
                        ['code' => $location['code']],
                        ['name' => $location['name'], 'type' => $location['type'], 'branch_id' => $location['branch_id'], 'is_active' => true]
                    );
                }
            }
        }
    }
}
