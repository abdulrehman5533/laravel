<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;

class AttendancePolicy
{
    public function create(User $user)
    {
        // Only allow if user is active and not already checked in
        return $user->is_active;
    }

    public function update(User $user, Attendance $attendance)
    {
        // Only allow if user owns the attendance and not already checked out
        return $attendance->user_id === $user->id && $attendance->check_out_time === null;
    }

    public function override(User $user)
    {
        // Only admins can override
        return $user->is_admin;
    }
}
