{{-- resources/views/auth/user_driver/resume_registration.blade.php --}}
<x-driver-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full max-w-[1200px] mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">
                    Resume Your Driver Registration
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    You can continue your registration from where you left off
                </p>
                <div class="mt-4 p-3 bg-blue-50 text-blue-700 rounded-md">
                    <p class="font-medium">Your registration is in progress.</p>
                    <p>We've saved your information and you can continue from step {{ $currentStep }}.</p>
                </div>
            </div>

            {{-- Componente Livewire con los parámetros para continuar el registro --}}
            <livewire:driver.driver-registration-manager 
                :carrier="$carrier ?? null" 
                :driverId="$driverId ?? null"
                :currentStep="$currentStep ?? 1"
            />
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</x-driver-layout>
