<?php

use App\Models\Application;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->string('selection_stage')->nullable()->after('status');
            $table->index(['exam_id', 'status', 'selection_stage'], 'applications_exam_status_stage_idx');
        });

        DB::table('applications')
            ->where('status', 'paid')
            ->update(['selection_stage' => Application::STAGE_PAID]);
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table): void {
            $table->dropIndex('applications_exam_status_stage_idx');
            $table->dropColumn('selection_stage');
        });
    }
};

