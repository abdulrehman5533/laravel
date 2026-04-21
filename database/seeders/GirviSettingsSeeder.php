<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class GirviSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'girvi_default_interest_rate', 'value' => '1.5', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_default_interest_type', 'value' => 'simple', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_default_interest_cycle', 'value' => 'monthly', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_default_grace_period', 'value' => '3', 'group' => 'girvi', 'type' => 'integer'],
            ['key' => 'girvi_default_penal_interest', 'value' => '2', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_rounding_rule', 'value' => 'nearest', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_ltv_limit', 'value' => '75', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_high_risk_ltv', 'value' => '85', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_rate_source', 'value' => 'MCX', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_number_prefix', 'value' => 'GRV-', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_number_padding', 'value' => '6', 'group' => 'girvi', 'type' => 'integer'],
            ['key' => 'girvi_calculation_mode', 'value' => '30_days', 'group' => 'girvi', 'type' => 'string'],
            ['key' => 'girvi_interest_slabs', 'value' => json_encode([
                ['limit' => 50000, 'rate' => 2.0],
                ['limit' => 200000, 'rate' => 1.5],
                ['limit' => 500000, 'rate' => 1.2],
                ['limit' => null, 'rate' => 1.0],
            ]), 'group' => 'girvi', 'type' => 'json'],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
