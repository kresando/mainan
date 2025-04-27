<?php

namespace App\Livewire\Layouts;

use App\Models\Category;
use Spatie\Tags\Tag;
use Livewire\Component;

class Navbar extends Component
{
    public $searchQuery = '';
    public $categories = [];
    public $tags = [];
    
    public function mount()
    {
        $this->categories = Category::all();
        $this->tags = Tag::take(10)->get();
    }
    
    public function render()
    {
        return view('livewire.layouts.navbar');
    }
}
