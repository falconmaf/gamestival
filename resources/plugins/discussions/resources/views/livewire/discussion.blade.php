<div class="{{ config('discussions.styles.container_classes') }}">
    @include('discussions::partials.custom-styles')
    <discussion-content-top>
        <div class="relative mb-5 space-y-2">
            <h1 class="{{ config('discussions.styles.header_classes') }}">{{ $this->discussion->title }}</h1>
            <div class="flex items-center justify-between w-full h-auto pb-3">
                <a href="{{ route('discussions') }}" class="relative inline-block w-auto text-sm font-medium text-black opacity-50 cursor-pointer dark:text-white hover:opacity-100 group">
                    <span>&larr; back to all {{ strtolower(trans('discussions::text.titles.discussions')) }}</span>
                    <span class="absolute bottom-0 left-0 w-0 h-px duration-200 ease-out bg-gray-900 group-hover:w-full"></span>
                </a>
            </div>
            @include('discussions::partials.guest-auth-message')
            @if (session()->has('message'))
            <div class="p-4 mb-4 text-white bg-green-500 rounded">
                {{ session('message') }}
            </div>
            @endif
        </div>
    </discussion-content-top>
    <div class="flex items-start w-full space-x-5">

        <discussion-content-left class="relative w-full">
            <div class="mb-4 space-y-4">
                @if ($editing)
                    <x-filament::input.wrapper class="w-full !{{ config('discussions.styles.rounded') }}">
                        <x-filament::input
                            type="text"
                            wire:model="editingTitle"
                            class="w-full"
                        />
                    </x-filament::input.wrapper>
                    {{ $this->form }}
                    <div class="flex justify-end mt-2 space-x-3">
                        <x-button wire:click="cancelEditing" color="gray" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.cancel')</x-button>
                        <x-button wire:click="updateDiscussion" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.save')</x-button>
                    </div>
                @else
                <div class="p-5 bg-white dark:bg-white/5 border border-neutral-200 dark:border-gray-700 @if (config('discussions.styles.rounded') == 'rounded-full') {{ 'rounded-xl' }}@else{{ config('discussions.styles.rounded') }} @endif">
                    <div class="flex items-center mb-5 space-x-2">
                        <a href="{{ $this->discussion->user->profile_url }}" class="flex items-center space-x-2 text-sm font-bold group">
                            @include('discussions::partials.discussion-avatar', [
                            'user' => $this->discussion->user,
                            'size' => 'sm',
                            ])
                            <span class="text-gray-900 group group-hover:underline dark:text-gray-100">{{ $this->discussion->user->name }}</span>
                        </a>
                        <p class="text-xs text-gray-500">on {{ $this->discussion->created_at->format('F jS, Y') }}
                        </p>
                    </div>
                    <dicussion-post class="mb-2 prose-sm prose dark:prose-invert">
                        {!! Str::markdown($this->discussion->content) !!}
                    </discussion-post>
                        @auth
                            <div class="flex justify-end mr-auto space-x-2 text-sm">
                                {{-- <button wire:click="reportDiscussion" class="font-medium text-neutral-500 hover:text-orange-400 hover:underline">@lang('discussions::messages.words.report')</button> --}}
                                @if (auth()->user()->id == $this->discussion->user_id)
                                    <button wire:click="editDiscussion" class="font-medium text-neutral-500 hover:text-blue-500 hover:underline">@lang('discussions::messages.words.edit')</button>
                                    <x-filament::modal id="delete-modal">
                                        <x-slot name="trigger">
                                            <button class="font-medium text-neutral-500 hover:text-red-500 hover:underline">@lang('discussions::messages.words.delete')</button>
                                        </x-slot>
                                        
                                        @include('discussions::partials.delete-modal-content', ['type' => 'discussion'])
                                    </x-filament::modal>
                                
                                @endif
                            </div>
                        @endauth
                </div>
                @endif
            </div>

            @livewire('discussion-posts', ['discussion' => $this->discussion], key($this->discussion->id))

            <div class="flex flex-col items-end mt-4 mb-4 space-y-4">
                {{-- @auth --}}
                    <div class="w-full mb-1">
                        {{ $this->replyForm }}
                    </div>
                    <x-button wire:click="answer" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.comment')</x-button>
                    
                {{-- @endif --}}
            </div>
        </discussion-content-left>

        <discussion-content-right class="{{ config('discussions.styles.sidebar_width') }} flex-shrink-0 text-sm ml-8">
            <h3 class="font-semibold text-neutral-500 dark:text-gray-400">Category</h3>
            @if(is_null($this->discussion->category_slug))
                <p class="w-full my-4 text-xs text-gray-500 rounded-md dark:text-gray-400">{{ trans('discussions::messages.discussion.no_category') }}</p>
            @else
                <p class="my-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ Wave\Plugins\Discussions\Helpers\Category::name($this->discussion->category_slug) }}
                </p>
            @endif
            <hr class="border-gray-200 dark:border-gray-700" />
            <h3 class="mt-5 font-semibold text-neutral-500 dark:text-gray-400">Participants</h3>
            
            <div class="my-2 space-y-1.5">
                @forelse ($this->discussion->users()->get() as $user)
                    <a href="{{ $user->link() }}" class="flex items-center space-x-2 font-medium text-gray-700 dark:text-gray-200 hover:underline">
                        @include('discussions::partials.discussion-avatar', [
                            'user' => $user,
                            'size' => 'xs',
                            ])
                        <span>{{ $user->name }}</span>
                    </a>
                @empty
                    <p class="w-full my-4 text-xs text-gray-400 rounded-md">{{ trans('discussions::messages.discussion.no_participants') }}</p>
                @endforelse
            </div>
            <hr class="border-gray-200 dark:border-gray-700" />
            @auth
                <h3 class="mt-5 font-semibold text-neutral-500 dark:text-gray-400">Notifications</h3>
                <div class="relative w-auto h-full my-2">
                    @if ($user_subscribed)
                        <x-button color="success" icon="phosphor-bell-ringing" x-on:click="$dispatch('toggleNotification')" class="flex-shrink-0 w-full flex items-center justify-center !{{ config('discussions.styles.rounded') }}">
                            <span>Subscribed</span>
                        </x-button>
                    @else
                        <x-button color="gray" icon="phosphor-bell" x-on:click="$dispatch('toggleNotification')" class="flex-shrink-0 w-full flex items-center justify-center !{{ config('discussions.styles.rounded') }}">
                            <span>Subscribe</span>
                        </x-button>
                    @endif
                </div>
                @if ($user_subscribed)
                    <p>You're receiving notifications because you're subscribed to this thread.</p>
                @else
                    <p>You are not recieving notifications about this discussion</p>
                @endif
            @endauth
        </discussion-content-right>
    </div>
</div>
