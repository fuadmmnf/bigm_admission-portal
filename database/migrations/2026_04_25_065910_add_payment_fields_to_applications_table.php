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
        // Payment columns are now included in the create_applications_table migration.
        // For fresh installs the table already has them; this guard only adds them on
        // existing databases that were created before the consolidated schema.
        if (! Schema::hasTable('applications')) {
            return;
        }

        Schema::table('applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('applications', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])
                    ->default('pending')
                    ->after('status');
            }
            if (! Schema::hasColumn('applications', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->unique()->after('payment_status');
            }
            if (! Schema::hasColumn('applications', 'payment_amount')) {
                $table->decimal('payment_amount', 10, 2)->nullable()->after('transaction_id');
            }
            if (! Schema::hasColumn('applications', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_amount');
            }
            if (! Schema::hasColumn('applications', 'payment_response')) {
                $table->json('payment_response')->nullable()->after('payment_method');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            foreach (['payment_status', 'transaction_id', 'payment_amount', 'payment_method', 'payment_response'] as $column) {
                if (Schema::hasColumn('applications', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
