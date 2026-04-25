<?php

namespace App\Models;

use App\Models\Concerns\HasPublicUlid;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Application extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use LogsActivity;
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
        'payment_status',
        'transaction_id',
        'payment_amount',
        'payment_method',
        'payment_response',
        'additional_info',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'payment_status' => 'string',
            'payment_amount' => 'decimal:2',
            'payment_response' => 'array',
            'additional_info' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function markAsPaid(string $transactionId, array $response = []): void
    {
        $this->update([
            'payment_status' => 'paid',
            'status' => 'submitted',
            'transaction_id' => $transactionId,
            'payment_method' => $response['card_type'] ?? null,
            'payment_response' => $response,
        ]);
    }

    public function markPaymentFailed(array $response = []): void
    {
        $this->update([
            'payment_status' => 'failed',
            'payment_response' => $response,
        ]);
    }

    public function markPaymentCancelled(array $response = []): void
    {
        $this->update([
            'payment_status' => 'cancelled',
            'payment_response' => $response,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'status',
                'payment_status',
                'transaction_id',
                'payment_amount',
                'payment_method',
            ])
            ->logOnlyDirty();
    }
}
