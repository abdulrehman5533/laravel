<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$email = 'sariapratab@gmail.com';
$user = \App\Models\User::where('email', $email)->first();

if ($user) {
    $user->is_super_admin = true;
    $user->is_active = true;
    $user->save();
    echo "SUCCESS: User $email is now Super Admin.\n";
    
    // Also ensure the tenant has a proper plan if multi-tenancy is active
    $tenant = \App\Models\Tenant::first();
    if ($tenant) {
        $tenant->plan_id = 1; // Assuming 1 is Enterprise or highest plan
        $tenant->save();
        echo "SUCCESS: Tenant updated with plan_id 1.\n";
    }
} else {
    echo "ERROR: User $email not found.\n";
}
