<div class="max-w-4xl mx-auto p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">Game Match Lobby</h1>
        <p class="text-gray-600 dark:text-gray-400">Create a new match or join an existing one!</p>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid md:grid-cols-2 gap-8">
        <!-- Create New Match -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Create New Match</h2>
            
            <p class="text-gray-600 dark:text-gray-400">Match creation form coming soon...</p>
        </div>

        <!-- Available Matches -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4 text-gray-900 dark:text-gray-100">Available Matches</h2>
            
            <p class="text-gray-500 dark:text-gray-400 text-center py-8">No matches available</p>
        </div>
    </div>
</div>
