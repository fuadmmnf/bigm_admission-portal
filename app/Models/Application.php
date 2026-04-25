<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use SoftDeletes;

    /** @use HasFactory<ApplicationFactory> */
    protected static function newFactory(): ApplicationFactory
    {
        return ApplicationFactory::new();
    }

    protected $fillable = [
        'ulid',
        'exam_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_id_number',
        'status',
        'additional_info',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'additional_info' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }
}

