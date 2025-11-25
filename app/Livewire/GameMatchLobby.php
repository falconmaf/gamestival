<?php

namespace App\Livewire;

use App\Models\GameMatch;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('theme::components.layouts.app')]
class GameMatchLobby extends Component
{
    public $gameName = '';

    public $games = [];

    public function mount()
    {
        $this->loadGames();
    }

    public function loadGames()
    {
        $this->games = GameMatch::with(['players.user'])
        
        ->where('status', 'new')

        ->latest()

        ->get();
    }

    public function render()
    {
        return view('livewire.game-match-lobby');
    }
}
