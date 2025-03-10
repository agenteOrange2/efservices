{{-- resources/views/auth/user_driver/select_carrier_registration.blade.php --}}
<x-guest-layout>
    <div class="container mx-auto px-4 py-8">
        <h2 class="text-2xl font-bold mb-6">Selecciona un Carrier</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach ($carriers as $carrier)
            <a href="{{ route('driver.register.form', $carrier->slug) }}" 
                   class="block p-4 border rounded hover:shadow-lg transition-shadow">
                    <h3 class="font-bold text-lg">{{ $carrier->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $carrier->city }}, {{ $carrier->state }}</p>
                </a>
            @endforeach
        </div>
    </div>
</x-guest-layout>
