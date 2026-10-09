<?php

namespace App\Services\Payment;

interface PaymentGateway
{
    /**
     * Start a payment and return an authorization URL for the buyer.
     *
     * @param  array<string, mixed>  $data
     * @return array{success: bool, authorization_url?: string, reference?: string, transaction_id?: string|int|null, message?: string}
     */
    public function initializePayment(array $data): array;

    /**
     * Verify a transaction directly with the provider.
     *
     * A successful result must carry the settled `amount`, its `currency` and
     * the provider reference, so the caller can prove it belongs to the order
     * before marking anything paid.
     *
     * @param  string|null  $transactionId  provider-side transaction id, when known
     * @return array{success: bool, amount?: float, currency?: string, reference?: string, transaction_id?: string|int|null, status?: string, message?: string}
     */
    public function verifyPayment(string $reference, string|int|null $transactionId = null): array;

    public function getName(): string;
}
