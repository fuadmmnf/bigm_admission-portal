<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasPublicUlid
{
    protected static function bootHasPublicUlid(): void
    {
        static::creating(function ($model): void {
            if (blank($model->ulid)) {
                $model->ulid = (string) Str::ulid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }
}

