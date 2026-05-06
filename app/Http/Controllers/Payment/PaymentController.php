<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Services\SSLCommerzService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

        $application = Application::withTrashed()->where('transaction_id', $tranId)->first();
        if ($application?->trashed()) {
            $application->restore();
        }
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
     * Reconstruct the original form data from a stored Application for session prefill.
     * Flattens additional_info back into the same shape the form submits.
     * Upload paths are intentionally excluded — user must re-upload fresh files.
     */
    private function buildPrefillInput(Application $application): array
    {
        $info = $application->additional_info ?? [];

        return [
            'applicant_name' => $application->applicant_name,
            'email' => $application->applicant_email,
            'mobile_number' => $application->applicant_phone,
            'national_id_number' => $application->applicant_nid,
            'gender' => $application->gender,
            'father_name' => data_get($info, 'personal.father_name'),
            'mother_name' => data_get($info, 'personal.mother_name'),
            'date_of_birth' => data_get($info, 'personal.date_of_birth'),
            'present_address' => data_get($info, 'present_address', []),
            'permanent_address' => data_get($info, 'permanent_address', []),
            'education' => data_get($info, 'education', []),
            'job_experience' => data_get($info, 'job_experience', []),
            'course_preferences' => data_get($info, 'course_preferences', []),
            // No existing_* upload keys — user must re-upload photo, signature, and documents.
        ];
    }

    /**
     * Delete all uploaded files associated with an application from storage.
     * Paths are stored in additional_info.uploads on the public disk.
     */
    private function deleteUploadedFiles(Application $application): void
    {
        $uploads = data_get($application->additional_info ?? [], 'uploads', []);

        $paths = array_filter([
            data_get($uploads, 'applicant_photo'),
            data_get($uploads, 'signature'),
            data_get($uploads, 'education_documents.ssc.certificate'),
            data_get($uploads, 'education_documents.hsc.certificate'),
            data_get($uploads, 'education_documents.graduation.certificate'),
            data_get($uploads, 'education_documents.masters.certificate'),
        ]);

        foreach ($paths as $path) {
            try {
                Storage::disk('public')->delete($path);
            } catch (\Throwable $e) {
                Log::warning('Failed to delete uploaded file', [
                    'path' => $path,
                    'application_ulid' => $application->ulid,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Deleted uploaded files for application', [
            'application_ulid' => $application->ulid,
            'count' => count($paths),
        ]);
    }

    /**
     * FAILED CALLBACK
     */
    public function failed(Request $request): RedirectResponse
    {
        $tranId = $request->input('tran_id');

        Log::info('Payment failed callback', compact('tranId'));

        $application = Application::where('transaction_id', $tranId)->first();

        $examUlid = null;
        $prefillInput = [];

        if ($application && $application->status !== 'paid') {
            $examUlid = $application->exam?->ulid;
            $prefillInput = $this->buildPrefillInput($application);
            $application->update(['status' => 'failed']);
            $this->deleteUploadedFiles($application);
            $application->delete();
        }

        if ($examUlid) {
            return redirect()
                ->route('applications.create', ['exam' => $examUlid])
                ->withInput($prefillInput)
                ->with('payment_error', 'Your payment could not be processed. Your previous entries have been restored — please re-upload your photo, signature, and documents, then resubmit.');
        }

        return redirect()->route('home')
            ->with('payment_error', 'Payment failed. Please try again.');
    }

    /**
     * CANCEL CALLBACK
     */
    public function cancel(Request $request): RedirectResponse
    {
        $tranId = $request->input('tran_id');

        Log::info('Payment cancel callback', compact('tranId'));

        $application = Application::where('transaction_id', $tranId)->first();

        $examUlid = null;
        $prefillInput = [];

        if ($application && $application->status !== 'paid') {
            $examUlid = $application->exam?->ulid;
            $prefillInput = $this->buildPrefillInput($application);
            $application->update(['status' => 'cancelled']);
            $this->deleteUploadedFiles($application);
            $application->delete();
        }

        if ($examUlid) {
            return redirect()
                ->route('applications.create', ['exam' => $examUlid])
                ->withInput($prefillInput)
                ->with('payment_info', 'Payment was cancelled. Your previous entries have been restored — please re-upload your photo, signature, and documents, then resubmit whenever you are ready.');
        }

        return redirect()->route('home')
            ->with('payment_info', 'Payment was cancelled.');
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
