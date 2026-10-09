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
        if ($this->isConfigured('paystack')) {
            $this->gateways['paystack'] = App::make(PaystackGateway::class);
        }
        if ($this->isConfigured('flutterwave')) {
            $this->gateways['flutterwave'] = App::make(FlutterwaveGateway::class);
        }
        if ($this->isConfigured('interswitch')) {
            $this->gateways['interswitch'] = App::make(InterswitchGateway::class);
        }
    }

    /**
     * Whether the named provider has the credentials required to charge and
     * verify a transaction. A provider missing its secret must never be used.
     */
    public function isConfigured(string $name): bool
    {
        return match ($name) {
            'paystack' => $this->nonEmpty(config('services.paystack.secret')),
            'flutterwave' => $this->nonEmpty(config('services.flutterwave.secret')),
            'interswitch' => $this->nonEmpty(config('services.interswitch.client_id'))
                && $this->nonEmpty(config('services.interswitch.client_secret')),
            default => false,
        };
    }

    /**
     * The gateway's shared secret used to authenticate inbound webhooks.
     *
     * Paystack signs payloads with the secret key; Flutterwave sends a plain
     * secret hash. An empty value means the webhook cannot be trusted and the
     * caller must fail closed.
     */
    public function webhookSecret(string $name): string
    {
        return match ($name) {
            'paystack' => (string) config('services.paystack.secret', ''),
            'flutterwave' => (string) config('services.flutterwave.secret_hash', ''),
            default => '',
        };
    }

    public function hasGateway(string $name): bool
    {
        return array_key_exists($name, $this->gateways);
    }

    protected function nonEmpty(mixed $value): bool
    {
        return is_string($value) ? trim($value) !== '' : ! empty($value);
    }

    public function gateway(?string $name = null): PaymentGateway
    {
        if ($name !== null) {
            if (! isset($this->gateways[$name])) {
                // Never silently substitute a different provider for the one the
                // order was created against.
                throw new \RuntimeException("Payment gateway [{$name}] is not configured.");
            }

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
