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
     * Initiate SSLCommerz payment session
     * Returns GatewayPageURL for redirect
     */
    public function initiate(array $data): string
    {
        // Ensure required credentials exist
        if (!$this->storeId || !$this->storePassword) {
            throw new RuntimeException('SSLCommerz credentials not configured');
        }

        // Build payload strictly from controller-provided data
        $payload = array_merge([
            'store_id'       => $this->storeId,
            'store_passwd'   => $this->storePassword,
            'currency'       => config('sslcommerz.currency', 'BDT'),
            'shipping_method' => 'NO',

            // DO NOT set callback URLs here (controller owns them)
        ], $data);

        Log::info('SSLCommerz initiate request', [
            'tran_id' => $data['tran_id'] ?? null,
            'amount'   => $data['total_amount'] ?? null,
        ]);

        $response = Http::asForm()
            ->timeout(30)
            ->post($this->initiateUrl, $payload);

        $this->throwOnHttpError($response, 'Initiation');

        $json = $response->json();

        Log::info('SSLCommerz initiate response', [
            'response' => $json,
        ]);

        // Strict validation of gateway response
        if (
            ($json['status'] ?? '') !== 'SUCCESS' ||
            empty($json['GatewayPageURL'])
        ) {
            Log::error('SSLCommerz initiation failed', [
                'response' => $json,
                'tran_id' => $data['tran_id'] ?? null,
            ]);

            throw new RuntimeException(
                $json['failedreason'] ?? 'SSLCommerz initiation failed'
            );
        }

        return $json['GatewayPageURL'];
    }

    /**
     * Validate payment using val_id from SSLCommerz
     */
    public function validate(string $valId): array
    {
        if (blank($valId)) {
            throw new RuntimeException('Missing val_id for validation');
        }


        $response = Http::asForm()
            ->timeout(30)
            ->post($this->validateUrl, [
                'val_id'        => $valId,
                'store_id'      => $this->storeId,
                'store_passwd'  => $this->storePassword,
                'v'             => 1,
                'format'        => 'json',
            ]);

        $this->throwOnHttpError($response, 'Validation');

        $json = $response->json();

        Log::info('SSLCommerz validation response', [
            'val_id' => $valId,
            'response' => $json,
        ]);

        return $json;
    }

    /**
     * Verify payment authenticity and integrity
     */
    public function isPaymentValid(array $validation, string $transactionId, float|int|string $amount): bool
    {
        $status = $validation['status'] ?? null;

        if (!in_array($status, ['VALID', 'VALIDATED'])) {
            return false;
        }

        if (($validation['tran_id'] ?? null) !== $transactionId) {
            return false;
        }

        $paidAmount = (float) ($validation['amount'] ?? 0);

        // Allow small rounding tolerance
        if (abs($paidAmount - (float) $amount) > 1) {
            return false;
        }

        return true;
    }

    /**
     * Throw exception for HTTP failures
     */
    private function throwOnHttpError(Response $response, string $context): void
    {
        if ($response->failed()) {
            Log::error("SSLCommerz {$context} HTTP error", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new RuntimeException(
                "SSLCommerz {$context} request failed ({$response->status()})"
            );
        }
    }
}
