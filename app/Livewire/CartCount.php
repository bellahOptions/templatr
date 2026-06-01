<?php

namespace App\Livewire;

use Livewire\Component;

class CartCount extends Component
{
    protected $listeners = ['cartUpdated' => '$refresh'];

    public function getCountProperty()
    {
        return count(session()->get('cart', []));
    }

    public function render()
    {
        return <<<'HTML'
        <div>
            <a href="{{ route('cart.index') }}" class="text-gray-300 hover:text-[#FFC300] transition-colors relative p-1" wire:navigate>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                @if($this->count > 0)
                    <span class="absolute -top-1 -right-1 bg-[#FFC300] text-black text-xs font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">{{ $this->count }}</span>
                @endif
            </a>
        </div>
        HTML;
    }
}
