<x-guest-layout>
    <div
        class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0',
            "before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5",
        ])>
            <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                <div
                    class="flex h-[55px] w-[55px] items-center justify-center rounded-[0.8rem] border border-primary/30">
                    <div
                        class="relative flex h-[50px] w-[50px] items-center justify-center rounded-[0.6rem] bg-white bg-gradient-to-b from-theme-1/90 to-theme-2/90">
                        <div class="relative h-[26px] w-[26px] -rotate-45 [&_div]:bg-white">
                            <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                            </div>
                            <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                            <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- JETSTREAM --}}

                <div class="mt-10">
                    <div class="text-2xl font-medium">Sign Up</div>
                    <div class="mt-2.5 text-slate-600">
                        Already have an account?
                        <a class="font-medium text-primary" href="">
                            Sign In
                        </a>
                    </div>

                    <div class="mt-6">
                        <form method="POST" action="{{ route('carrier.register') }}">
                            @csrf

                            @if ($errors->has('email'))
                                <div class="alert alert-danger">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif

                            <x-validation-errors class="mb-4" />
                            <x-base.form-label>Full Name*</x-base.form-label>
                            <x-base.form-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                type="text" placeholder="Jonh Doe" name="name" value="{{ old('name') }}" />
                            <x-base.form-label class="mt-5">Email*</x-base.form-label>
                            <x-base.form-input id="email" class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                type="email" placeholder="Jonh@efservices.com" name="email"
                                value="{{ old('email') }}" />
                            <x-base.form-label class="mt-5">Phone</x-base.form-label>
                            <div class="flex">
                                <div class="w-[90px] mr-2">
                                    <select id="country-code" class="block w-full rounded-[0.6rem] border-slate-300/80 px-3 py-3.5 text-gray-500 focus:ring-0 sm:text-sm">
                                        <option value="+1">+1 (US)</option>
                                        <option value="+52">+52 (MX)</option>
                                        <option value="+44">+44 (UK)</option>
                                        <option value="+34">+34 (ES)</option>
                                        <option value="+33">+33 (FR)</option>
                                        <option value="+49">+49 (DE)</option>
                                    </select>
                                </div>
                                <div class="flex-1">
                                    <x-base.form-input id="phone" class="block w-full rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                        type="tel" placeholder="(123) 456-7890" name="phone" value="{{ old('phone') }}" />
                                </div>
                            </div>
                            <x-base.form-label class="mt-5">Job Position</x-base.form-label>
                            <x-base.form-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                type="text" placeholder="Administrative" name="job_position"
                                value="{{ old('job_position') }}" />
                            <x-base.form-label class="mt-5">Password*</x-base.form-label>
                            <div class="relative">
                                <x-base.form-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5 pr-10"
                                    type="password" name="password" id="password" placeholder="************" />
                                <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-off-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            {{-- <div class="mt-3.5 grid h-1.5 w-full grid-cols-12 gap-4">
                                <div
                                    class="active col-span-3 h-full rounded border border-slate-400/20 bg-slate-400/30 [&.active]:border-theme-1/20 [&.active]:bg-theme-1/30">
                                </div>
                                <div
                                    class="active col-span-3 h-full rounded border border-slate-400/20 bg-slate-400/30 [&.active]:border-theme-1/20 [&.active]:bg-theme-1/30">
                                </div>
                                <div
                                    class="active col-span-3 h-full rounded border border-slate-400/20 bg-slate-400/30 [&.active]:border-theme-1/20 [&.active]:bg-theme-1/30">
                                </div>
                                <div
                                    class="col-span-3 h-full rounded border border-slate-400/20 bg-slate-400/30 [&.active]:border-theme-1/20 [&.active]:bg-theme-1/30">
                                </div>
                            </div> --}}
                            <a class="mt-3 block text-xs text-slate-500/80 sm:text-sm" href="">
                                What is a secure password?
                            </a>
                            <x-base.form-label class="mt-5">Password Confirmation*</x-base.form-label>
                            <div class="relative">
                                <x-base.form-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5 pr-10"
                                    type="password" name="password_confirmation" id="password_confirmation"
                                    placeholder="************" />
                                <button type="button" id="toggle-password-confirmation" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 eye-off-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                    </svg>
                                </button>
                            </div>
                            {{-- <div class="mt-5 flex items-center text-xs text-slate-500 sm:text-sm">
                                <x-base.form-check.input class="mr-2 border" id="remember-me" type="checkbox" />
                                <label class="cursor-pointer select-none" for="remember-me">
                                    I agree to the Envato
                                </label>
                                <a class="ml-1 text-primary" href="">
                                    Privacy Policy
                                </a>
                                .
                            </div> --}}
                            <div class="mt-5 text-center xl:mt-8 xl:text-left">
                                <x-base.button
                                    class="w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3"
                                    variant="primary" rounded>
                                    Sign In
                                </x-base.button>
                                <x-base.button type="submit" class="mt-3 w-full bg-white/70 py-3.5"
                                    variant="outline-secondary" rounded>
                                    Sign Up
                                </x-base.button>
                            </div>
                        </form>
                    </div>

                    <!-- Script para IMask y mostrar/ocultar contraseña -->
                    <script src="https://unpkg.com/imask"></script>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // IMask para email
                            const emailMask = IMask(document.getElementById('email'), {
                                mask: /^\S*@?\S*$/
                            });

                            // IMask para teléfono
                            const phoneMask = IMask(document.getElementById('phone'), {
                                mask: '(000) 000-0000',
                                lazy: false,
                                placeholderChar: '_'
                            });

                            // Actualizar el formato del teléfono cuando cambia el código de país
                            document.getElementById('country-code').addEventListener('change', function() {
                                const countryCode = this.value;
                                let mask = '(000) 000-0000';
                                
                                // Ajustar la máscara según el país seleccionado
                                switch(countryCode) {
                                    case '+52': // México
                                        mask = '(00) 0000-0000';
                                        break;
                                    case '+44': // Reino Unido
                                        mask = '00 0000 0000';
                                        break;
                                    case '+34': // España
                                        mask = '000 000 000';
                                        break;
                                    case '+33': // Francia
                                        mask = '0 00 00 00 00';
                                        break;
                                    case '+49': // Alemania
                                        mask = '000 0000000';
                                        break;
                                    default: // Estados Unidos y otros
                                        mask = '(000) 000-0000';
                                }
                                
                                // Actualizar la máscara
                                phoneMask.updateOptions({
                                    mask: mask
                                });
                            });

                            // Función para mostrar/ocultar contraseña
                            function setupPasswordToggle(toggleId, passwordId) {
                                const toggleButton = document.getElementById(toggleId);
                                const passwordInput = document.getElementById(passwordId);
                                const eyeIcon = toggleButton.querySelector('.eye-icon');
                                const eyeOffIcon = toggleButton.querySelector('.eye-off-icon');

                                toggleButton.addEventListener('click', function() {
                                    if (passwordInput.type === 'password') {
                                        passwordInput.type = 'text';
                                        eyeIcon.classList.add('hidden');
                                        eyeOffIcon.classList.remove('hidden');
                                    } else {
                                        passwordInput.type = 'password';
                                        eyeIcon.classList.remove('hidden');
                                        eyeOffIcon.classList.add('hidden');
                                    }
                                });
                            }

                            // Configurar los botones de mostrar/ocultar contraseña
                            setupPasswordToggle('toggle-password', 'password');
                            setupPasswordToggle('toggle-password-confirmation', 'password_confirmation');
                        });
                    </script>
                </div>

            </div>
        </div>
    </div>
    <div
        class="container fixed inset-0 grid h-screen w-screen grid-cols-12 pl-14 pr-12 lg:max-w-[1550px] xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative h-screen col-span-12 lg:col-span-5 2xl:col-span-4 z-20',
            "after:bg-white after:hidden after:lg:block after:content-[''] after:absolute after:right-0 after:inset-y-0 after:bg-gradient-to-b after:from-white after:to-slate-100/80 after:w-[800%] after:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
            "before:content-[''] before:hidden before:lg:block before:absolute before:right-0 before:inset-y-0 before:my-6 before:bg-gradient-to-b before:from-white/10 before:to-slate-50/10 before:bg-white/50 before:w-[800%] before:-mr-4 before:rounded-[0_1.2rem_1.2rem_0/0_1.7rem_1.7rem_0]",
        ])></div>
        <div @class([
            'h-full col-span-7 2xl:col-span-8 lg:relative',
            "before:content-[''] before:absolute before:lg:-ml-10 before:left-0 before:inset-y-0 before:bg-gradient-to-b before:from-theme-1 before:to-theme-2 before:w-screen before:lg:w-[800%]",
            "after:content-[''] after:absolute after:inset-y-0 after:left-0 after:w-screen after:lg:w-[800%] after:bg-texture-white after:bg-fixed after:bg-center after:lg:bg-[25rem_-25rem] after:bg-no-repeat",
        ])>
            <div class="sticky top-0 z-10 flex-col justify-center hidden h-screen ml-16 lg:flex xl:ml-28 2xl:ml-36">
                <div class="text-[2.6rem] font-medium leading-[1.4] text-white xl:text-5xl xl:leading-[1.2]">
                    Welcome to EF Services
                </div>
                <div class="mt-5 text-base leading-relaxed text-white/70 xl:text-lg">
                    Our dedicated team is committed to guiding you at every turn. We go above and beyond to ensure
                    complete customer satisfaction, delivering tailored transport solutions designed to keep you moving
                    forward.
                </div>
                <div class="flex flex-col gap-3 mt-10 xl:flex-row xl:items-center">
                    {{-- <div class="flex items-center">
                            <div class="image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                                <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                    src="{{ Vite::asset($users[0]['photo']) }}"
                                    alt="Tailwise - Admin Dashboard Template" as="img"
                                    content="{{ $users[0]['name'] }}" />
                            </div>
                            <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                                <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                    src="{{ Vite::asset($users[1]['photo']) }}"
                                    alt="Tailwise - Admin Dashboard Template" as="img"
                                    content="{{ $users[1]['name'] }}" />
                            </div>
                            <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                                <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                    src="{{ Vite::asset($users[2]['photo']) }}"
                                    alt="Tailwise - Admin Dashboard Template" as="img"
                                    content="{{ $users[2]['name'] }}" />
                            </div>
                            <div class="-ml-3 image-fit zoom-in h-9 w-9 2xl:h-11 2xl:w-11">
                                <x-base.tippy class="rounded-full border-[3px] border-white/50"
                                    src="{{ Vite::asset($users[3]['photo']) }}"
                                    alt="Tailwise - Admin Dashboard Template" as="img"
                                    content="{{ $users[3]['name'] }}" />
                            </div>
                        </div> --}}
                    <div class="text-base text-white/70 xl:ml-2 2xl:ml-3">
                        Log in now and experience the difference that passion, reliability, and innovation can bring to
                        your operations.
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- <ThemeSwitcher /> --}}


</x-guest-layout>
