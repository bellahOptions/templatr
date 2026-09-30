<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\App;

class PaymentManager
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->registerGateways();
    }

    protected function registerGateways(): void
    {
        // Only register gateways that have their keys configured. Credentials are
        // read from config so `php artisan config:cache` does not blank them.
        if (config('services.paystack.secret')) {
            $this->gateways['paystack'] = App::make(PaystackGateway::class);
        }
        if (config('services.flutterwave.secret')) {
            $this->gateways['flutterwave'] = App::make(FlutterwaveGateway::class);
        }
        if (config('services.interswitch.client_id') && config('services.interswitch.client_secret')) {
            $this->gateways['interswitch'] = App::make(InterswitchGateway::class);
        }
    }

    public function gateway(?string $name = null): PaymentGateway
    {
        if ($name && isset($this->gateways[$name])) {
            return $this->gateways[$name];
        }

        // Return first available gateway
        if (! empty($this->gateways)) {
            return reset($this->gateways);
        }

        throw new \Exception('No payment gateway configured. Please set up at least one payment provider.');
    }

    public function getAvailableGateways(): array
    {
        $available = [];
        foreach ($this->gateways as $name => $gateway) {
            $available[$name] = $gateway;
        }

        return $available;
    }

    public function hasGateways(): bool
    {
        return ! empty($this->gateways);
    }

    public function getDefaultGateway(): ?string
    {
        if (! empty($this->gateways)) {
            return array_key_first($this->gateways);
        }

        return null;
    }
}
