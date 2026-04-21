<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
use Illuminate\Support\Facades\Schema;

echo "GIRVI_ITEMS:\n";
print_r(Schema::getColumnListing('girvi_items'));
echo "\nGIRVIS:\n";
print_r(Schema::getColumnListing('girvis'));
echo "\nCUSTOMERS:\n";
print_r(Schema::getColumnListing('customers'));
