<x-filament::dropdown placement="bottom-start">
    <x-slot name="trigger">
        <x-button color="gray" icon="heroicon-m-chevron-down" iconPosition="after" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">
            Category
        </x-button>
    </x-slot>

    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item tag="a" href="/{{ config('discussions.route_prefix') }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:text-gray-900 dark-mode:hover:text-gray-200">
            View All
        </x-filament::dropdown.list.item>
        <!-- List categories from Livewire -->
        @foreach(config('discussions.categories') as $slug => $category)
            <x-filament::dropdown.list.item tag="a" href="/{{ config('discussions.route_prefix') }}/category/{{ $slug }}" wire:navigate class="block px-4 py-2 text-sm text-gray-700 transition-colors duration-150 hover:text-gray-900 dark-mode:hover:text-gray-200">
                {{ $category['title'] }}
            </x-filament::dropdown.list.item>
        @endforeach
    </x-filament::dropdown.list>
</x-filament::dropdown>