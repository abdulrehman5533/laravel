<?php

namespace Database\Seeders;

use App\Models\HR\AttendanceRule;
use App\Models\HR\LeaveType;
use App\Models\HR\Shift;
use Illuminate\Database\Seeder;

class HRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Default Attendance Rule
        AttendanceRule::firstOrCreate(
            ['rule_name' => 'Default Company Rules'],
            [
                'shift_start' => '09:00',
                'shift_end' => '18:00',
                'grace_time_minutes' => 15,
                'half_day_late_minutes' => 120,
                'ot_rate_multiplier' => 1.5,
                'require_gps' => false,
                'require_selfie' => false,
                'is_default' => true,
                'weekly_off_days' => ['Sunday'],
            ]
        );

        // 2. Default Leave Types
        $leaveTypes = [
            [
                'name' => 'Casual Leave',
                'code' => 'CL',
                'is_paid' => true,
                'annual_allocation' => 12,
            ],
            [
                'name' => 'Sick Leave',
                'code' => 'SL',
                'is_paid' => true,
                'annual_allocation' => 8,
            ],
            [
                'name' => 'Privilege Leave',
                'code' => 'PL',
                'is_paid' => true,
                'annual_allocation' => 15,
            ],
            [
                'name' => 'Unpaid Leave',
                'code' => 'UL',
                'is_paid' => false,
                'annual_allocation' => 0,
            ],
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(['code' => $type['code']], $type);
        }

        // 3. Default Shifts
        Shift::firstOrCreate(
            ['name' => 'Morning Shift'],
            [
                'start_time' => '09:00',
                'end_time' => '18:00',
                'grace_period_minutes' => 15,
            ]
        );

        Shift::firstOrCreate(
            ['name' => 'Evening Shift'],
            [
                'start_time' => '12:00',
                'end_time' => '21:00',
                'grace_period_minutes' => 15,
            ]
        );
    }
}
