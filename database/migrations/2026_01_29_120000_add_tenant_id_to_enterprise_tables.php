<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'workflows',
            'workflow_approvals',
            'workflow_steps',
            'notification_templates',
            'notification_history',
            'api_usage_logs',
            'document_access_logs',
            'api_keys',
            'audit_logs',
            'period_locks',
            'transaction_approvals',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && ! Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('tenant_id')->nullable()->after('id')->constrained()->onDelete('cascade');
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'workflows',
            'workflow_approvals',
            'workflow_steps',
            'notification_templates',
            'notification_history',
            'api_usage_logs',
            'document_access_logs',
            'api_keys',
            'audit_logs',
            'period_locks',
            'transaction_approvals',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'tenant_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropForeign(['tenant_id']);
                    $table->dropColumn('tenant_id');
                });
            }
        }
    }
};
