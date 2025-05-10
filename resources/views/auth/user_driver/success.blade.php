<x-guest-layout>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center">
        <div class="max-w-lg w-full bg-white shadow-lg rounded-lg p-8">
            <div class="text-center">
                <!-- Icono de éxito -->
                <div class="flex justify-center items-center w-16 h-16 mx-auto bg-green-100 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
    
                <!-- Título principal -->
                <h1 class="mt-6 text-2xl font-bold text-gray-800">Your Driver Account Has Been Created!</h1>
                
                <!-- Mensaje de confirmación -->
                <p class="mt-4 text-gray-600">
                    Thank you for registering as a driver with <span class="font-semibold text-blue-500">{{ $carrierName }} </span>. 
                    Your account is under review, and you will receive an email notification once it is activated.
                </p>
            </div>
    
            <!-- Información adicional -->
            <div class="mt-6">
                <p class="text-sm text-gray-500 text-center">
                    Please allow up to 48 hours for our team to review your details. If you have any questions, feel free to 
                    {{-- <a href="{{ route('contact') }}" class="text-blue-500 hover:underline">contact us</a>. --}}
                </p>
            </div>
    
            <!-- Botón de regreso -->
            <div class="mt-8 text-center">
                <a href="{{ route('login') }}" 
                   class="inline-block px-6 py-3 text-white bg-blue-500 rounded-lg shadow hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
                    Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</x-guest-layout>
