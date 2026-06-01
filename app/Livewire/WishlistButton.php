<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\Wishlist;
use Livewire\Component;

class WishlistButton extends Component
{
    public Product $product;
    public bool $isInWishlist = false;

    public function mount(Product $product)
    {
        $this->product = $product;
        if (auth()->check()) {
            $this->isInWishlist = Wishlist::where('user_id', auth()->id())
                ->where('product_id', $product->id)
                ->exists();
        }
    }

    public function toggle()
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ($this->isInWishlist) {
            Wishlist::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->delete();
            $this->isInWishlist = false;
            $this->dispatch('wishlistUpdated');
        } else {
            Wishlist::create([
                'user_id' => auth()->id(),
                'product_id' => $this->product->id,
            ]);
            $this->isInWishlist = true;
            $this->dispatch('wishlistUpdated');
        }
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <button wire:click="toggle" type="button" class="p-2.5 border border-gray-200 rounded-xl hover:border-red-300 hover:text-red-500 transition-colors {{ $isInWishlist ? 'text-red-500 border-red-300 bg-red-50' : 'text-gray-400' }}">
                <svg class="w-5 h-5" fill="{{ $isInWishlist ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </button>
        </div>
        HTML;
    }
}
