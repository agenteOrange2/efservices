@props(['title', 'icon' => null, 'class' => ''])

<div class="bg-white shadow-sm rounded-lg border border-gray-200 {{ $class }}">
    @if($title || $icon)
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <div class="flex items-center">
                @if($icon)
                    <div class="flex-shrink-0 mr-3">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <x-base.lucide icon="{{ $icon }}" class="w-5 h-5 text-blue-600" />
                        </div>
                    </div>
                @endif
                <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
            </div>
        </div>
    @endif
    
    <div class="px-6 py-6">
        {{ $slot }}
    </div>
</div>