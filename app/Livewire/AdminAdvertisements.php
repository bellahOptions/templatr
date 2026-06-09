<?php

namespace App\Livewire;

use App\Models\Advertisement;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAdvertisements extends Component
{
    use WithPagination;

    public string $title = '';

    public string $description = '';

    public string $image_url = '';

    public string $link_url = '';

    public string $position = 'banner';

    public string $type = 'banner';

    public int $sort_order = 0;

    public bool $is_active = true;

    public ?int $editingId = null;

    public bool $showForm = false;

    public ?int $viewingId = null;

    protected $rules = [
        'title' => 'required|min:3',
        'position' => 'required|in:banner,sidebar,inline,popup,marquee',
        'type' => 'required',
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
        $this->reset(['title', 'description', 'image_url', 'link_url', 'position', 'type', 'sort_order', 'is_active', 'editingId']);
        $this->showForm = true;
    }

    public function edit($id)
    {
        $ad = Advertisement::find($id);
        $this->editingId = $ad->id;
        $this->title = $ad->title;
        $this->description = $ad->description ?? '';
        $this->image_url = $ad->image_url ?? '';
        $this->link_url = $ad->link_url ?? '';
        $this->position = $ad->position;
        $this->type = $ad->type;
        $this->sort_order = $ad->sort_order;
        $this->is_active = $ad->is_active;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'image_url' => $this->image_url ?: null,
            'link_url' => $this->link_url ?: null,
            'position' => $this->position,
            'type' => $this->type,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Advertisement::find($this->editingId)->update($data);
            session()->flash('message', 'Advertisement updated.');
        } else {
            Advertisement::create($data);
            session()->flash('message', 'Advertisement created.');
        }

        $this->showForm = false;
        $this->reset(['title', 'description', 'image_url', 'link_url', 'position', 'type', 'sort_order', 'editingId']);
    }

    public function delete($id)
    {
        Advertisement::find($id)?->delete();
        session()->flash('message', 'Advertisement deleted.');
    }

    public function render(): View
    {
        return view('livewire.admin-advertisements', [
            'ads' => Advertisement::latest()->paginate(10),
        ])->extends('admin.layouts.admin')->section('content');
    }
}
