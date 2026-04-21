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
        Schema::create('production_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('job_number')->unique();
            $table->foreignId('product_bom_id')->constrained();
            $table->unsignedBigInteger('karigar_id');
            $table->unsignedBigInteger('branch_id');

            $table->enum('status', ['draft', 'issued', 'in_progress', 'completed', 'cancelled', 'reconciled'])->default('draft');

            // Issuance tracking
            $table->decimal('metal_weight_issued', 12, 4)->default(0);
            $table->unsignedBigInteger('issued_purity_id');
            $table->decimal('fine_weight_issued', 12, 4)->default(0);

            // Reception tracking
            $table->decimal('metal_weight_received', 12, 4)->default(0);
            $table->unsignedBigInteger('received_purity_id')->nullable();
            $table->decimal('fine_weight_received', 12, 4)->default(0);

            // Wastage & Loss
            $table->decimal('wastage_allowed', 12, 4)->default(0);
            $table->decimal('wastage_actual', 12, 4)->default(0);
            $table->decimal('gold_loss_weight', 12, 4)->default(0);

            // Financials
            $table->decimal('labor_charges', 15, 2)->default(0);
            $table->decimal('other_charges', 15, 2)->default(0);
            $table->decimal('total_job_cost', 15, 2)->default(0);

            $table->date('issue_date')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();

            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('karigar_id')->references('id')->on('suppliers');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('issued_purity_id')->references('id')->on('purity_levels');
            $table->foreign('received_purity_id')->references('id')->on('purity_levels');
            $table->foreign('created_by')->references('id')->on('users');
        });

        Schema::create('production_job_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_job_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['metal', 'stone', 'finding']);
            $table->string('item_name');

            // Issuance
            $table->decimal('issued_qty', 12, 4)->default(0);
            $table->decimal('issued_weight', 12, 4)->default(0);

            // Return
            $table->decimal('received_qty', 12, 4)->default(0);
            $table->decimal('received_weight', 12, 4)->default(0);
            $table->decimal('consumed_qty', 12, 4)->default(0);
            $table->decimal('broken_qty', 12, 4)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('production_job_items');
        Schema::dropIfExists('production_jobs');
    }
};
