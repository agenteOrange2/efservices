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
                            <div class="absolute inset-y-0 left-0 my-auto h-[75%] w-[20%] rounded-full opacity-50"></div>
                            <div class="absolute inset-0 m-auto h-[120%] w-[20%] rounded-full"></div>
                            <div class="absolute inset-y-0 right-0 my-auto h-[75%] w-[20%] rounded-full opacity-50">
                            </div>
                        </div>
                    </div>
                </div>
                {{-- JETSTREAM --}}

                <div class="mt-10">
                    <div class="text-2xl font-medium">Complete Your Carrier Registration</div>
                    <div class="mt-7">


                        @if (session('status'))
                            <div class="alert alert-success">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form class="max-w-md mx-auto" method="POST"
                            action="{{ route('carrier.complete_registration') }}">
                            @csrf

                            <div class="relative z-0 w-full mb-5 group">
                                <x-label for="email" value="{{ __('Carrier Name') }}" />
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="text" name="name" id="name" value="{{ old('name') }}"
                                    placeholder="Company Name" required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="carrier_address">Address</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="text" name="address" id="address" value="{{ old('address') }}"
                                    required />
                            </div>

                            <!-- State -->
                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="state">State</label>
                                <select name="state" id="state"
                                    class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full" required>
                                    <option value="">{{ __('Select State') }}</option>
                                    @foreach ($usStates as $abbr => $name)
                                        <option value="{{ $abbr }}"
                                            {{ old('state') === $abbr ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="zipcode">Zip Code</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="number" name="zipcode" id="zipcode" value="{{ old('zipcode') }}"
                                    required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="ein_number">EIN Number</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="number" name="ein_number" id="ein_number" value="{{ old('ein_number') }}"
                                    required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="dot_number">Dot Number</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="number" name="dot_number" id="dot_number" value="{{ old('dot_number') }}"
                                    required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="mc_number">MC Number</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="number" name="mc_number" id="mc_number" value="{{ old('mc_number') }}"
                                    required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="state_dot">State Dot</label>
                                <x-input class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    type="text" name="state_dot" id="state_dot" value="{{ old('state_dot') }}"
                                    required />
                            </div>

                            <div class="relative z-0 w-full mb-5 group">
                                <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                    for="ifta_account">IFTA Account</label>
                                <x-input type="text" name="ifta_account" id="ifta_account"
                                    class="block rounded-[0.6rem] border-slate-300/80 px-4 py-2.5 mt-1 w-full"
                                    value="{{ old('ifta_account') }}" required />
                            </div>

                            <!-- Membership Selection (opcional) -->
                            
                                <div class="relative z-0 w-full mb-5 group">
                                    <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white"
                                        for="membership">Membership</label>
                                        <select data-tw-merge aria-label="Default select example"
                                        class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                        id="id_plan" name="id_plan">
                                        <option value="">Select a Membership Plan</option>
                                        @foreach ($memberships as $membership)
                                            <option value="{{ $membership->id }}"
                                                {{ old('id_plan') == $membership->id ? 'selected' : '' }}>
                                                {{ $membership->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            

                            <div class="mt-5 text-center xl:mt-8 xl:text-left">
                                <x-base.button type="submit"
                                    class="w-full bg-gradient-to-r from-theme-1/70 to-theme-2/70 py-3.5 xl:mr-3 text-white">
                                    {{ __('Complete Registration') }}
                                </x-base.button>
                            </div>
                        </form>
                    </div>
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
                    Embrace Excellence <br> in Dashboard Development
                </div>
                <div class="mt-5 text-base leading-relaxed text-white/70 xl:text-lg">
                    Unlock the potential of Tailwise, where developers craft
                    meticulously structured, visually stunning dashboards with
                    feature-rich modules. Join us today to shape the future of your
                    application development.
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
                        Over 7k+ strong and growing! Your journey begins here.
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
