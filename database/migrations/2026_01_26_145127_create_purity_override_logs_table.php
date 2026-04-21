<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purity_override_logs', function (Blueprint $table) {
            $table->id();
            // Manually define morphs to avoid too long index names
            $table->string('purity_loggable_type');
            $table->unsignedBigInteger('purity_loggable_id');
            $table->index(['purity_loggable_type', 'purity_loggable_id'], 'purity_log_morph_index');

            $table->decimal('old_purity', 10, 2);
            $table->decimal('new_purity', 10, 2);
            $table->string('reason');
            $table->foreignId('user_id')->constrained();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purity_override_logs');
    }
};
