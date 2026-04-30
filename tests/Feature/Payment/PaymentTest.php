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
        config()->set('sslcommerz.sandbox', true);
        config()->set('sslcommerz.sandbox_routes', [
            'success' => 'https://public-callback.example/payment/success',
            'failed' => 'https://public-callback.example/payment/failed',
            'cancel' => 'https://public-callback.example/payment/cancel',
            'ipn' => 'https://public-callback.example/payment/ipn',
        ]);

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

        Http::assertSent(function ($request) {
            return $request['success_url'] === 'https://public-callback.example/payment/success'
                && $request['fail_url'] === 'https://public-callback.example/payment/failed'
                && $request['cancel_url'] === 'https://public-callback.example/payment/cancel'
                && $request['ipn_url'] === 'https://public-callback.example/payment/ipn';
        });
    }

    public function test_payment_initiation_allows_localhost_callback_urls_in_sandbox_mode(): void
    {
        config()->set('sslcommerz.sandbox', true);
        config()->set('sslcommerz.sandbox_routes', [
            'success' => '/payment/success',
            'failed' => '/payment/failed',
            'cancel' => '/payment/cancel',
            'ipn' => '/payment/ipn',
        ]);

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

        Http::assertSent(function ($request) {
            return parse_url($request['success_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['success_url'], PHP_URL_PATH) === '/payment/success'
                && parse_url($request['fail_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['fail_url'], PHP_URL_PATH) === '/payment/failed'
                && parse_url($request['cancel_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['cancel_url'], PHP_URL_PATH) === '/payment/cancel'
                && parse_url($request['ipn_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['ipn_url'], PHP_URL_PATH) === '/payment/ipn';
        });
    }

    public function test_payment_initiation_allows_localhost_callback_url_when_sandbox_is_disabled(): void
    {
        config()->set('sslcommerz.sandbox', false);

        Http::fake([
            'securepay.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://securepay.sslcommerz.com/pay/fake-session',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'status' => 'draft',
            'payment_amount' => 500.00,
        ]);

        $response = $this->get(route('payment.initiate', $application));

        $response->assertRedirect('https://securepay.sslcommerz.com/pay/fake-session');

        Http::assertSent(function ($request) {
            return parse_url($request['success_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['success_url'], PHP_URL_PATH) === '/payment/callback/success'
                && parse_url($request['fail_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['fail_url'], PHP_URL_PATH) === '/payment/callback/failed'
                && parse_url($request['cancel_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['cancel_url'], PHP_URL_PATH) === '/payment/callback/cancel'
                && parse_url($request['ipn_url'], PHP_URL_HOST) === 'localhost'
                && parse_url($request['ipn_url'], PHP_URL_PATH) === '/payment/ipn';
        });
    }

    public function test_successful_payment_marks_application_as_paid(): void
    {
        config()->set('sslcommerz.sandbox', true);

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

        $response->assertOk();
        $response->assertSee('Payment Successful');

        $application->refresh();
        $this->assertEquals('paid', $application->status);
        $this->assertEquals('TXN-TEST-001', $application->transaction_id);
    }

    public function test_sandbox_success_callback_uses_fallback_when_validation_api_returns_500(): void
    {
        config()->set('sslcommerz.sandbox', true);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response('validator-error', 500),
        ]);

        $application = Application::factory()->create([
            'transaction_id' => 'TXN-SANDBOX-FALLBACK-001',
            'status' => 'pending',
            'payment_amount' => 500.00,
        ]);

        $response = $this->post(route('payment.success'), [
            'val_id' => 'fallback-val-id',
            'tran_id' => 'TXN-SANDBOX-FALLBACK-001',
            'status' => 'VALID',
            'amount' => '500.00',
            'card_type' => 'BKASH-BKash',
        ]);

        $response->assertOk();
        $response->assertSee('Payment Successful');

        $application->refresh();
        $this->assertEquals('paid', $application->status);
        $this->assertEquals('TXN-SANDBOX-FALLBACK-001', $application->transaction_id);
        $this->assertTrue((bool) data_get($application->payment_response, 'validation_fallback'));
    }

    public function test_failed_payment_deletes_unpaid_application(): void
    {
        $application = Application::factory()->create([
            'transaction_id' => 'TXN-FAIL-001',
            'status' => 'pending',
        ]);

        $response = $this->post(route('payment.failed'), [
            'tran_id' => 'TXN-FAIL-001',
            'status' => 'FAILED',
        ]);

        $response->assertOk();
        $response->assertSee('Payment Failed');

        $this->assertSoftDeleted('applications', [
            'id' => $application->id,
        ]);
    }

    public function test_cancelled_payment_deletes_unpaid_application(): void
    {
        $application = Application::factory()->create([
            'transaction_id' => 'TXN-CANCEL-001',
            'status' => 'pending',
        ]);

        $response = $this->post(route('payment.cancel'), [
            'tran_id' => 'TXN-CANCEL-001',
            'status' => 'CANCELLED',
        ]);

        $response->assertOk();
        $response->assertSee('Payment Cancelled');

        $this->assertSoftDeleted('applications', [
            'id' => $application->id,
        ]);
    }

    public function test_reloading_payment_initiate_keeps_pending_application_and_redirects_to_gateway(): void
    {
        config()->set('sslcommerz.sandbox', true);

        Http::fake([
            'sandbox.sslcommerz.com/*' => Http::response([
                'status' => 'SUCCESS',
                'GatewayPageURL' => 'https://sandbox.sslcommerz.com/pay/retry-safe-session',
            ], 200),
        ]);

        $application = Application::factory()->create([
            'status' => 'pending',
            'transaction_id' => 'TXN-RELOAD-001',
        ]);

        $response = $this
            ->withSession(['active_payment_application_ulid' => $application->ulid])
            ->get(route('payment.initiate', $application));

        $response->assertRedirect('https://sandbox.sslcommerz.com/pay/retry-safe-session');

        $application->refresh();
        $this->assertSame('pending', $application->status);
        $this->assertNull($application->deleted_at);
    }

    public function test_ipn_validates_and_marks_application_as_paid(): void
    {
        config()->set('sslcommerz.sandbox', true);

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

