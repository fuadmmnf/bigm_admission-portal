<?php

namespace Tests\Feature\Payment;

use App\Models\Application;
use App\Models\Exam;
use App\Services\SSLCommerzService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_initiation_redirects_to_gateway(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/fake-session',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'status' => 'draft',
            'payment_amount' => 500.00,
        ]);

        $response = $this->get(route('payment.initiate', $application));

        $response->assertRedirect('https://sandbox.sslcommerz.com/pay/fake-session');
    }

    public function test_successful_payment_marks_application_as_paid(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'TXN-TEST-001',
                'amount' => '500.00',
                'card_type' => 'VISA-Dutch Bangla',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'transaction_id' => 'TXN-TEST-001',
            'status' => 'pending',
            'payment_amount' => 500.00,
        ]);

        $response = $this->post(route('payment.success'), [
            'val_id' => 'fake-val-id',
            'tran_id' => 'TXN-TEST-001',
            'status' => 'VALID',
        ]);

        $response->assertRedirect();

        $application->refresh();
        $this->assertEquals('paid', $application->status);
        $this->assertEquals('TXN-TEST-001', $application->transaction_id);
    }

    public function test_failed_payment_marks_application_status_as_failed(): void
    {
        $application = Application::factory()->create([
            'transaction_id' => 'TXN-FAIL-001',
            'status' => 'pending',
        ]);

        $response = $this->post(route('payment.failed'), [
            'tran_id' => 'TXN-FAIL-001',
            'status' => 'FAILED',
        ]);

        $response->assertRedirect(route('payment.failed-page'));

        $application->refresh();
        $this->assertEquals('failed', $application->status);
    }

    public function test_cancelled_payment_marks_application_status_as_cancelled(): void
    {
        $application = Application::factory()->create([
            'transaction_id' => 'TXN-CANCEL-001',
            'status' => 'pending',
        ]);

        $response = $this->post(route('payment.cancel'), [
            'tran_id' => 'TXN-CANCEL-001',
            'status' => 'CANCELLED',
        ]);

        $response->assertRedirect(route('payment.cancel-page'));

        $application->refresh();
        $this->assertEquals('cancelled', $application->status);
    }

    public function test_ipn_validates_and_marks_application_as_paid(): void
    {
        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'VALID',
                'tran_id' => 'TXN-IPN-001',
                'amount' => '300.00',
                'card_type' => 'bKash',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'transaction_id' => 'TXN-IPN-001',
            'status' => 'pending',
            'payment_amount' => 300.00,
        ]);

        $response = $this->postJson(route('payment.ipn'), [
            'val_id' => 'fake-val-id',
            'tran_id' => 'TXN-IPN-001',
            'status' => 'VALID',
        ]);

        $response->assertOk()->assertJson(['result' => 'success']);

        $application->refresh();
        $this->assertEquals('paid', $application->status);
    }

    public function test_already_paid_application_is_not_marked_twice(): void
    {
        $application = Application::factory()->create([
            'transaction_id' => 'TXN-DUP-001',
            'status' => 'paid',
        ]);

        $response = $this->get(route('payment.initiate', $application));

        $response->assertRedirect(route('payment.success-page'));
    }

    public function test_application_model_helper_mark_as_paid(): void
    {
        $application = Application::factory()->create([
            'status' => 'pending',
        ]);

        $application->markAsPaid('TXN-HELPER-001', ['card_type' => 'MasterCard']);

        $this->assertEquals('paid', $application->fresh()->status);
        $this->assertEquals('TXN-HELPER-001', $application->fresh()->transaction_id);
    }
}

