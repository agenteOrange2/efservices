<div class="relative flex items-center w-full">
    <svg class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500 {{ $searchIconClass }}"
        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10.5" cy="10.5" r="6.5" stroke="#ababab" stroke-linejoin="round"></circle>
        <path d="m15.3536 14.6464 4.2938 4.2939c.1953.1953.5118.1953.7071.7071-.1953-.1953-.5118-.1953-.7071 0l-4.2939-4.2938"
            stroke="#ababab" fill="#ababab"></path>
    </svg>
    <div class="relative w-full flex items-center">
        <input 
            type="text" 
            wire:model.live.debounce.{{ $debounce }}ms="search"
            placeholder="{{ $placeholder }}" 
            class="rounded-[0.5rem] pl-9 sm:w-64 border border-gray-300 px-4 py-2 w-full {{ $inputClass }}"
        >
        @if (!empty($search))
            <button 
                wire:click="clearSearch" 
                class="absolute right-2 text-gray-500 hover:text-gray-700"
                title="Clear search">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        @endif
    </div>
    @if($showSearchButton)
        <button 
            type="button" 
            wire:click="search" 
            class="ml-2 bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition-colors">
            {{ $buttonText }}
        </button>
    @endif
</div>
