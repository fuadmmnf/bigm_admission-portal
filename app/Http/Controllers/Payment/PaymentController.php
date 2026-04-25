<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\SSLCommerzService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class PaymentController extends Controller
{
    public function __construct(private SSLCommerzService $sslcommerz) {}

    /**
     * Initiate a payment for the given application.
     * Redirects the user to the SSLCommerz payment gateway.
     */
    public function initiate(Application $application): RedirectResponse
    {
        if ($application->payment_status === 'paid') {
            return redirect()->route('payment.success-page')->with('info', 'This application has already been paid.');
        }

        // Generate a unique transaction ID and persist it before redirect
        $transactionId = 'TXN-' . strtoupper($application->ulid) . '-' . now()->format('YmdHis');

        $application->update([
            'transaction_id' => $transactionId,
            'payment_status' => 'pending',
            'payment_amount' => $application->exam?->fee ?? 0,
        ]);

        try {
            $gatewayUrl = $this->sslcommerz->initiate([
                'tran_id' => $transactionId,
                'total_amount' => (float) $application->payment_amount,
                'cus_name' => $application->applicant_name,
                'cus_email' => $application->applicant_email,
                'cus_phone' => $application->applicant_phone,
                'product_name' => 'Admission: ' . ($application->exam?->name ?? 'Exam'),
                'product_category' => 'Admission',
            ]);
        } catch (RuntimeException $e) {
            Log::error('SSLCommerz initiation error', [
                'application_ulid' => $application->ulid,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('payment.failed-page')->with('error', 'Payment gateway unavailable. Please try again later.');
        }

        return redirect()->away($gatewayUrl);
    }

    /**
     * SSLCommerz success callback endpoint.
     * Validates payment and marks application as paid.
     */
    public function success(Request $request): RedirectResponse
    {
        $valId = $request->input('val_id');
        $tranId = $request->input('tran_id');

        $application = Application::where('transaction_id', $tranId)->first();

        if (! $application) {
            Log::warning('SSLCommerz success: application not found for tran_id', ['tran_id' => $tranId]);
            return redirect()->route('payment.failed-page')->with('error', 'Application not found.');
        }

        if ($application->payment_status === 'paid') {
            return redirect()->route('payment.success-page')->with('info', 'Payment already confirmed.');
        }

        try {
            $validation = $this->sslcommerz->validate($valId);
        } catch (RuntimeException $e) {
            Log::error('SSLCommerz validation error', ['val_id' => $valId, 'message' => $e->getMessage()]);
            return redirect()->route('payment.failed-page')->with('error', 'Payment validation failed.');
        }

        if ($this->sslcommerz->isPaymentValid($validation, $tranId, $application->payment_amount)) {
            $application->markAsPaid($tranId, $validation);
            Log::info('Payment confirmed', ['application_ulid' => $application->ulid, 'tran_id' => $tranId]);
            return redirect()->route('payment.success-page', ['application' => $application->ulid]);
        }

        $application->markPaymentFailed($validation);
        return redirect()->route('payment.failed-page')->with('error', 'Payment could not be verified.');
    }

    /**
     * SSLCommerz failure callback endpoint.
     */
    public function failed(Request $request): RedirectResponse
    {
        $tranId = $request->input('tran_id');
        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->payment_status !== 'paid') {
            $application->markPaymentFailed($request->all());
        }

        Log::info('SSLCommerz payment failed', ['tran_id' => $tranId]);

        return redirect()->route('payment.failed-page')->with('error', 'Payment failed. Please try again.');
    }

    /**
     * SSLCommerz cancel callback endpoint.
     */
    public function cancel(Request $request): RedirectResponse
    {
        $tranId = $request->input('tran_id');
        $application = Application::where('transaction_id', $tranId)->first();

        if ($application && $application->payment_status !== 'paid') {
            $application->markPaymentCancelled($request->all());
        }

        Log::info('SSLCommerz payment cancelled', ['tran_id' => $tranId]);

        return redirect()->route('payment.cancel-page')->with('info', 'Payment was cancelled.');
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

        if ($application->payment_status === 'paid') {
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

        $application->markPaymentFailed($request->all());
        return response()->json(['result' => 'failed']);
    }
}

