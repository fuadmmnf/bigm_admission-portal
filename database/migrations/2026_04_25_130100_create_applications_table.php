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
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('applicant_phone');
            $table->string('applicant_id_number')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'pending', 'paid', 'failed', 'cancelled'])->default('draft');
            $table->string('transaction_id')->nullable()->unique();
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->json('payment_response')->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};

