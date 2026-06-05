<?php

namespace App\Livewire;

use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AdminNotifications extends Component
{
    use WithPagination;

    public string $title = '';

    public string $message = '';

    public string $type = 'info';

    public string $action_url = '';

    public string $action_text = '';

    public bool $is_global = true;

    public ?int $user_id = null;

    public ?int $editingId = null;

    public bool $showForm = false;

    public ?int $viewingId = null;

    protected $rules = [
        'title' => 'required|min:3',
        'message' => 'required|min:10',
        'type' => 'required|in:info,success,warning,alert',
    ];

    public function view($id): void
    {
        $this->viewingId = $id;
        $this->showForm = false;
    }

    public function closeView(): void
    {
        $this->viewingId = null;
    }

    public function create()
    {
        $this->reset(['title', 'message', 'type', 'action_url', 'action_text', 'is_global', 'user_id', 'editingId']);
        $this->showForm = true;
    }

    public function edit($id)
    {
        $notif = Notification::find($id);
        $this->editingId = $notif->id;
        $this->title = $notif->title;
        $this->message = $notif->message;
        $this->type = $notif->type;
        $this->action_url = $notif->action_url ?? '';
        $this->action_text = $notif->action_text ?? '';
        $this->is_global = $notif->is_global;
        $this->user_id = $notif->user_id;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'action_url' => $this->action_url ?: null,
            'action_text' => $this->action_text ?: null,
            'is_global' => $this->is_global,
            'user_id' => ! $this->is_global ? $this->user_id : null,
        ];

        if ($this->editingId) {
            Notification::find($this->editingId)->update($data);
            session()->flash('message', 'Notification updated successfully.');
        } else {
            Notification::create($data);
            session()->flash('message', 'Notification created successfully.');
        }

        $this->showForm = false;
        $this->reset(['title', 'message', 'type', 'action_url', 'action_text', 'editingId']);
    }

    public function delete($id)
    {
        Notification::find($id)?->delete();
        session()->flash('message', 'Notification deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin-notifications', [
            'notifications' => Notification::latest()->paginate(10),
        ])->extends('admin.layouts.admin')->section('content');
    }
}
