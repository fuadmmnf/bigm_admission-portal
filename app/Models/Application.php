<?php

namespace App\Models;

use App\Mail\ApplicationConfirmationMail;
use App\Models\Concerns\HasPublicUlid;
use Database\Factories\ApplicationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Application extends Model
{
    use HasFactory;
    use HasPublicUlid;
    use LogsActivity;
    use SoftDeletes;

    public const STAGE_PAID = 'paid';

    public const STAGE_VIVA_SELECTED = 'viva_selected';

    public const STAGE_PROGRAM_SELECTED = 'program_selected';

    public const STAGE_ALUMNI = 'alumni';

    /** @use HasFactory<ApplicationFactory> */
    protected static function newFactory(): ApplicationFactory
    {
        return ApplicationFactory::new();
    }

    protected $fillable = [
        'ulid',
        'exam_id',
        'application_id',
        'applicant_name',
        'applicant_email',
        'applicant_phone',
        'applicant_nid',
        'gender',
        'status',
        'transaction_id',
        'payment_amount',
        'payment_method',
        'payment_response',
        'written_exam_marks',
        'viva_exam_marks',
        'selected_category_id',
        'selection_stage',
        'additional_info',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'string',
            'gender' => 'string',
            'payment_amount' => 'decimal:2',
            'payment_response' => 'array',
            'written_exam_marks' => 'decimal:2',
            'viva_exam_marks' => 'decimal:2',
            'selected_category_id' => 'integer',
            'selection_stage' => 'string',
            'additional_info' => 'array',
        ];
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function selectedCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'selected_category_id');
    }

    /**
     * Mark this application as paid, assigning a unique sequential application ID.
     *
     * Application ID format: YYYYXXXX
     *   YYYY = exam start year (fallback: current year)
     *   XXXX = 4-digit zero-padded sequence per exam (0001, 0002, …)
     *
     * A row-level lock on the exam record serialises concurrent payments for the
     * same exam so that no two applicants can ever receive the same application ID.
     */
    public function markAsPaid(string $transactionId, array $response = []): void
    {
        DB::transaction(function () use ($transactionId, $response): void {
            // Lock the exam row — serialises all concurrent payments for this exam.
            $exam = Exam::where('id', $this->exam_id)->lockForUpdate()->first();

            $year = $exam?->start_date?->year ?? now()->year;

            // Count already-paid applications for this exam (excluding this one).
            $paidCount = self::where('exam_id', $this->exam_id)
                ->where('status', 'paid')
                ->whereNotNull('application_id')
                ->count();

            $sequence = $paidCount + 1;
            $applicationId = (string)$year . sprintf('%04d', $sequence);

            $this->update([
                'status' => 'paid',
                'selection_stage' => self::STAGE_PAID,
                'transaction_id' => $transactionId,
                'payment_method' => $response['card_type'] ?? null,
                'payment_response' => $response,
                'application_id' => $applicationId,
            ]);
        });

        Log::info('Application marked as paid', [
            'application_ulid' => $this->ulid,
            'application_id' => $this->application_id,
        ]);

        // Send application confirmation email to the applicant
        if (filled($this->applicant_email)) {
            try {
                Mail::to($this->applicant_email)
                    ->send(new ApplicationConfirmationMail($this->fresh()));

            } catch (\Throwable $e) {
                Mail::to($this->applicant_email)
                    ->queue(new ApplicationConfirmationMail($this->fresh()));
            }
        }
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly([
                'status',
                'application_id',
                'selection_stage',
                'transaction_id',
                'payment_amount',
                'payment_method',
                'written_exam_marks',
                'viva_exam_marks',
                'selected_category_id',
            ])
            ->logOnlyDirty();
    }
}
