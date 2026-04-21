<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (!Schema::hasColumn('branches', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
            if (!Schema::hasColumn('branches', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('branches', 'country')) {
                $table->string('country')->nullable()->after('state');
            }
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (Schema::hasColumn('branches', 'city')) {
                $table->dropColumn('city');
            }
            if (Schema::hasColumn('branches', 'state')) {
                $table->dropColumn('state');
            }
            if (Schema::hasColumn('branches', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
