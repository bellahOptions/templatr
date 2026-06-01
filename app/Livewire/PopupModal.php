<?php

namespace App\Livewire;

use App\Models\Popup;
use Livewire\Component;

class PopupModal extends Component
{
    public ?array $activePopup = null;
    public bool $show = false;

    public function mount()
    {
        $popup = Popup::active()->first();
        
        if ($popup) {
            // Check display frequency
            $sessionKey = 'popup_shown_' . $popup->id;
            
            if ($popup->display_frequency === 'once' && session()->has($sessionKey)) {
                return;
            }
            
            if ($popup->display_frequency === 'once_session' && session()->has($sessionKey)) {
                return;
            }
            
            $this->activePopup = $popup->toArray();
            $this->show = true;
            
            $popup->incrementDisplay();
            session([$sessionKey => true]);
        }
    }

    public function dismiss()
    {
        $this->show = false;
    }

    public function trackClick()
    {
        if ($this->activePopup) {
            Popup::find($this->activePopup['id'])?->incrementClick();
        }
    }

    public function render()
    {
        return view('livewire.popup-modal');
    }
}
