<div>
    @if($count > 0)
        <div class="absolute -top-2 -right-2 h-5 w-5 rounded-full bg-danger flex items-center justify-center text-white text-xs">
            {{ $count > 9 ? '9+' : $count }}
        </div>
    @endif
</div>