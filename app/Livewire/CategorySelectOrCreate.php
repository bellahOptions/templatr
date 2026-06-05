<?php

namespace App\Livewire;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

class CategorySelectOrCreate extends Component
{
    public ?int $categoryId = null;

    public bool $showForm = false;

    public string $newName = '';

    public string $newSlug = '';

    protected $rules = [
        'newName' => 'required|min:2|unique:categories,name',
    ];

    protected $messages = [
        'newName.unique' => 'A category with this name already exists.',
    ];

    public function mount(?int $selected = null): void
    {
        $this->categoryId = $selected ?? (int) old('category_id') ?: null;
    }

    public function updatedNewName(): void
    {
        $this->newSlug = Str::slug($this->newName);
    }

    public function createCategory(): void
    {
        $this->validate();

        $category = Category::create([
            'name' => $this->newName,
            'slug' => $this->newSlug ?: Str::slug($this->newName),
            'order' => Category::max('order') + 1,
        ]);

        $this->categoryId = $category->id;
        $this->showForm = false;
        $this->newName = '';
        $this->newSlug = '';
    }

    public function render(): View
    {
        return view('livewire.category-select-or-create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
