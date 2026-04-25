<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class SSLCommerzService
{
    private string $storeId;
    private string $storePassword;
    private bool $sandbox;
    private string $initiateUrl;
    private string $validateUrl;

    public function __construct()
    {
        $this->storeId = (string) config('sslcommerz.store_id');
        $this->storePassword = (string) config('sslcommerz.store_password');
        $this->sandbox = (bool) config('sslcommerz.sandbox', true);

        $env = $this->sandbox ? 'sandbox' : 'production';
        $this->initiateUrl = config("sslcommerz.api.{$env}.initiate");
        $this->validateUrl = config("sslcommerz.api.{$env}.validate");
    }

    /**
     * Initiate a payment session with SSLCommerz.
     * Returns the gateway page URL to redirect the applicant to.
     *
     * @param  array{
     *     tran_id: string,
     *     total_amount: float|int|string,
     *     cus_name: string,
     *     cus_email: string,
     *     cus_phone: string,
     *     cus_add1?: string,
     *     cus_city?: string,
     *     cus_country?: string,
     *     product_name?: string,
     *     product_category?: string,
     * } $data
     * @throws RuntimeException
     */
    public function initiate(array $data): string
    {
        $payload = array_merge([
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'currency' => config('sslcommerz.currency', 'BDT'),
            'success_url' => url(config('sslcommerz.routes.success')),
            'fail_url' => url(config('sslcommerz.routes.failed')),
            'cancel_url' => url(config('sslcommerz.routes.cancel')),
            'ipn_url' => url(config('sslcommerz.routes.ipn')),
            'shipping_method' => 'NO',
            'product_name' => 'Admission Application',
            'product_category' => 'Admission',
            'product_profile' => 'general',
            'cus_add1' => 'N/A',
            'cus_city' => 'Dhaka',
            'cus_country' => 'Bangladesh',
        ], $data);

        $response = Http::asForm()->post($this->initiateUrl, $payload);

        $this->throwOnHttpError($response, 'Initiation');

        $json = $response->json();

        if (($json['status'] ?? '') !== 'SUCCESS') {
            Log::error('SSLCommerz initiation failed', ['response' => $json]);
            throw new RuntimeException('SSLCommerz initiation failed: ' . ($json['failedreason'] ?? 'Unknown reason'));
        }

        return $json['GatewayPageURL'];
    }

    /**
     * Validate a payment callback using SSLCommerz's validation API.
     * Pass the val_id received in the success/IPN callback.
     */
    public function validate(string $valId): array
    {
        $response = Http::asForm()->post($this->validateUrl, [
            'val_id' => $valId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'v' => 1,
            'format' => 'json',
        ]);

        $this->throwOnHttpError($response, 'Validation');

        return $response->json();
    }

    /**
     * Check that a validated response genuinely indicates a paid transaction.
     */
    public function isPaymentValid(array $validationResponse, string $transactionId, string|int|float $amount): bool
    {
        if (($validationResponse['status'] ?? '') !== 'VALID' &&
            ($validationResponse['status'] ?? '') !== 'VALIDATED') {
            return false;
        }

        if (($validationResponse['tran_id'] ?? '') !== $transactionId) {
            return false;
        }

        // Allow small floating-point rounding tolerance
        if (abs((float) ($validationResponse['amount'] ?? 0) - (float) $amount) > 1) {
            return false;
        }

        return true;
    }

    private function throwOnHttpError(Response $response, string $context): void
    {
        if ($response->failed()) {
            Log::error("SSLCommerz {$context} HTTP error", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new RuntimeException("SSLCommerz {$context} request failed with HTTP {$response->status()}");
        }
    }
}

