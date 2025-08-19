<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />




    <!-- Alpine.js CDN - Load before any x-data -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>

<body>
    <div class="font-sans text-gray-900 antialiased">
        @hasSection('content')
            @yield('content')
        @else
            {{ $slot ?? '' }}
        @endif
    </div>

    @livewireScripts

    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Ensure Alpine.js is available -->
    <script>
        // Wait for Alpine.js to be available before initializing components
        document.addEventListener('DOMContentLoaded', function() {
            // Check if Alpine is available, if not wait for it
            if (typeof window.Alpine === 'undefined') {
                const checkAlpine = setInterval(() => {
                    if (typeof window.Alpine !== 'undefined') {
                        clearInterval(checkAlpine);
                        console.log('Alpine.js is now available');
                    }
                }, 50);
            }
        });
    </script>
    
    <script>
        // Mobile menu functionality
        const menuToggle = document.getElementById('menu-toggle');
        const closeMenu = document.getElementById('close-menu');
        const mobileMenu = document.getElementById('mobile-menu');

        if (menuToggle && mobileMenu) {
            menuToggle.addEventListener('click', () => {
                mobileMenu.classList.add('active');
            });
        }

        if (closeMenu && mobileMenu) {
            closeMenu.addEventListener('click', () => {
                mobileMenu.classList.remove('active');
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
            // Initialize Swiper only if available
            if (typeof Swiper !== 'undefined' && document.querySelector('.swiper')) {
                const swiper = new Swiper('.swiper', {
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                speed: 1000,
                loop: true,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
            });

            // Navigation boxes control
            const navBoxes = document.querySelectorAll('.nav-box');

            navBoxes.forEach(box => {
                box.addEventListener('click', function() {
                    const index = this.getAttribute('data-index');

                    // Remove active class from all boxes
                    navBoxes.forEach(b => b.classList.remove('active'));

                    // Add active class to clicked box
                    this.classList.add('active');

                    // Change slide
                    swiper.slideTo(parseInt(index) + 1);
                });
            });

                // Update active nav box on slide change
                swiper.on('slideChange', function() {
                    const realIndex = swiper.realIndex;

                    navBoxes.forEach((box, i) => {
                        if (i === realIndex) {
                            box.classList.add('active');
                        } else {
                            box.classList.remove('active');
                        }
                    });
                });
            }

        });
    </script>
</body>

</html>
