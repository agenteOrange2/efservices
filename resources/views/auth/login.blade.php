<x-guest-layout>

    <div
        class="container grid grid-cols-12 px-5 py-10 sm:px-10 sm:py-14 md:px-36 lg:h-screen lg:max-w-[1550px] lg:py-0 lg:pl-14 lg:pr-12 xl:px-24 2xl:max-w-[1750px]">
        <div @class([
            'relative z-50 h-full col-span-12 p-7 sm:p-14 bg-white rounded-2xl lg:bg-transparent lg:pr-10 lg:col-span-5 xl:pr-24 2xl:col-span-4 lg:p-0',
            "before:content-[''] before:absolute before:inset-0 before:-mb-3.5 before:bg-white/40 before:rounded-2xl before:mx-5",
        ])>
            <div class="relative z-10 flex flex-col justify-center w-full h-full py-2 lg:py-32">
                {{-- <div
                    class="flex h-[55px] w-[55px] items-center justify-center rounded-[0.8rem] border border-primary/30">                    
                    <div
                        class="relative flex h-[50px] w-[50px] items-center justify-center rounded-[0.6rem] bg-white bg-gradient-to-b from-theme-1/90 to-theme-2/90">
                        <div class="relative h-[26px] w-[26px] -rotate-45 [&_div]:bg-white">
                            <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                            <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                            <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                            </div>
                        </div>
                    </div>
                </div> --}}


                {{-- JETSTREAM --}}


                <div class="mt-10">
                    <img src="{{ asset('build/img/logo_efservices_logo.png') }}" class="w-[80px]" alt="">
                    <div class="text-2xl font-medium">Sign In</div>
                    <div class="mt-2.5 text-slate-600">
                        Don't have an account?
                        <a class="font-medium text-primary">
                            Sign Up
                        </a>
                    </div>
                    <x-base.alert
                        class="my-7 flex items-center rounded-[0.6rem] border-primary/20 bg-primary/5 px-4 py-3 leading-[1.7]"
                        variant="outline-primary">
                        <div class="">
                            <x-base.lucide class="mr-2 h-7 w-7 fill-primary/10 stroke-[0.8]" icon="Lightbulb" />
                        </div>
                        <div class="ml-1 mr-8">
                            Welcome to <span class="font-medium">EF Services</span>
                            demo! Simply click
                            <span class="font-medium">Sign In</span> to explore
                            and access our documentation.
                        </div>
                        <x-base.alert.dismiss-button class="btn-close text-primary">
                            <x-base.lucide class="w-5 h-5" icon="X" />
                        </x-base.alert.dismiss-button>
                    </x-base.alert>

                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <x-validation-errors class="mb-4" />

                        @session('status')
                            <div class="mb-4 font-medium text-sm text-green-600">
                                {{ $value }}
                            </div>
                        @endsession


                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <div>
                            <x-label for="email" value="{{ __('Email') }}" />
                            <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                placeholder="user@efservices.com" id="email" class="block mt-1 w-full"
                                type="email" name="email" :value="old('email')" required autofocus
                                autocomplete="username" />
                        </div>

                        <div class="mt-4">
                            <x-label for="password" value="{{ __('Password') }}" />
                            <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-3.5"
                                placeholder="************" id="password" class="block mt-1 w-full" type="password"
                                name="password" required autocomplete="current-password" />
                        </div>

                        <div class="flex mt-4 text-xs text-slate-500 sm:text-sm">
                            <div class="flex items-center mr-auto">
                                <label for="remember_me" class="flex items-center">
                                    <x-base.form-check.input class="mr-2.5 border" id="remember-me" type="checkbox"
                                        name="remember" />
                                    <label class="cursor-pointer select-none">{{ __('Remember me') }}</label>
                                </label>
                            </div>

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}">
                                    {{ __('Forgot your password?') }}
                                </a>
                            @endif
                        </div>
                        <div class="mt-5 text-center xl:mt-8 xl:text-left">
                            <x-base.button
                                class="w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3 text-white">
                                {{ __('Log in') }}
                            </x-base.button>
                        </div>
                    </form>
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
