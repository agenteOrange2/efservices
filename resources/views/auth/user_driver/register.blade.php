{{-- resources/views/auth/driver/register.blade.php --}}
<x-guest-layout>
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            <div class="mb-6 text-center">
                <h2 class="text-2xl font-bold text-gray-900">
                    Join {{ $carrier->name }} as a Driver
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Complete your registration to get started
                </p>
            </div>

            {{-- <form method="POST"
                action="{{ route('driver.register.submit', ['carrier' => $carrier->slug, 'token' => request()->query('token')]) }}">
                @csrf

                <input type="hidden" name="token" value="{{ request()->query('token') }}">

                <div>
                    <x-label for="name" value="Full Name" />
                    <x-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')"
                        required autofocus autocomplete="name" />
                </div>

                <div class="mt-4">
                    <x-label for="email" value="Email" />
                    <x-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')"
                        required />
                </div>

                <div class="mt-4">
                    <x-label for="phone" value="Phone Number" />
                    <x-input id="phone" class="block mt-1 w-full" type="tel" name="phone" :value="old('phone')"
                        required />
                </div>
                <!-- Address -->
                <div class="mt-4">
                    <x-label for="address" value="{{ __('Address') }}" />
                    <x-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')"
                        required />
                </div>

                <!-- Birth Date -->
                <div class="mt-4">
                    <x-label for="birth_date" value="{{ __('Birth Date') }}" />
                    <x-input id="birth_date" class="block mt-1 w-full" type="date" name="birth_date"
                        :value="old('birth_date')" required />
                </div>

                <!-- Years of Experience -->
                <div class="mt-4">
                    <x-label for="years_experience" value="{{ __('Years of Experience') }}" />
                    <x-input id="years_experience" class="block mt-1 w-full" type="number" name="years_experience"
                        :value="old('years_experience')" required min="0" max="50" />
                </div>
                <div class="mt-4">
                    <x-label for="license_number" value="Driver's License Number" />
                    <x-input id="license_number" class="block mt-1 w-full" type="text" name="license_number"
                        :value="old('license_number')" required />
                </div>

                <div class="mt-4">
                    <x-label for="password" value="Password" />
                    <x-input id="password" class="block mt-1 w-full" type="password" name="password" required
                        autocomplete="new-password" />
                </div>

                <div class="mt-4">
                    <x-label for="password_confirmation" value="Confirm Password" />
                    <x-input id="password_confirmation" class="block mt-1 w-full" type="password"
                        name="password_confirmation" required autocomplete="new-password" />
                </div>

                <div class="flex items-center justify-end mt-4">
                    <x-button class="w-full justify-center">
                        Register
                    </x-button>
                </div>
            </form> --}}

            <form method="POST"
                  action="{{ route('driver.register.submit', ['carrier' => $carrier->slug, 'token' => request()->query('token')]) }}">
                @csrf

                {{-- Token de referencia del carrier --}}
                <input type="hidden" name="token" value="{{ request()->query('token') }}">

                {{-- Nombre (tabla users) --}}
                <div>
                    <x-label for="name" value="Nombre (User.name)" />
                    <x-input id="name" 
                             class="block mt-1 w-full"
                             type="text"
                             name="name"
                             :value="old('name')"
                             required
                             autofocus />
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Middle Name (user_driver_details) --}}
                <div class="mt-4">
                    <x-label for="middle_name" value="Segundo Nombre (middle_name)" />
                    <x-input id="middle_name"
                             class="block mt-1 w-full"
                             type="text"
                             name="middle_name"
                             :value="old('middle_name')" />
                    @error('middle_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Apellido (user_driver_details) --}}
                <div class="mt-4">
                    <x-label for="last_name" value="Apellido (last_name)" />
                    <x-input id="last_name" 
                             class="block mt-1 w-full"
                             type="text"
                             name="last_name"
                             :value="old('last_name')"
                             required />
                    @error('last_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email (tabla users) --}}
                <div class="mt-4">
                    <x-label for="email" value="Correo Electrónico (User.email)" />
                    <x-input id="email" 
                             class="block mt-1 w-full"
                             type="email"
                             name="email"
                             :value="old('email')"
                             required />
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Teléfono (user_driver_details) --}}
                <div class="mt-4">
                    <x-label for="phone" value="Teléfono (user_driver_details.phone)" />
                    <x-input id="phone"
                             class="block mt-1 w-full"
                             type="tel"
                             name="phone"
                             :value="old('phone')"
                             required />
                    @error('phone')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- State of Issue (user_driver_details.state_of_issue) --}}
                <div class="mt-4">
                    <x-label for="state_of_issue" value="Estado de Emisión de la Licencia (state_of_issue)" />
                    <x-input id="state_of_issue"
                             class="block mt-1 w-full"
                             type="text"
                             name="state_of_issue"
                             :value="old('state_of_issue')"
                             required />
                    @error('state_of_issue')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Número de Licencia (user_driver_details.license_number) --}}
                <div class="mt-4">
                    <x-label for="license_number" value="Número de Licencia (license_number)" />
                    <x-input id="license_number"
                             class="block mt-1 w-full"
                             type="text"
                             name="license_number"
                             :value="old('license_number')"
                             required />
                    @error('license_number')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Fecha de Nacimiento (user_driver_details.date_of_birth) --}}
                <div class="mt-4">
                    <x-label for="date_of_birth" value="Fecha de Nacimiento (date_of_birth)" />
                    <x-input id="date_of_birth"
                             class="block mt-1 w-full"
                             type="date"
                             name="date_of_birth"
                             :value="old('date_of_birth')"
                             required />
                    @error('date_of_birth')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Contraseña (tabla users) --}}
                <div class="mt-4">
                    <x-label for="password" value="Contraseña" />
                    <x-input id="password"
                             class="block mt-1 w-full"
                             type="password"
                             name="password"
                             required
                             autocomplete="new-password" />
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Confirmar Contraseña (tabla users) --}}
                <div class="mt-4">
                    <x-label for="password_confirmation" value="Confirmar Contraseña" />
                    <x-input id="password_confirmation"
                             class="block mt-1 w-full"
                             type="password"
                             name="password_confirmation"
                             required
                             autocomplete="new-password" />
                </div>

                {{-- Términos y Condiciones (user_driver_details.terms_accepted) --}}
                <div class="mt-4 flex items-center">
                    <input id="terms_accepted" type="checkbox" name="terms_accepted" value="1" />
                    <x-label for="terms_accepted" value="Acepto los términos y condiciones" class="ml-2" />
                </div>

                {{-- Botón de Enviar --}}
                <div class="flex items-center justify-end mt-4">
                    <x-button class="w-full justify-center">
                        Registrar
                    </x-button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
