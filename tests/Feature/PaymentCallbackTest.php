<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Exam;
use App\Services\SSLCommerzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PaymentCallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_failed_callback_deletes_unpaid_application(): void
    {
        $exam = Exam::factory()->create(['status' => 'active']);

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'transaction_id' => 'TXN-FAILED-123',
        ]);

        $response = $this->post(route('payment.failed'), [
            'tran_id' => $application->transaction_id,
            'status' => 'FAILED',
        ]);

        $response->assertOk();
        $this->assertSoftDeleted('applications', ['id' => $application->id]);
    }

    public function test_cancel_callback_deletes_unpaid_application(): void
    {
        $exam = Exam::factory()->create(['status' => 'active']);

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'transaction_id' => 'TXN-CANCEL-123',
        ]);

        $response = $this->post(route('payment.cancel'), [
            'tran_id' => $application->transaction_id,
            'status' => 'CANCELLED',
        ]);

        $response->assertOk();
        $this->assertSoftDeleted('applications', ['id' => $application->id]);
    }

    public function test_success_callback_uses_sandbox_fallback_when_validation_throws(): void
    {
        config()->set('sslcommerz.sandbox', true);

        $this->mock(SSLCommerzService::class, function ($mock): void {
            $mock->shouldReceive('validate')->once()->andThrow(new RuntimeException('Sandbox validator offline'));
        });

        $exam = Exam::factory()->create(['status' => 'active']);

        $application = Application::factory()->create([
            'exam_id' => $exam->id,
            'status' => 'pending',
            'transaction_id' => 'TXN-SUCCESS-123',
            'payment_amount' => 10,
        ]);

        $response = $this->post(route('payment.success'), [
            'val_id' => 'VAL-123',
            'tran_id' => $application->transaction_id,
            'status' => 'VALID',
            'amount' => '10.00',
        ]);

        $response->assertOk();

        $application->refresh();
        $this->assertSame('paid', $application->status);
    }
}

