<?php

namespace Tests\Feature\Livewire;

use App\Livewire\TagBrowser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use Tests\TestCase;

class TagBrowserTest extends TestCase
{
    public function test_renders_successfully()
    {
        Livewire::test(TagBrowser::class)
            ->assertStatus(200);
    }
}
