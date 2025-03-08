@if ($errors->any())
    <div {{ $attributes }}>
        <div class="font-medium text-red-600">{{ __('Whoops! Something went wrong.') }}</div>

        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
            @foreach ($errors->all() as $error)
            <div role="alert" class="alert relative border rounded-md px-5 py-4 border-danger text-danger dark:border-danger mb-2 flex items-center">
                <i data-tw-merge data-lucide="alert-octagon" class="stroke-[1] w-5 h-5 mr-2 h-6 w-6 mr-2 h-6 w-6"></i>                    
                {{ $error }}
                <button data-tw-merge data-tw-dismiss="alert" type="button" aria-label="Close"
                    class="text-slate-800 py-2 px-3 absolute right-0 my-auto mr-2 btn-close"><i data-tw-merge data-lucide="x"
                        class="stroke-[1] w-5 h-5 h-4 w-4 h-4 w-4"></i></button>
            </div>
                            
            @endforeach
        </ul>
    </div>
@endif


