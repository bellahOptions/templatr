<?php

namespace App\Services\Payment;

interface PaymentGateway
{
    public function initializePayment(array $data): array;
    public function verifyPayment(string $reference): array;
    public function getName(): string;
}
