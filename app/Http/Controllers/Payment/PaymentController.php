<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\SSLCommerzService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private SSLCommerzService $sslcommerz) {}

    /**
     * Initiate a payment for the given application.
     * Redirects the user to the SSLCommerz payment gateway.
     */
    public function initiate(Request $request, Application $application): RedirectResponse
    {
        if ($application->status === 'paid') {
            return redirect()->route('payment.success-page')->with('info', 'This application has already been paid.');
        }

        $sessionPaymentKey = (string) $request->session()->get('active_payment_application_ulid', '');
        if ($application->status === 'pending' && $sessionPaymentKey === (string) $application->ulid) {
            $application->delete();

            $request->session()->forget('active_payment_application_ulid');

            return redirect()->route('payment.failed-page')->with(
                'error',
                'Application deleted because the payment page was reloaded before payment completion. Please submit a new application.'
            );
        }

        // Generate a unique transaction ID and persist it before redirect
        $transactionId = 'TXN-' . strtoupper($application->ulid) . '-' . now()->format('YmdHis');
        $defaultAmount = (float) config('sslcommerz.default_amount', 0);

        $application->update([
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'payment_amount' => $defaultAmount,
        ]);

        $request->session()->put('active_payment_application_ulid', $application->ulid);

        $callbackBaseUrl = $request->getSchemeAndHttpHost();
        $callbackRoutes = (array) config($this->isSandboxMode() ? 'sslcommerz.sandbox_routes' : 'sslcommerz.routes', []);

        $successUrl = $this->buildGatewayCallbackUrl($callbackBaseUrl, (string) ($callbackRoutes['success'] ?? '/payment/success'));
        $failedUrl = $this->buildGatewayCallbackUrl($callbackBaseUrl, (string) ($callbackRoutes['failed'] ?? '/payment/failed'));
        $cancelUrl = $this->buildGatewayCallbackUrl($callbackBaseUrl, (string) ($callbackRoutes['cancel'] ?? '/payment/cancel'));
        $ipnUrl = $this->buildGatewayCallbackUrl($callbackBaseUrl, (string) ($callbackRoutes['ipn'] ?? '/payment/ipn'));

        $paymentData = [
            'tran_id' => $transactionId,
            'total_amount' => (float) $application->payment_amount,
            'cus_name' => $application->applicant_name,
            'cus_email' => $application->applicant_email,
            'cus_phone' => $application->applicant_phone,
            'product_name' => 'Admission: ' . ($application->exam?->name ?? 'Exam'),
            'product_category' => 'Admission',
            'success_url' => $successUrl,
            'fail_url' => $failedUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => $ipnUrl,
        ];

        Log::info('SSLCommerz initiation payload prepared', [
            'application_ulid' => $application->ulid,
            'tran_id' => $transactionId,
            'sandbox' => $this->isSandboxMode(),
            'success_url' => $successUrl,
            'fail_url' => $failedUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => $ipnUrl,
        ]);

        try {
            $gatewayUrl = $this->sslcommerz->initiate($paymentData);
        } catch (RuntimeException $e) {
            Log::error('SSLCommerz initiation error', [
                'application_ulid' => $application->ulid,
                'sandbox' => $this->isSandboxMode(),
                'message' => $e->getMessage(),
            ]);

            $errorMessage = 'Payment gateway unavailable. Please try again later.';
            if (app()->environment(['local', 'testing'])) {
                $errorMessage .= ' Gateway response: '.$e->getMessage();
            }

            return redirect()->route('payment.failed-page')->with('error', $errorMessage);
        }

        return redirect()->away($gatewayUrl);
    }

    /**
     * SSLCommerz success callback endpoint.
     * Validates payment and marks application as paid.
     */
    public function success(Request $request): View|RedirectResponse
    {
        Log::info('SSLCommerz success callback hit', [
            'method' => $request->method(),
            'tran_id' => $request->input('tran_id'),
            'val_id' => $request->input('val_id'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
            'card_type' => $request->input('card_type'),
        ]);

        $valId = $request->input('val_id');
        $tranId = $request->input('tran_id');

        $application = Application::where('transaction_id', $tranId)->first();

        if (! $application) {
            Log::warning('SSLCommerz success: application not found for tran_id', ['tran_id' => $tranId]);
            return view('pages.payment-failed', [
                'error' => 'Application not found.',
            ]);
        }

        if ($application->status === 'paid') {
            return view('pages.payment-success', [
                'info' => 'Payment already confirmed.',
                'application' => $application->ulid,
            ]);
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (RuntimeException $e) {
            Log::error('SSLCommerz validation error', ['val_id' => $valId, 'message' => $e->getMessage()]);

            if ($this->canUseSandboxSuccessFallback($request, $application)) {
                $fallbackPayload = array_merge($request->all(), [
                    'validation_fallback' => true,
                    'validation_error' => $e->getMessage(),
                ]);

                $application->markAsPaid($tranId, $fallbackPayload);
                $request->session()->forget('active_payment_application_ulid');

                Log::warning('SSLCommerz sandbox fallback used after validation error.', [
                    'application_ulid' => $application->ulid,
                    'tran_id' => $tranId,
                    'val_id' => $valId,
                ]);

                return view('pages.payment-success', [
                    'application' => $application->ulid,
                    'info' => 'Payment confirmed (sandbox fallback used due to validator error).',
                ]);
            }

            return view('pages.payment-failed', [
                'error' => 'Payment validation failed.',
            ]);
        }

        if ($this->sslcommerz->isPaymentValid($validation, $tranId, $application->payment_amount)) {
            $application->markAsPaid($tranId, $validation);
            $request->session()->forget('active_payment_application_ulid');
            Log::info('Payment confirmed', ['application_ulid' => $application->ulid, 'tran_id' => $tranId]);
            return view('pages.payment-success', [
                'application' => $application->ulid,
            ]);
        }

        $application->delete();
        $request->session()->forget('active_payment_application_ulid');

        return view('pages.payment-failed', [
            'error' => 'Payment could not be verified. The application has been deleted. Please submit again.',
        ]);
    }

    /**
     * SSLCommerz failure callback endpoint.
     */
    public function failed(Request $request): View
    {
        Log::info('SSLCommerz failed callback hit', [
            'method' => $request->method(),
            'tran_id' => $request->input('tran_id'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
        ]);

        $tranId = $request->input('tran_id');
        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->status !== 'paid') {
            $application->delete();
        }

        $request->session()->forget('active_payment_application_ulid');

        Log::info('SSLCommerz payment failed', ['tran_id' => $tranId]);

        return view('pages.payment-failed', [
            'error' => 'Payment failed. The application has been deleted. Please submit a new application and try again.',
        ]);
    }

    /**
     * SSLCommerz cancel callback endpoint.
     */
    public function cancel(Request $request): View
    {
        Log::info('SSLCommerz cancel callback hit', [
            'method' => $request->method(),
            'tran_id' => $request->input('tran_id'),
            'status' => $request->input('status'),
            'amount' => $request->input('amount'),
        ]);

        $tranId = $request->input('tran_id');
        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->status !== 'paid') {
            $application->delete();
        }

        $request->session()->forget('active_payment_application_ulid');

        Log::info('SSLCommerz payment cancelled', ['tran_id' => $tranId]);

        return view('pages.payment-cancel', [
            'info' => 'Payment was cancelled. The application has been deleted. Please submit a new application if you want to apply again.',
        ]);
    }

    /**
     * SSLCommerz IPN (Instant Payment Notification) endpoint.
     * Server-to-server notification — validates and updates status.
     */
    public function ipn(Request $request)
    {
        $valId = $request->input('val_id');
        $tranId = $request->input('tran_id');
        $status = $request->input('status');

        Log::info('SSLCommerz IPN received', ['tran_id' => $tranId, 'status' => $status]);

        $application = Application::where('transaction_id', $tranId)->first();

        if (! $application) {
            return response()->json(['result' => 'application_not_found'], 422);
        }

        if ($application->status === 'paid') {
            return response()->json(['result' => 'already_paid']);
        }

        if (in_array($status, ['VALID', 'VALIDATED'])) {
            try {
                $validation = $this->sslcommerz->validate($valId);
            } catch (RuntimeException $e) {
                Log::error('SSLCommerz IPN validation error', ['val_id' => $valId, 'message' => $e->getMessage()]);
                return response()->json(['result' => 'validation_error'], 500);
            }

            if ($this->sslcommerz->isPaymentValid($validation, $tranId, $application->payment_amount)) {
                $application->markAsPaid($tranId, $validation);
                return response()->json(['result' => 'success']);
            }
        }

        $application->delete();
        return response()->json(['result' => 'failed']);
    }



    private function isSandboxMode(): bool
    {
        return (bool) config('sslcommerz.sandbox', true);
    }

    private function canUseSandboxSuccessFallback(Request $request, Application $application): bool
    {
        if (! $this->isSandboxMode()) {
            return false;
        }

        if (($request->input('status') ?? '') !== 'VALID') {
            return false;
        }

        $tranId = (string) $request->input('tran_id');
        if ($tranId === '' || $tranId !== (string) $application->transaction_id) {
            return false;
        }

        $callbackAmount = (float) ($request->input('amount') ?? 0);
        $expectedAmount = (float) $application->payment_amount;

        // Keep the same tolerance used by SSLCommerzService::isPaymentValid.
        return abs($callbackAmount - $expectedAmount) <= 1;
    }

    private function buildGatewayCallbackUrl(string $baseUrl, string $pathOrUrl): string
    {
        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        return rtrim($baseUrl, '/').'/'.ltrim($pathOrUrl, '/');
    }
}

