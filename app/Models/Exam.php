<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Database\Factories\ExamFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Exam extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use LogsActivity;
    use SoftDeletes;

    /** @use HasFactory<ExamFactory> */
    protected static function newFactory(): ExamFactory
    {
        return ExamFactory::new();
    }

    protected $fillable = [
        'name',
        'ulid',
        'description',
        'status',
        'start_date',
        'end_date',
        'additional_info',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'additional_info' => 'array',
        ];
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeAvailableForApplication(Builder $query): Builder
    {
        return $query
            ->where('status', 'active')
            ->where(function (Builder $query): void {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function (Builder $query): void {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('exam')
            ->logOnly([
                'ulid',
                'name',
                'description',
                'status',
                'start_date',
                'end_date',
                'additional_info',
            ])
            ->logOnlyDirty();
    }
}
