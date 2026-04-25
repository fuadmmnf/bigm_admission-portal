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

    /**
     * Fields retained as first-class searchable columns.
     *
     * @var array<int, string>
     */
    public const SEARCHABLE_FIELDS = [
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_id_number',
    ];

    /**
     * Google Form labels (normalized) to searchable application column mapping.
     *
     * @var array<string, string>
     */
    public const GOOGLE_FORM_SEARCHABLE_FIELD_MAP = [
        'applicant_s_name' => 'applicant_name',
        'email' => 'applicant_email',
        'mobile_number' => 'applicant_phone',
        'national_id_birth_registration_passport_number' => 'applicant_id_number',
    ];

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
            'payment_amount' => 'decimal:2',
            'payment_response' => 'array',
            'additional_info' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    /**
     * Split a Google Form payload into searchable columns and JSON metadata.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $baseAttributes
     * @return array<string, mixed>
     */
    public static function fromGoogleFormPayload(array $payload, array $baseAttributes = []): array
    {
        $attributes = $baseAttributes;
        $metadata = [];

        foreach ($payload as $key => $value) {
            $normalizedKey = static::normalizeGoogleFormFieldLabel((string) $key);
            $mappedColumn = static::GOOGLE_FORM_SEARCHABLE_FIELD_MAP[$normalizedKey] ?? null;

            if ($mappedColumn !== null) {
                $attributes[$mappedColumn] = $value;
                continue;
            }

            $metadata[$normalizedKey !== '' ? $normalizedKey : (string) $key] = $value;
        }

        $existingMetadata = data_get($baseAttributes, 'additional_info', []);

        $attributes['additional_info'] = array_merge(
            is_array($existingMetadata) ? $existingMetadata : [],
            $metadata
        );

        return $attributes;
    }

    private static function normalizeGoogleFormFieldLabel(string $label): string
    {
        $normalized = str_replace(['*', "\n", "\r"], '', trim($label));
        $normalized = strtolower($normalized);
        $normalized = preg_replace('/[^a-z0-9]+/', '_', $normalized) ?? '';

        return trim($normalized, '_');
    }

    public function markAsPaid(string $transactionId, array $response = []): void
    {
        $this->update([
            'status' => 'paid',
            'transaction_id' => $transactionId,
            'payment_method' => $response['card_type'] ?? null,
            'payment_response' => $response,
        ]);
    }

    public function markPaymentFailed(array $response = []): void
    {
        $this->update([
            'status' => 'failed',
            'payment_response' => $response,
        ]);
    }

    public function markPaymentCancelled(array $response = []): void
    {
        $this->update([
            'status' => 'cancelled',
            'payment_response' => $response,
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'status',
                'transaction_id',
                'payment_amount',
                'payment_method',
            ])
            ->logOnlyDirty();
    }
}
