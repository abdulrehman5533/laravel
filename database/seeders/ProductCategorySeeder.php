<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    public function run()
    {
        // Set tenant context if tenancy is used
        if (class_exists('App\\Models\\Tenant') && method_exists(app(), 'instance')) {
            $tenant = \App\Models\Tenant::first();
            if ($tenant) {
                app()->instance('current_tenant', $tenant);
            }
        }
        $categories = [
            ['name' => 'Rings', 'description' => 'Gold, silver, diamond rings', 'code' => 'RING', 'is_active' => true],
            ['name' => 'Necklaces', 'description' => 'Necklaces and chains', 'code' => 'NECK', 'is_active' => true],
            ['name' => 'Bracelets', 'description' => 'Bracelets and bangles', 'code' => 'BRAC', 'is_active' => true],
            ['name' => 'Earrings', 'description' => 'Earrings and studs', 'code' => 'EAR', 'is_active' => true],
            ['name' => 'Pendants', 'description' => 'Pendants and lockets', 'code' => 'PEND', 'is_active' => true],
            ['name' => 'Anklets', 'description' => 'Anklets and payal', 'code' => 'ANK', 'is_active' => true],
            ['name' => 'Brooches', 'description' => 'Brooches and pins', 'code' => 'BRO', 'is_active' => true],
            ['name' => 'Sets', 'description' => 'Jewellery sets', 'code' => 'SET', 'is_active' => true],
        ];
        foreach ($categories as $cat) {
            \App\Models\ProductCategory::updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
