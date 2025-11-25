<div class="max-w-4xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Memory Game Lobby</h1>
        <p class="text-gray-600">Create a new game or join an existing one!</p>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Create New Game -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Create New Game</h2>
            
            <form wire:submit="createGame">
                <div class="mb-4">
                    <label for="gameName" class="block text-sm font-medium text-gray-700 mb-2">
                        Game Name
                    </label>
                    <input
                        type="text"
                        id="gameName"
                        wire:model="gameName"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter a game name..."
                        required
                    />
                    @error('gameName')
                        <span class="text-red-500 text-sm">{{ $message }}</span>
                    @enderror
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 transition"
                >
                    Create Game
                </button>
            </form>
        </div>

        <!-- Available Games -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Available Games</h2>
            
            @if(count($games) > 0)
                <div class="space-y-3">
                    @foreach($games as $game)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-medium text-lg">{{ $game->name }}</h3>
                                <span class="text-sm text-gray-500">
                                    {{ $game->players->count() }}/{{ $game->max_players }} players
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-2 mb-3">
                                @foreach($game->players as $player)
                                    <span class="inline-block bg-gray-100 text-gray-700 text-xs px-2 py-1 rounded">
                                        {{ $player->name }}
                                    </span>
                                @endforeach
                            </div>

                            <button
                                wire:click="joinGame({{ $game->id }})"
                                class="w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700 transition"
                                @if($game->players->count() >= $game->max_players) disabled @endif
                            >
                                @if($game->players->count() >= $game->max_players)
                                    Game Full
                                @else
                                    Join Game
                                @endif
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No games available</p>
            @endif
        </div>
    </div>
</div>
