<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();

            // Application ID — YYYYXXXX format, assigned on successful payment
            $table->string('application_id', 20)->nullable();

            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();

            // Core applicant details
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->string('applicant_nid')->nullable();
            $table->string('gender', 20)->nullable();

            // Application lifecycle
            $table->enum('status', [
                'draft', 'submitted', 'approved', 'rejected',
                'pending', 'paid', 'failed', 'cancelled',
            ])->default('draft');

            // Selection pipeline stage (populated after status = paid)
            $table->string('selection_stage')->nullable();

            // Payment details
            $table->string('transaction_id')->nullable()->unique();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_response')->nullable();

            // Assessment marks
            $table->decimal('written_exam_marks', 6, 2)->nullable();
            $table->decimal('viva_exam_marks', 6, 2)->nullable();

            // Selected programme (set at program_selected stage)
            $table->foreignId('selected_category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            // All other form data stored as JSON
            $table->json('additional_info')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite index for admin list queries (tab + sort)
            $table->index(['exam_id', 'status', 'selection_stage'], 'applications_exam_status_stage_idx');

            // Unique application ID per exam (no two applicants in the same exam share an ID)
            $table->unique(['exam_id', 'application_id'], 'applications_exam_id_application_id_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

