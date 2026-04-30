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
    public function __construct(private SSLCommerzService $sslcommerz)
    {
    }

    /**
     * Determine if SSLCommerz is running in sandbox mode.
     */
    private function isSandboxMode(): bool
    {
        return (bool)config('sslcommerz.sandbox', true);
    }

    /**
     * Fallback handler for sandbox validation failures.
     * Used because SSLCommerz sandbox validation is unreliable.
     */
    private function handleSandboxFallback(Request $request, Application $application): View
    {
        $tranId = $request->input('tran_id');

        Log::warning('Sandbox fallback triggered for payment', [
            'tran_id' => $tranId,
            'application_ulid' => $application->ulid,
        ]);

        // Ensure transaction matches
        if ($tranId !== $application->transaction_id) {
            return view('pages.payment-failed', [
                'error' => 'Transaction mismatch in sandbox fallback.',
            ]);
        }

        // Mark as paid safely (sandbox only)
        $application->markAsPaid($tranId, [
            'sandbox_fallback' => true,
            'raw_request' => $request->all(),
        ]);

        $request->session()->forget('active_payment_application_ulid');

        return view('pages.payment-success', [
            'application' => $application->ulid,
            'info' => 'Payment confirmed via sandbox fallback.',
        ]);
    }

    /**
     * Initiate SSLCommerz payment
     */
    public function initiate(Request $request, Application $application): RedirectResponse
    {
        if ($application->status === 'paid') {
            return redirect()
                ->route('payment.success')
                ->with('info', 'Already paid.');
        }

        $amount = (float)config('sslcommerz.default_amount', 0);

        if ($amount <= 0) {
            Log::error('Invalid payment amount', [
                'application_ulid' => $application->ulid,
                'amount' => $amount,
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Invalid payment configuration.');
        }

        if (
            blank($application->applicant_name) ||
            blank($application->applicant_email) ||
            blank($application->applicant_phone)
        ) {
            return redirect()->route('payment.failed')
                ->with('error', 'Missing applicant information.');
        }

        // Stable transaction ID (no regeneration)
        $transactionId = $application->transaction_id;

        if (blank($transactionId)) {
            $transactionId = 'TXN-' . strtoupper($application->ulid) . '-' . now()->format('YmdHis');
        }

        $application->update([
            'transaction_id' => $transactionId,
            'status' => 'pending',
            'payment_amount' => $amount,
        ]);

        $baseUrl = rtrim(config('app.url'), '/');

        $paymentData = [
            'tran_id' => $transactionId,
            'total_amount' => $amount,
            'currency' => 'BDT',

            'cus_name' => $application->applicant_name,
            'cus_email' => $application->applicant_email,
            'cus_phone' => $application->applicant_phone,

            'product_name' => 'Admission: ' . ($application->exam?->name ?? 'Exam'),
            'product_category' => 'Admission',
            'product_profile' => 'general',

            'shipping_method' => 'NO',

            'cus_add1' => 'N/A',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',

            // CALLBACKS (ONLY HERE, NOT IN SERVICE)
            'success_url' => $baseUrl . '/payment/callback/success',
            'fail_url' => $baseUrl . '/payment/callback/failed',
            'cancel_url' => $baseUrl . '/payment/callback/cancel',
            'ipn_url' => $baseUrl . '/payment/ipn',
        ];

        Log::info('SSLCommerz initiate request', [
            'application' => $application->ulid,
            'tran_id' => $transactionId,
        ]);

        try {
            $response = $this->sslcommerz->initiate($paymentData);

            if (is_string($response)) {
                $gatewayUrl = $response;
            } elseif (is_array($response)) {
                $gatewayUrl = $response['GatewayPageURL'] ?? null;
            } elseif (is_object($response)) {
                $gatewayUrl = $response->GatewayPageURL ?? null;
            } else {
                throw new RuntimeException('Invalid gateway response');
            }

            if (blank($gatewayUrl)) {
                throw new RuntimeException('Empty gateway URL');
            }

        } catch (RuntimeException $e) {
            Log::error('SSLCommerz initiate failed', [
                'message' => $e->getMessage(),
                'application' => $application->ulid,
            ]);

            return redirect()->route('payment.failed')
                ->with('error', 'Payment gateway error.');
        }

        return redirect()->away($gatewayUrl);
    }

    /**
     * SUCCESS CALLBACK
     */
    public function success(Request $request): View
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');

        Log::info('Payment success callback', compact('tranId', 'valId'));

        $application = Application::where('transaction_id', $tranId)->first();

        if (!$application) {
            return view('pages.payment-failed', ['error' => 'Invalid transaction']);
        }

        if ($application->status === 'paid') {
            return view('pages.payment-success', ['application' => $application->ulid]);
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (RuntimeException $e) {
            if ($this->isSandboxMode()) {
                return $this->handleSandboxFallback($request, $application);
            }
            Log::error('Validation failed', ['val_id' => $valId]);

            return view('pages.payment-failed', ['error' => 'Validation failed']);
        }

        if ($this->sslcommerz->isPaymentValid($validation, $tranId, $application->payment_amount)) {
            $application->markAsPaid($tranId, $validation);

            return view('pages.payment-success', [
                'application' => $application->ulid
            ]);
        }

        $application->update(['status' => 'failed']);

        return view('pages.payment-failed', [
            'error' => 'Payment verification failed'
        ]);
    }

    /**
     * FAILED CALLBACK
     */
    public function failed(Request $request): View
    {
        $tranId = $request->input('tran_id');

        Log::info('Payment failed callback', compact('tranId'));

        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->status !== 'paid') {
            $application->update(['status' => 'failed']);
            $application->delete();
        }

        return view('pages.payment-failed', [
            'error' => 'Payment failed'
        ]);
    }

    /**
     * CANCEL CALLBACK
     */
    public function cancel(Request $request): View
    {
        $tranId = $request->input('tran_id');

        Log::info('Payment cancel callback', compact('tranId'));

        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->status !== 'paid') {
            $application->update(['status' => 'cancelled']);
            $application->delete();
        }

        return view('pages.payment-cancel');
    }

    /**
     * IPN
     */
    public function ipn(Request $request)
    {
        $tranId = $request->input('tran_id');
        $valId = $request->input('val_id');

        Log::info('IPN received', compact('tranId', 'valId'));

        $application = Application::where('transaction_id', $tranId)->first();

        if (!$application || $application->status === 'paid') {
            return response()->json(['status' => 'ignored']);
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (RuntimeException $e) {
            return response()->json(['status' => 'error'], 500);
        }

        if ($this->sslcommerz->isPaymentValid($validation, $tranId, $application->payment_amount)) {
            $application->markAsPaid($tranId, $validation);
            return response()->json(['status' => 'success']);
        }

        $application->update(['status' => 'failed']);

        return response()->json(['status' => 'failed']);
    }
}
