{{-- resources/views/auth/user_driver/register.blade.php --}}
<x-driver-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full max-w-[1200px] mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">
                    @if($isIndependent)
                        Register as an Independent Driver
                    @else
                        Join {{ $carrier->name }} as a Driver
                    @endif
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Complete your registration to get started
                </p>
            </div>

            {{-- Para depuración, podemos agregar esto temporalmente --}}
            <div class="mb-4 p-4 bg-gray-100 rounded">
                <h3 class="font-medium">Debug Info:</h3>
                <p>IsIndependent: {{ $isIndependent ? 'Yes' : 'No' }}</p>
                <p>Carrier ID: {{ $carrier->id ?? 'None' }}</p>
                <p>Carrier Name: {{ $carrier->name ?? 'None' }}</p>
            </div>

            {{-- Aquí deberíamos ver el componente Livewire --}}
            <livewire:driver.driver-registration-manager 
                :carrier="$carrier ?? null" 
                :token="$token ?? null" 
            />
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-driver-layout>