<div class="items-center sm:flex-col">
    <div class="flex items-center justify-center flex-shrink-0 w-12 h-12 mx-auto bg-red-100 rounded-full sm:h-10 sm:w-10">
        <svg class="w-6 h-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"></path></svg>
    </div>
    <div class="mt-3 text-center sm:ml-4 sm:mt-0">
        <h3 class="mt-2 text-base font-semibold leading-6 text-gray-900">
            @lang('discussions::messages.words.delete')
            @if($type == 'post')
                @lang('discussions::text.titles.post')
            @else
                @lang('discussions::text.titles.discussion')
            @endif
        </h3>
        <p class="my-0 text-sm text-gray-500">@lang('discussions::messages.response.confirm')</p>
    </div>
</div>
<div class="items-center justify-center sm:flex sm:space-x-3">
    <div class="w-full">
        <x-button color="gray" x-on:click="$dispatch('close-modal', { id: 'delete-modal' })" type="button" class="w-full flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.cancel')</x-button>
    </div>
    <div class="w-full">
        <x-button color="danger" wire:click="deleteDiscussion" type="button" class="w-full flex-shrink-0 !{{ config('discussions.styles.rounded') }}">@lang('discussions::messages.words.delete')</x-button>
    </div>
</div>