<x-guest-layout>

    {{-- <div id="root">
        <div class="min-h-screen bg-gray-50">
            <nav class="bg-navy-900 text-white px-6 py-4">
                <div class="max-w-7xl mx-auto flex justify-between items-center">
                    <div class="flex items-center space-x-2"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round"
                            class="lucide lucide-truck h-8 w-8 text-blue-400">
                            <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                            <path d="M15 18H9"></path>
                            <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                            </path>
                            <circle cx="17" cy="18" r="2"></circle>
                            <circle cx="7" cy="18" r="2"></circle>
                        </svg><span class="text-xl font-bold text-gray-600">EF Services</span></div>
                    <div class="hidden md:flex space-x-6"><a href="#" class="hover:text-blue-400">Services</a><a
                            href="#" class="hover:text-blue-400">About</a><a href="#"
                            class="hover:text-blue-400">Contact</a></div>
                    @if (Route::has('login'))
                        <nav class="-mx-3 flex flex-1 justify-end">
                            @auth
                                <a href="{{ url('/admin') }}"
                                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}"
                                    class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}"
                                        class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20]">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                    <button class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700 transition">Login</button>
                </div>
            </nav>
            <main>
                <section class="bg-navy-800 text-white py-20">
                    <div class="max-w-7xl mx-auto px-6">
                        <div class="grid md:grid-cols-2 gap-12 items-center">
                            <div>
                                <h1 class="text-4xl md:text-5xl font-bold mb-6 text-gray-600">Smart Fleet Management
                                    Solutions</h1>
                                <p class="text-lg text-gray-600 mb-8">Optimize your fleet operations with our
                                    comprehensive management platform. Track, manage, and improve your transportation
                                    services efficiently.</p>
                                <div class="flex space-x-4"><button
                                        class="bg-blue-600 px-6 py-3 rounded-lg hover:bg-blue-700 transition flex items-center">Get
                                        Started <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round"
                                            class="lucide lucide-chevron-right ml-2">
                                            <path d="m9 18 6-6-6-6"></path>
                                        </svg></button><button
                                        class="border border-white px-6 py-3 rounded-lg hover:bg-white hover:text-navy-800 transition">Learn
                                        More</button></div>
                            </div>
                            <div class="hidden md:block"><img
                                    src="https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&amp;fit=crop&amp;w=800"
                                    alt="Fleet Management" class="rounded-lg shadow-xl"></div>
                        </div>
                    </div>
                </section>
                <section class="py-20 bg-white">
                    <div class="max-w-7xl mx-auto px-6">
                        <h2 class="text-3xl font-bold text-center mb-12 text-navy-800">Comprehensive Fleet Management
                            Features</h2>
                        <div class="grid md:grid-cols-3 gap-8">
                            <div class="p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition">
                                <div class="mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-bar-chart3 h-8 w-8 text-blue-600">
                                        <path d="M3 3v18h18"></path>
                                        <path d="M18 17V9"></path>
                                        <path d="M13 17V5"></path>
                                        <path d="M8 17v-3"></path>
                                    </svg></div>
                                <h3 class="text-xl font-semibold mb-2 text-navy-800">Real-time Analytics</h3>
                                <p class="text-gray-600">Monitor your fleet performance with advanced analytics and
                                    reporting tools.</p>
                            </div>
                            <div class="p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition">
                                <div class="mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-users h-8 w-8 text-blue-600">
                                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg></div>
                                <h3 class="text-xl font-semibold mb-2 text-navy-800">Driver Management</h3>
                                <p class="text-gray-600">Efficiently manage drivers, schedules, and compliance
                                    requirements.</p>
                            </div>
                            <div class="p-6 bg-white rounded-xl shadow-lg hover:shadow-xl transition">
                                <div class="mb-4"><svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-clock h-8 w-8 text-blue-600">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg></div>
                                <h3 class="text-xl font-semibold mb-2 text-navy-800">Route Optimization</h3>
                                <p class="text-gray-600">Optimize routes for better efficiency and reduced fuel
                                    consumption.</p>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="py-20 bg-gray-50">
                    <div class="max-w-7xl mx-auto px-6">
                        <h2 class="text-3xl font-bold text-center mb-12 text-navy-800">Our Services</h2>
                        <div class="grid md:grid-cols-2 gap-8">
                            <div
                                class="p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition flex items-start space-x-6">
                                <div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-package h-12 w-12 text-blue-600">
                                        <path d="m7.5 4.27 9 5.15"></path>
                                        <path
                                            d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z">
                                        </path>
                                        <path d="m3.3 7 8.7 5 8.7-5"></path>
                                        <path d="M12 22V12"></path>
                                    </svg></div>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2 text-navy-800">Freight Management</h3>
                                    <p class="text-gray-600">Complete freight management solutions including tracking,
                                        scheduling, and optimization.</p>
                                </div>
                            </div>
                            <div
                                class="p-8 bg-white rounded-xl shadow-lg hover:shadow-xl transition flex items-start space-x-6">
                                <div><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="lucide lucide-warehouse h-12 w-12 text-blue-600">
                                        <path
                                            d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35Z">
                                        </path>
                                        <path d="M6 18h12"></path>
                                        <path d="M6 14h12"></path>
                                        <rect width="12" height="12" x="6" y="10"></rect>
                                    </svg></div>
                                <div>
                                    <h3 class="text-xl font-semibold mb-2 text-navy-800">Warehouse Operations</h3>
                                    <p class="text-gray-600">Efficient warehouse management integrated with your
                                        transportation operations.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
            <footer class="bg-navy-900 text-white py-12">
                <div class="max-w-7xl mx-auto px-6">
                    <div class="grid md:grid-cols-4 gap-8">
                        <div>
                            <div class="flex items-center space-x-2 mb-6"><svg xmlns="http://www.w3.org/2000/svg"
                                    width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-truck h-8 w-8 text-blue-400">
                                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path>
                                    <path d="M15 18H9"></path>
                                    <path
                                        d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14">
                                    </path>
                                    <circle cx="17" cy="18" r="2"></circle>
                                    <circle cx="7" cy="18" r="2"></circle>
                                </svg><span class="text-xl font-bold">EF Services</span></div>
                            <p class="text-gray-400">Leading provider of fleet management and transportation solutions.
                            </p>
                        </div>
                        <div>
                            <h3 class="font-bold mb-4">Services</h3>
                            <ul class="space-y-2 text-gray-400">
                                <li>Fleet Management</li>
                                <li>Driver Solutions</li>
                                <li>Route Optimization</li>
                                <li>Analytics</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-bold mb-4">Company</h3>
                            <ul class="space-y-2 text-gray-400">
                                <li>About Us</li>
                                <li>Careers</li>
                                <li>Contact</li>
                                <li>Blog</li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="font-bold mb-4">Contact</h3>
                            <ul class="space-y-2 text-gray-400">
                                <li>support@efservices.com</li>
                                <li>+1 (555) 123-4567</li>
                                <li>123 Fleet Street</li>
                                <li>New York, NY 10001</li>
                            </ul>
                        </div>
                    </div>
                    <div class="border-t border-gray-800 mt-12 pt-8 text-center text-gray-400">
                        <p>© 2024 EF Services. All rights reserved.</p>
                    </div>
                </div>
            </footer>
        </div>
    </div> --}}

    <header class="header_nav  text-white">
        <!-- Top Bar -->
        <div class="hidden lg:flex justify-end items-center px-6 py-2 bg-[#0a1922] text-sm space-x-8">
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                801 Magnolia St Kermit, TX 79745
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Track Your Order
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Mon - Sat : 8 am - 5 pm
            </div>
            <div class="flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
                (432) 853-5493
            </div>
        </div>

        <!-- Main Navigation -->
        <div class="mx-auto px-4 py-4 main_navigation">
            <div class="flex justify-between items-center ">
                <!-- Logo -->
                <div class="flex items-center">
                    <img class="w-11" src="{{ asset('respaldo/img/logo_efservices_logo.png') }}" alt="">
                    <span class="text-2xl font-bold">EFTCS</span>
                </div>

                <!-- Desktop Menu -->
                <nav class="desktop-menu hidden lg:flex items-center space-x-6 ">
                    <div class="dropdown">
                        <a href="#" class="text-lg hover:text-[#08459f] nav-plus">Home</a>
                    </div>
                    <div class="dropdown">
                        <a href="#features" class="text-lg hover:text-[#08459f] nav-plus">Features</a>
                    </div>
                    <div class="dropdown">
                        <a href="#pricing" class="text-lg hover:text-[#08459f] nav-plus">Pricing</a>
                    </div>
                    <div class="dropdown">
                        <a href="#testimonials" class="text-lg hover:text-[#08459f] nav-plus">Testimonials</a>
                    </div>
                    {{-- <div class="dropdown">
                        <a href="#" class="font-medium hover:text-[#08459f] nav-plus">Blogs</a>
                        <div class="dropdown-content">
                            <a href="#">Blog Grid</a>
                            <a href="#">Blog List</a>
                            <a href="#">Blog Details</a>
                        </div>
                    </div> --}}
                    <a href="#contact" class="text-lg hover:text-[#08459f]">Contact</a>
                </nav>

                <!-- CTA Button -->
                <div class="hidden lg:block">
                    <a href="#" class="button_quote_header">
                        <div class="wdt-buton-text">
                            <span>
                                Get A Quote !
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="lg:hidden">
                    <button id="menu-toggle" class="menu-button w-8 h-8 flex flex-col justify-center items-center">
                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" id="menu" class="icon glyph"
                            fill="#ffffff" stroke="#ffffff">
                            <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                            <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                            <g id="SVGRepo_iconCarrier">
                                <path d="M21,19H9a1,1,0,0,1,0-2H21a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path>
                                <path d="M21,13H3a1,1,0,0,1,0-2H21a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path>
                                <path d="M15,7H3A1,1,0,0,1,3,5H15a1,1,0,0,1,0,2Z" style="fill:#ffffff"></path>
                            </g>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="mobile-menu lg:hidden fixed top-0 right-0 w-4/5 h-full bg-[#0f2231] z-50 p-6">
            <div class="flex justify-end mb-8">
                <button id="close-menu" class="text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Info -->
            <div class="mb-8 space-y-4 text-sm">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    801 Magnolia St Kermit, TX 79745
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Track Your Order
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Mon - Sat : 8 am - 5 pm
                </div>
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    0-123-456-789
                </div>
            </div>

            <!-- Mobile Navigation -->
            <nav class="space-y-4">
                <div class="py-2 border-b border-gray-700">
                    <a href="#" class="block font-medium text-white hover:text-[#08459f]">Home</a>
                </div>
                <div class="py-2 border-b border-gray-700">
                    <a href="#features" class="block font-medium text-white hover:text-[#08459f]">Features</a>
                </div>
                <div class="py-2 border-b border-gray-700">
                    <a href="#pricing" class="block font-medium text-white hover:text-[#08459f]">Pricing</a>
                </div>
                <div class="py-2 border-b border-gray-700">
                    <a href="#testimonials" class="block font-medium text-white hover:text-[#08459f]">Testimonials</a>
                </div>
                <div class="py-2 border-b border-gray-700">
                    <a href="#contact" class="block font-medium text-white hover:text-[#08459f]">Contact</a>
                </div>
            </nav>

            <!-- Mobile CTA -->
            <div class="mt-8">
                <a href="#"
                    class="block text-center bg-[#08459f] text-white py-3 px-5 rounded-md font-medium hover:bg-opacity-90 transition duration-300">Get
                    A Quote !</a>
            </div>
        </div>
    </header>


    <!-- Hero Slider -->
    <div class="slider-container">
        <div class="swiper">
            <div class="swiper-wrapper">
                <!-- Slide 1 -->
                <div class="swiper-slide">
                    <div class="overlay"></div>
                    <img src="{{ asset('img/sliders/slider_1.webp') }}" alt="Package Delivery">
                    <div class="slide-content">
                        <div class="space-y-8">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                                Smart Transportation Compliance
                            </span>

                            <h2 class="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl text-white">
                                Stay Ready for Audits with
                                <span class="relative inline-block">
                                    With
                                    <span class="absolute bottom-2 left-0 w-full h-2 bg-blue-200/70"></span>
                                </span>
                                EfServiceTCS
                            </h2>

                            <p class="text-xl text-gray-200 max-w-[600px]">
                                Our platform helps transport companies and drivers stay organized, compliant, and
                                stress-free. Manage permits, inspections, driver documents, and tax records—all in one
                                place.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  bg-blue-700 px-6 py-3 text-base font-medium text-white shadow-lg hover:bg-blue-800 hover:shadow-xl transition-all">
                                    Request a Demo
                                </button>
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  border border-blue-900 px-6 py-3 text-base font-medium text-white hover:bg-blue-800 transition-colors">
                                    Learn More
                                </button>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Slide 2 -->
                <div class="swiper-slide">
                    <div class="overlay"></div>
                    <img src="{{ asset('img/sliders/slider_2.webp') }}" alt="Air Freight">
                    <div class="slide-content">
                        <div class="space-y-8">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                                Effortless Operations, Full Compliance
                            </span>

                            <h2 class="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl text-white">
                                Organize Your Fleet the Smart Way
                            </h2>

                            <p class="text-xl text-gray-200 max-w-[600px]">
                                With EfServiceTC, streamline your administrative tasks, keep your drivers' files
                                updated, and ensure your fleet meets all legal requirements. Be audit-ready at any time.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  bg-blue-700 px-6 py-3 text-base font-medium text-white shadow-lg hover:bg-blue-800 hover:shadow-xl transition-all">
                                    Request a Demo
                                </button>
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  border border-blue-900 px-6 py-3 text-base font-medium text-white hover:bg-blue-800 transition-colors">
                                    Learn More
                                </button>
                            </div>


                        </div>
                    </div>
                </div>

                <!-- Slide 3 -->
                <div class="swiper-slide">
                    <div class="overlay"></div>
                    <img src="{{ asset('img/sliders/slider_3.webp') }}" alt="Transport Logistics">
                    <div class="slide-content">
                        <div class="space-y-8">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                                Your All-in-One Transportation Assistant
                            </span>

                            <h2 class="text-4xl font-bold tracking-tight sm:text-5xl md:text-6xl text-white">
                                Support for Drivers, Carriers, and Administrators
                            </h2>

                            <p class="text-xl text-gray-200 max-w-[600px]">
                                From document tracking to compliance alerts, EfServiceTC empowers transportation
                                businesses with tools to control operations and avoid costly penalties.
                            </p>

                            <div class="flex flex-col sm:flex-row gap-4">
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  bg-blue-700 px-6 py-3 text-base font-medium text-white shadow-lg hover:bg-blue-800 hover:shadow-xl transition-all">
                                    Start Now
                                </button>
                                <button
                                    class="inline-flex items-center justify-center whitespace-nowrap  border border-blue-900 px-6 py-3 text-base font-medium text-white hover:bg-blue-800 transition-colors">
                                    See How It Works
                                </button>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Boxes -->
        <div class="slider-navigation">
            <div class="nav-box active" data-index="0">
                <div class="nav-box-number">01</div>
                <div class="nav-box-title">Land Transport</div>
            </div>
            <div class="nav-box" data-index="1">
                <div class="nav-box-number">02</div>
                <div class="nav-box-title">Air Freight</div>
            </div>
            <div class="nav-box" data-index="2">
                <div class="nav-box-number">03</div>
                <div class="nav-box-title">Transport Logistics</div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <section class="w-full py-20 md:py-32" id="features">
        <div class="px-4 md:px-6 mx-auto md:w-[90%]">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-12">
                <span
                    class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                    Features
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-blue-900 max-w-3xl">
                    Complete compliance and driver management solution
                </h2>
                <p class="max-w-[800px] text-xl text-gray-600">
                    Our specialized platform helps carriers manage regulatory compliance and streamline driver
                    operations in line with US regulations.
                </p>
            </div>

            <div class="grid gap-12 mt-16">
                <!-- Feature 1 -->
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div class="order-2 md:order-1">
                        <div class="space-y-6">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                                Driver Management
                            </span>
                            <h3 class="text-2xl md:text-3xl font-bold text-blue-900">
                                Streamlined driver recruitment and compliance
                            </h3>
                            <p class="text-gray-600 text-lg">
                                Simplify your driver onboarding process with automated document verification and
                                regulatory compliance checks.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Automated background checks and verification</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Document management in compliance with DOT
                                        regulations</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Driver qualification file maintenance and alerts</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">License and certification expiration tracking</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Digital driver application form with e-signature
                                        capability</span>
                                </li>
                            </ul>
                            <div>
                                <a href="#" class="inline-flex items-center text-blue-900 font-medium">
                                    Learn more <i data-lucide="chevron-right" class="h-4 w-4 ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="order-1 md:order-2">
                        <div class="relative">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-blue-900 rounded-2xl blur-lg opacity-20">
                            </div>
                            <div
                                class="relative bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100">
                                <img src="{{ asset('img/images/gps.jpg') }}" alt="Driver Management"
                                    class="w-full h-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="grid md:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="relative">
                            <div
                                class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-blue-900 rounded-2xl blur-lg opacity-20">
                            </div>
                            <div
                                class="relative bg-white rounded-2xl shadow-xl overflow-hidden border border-blue-100">
                                <img src="{{ asset('img/images/data.jpg') }}" alt="Compliance Reports"
                                    class="w-full h-auto">
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="space-y-6">
                            <span
                                class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                                Compliance & Reporting
                            </span>
                            <h3 class="text-2xl md:text-3xl font-bold text-blue-900">
                                Audit-ready compliance management
                            </h3>
                            <p class="text-gray-600 text-lg">
                                Stay prepared for DOT audits with comprehensive compliance reports and documentation
                                tracking.
                            </p>
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Real-time compliance status monitoring</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Automated audit preparation reports</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Regulatory updates and compliance requirements
                                        tracking</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">Document retention management per DOT
                                        requirements</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center mt-0.5">
                                        <i data-lucide="check" class="h-4 w-4 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700">FMCSA safety rating monitoring and improvement
                                        tools</span>
                                </li>
                            </ul>
                            <div>
                                <a href="#" class="inline-flex items-center text-blue-900 font-medium">
                                    Learn more <i data-lucide="chevron-right" class="h-4 w-4 ml-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Features Cards -->
            <div class="mt-24 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="bg-white border border-blue-100 rounded-lg shadow-lg hover:shadow-xl transition-all p-6">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="clock" class="h-6 w-6 text-blue-900"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Hours of Service Tracking</h3>
                    <p class="text-gray-600 mb-4">
                        Monitor driver hours and breaks to ensure compliance with FMCSA regulations.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">ELD/HOS compliance monitoring</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Break and rest period tracking</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Violation risk alerts and prevention</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Driver duty status logs and history</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Automated RODS (Record of Duty Status) reporting</span>
                        </li>
                    </ul>
                </div>

                <!-- Card 2 -->
                <div class="bg-white border border-blue-100 rounded-lg shadow-lg hover:shadow-xl transition-all p-6">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="truck" class="h-6 w-6 text-blue-900"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Vehicle Management</h3>
                    <p class="text-gray-600 mb-4">
                        Comprehensive tracking of your fleet maintenance and inspection schedules.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Preventive maintenance scheduling</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Vehicle inspection reports and history</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Service record management and alerts</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">DVIR (Driver Vehicle Inspection Report) system</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Registration and permit expiration tracking</span>
                        </li>
                    </ul>
                </div>

                <!-- Card 3 -->
                <div class="bg-white border border-blue-100 rounded-lg shadow-lg hover:shadow-xl transition-all p-6">
                    <div class="h-12 w-12 rounded-lg bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="route" class="h-6 w-6 text-blue-900"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-blue-900 mb-2">Route Management</h3>
                    <p class="text-gray-600 mb-4">
                        Track and optimize driver routes and stops for better operational efficiency.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Driver route assignment and tracking</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Stop and rest location management</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Performance metrics and efficiency reporting</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Historical route data analysis</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <div
                                class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                            </div>
                            <span class="text-gray-700 text-sm">Driver behavior and safety monitoring</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section class="w-full py-20 md:py-32 bg-gradient-to-b from-white to-blue-50" id="pricing">
        <div class="container px-4 md:px-6 mx-auto">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-12">
                <span
                    class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                    Pricing
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-blue-900 max-w-3xl">
                    Plans tailored to your needs
                </h2>
                <p class="max-w-[800px] text-xl text-gray-600">
                    Choose the plan that best fits your fleet size and specific requirements.
                </p>
            </div>

            <div class="flex justify-center mb-12">
                <div class="inline-flex rounded-md bg-blue-100 p-1 w-[400px]">
                    <button class="bg-blue-900 text-white rounded-md px-4 py-2 w-1/2">Monthly</button>
                    <button class="text-blue-900 rounded-md px-4 py-2 w-1/2">Annual (20% off)</button>
                </div>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Beginner Plan -->
                <div class="relative group">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-b from-blue-100 to-blue-50 rounded-sm blur opacity-75 group-hover:opacity-100 transition duration-300">
                    </div>
                    <div class="relative bg-white border border-blue-100 rounded-sm shadow-lg overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-blue-900">Beginner</h3>
                            <p class="text-gray-600">For small fleets</p>
                            <div class="mt-4 flex items-baseline text-blue-900">
                                <span class="text-5xl font-extrabold tracking-tight">$400</span>
                                <span class="ml-1 text-xl font-semibold">/month</span>
                            </div>
                            <p class="text-sm text-gray-500">USD, billed monthly</p>
                        </div>
                        <div class="px-6 pb-6">
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">1 platform user access</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">5 drivers management</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">5 vehicles in the system</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">Compliance reporting</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">Email support</span>
                                </li>
                            </ul>
                        </div>
                        <div class="px-6 pb-6">
                            <button
                                class="w-full bg-blue-900 hover:bg-blue-800 text-white py-3 rounded-sm transition-colors">
                                Get Started
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Intermediate Plan -->
                <div class="relative group mt-[-20px]">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-b from-blue-600 to-blue-900 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-300">
                    </div>
                    <div class="relative bg-white border border-blue-900 rounded-2xl shadow-2xl overflow-hidden">
                        <div
                            class="absolute top-0 left-0 right-0 bg-blue-900 text-white text-center py-2 text-sm font-medium">
                            Most popular
                        </div>
                        <div class="p-6 pt-12">
                            <h3 class="text-2xl font-bold text-blue-900">Intermediate</h3>
                            <p class="text-gray-600">For medium fleets</p>
                            <div class="mt-4 flex items-baseline text-blue-900">
                                <span class="text-5xl font-extrabold tracking-tight">$600</span>
                                <span class="ml-1 text-xl font-semibold">/month</span>
                            </div>
                            <p class="text-sm text-gray-500">USD, billed monthly</p>
                        </div>
                        <div class="px-6 pb-6">
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">2 platform user access</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">10 drivers management</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">10 vehicles in the system</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">Advanced compliance tools</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">Priority email & phone support</span>
                                </li>
                            </ul>
                        </div>
                        <div class="px-6 pb-6">
                            <button
                                class="w-full bg-blue-900 hover:bg-blue-800 text-white py-3 rounded-sm transition-colors">
                                Get Started
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="relative group">
                    <div
                        class="absolute -inset-0.5 bg-gradient-to-b from-blue-100 to-blue-50 rounded-3xl blur opacity-75 group-hover:opacity-100 transition duration-300">
                    </div>
                    <div class="relative bg-white border border-blue-100 rounded-2xl shadow-lg overflow-hidden">
                        <div class="p-6">
                            <h3 class="text-2xl font-bold text-blue-900">Pro</h3>
                            <p class="text-gray-600">For growing fleets</p>
                            <div class="mt-4 flex items-baseline text-blue-900">
                                <span class="text-5xl font-extrabold tracking-tight">$800</span>
                                <span class="ml-1 text-xl font-semibold">/month</span>
                            </div>
                            <p class="text-sm text-gray-500">USD, billed monthly</p>
                        </div>
                        <div class="px-6 pb-6">
                            <ul class="space-y-3">
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">3 platform user access</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">15 drivers management</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">15 vehicles in the system</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">Advanced document management</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <div
                                        class="h-5 w-5 rounded-full bg-blue-100 flex items-center justify-center mt-0.5 flex-shrink-0">
                                        <i data-lucide="check" class="h-3 w-3 text-blue-900"></i>
                                    </div>
                                    <span class="text-gray-700 text-sm">24/7 priority support</span>
                                </li>
                            </ul>
                        </div>
                        <div class="px-6 pb-6">
                            <button
                                class="w-full bg-blue-900 hover:bg-blue-800 text-white py-3 rounded-sm transition-colors">
                                Get Started
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-16 bg-blue-50 rounded-2xl p-8 border border-blue-100">
                <div class="flex flex-col md:flex-row gap-8 items-center">
                    <div class="md:w-2/3">
                        <h3 class="text-2xl font-bold text-blue-900 mb-4">Need a custom solution?</h3>
                        <p class="text-gray-600">
                            Contact us to discuss your specific needs and create a tailored plan for your company.
                        </p>
                    </div>
                    <div class="md:w-1/3 flex justify-center">
                        <button
                            class="bg-blue-900 hover:bg-blue-800 text-white py-3 px-8 rounded-sm text-base transition-colors">
                            Contact Sales
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="w-full py-20 md:py-32" id="testimonials">
        <div class="container px-4 md:px-6 mx-auto">
            <div class="flex flex-col items-center justify-center space-y-4 text-center mb-12">
                <span
                    class="inline-flex items-center rounded-md bg-blue-100 px-3 py-1.5 text-sm font-medium text-blue-900">
                    Testimonials
                </span>
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight text-blue-900 max-w-3xl">
                    What our clients say
                </h2>
                <p class="max-w-[800px] text-xl text-gray-600">
                    Transportation companies across the country trust ef Services to optimize their operations.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Testimonial 1 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-blue-100 relative">
                    <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i data-lucide="star" class="h-6 w-6 text-blue-900"></i>
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <p class="text-gray-700 italic">
                                "Since implementing ef Services, we have reduced our operating costs by 20% and
                                significantly improved our fleet efficiency. The technical support is exceptional."
                            </p>
                        </div>
                        <div class="mt-auto pt-6 border-t border-blue-100">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full overflow-hidden">
                                    <img src="https://via.placeholder.com/60" alt="Avatar"
                                        class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-semibold text-blue-900">Carlos Rodríguez</h4>
                                    <p class="text-sm text-gray-500">Operations Director, Transportes XYZ</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-blue-100 relative">
                    <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i data-lucide="star" class="h-6 w-6 text-blue-900"></i>
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <p class="text-gray-700 italic">
                                "The platform is intuitive and easy to use. The support team is excellent and always
                                available to help us with any questions. It has transformed our operation."
                            </p>
                        </div>
                        <div class="mt-auto pt-6 border-t border-blue-100">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full overflow-hidden">
                                    <img src="https://via.placeholder.com/60" alt="Avatar"
                                        class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-semibold text-blue-900">María González</h4>
                                    <p class="text-sm text-gray-500">CEO, Fast Logistics</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="bg-white p-8 rounded-2xl shadow-lg border border-blue-100 relative">
                    <div class="absolute top-0 right-0 transform translate-x-1/4 -translate-y-1/4">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i data-lucide="star" class="h-6 w-6 text-blue-900"></i>
                        </div>
                    </div>
                    <div class="flex flex-col h-full">
                        <div class="mb-6">
                            <p class="text-gray-700 italic">
                                "Route optimization has allowed us to save fuel and time. Our customers are more
                                satisfied with more accurate delivery times and real-time visibility."
                            </p>
                        </div>
                        <div class="mt-auto pt-6 border-t border-blue-100">
                            <div class="flex items-center gap-4">
                                <div class="h-12 w-12 rounded-full overflow-hidden">
                                    <img src="https://via.placeholder.com/60" alt="Avatar"
                                        class="h-full w-full object-cover">
                                </div>
                                <div>
                                    <h4 class="font-semibold text-blue-900">Javier López</h4>
                                    <p class="text-sm text-gray-500">Fleet Manager, National Transport</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="mt-16 grid md:grid-cols-4 gap-8">
                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex flex-col items-center text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="truck" class="h-8 w-8 text-blue-900"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-blue-900 mb-2">500+</h3>
                    <p class="text-gray-600">Companies trust us</p>
                </div>

                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex flex-col items-center text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="map-pin" class="h-8 w-8 text-blue-900"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-blue-900 mb-2">15K+</h3>
                    <p class="text-gray-600">Vehicles monitored</p>
                </div>

                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex flex-col items-center text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="zap" class="h-8 w-8 text-blue-900"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-blue-900 mb-2">30%</h3>
                    <p class="text-gray-600">Average cost reduction</p>
                </div>

                <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 flex flex-col items-center text-center">
                    <div class="h-16 w-16 rounded-full bg-blue-100 flex items-center justify-center mb-4">
                        <i data-lucide="award" class="h-8 w-8 text-blue-900"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-blue-900 mb-2">99.9%</h3>
                    <p class="text-gray-600">Guaranteed uptime</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Contact Section -->
    <section class="w-full py-20 md:py-32 bg-blue-900 text-white" id="contact">
        <div class="container px-4 md:px-6 mx-auto">
            <div class="grid gap-8 lg:grid-cols-2 lg:gap-12 items-center">
                <div class="space-y-6">
                    <span
                        class="inline-flex items-center rounded-md bg-white/20 px-3 py-1.5 text-sm font-medium text-white">
                        Contact
                    </span>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold tracking-tight">
                        Ready to optimize your fleet?
                    </h2>
                    <p class="text-xl text-blue-100 max-w-[600px]">
                        Contact us today for a personalized demo and discover how ef Services can transform your
                        transportation operations.
                    </p>

                    <div class="grid gap-4 sm:grid-cols-2 max-w-[600px]">
                        <div class="bg-blue-800/50 p-6 rounded-xl">
                            <i data-lucide="users" class="h-8 w-8 mb-4 text-blue-200"></i>
                            <h3 class="text-xl font-semibold mb-2">Dedicated Support</h3>
                            <p class="text-blue-100">Specialized support team available 24/7 to assist you.</p>
                        </div>

                        <div class="bg-blue-800/50 p-6 rounded-xl">
                            <i data-lucide="zap" class="h-8 w-8 mb-4 text-blue-200"></i>
                            <h3 class="text-xl font-semibold mb-2">Fast Implementation</h3>
                            <p class="text-blue-100">Deployment in less than 48 hours for your entire fleet.</p>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 pt-4">
                        <button
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-xl bg-white px-8 py-6 text-base font-medium text-blue-900 shadow-lg hover:bg-blue-50 hover:shadow-xl transition-all">
                            Request Demo
                            <i data-lucide="arrow-right" class="ml-2 h-5 w-5"></i>
                        </button>
                        <button
                            class="inline-flex items-center justify-center whitespace-nowrap rounded-xl border border-white px-8 py-6 text-base font-medium text-white hover:bg-blue-800 transition-colors">
                            Contact Sales
                        </button>
                    </div>
                </div>
                <div class="relative">
                    <div
                        class="absolute -inset-1 bg-gradient-to-r from-blue-400 to-blue-600 rounded-2xl blur-xl opacity-50">
                    </div>
                    <div
                        class="relative bg-blue-800 rounded-2xl shadow-2xl overflow-hidden border border-blue-700 p-8">
                        <h3 class="text-2xl font-bold mb-6">Request Information</h3>
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">First Name</label>
                                    <input type="text"
                                        class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white placeholder:text-blue-300"
                                        placeholder="Your first name">
                                </div>
                                <div class="space-y-2">
                                    <label class="text-sm font-medium">Last Name</label>
                                    <input type="text"
                                        class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white placeholder:text-blue-300"
                                        placeholder="Your last name">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Email</label>
                                <input type="email"
                                    class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white placeholder:text-blue-300"
                                    placeholder="email@company.com">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Company</label>
                                <input type="text"
                                    class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white placeholder:text-blue-300"
                                    placeholder="Your company name">
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Fleet Size</label>
                                <select
                                    class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white">
                                    <option>1-10 vehicles</option>
                                    <option>11-30 vehicles</option>
                                    <option>31-100 vehicles</option>
                                    <option>More than 100 vehicles</option>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-medium">Message</label>
                                <textarea
                                    class="w-full px-4 py-3 rounded-lg bg-blue-700/50 border border-blue-600 text-white placeholder:text-blue-300 min-h-[100px]"
                                    placeholder="How can we help you?"></textarea>
                            </div>
                            <button
                                class="w-full bg-white text-blue-900 hover:bg-blue-50 py-6 rounded-xl transition-colors">
                                Submit Request
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-guest-layout>
