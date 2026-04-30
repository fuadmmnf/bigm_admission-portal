<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->decimal('written_exam_marks', 6, 2)->nullable()->after('payment_response');
            $table->decimal('viva_exam_marks', 6, 2)->nullable()->after('written_exam_marks');
            $table->foreignId('selected_category_id')
                ->nullable()
                ->after('viva_exam_marks')
                ->constrained('categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('selected_category_id');
            $table->dropColumn(['written_exam_marks', 'viva_exam_marks']);
        });
    }
};

