<div id="discussion-create"
    x-data="{ open: false }" x-cloak>
<div x-show="open" class="fixed z-[149] inset-0 w-screen h-screen bg-black/30" x-cloak></div>
<div 
    x-show="open"
    x-on:click.outside="open=false"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
    x-transition:leave="transition ease-in duration-300" x-transition:leave-start="translate-y-0"
    x-transition:leave-end="translate-y-full" @discussion-new-open.window="open = true"
    @close-new-discussion.window="open=false"
    x-init="
        $watch('open', function(value){
            if(value){ 
                setTimeout(function(){
                    $refs.title.focus();
                }, 300);
            }
        });
    "
    class="fixed discussion-editor bottom-0 left-1/2 z-[150] -translate-x-1/2 flex items-center justify-end w-full max-w-4xl md:px-5 px-0 mx-auto z-30">
    <div class="relative bottom-0 flex flex-col w-full max-h-screen bg-white border border-b-0 border-gray-300 md:rounded-t-xl shadow-3xl" x-cloak>
        <div class="flex items-start p-5 pb-4 space-x-1 border-b border-gray-200">
            @include('discussions::partials.discussion-avatar', ['user' => auth()->user(), 'size' => 'sm'])
            <div class="relative flex flex-col w-full">
                <div class="pr-10">
                    <input x-ref="title" wire:model="title" type="text" placeholder="@lang('discussions::messages.editor.title')"
                        class="w-full py-2 pr-3 font-medium border-0 focus:ring-0 focus:outline-none">
                </div>
            </div>
        </div>
        <div x-data class="relative w-full min-h-[250px] flex-grow-0 overflow-y-auto">
            {{ $this->form }}
        </div>
        <div
            class="relative flex items-center justify-between px-5 pt-4 pb-3 text-xs font-semibold bg-white border-t border-gray-200 hover:text-gray-700">
            <div x-data="{ dropdownOpen: false }" @click.away="dropdownOpen = false">
                <button @click="dropdownOpen = !dropdownOpen"
                    class="flex items-center px-4 py-2 space-x-1 font-medium text-gray-500 bg-gray-100 rounded-full hover:bg-gray-200/60">
                    @if ($category_slug)
                        <span>{{ Wave\Plugins\Discussions\Helpers\Category::name($category_slug) }}</span>
                    @else
                        <span>Select a Category</span>
                    @endif
                    <svg class="w-4 h-4 rotate-180 translate-y-px" aria-hidden="true" viewBox="0 0 16 16" version="1.1" data-view-component="true"><path d="m4.427 7.427 3.396 3.396a.25.25 0 0 0 .354 0l3.396-3.396A.25.25 0 0 0 11.396 7H4.604a.25.25 0 0 0-.177.427Z"></path></svg>
                </button>
                <div x-show="dropdownOpen"
                    class="absolute w-48 mb-2 bg-white rounded-md shadow-lg bottom-full ring-1 ring-black ring-opacity-5">
                    <div class="py-1" role="menu" aria-orientation="vertical"
                        aria-labelledby="options-menu">
                        @foreach (config('discussions.categories') as $index => $category)
                            <button wire:click="setCreateCategory('{{ $index }}')"
                                @click="dropdownOpen = !dropdownOpen"
                                class="block w-full px-4 py-2 text-sm text-left text-gray-700 hover:bg-gray-100 hover:text-gray-900"
                                role="menuitem">{{ Wave\Plugins\Discussions\Helpers\Category::name($index) }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="relative flex items-center space-x-2">
                <x-button color="gray" @click="open=false" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.cancel')</x-button>
                <x-button wire:click="createDiscussion" class="flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.submit')</x-button>
            </div>
        </div>

        <div class="absolute top-0 right-0 mt-3 mr-3">
            <div @click="open = false" class="block p-2 text-gray-500 rounded-full cursor-pointer hover:text-gray-700 hover:bg-gray-50">
                <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
            </div>
        </div>
    </div>
</div></div>