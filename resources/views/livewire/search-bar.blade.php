<div class="relative">
    <svg class="absolute inset-y-0 left-0 z-10 my-auto ml-3 h-4 w-4 stroke-[1.3] text-slate-500"
        viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
        <circle cx="10.5" cy="10.5" r="6.5" stroke="#ababab" stroke-linejoin="round"></circle>
        <path d="m15.3536 14.6464 4.2938 4.2939c.1953.1953.5118.1953.7071.7071-.1953-.1953-.5118-.1953-.7071 0l-4.2939-4.2938"
            stroke="#ababab" fill="#ababab"></path>
    </svg>
    <input 
        type="text" 
        wire:model.live.debounce.500ms="search"
        placeholder="{{ $placeholder }}" 
        class="rounded-[0.5rem] pl-9 sm:w-64 border border-gray-300 px-4 py-2 w-full">
</div>
