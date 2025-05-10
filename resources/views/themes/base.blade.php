<!DOCTYPE html>
<html class="opacity-0" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!-- BEGIN: Head -->

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description"
        content="Kuiraweb admin is super flexible, powerful, clean & modern responsive tailwind admin template with unlimited possibilities.">
    <meta name="keywords"
        content="admin template, Kuiraweb Admin Template, dashboard template, flat admin template, responsive admin template, web app">
    <meta name="author" content="KUIRAWEB">    

    <link rel="mask-icon" href="{{asset('build/img/favicon_efservices.png')}}" color="#000000">
    <link rel="alternate icon" class="js-site-favicon" type="image/png" href="{{asset('build/img/favicon_efservices.png')}}">
    <link rel="icon" class="js-site-favicon" type="image/svg+xml" href="{{asset('build/img/favicon_efservices.png')}}" data-bse-href="{{asset('build/img/favicon_efservices.png')}}">
    {{-- <link href="https://unpkg.com/filepond/dist/filepond.min.css" rel="stylesheet"> --}}

    <title>@yield('title') | EF Services</title>
    {{-- @yield('head') --}}

    @stack('head')

    <!-- BEGIN: CSS Assets-->
    @stack('styles')
    <!-- END: CSS Assets-->

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles -->
    @livewireStyles
</head>
<!-- END: Head -->

<body>
    
    {{-- <x-theme-switcher /> --}}

    @yield('content')

    <!-- BEGIN: Vendor JS Assets-->
    @vite('resources/js/app.js')
    @vite('resources/js/vendors/dom.js')
    @vite('resources/js/vendors/tailwind-merge.js')
    @stack('vendors')
    <!-- END: Vendor JS Assets-->
    <!-- BEGIN: Pages, layouts, components JS Assets-->
    @vite('resources/js/components/base/theme-color.js')
    @stack('scripts')
    <!-- END: Pages, layouts, components JS Assets-->

    @livewireScripts    

    <script>
        // Definir __WS_TOKEN__ para evitar errores con el hot reload de Vite
        window.__WS_TOKEN__ = window.__WS_TOKEN__ || null;
    </script>
</body>

</html>
