<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('refinery_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->unsignedBigInteger('branch_id');
            $table->enum('status', ['open', 'sent_to_refinery', 'received', 'closed'])->default('open');

            $table->decimal('total_gross_weight_sent', 12, 4)->default(0);
            $table->decimal('estimated_fine_weight_sent', 12, 4)->default(0);

            $table->decimal('actual_gross_weight_received', 12, 4)->default(0);
            $table->decimal('actual_fine_weight_received', 12, 4)->default(0);

            $table->decimal('refining_loss_weight', 12, 4)->default(0);
            $table->decimal('refining_charges', 15, 2)->default(0);

            $table->date('sent_date')->nullable();
            $table->date('received_date')->nullable();

            $table->unsignedBigInteger('refiner_id')->nullable()->comment('Supplier of type Refiner');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('refiner_id')->references('id')->on('suppliers');
            $table->foreign('created_by')->references('id')->on('users');
        });

        // Add foreign key to Buybacks
        Schema::table('buybacks', function (Blueprint $table) {
            $table->unsignedBigInteger('refinery_batch_id')->nullable()->change();
            $table->foreign('refinery_batch_id')->references('id')->on('refinery_batches');
        });
    }

    public function down(): void
    {
        Schema::table('buybacks', function (Blueprint $table) {
            $table->dropForeign(['refinery_batch_id']);
        });
        Schema::dropIfExists('refinery_batches');
    }
};
