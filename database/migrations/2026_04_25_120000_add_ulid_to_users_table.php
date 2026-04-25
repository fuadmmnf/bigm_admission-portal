<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
        });

        User::query()
            ->whereNull('ulid')
            ->eachById(function (User $user): void {
                $user->forceFill(['ulid' => (string) Str::ulid()])->saveQuietly();
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_ulid_unique');
            $table->dropColumn('ulid');
        });
    }
};

