<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add multiple photos support
        if (!Schema::hasTable('girvi_item_photos')) {
            Schema::create('girvi_item_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('girvi_item_id')->constrained('girvi_items')->onDelete('cascade');
                $table->string('photo_path');
                $table->enum('photo_type', ['before', 'after', 'general'])->default('general');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Add partial release tracking
        if (!Schema::hasTable('girvi_partial_releases')) {
            Schema::create('girvi_partial_releases', function (Blueprint $table) {
                $table->id();
                $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
                $table->date('release_date');
                $table->decimal('amount_paid', 15, 2);
                $table->decimal('principal_component', 15, 2);
                $table->decimal('interest_component', 15, 2);
                $table->text('items_released')->nullable(); // JSON array of item IDs
                $table->foreignId('released_by')->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Add top-up loans
        if (!Schema::hasTable('girvi_topups')) {
            Schema::create('girvi_topups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('girvi_id')->constrained('girvis')->onDelete('cascade');
                $table->date('topup_date');
                $table->decimal('topup_amount', 15, 2);
                $table->decimal('new_interest_rate', 5, 2)->nullable();
                $table->date('new_maturity_date')->nullable();
                $table->text('additional_items')->nullable(); // JSON
                $table->foreignId('approved_by')->constrained('users');
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Add SMS/WhatsApp log enhancements
        Schema::table('girvi_reminders', function (Blueprint $table) {
            if (!Schema::hasColumn('girvi_reminders', 'message_id')) {
                $table->string('message_id')->nullable()->after('gateway_response');
            }
            if (!Schema::hasColumn('girvi_reminders', 'retry_count')) {
                $table->integer('retry_count')->default(0)->after('message_id');
            }
            if (!Schema::hasColumn('girvi_reminders', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable()->after('sent_at');
            }
        });

        // Add grace period tracking
        Schema::table('girvis', function (Blueprint $table) {
            if (!Schema::hasColumn('girvis', 'auto_renew')) {
                $table->boolean('auto_renew')->default(false)->after('is_high_risk');
            }
            if (!Schema::hasColumn('girvis', 'renewal_count')) {
                $table->integer('renewal_count')->default(0)->after('auto_renew');
            }
            if (!Schema::hasColumn('girvis', 'last_renewed_at')) {
                $table->date('last_renewed_at')->nullable()->after('renewal_count');
            }
        });

        // Auction enhancements (Safely)
        Schema::table('girvi_auctions', function (Blueprint $table) {
            if (!Schema::hasColumn('girvi_auctions', 'auction_number')) {
                $table->string('auction_number')->unique()->after('id');
            }
            if (!Schema::hasColumn('girvi_auctions', 'auction_status')) {
                $table->enum('auction_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled')->after('auction_date');
            }
            if (!Schema::hasColumn('girvi_auctions', 'winning_bid')) {
                $table->decimal('winning_bid', 15, 2)->nullable()->after('auction_status');
            }
            if (!Schema::hasColumn('girvi_auctions', 'winner_name')) {
                $table->string('winner_name')->nullable()->after('winning_bid');
            }
            if (!Schema::hasColumn('girvi_auctions', 'winner_contact')) {
                $table->string('winner_contact')->nullable()->after('winner_name');
            }
            if (!Schema::hasColumn('girvi_auctions', 'auction_notes')) {
                $table->text('auction_notes')->nullable()->after('winner_contact');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('girvi_item_photos');
        Schema::dropIfExists('girvi_partial_releases');
        Schema::dropIfExists('girvi_topups');
        
        Schema::table('girvi_reminders', function (Blueprint $table) {
            $table->dropColumn(['message_id', 'retry_count', 'delivered_at']);
        });
        
        Schema::table('girvis', function (Blueprint $table) {
            $table->dropColumn(['auto_renew', 'renewal_count', 'last_renewed_at']);
        });
        
        Schema::table('girvi_auctions', function (Blueprint $table) {
            $table->dropColumn(['auction_number', 'auction_status', 'winning_bid', 'winner_name', 'winner_contact', 'auction_notes']);
        });
    }
};
