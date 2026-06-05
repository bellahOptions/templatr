<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class CartDrawer extends Component
{
    public bool $open = false;

    public array $items = [];

    public float $total = 0.0;

    public function mount(): void
    {
        $this->loadCart();

        if (session()->pull('cart_drawer_open')) {
            $this->open = true;
        }
    }

    public function loadCart(): void
    {
        $cart = session()->get('cart', []);
        $this->items = $cart;
        $this->total = (float) collect($cart)->sum('price');
    }

    public function open(): void
    {
        $this->loadCart();
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function removeItem(int $productId): void
    {
        $cart = session()->get('cart', []);
        unset($cart[$productId]);
        session()->put('cart', $cart);
        $this->loadCart();
        $this->dispatch('cartUpdated');
    }

    public function render(): View
    {
        return view('livewire.cart-drawer');
    }
}
