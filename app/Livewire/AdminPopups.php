<?php

namespace App\Livewire;

use App\Models\Popup;
use Livewire\Component;
use Livewire\WithPagination;

class AdminPopups extends Component
{
    use WithPagination;

    public string $title = '';
    public string $content = '';
    public string $image_url = '';
    public string $button_text = '';
    public string $button_url = '';
    public string $trigger_type = 'timed';
    public int $trigger_delay = 5;
    public string $display_frequency = 'once';
    public bool $is_active = false;
    public ?int $editingId = null;
    public bool $showForm = false;

    protected $rules = [
        'title' => 'required|min:3',
        'content' => 'required|min:10',
        'trigger_type' => 'required',
        'display_frequency' => 'required',
    ];

    public function create()
    {
        $this->reset(['title', 'content', 'image_url', 'button_text', 'button_url', 'trigger_type', 'trigger_delay', 'display_frequency', 'is_active', 'editingId']);
        $this->showForm = true;
    }

    public function edit($id)
    {
        $popup = Popup::find($id);
        $this->editingId = $popup->id;
        $this->title = $popup->title;
        $this->content = $popup->content;
        $this->image_url = $popup->image_url ?? '';
        $this->button_text = $popup->button_text ?? '';
        $this->button_url = $popup->button_url ?? '';
        $this->trigger_type = $popup->trigger_type;
        $this->trigger_delay = $popup->trigger_delay;
        $this->display_frequency = $popup->display_frequency;
        $this->is_active = $popup->is_active;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'content' => $this->content,
            'image_url' => $this->image_url ?: null,
            'button_text' => $this->button_text ?: null,
            'button_url' => $this->button_url ?: null,
            'trigger_type' => $this->trigger_type,
            'trigger_delay' => $this->trigger_delay,
            'display_frequency' => $this->display_frequency,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Popup::find($this->editingId)->update($data);
            session()->flash('message', 'Popup updated.');
        } else {
            Popup::create($data);
            session()->flash('message', 'Popup created.');
        }

        $this->showForm = false;
        $this->reset(['title', 'content', 'image_url', 'button_text', 'button_url', 'trigger_type', 'trigger_delay', 'display_frequency', 'editingId']);
    }

    public function delete($id)
    {
        Popup::find($id)?->delete();
        session()->flash('message', 'Popup deleted.');
    }

    public function render()
    {
        return view('livewire.admin-popups', [
            'popups' => Popup::latest()->paginate(10),
        ]);
    }
}
