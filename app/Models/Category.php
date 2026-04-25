<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kalnoy\Nestedset\NodeTrait;

class Category extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use NodeTrait;

    /** @use HasFactory<CategoryFactory> */
    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }

    protected $fillable = [
        'name',
        'ulid',
        'type',
        'additional_info',
        'parent_id',
    ];

    protected function casts(): array
    {
        return [
            'additional_info' => 'array',
        ];
    }
}


