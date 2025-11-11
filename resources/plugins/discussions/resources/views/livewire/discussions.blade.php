<div class="{{ config('discussions.styles.container_classes') }} discussion">
    @include('discussions::partials.custom-styles')
    <div class="space-y-6">
        <h1 class="{{ config('discussions.styles.header_classes') }}">Discussions</h1>

        <top-bar-discussions class="flex justify-between w-full space-x-3 h-9">
            <search-discussions class="relative flex items-center w-full h-full">
                <div class="absolute left-0 flex items-center justify-center h-full pl-2.5 pr-1">
                    <svg class="w-4 h-4 text-gray-400 -translate-y-px fill-current" viewBox="0 0 16 16" version="1.1"><path d="M10.68 11.74a6 6 0 0 1-7.922-8.982 6 6 0 0 1 8.982 7.922l3.04 3.04a.749.749 0 0 1-.326 1.275.749.749 0 0 1-.734-.215ZM11.5 7a4.499 4.499 0 1 0-8.997 0A4.499 4.499 0 0 0 11.5 7Z"></path></svg>
                </div>
                <x-filament::input.wrapper class="w-full !{{ config('discussions.styles.rounded') }}">
                    <x-filament::input
                        type="text"
                        wire:model="search" wire:keydown.enter="performSearch" 
                        placeholder="Search all discussions"
                        class="w-full !pl-8"
                    />
                </x-filament::input.wrapper>

            </search-discussions>


            @include('discussions::partials.category-dropdown')

            <x-filament::dropdown placement="bottom-start">
                <x-slot name="trigger">
                    <x-button color="gray" icon="heroicon-m-chevron-down" iconPosition="after" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">
                        Sort
                    </x-button>
                </x-slot>
                
                <x-filament::dropdown.list>
                    <x-filament::dropdown.list.item wire:click.prevent="updateSortOrder('desc')">
                        Newest
                    </x-filament::dropdown.list.item>
                    
                    <x-filament::dropdown.list.item wire:click.prevent="updateSortOrder('asc')">
                        Oldest
                    </x-filament::dropdown.list.item>
                </x-filament::dropdown.list>
            </x-filament::dropdown>
            
            @if(auth()->guest())
                <x-button tag="button" 
                    onclick="window.location.href='/login';" 
                    class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">
                    {{ trans('discussions::messages.discussion.new') }}
                </x-button>
            @else
                <x-button tag="button" 
                    onclick="window.dispatchEvent(new CustomEvent('discussion-new-open'))" 
                    class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">
                    {{ trans('discussions::messages.discussion.new') }}
                </x-button>
            @endif

        </top-bar-discussions>
        @include('discussions::partials.guest-auth-message')
        @if (session()->has('message'))
            <div class="p-4 mb-4 text-white bg-green-500 {{ config('discussions.styles.rounded') }}">
                {{ session('message') }}
            </div>
        @endif
        <div class="relative flex">
            @if (config('discussions.show_categories'))
                @include('discussions::partials.categories')
            @endif
            <div class="w-full space-y-2">
                @forelse ($discussions as $discussion)
                    @if (!$loop->first)
                        <div class="w-full h-px bg-gray-200 dark:bg-gray-700"></div>
                    @endif
                    <div class="py-2 flex items-start @if (config('discussions.styles.rounded') == 'rounded-full') {{ 'rounded-xl' }}@else{{ config('discussions.styles.rounded') }} @endif" wire:key="{{ $discussion->id }}">
                        @include('discussions::partials.discussion-avatar', ['user' => $discussion->user, 'size' => 'sm'])
                        <div class="relative flex flex-col items-start justify-start w-full ml-3 space-y-1 text-left">
                            {{-- <x-filament::link :href="route('discussion', $discussion->slug)" size="2xl" class="dark:text-white">{{ $discussion->title }}</x-filament::link> --}}
                            <a href="{{ route('discussion', $discussion->slug) }}" class="pt-px mb-1 font-semibold leading-tight tracking-tight text-gray-800 dark:text-gray-100 hover:underline">{{ $discussion->title }}</a>
                            <div class="flex items-center space-x-1 text-gray-500">
                                <p class="text-xs leading-none">
                                    @lang('discussions::messages.discussion.posted_by') {{ $discussion->user->name }}
                                </p>
                                @if($discussion->category())
                                    <div class="inline text-xs leading-none">
                                        in <a href="{{ url(config('discussions.route_prefix') . '/category/' . $discussion->category_slug) }}" class="underline">{{ $discussion->category()->title }}</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex items-center justify-center h-full bg-gray-100 rounded-lg">
                        <p class="text-sm text-gray-500">@lang('discussions::messages.discussion.no_discussions')</p>
                    </div>
                @endforelse
                @if ($discussions->hasMorePages())
                    <div class="flex justify-center w-full pt-2 text-neutral-900">
                        <div class="relative w-full overflow-hidden {{ config('discussions.styles.rounded') }}">
                            <x-button color="gray" class="w-full !{{ config('discussions.styles.rounded') }}" wire:click="loadMoreDiscussions">
                                @lang('discussions::messages.discussion.load_more')
                            </x-button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @auth
        @include('discussions::partials.create-discussion')
    @endauth

</div>
