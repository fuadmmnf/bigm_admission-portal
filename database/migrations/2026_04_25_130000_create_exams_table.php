<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table): void {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['draft', 'active', 'closed'])->default('draft');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->json('additional_info')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};

