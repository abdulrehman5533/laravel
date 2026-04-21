<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

$user = User::where('email', 'sariapratab@gmail.com')->first();

if ($user) {
    echo "User Found:\n";
    echo "ID: " . $user->id . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Is Super Admin (Model): " . ($user->is_super_admin ? 'Yes' : 'No') . "\n";
    
    $rawUser = \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->first();
    echo "Is Super Admin (Raw DB): " . ($rawUser->is_super_admin ? 'Yes' : 'No') . " (Value: " . $rawUser->is_super_admin . ")\n";
    
    echo "User Type: " . $user->user_type . "\n";
    echo "Role: " . ($user->role ? $user->role->name . " (Slug: " . $user->role->slug . ")" : 'None') . "\n";
} else {
    echo "User NOT Found\n";
}

echo "\nAvailable Roles:\n";
foreach (\App\Models\Role::all() as $role) {
    echo " - " . $role->name . " (Slug: " . $role->slug . ", ID: " . $role->id . ")\n";
}
