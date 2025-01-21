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

            <form method="POST"
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
            </form>
        </div>
    </div>
</x-guest-layout>
