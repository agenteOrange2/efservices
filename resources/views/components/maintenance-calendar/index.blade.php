<div
    data-tw-merge
    {{ $attributes->class(['full-calendar'])->merge($attributes->whereDoesntStartWith('class')->getAttributes()) }}>
    <div></div>
</div>

@pushOnce('styles')
    @vite('resources/css/vendors/full-calendar.css')
@endPushOnce

@pushOnce('vendors')
    @vite('resources/js/vendors/calendar/plugins/interaction.js')
    @vite('resources/js/vendors/calendar/plugins/day-grid.js')
    @vite('resources/js/vendors/calendar/plugins/time-grid.js')
    @vite('resources/js/vendors/calendar/plugins/list.js')
@endPushOnce

@pushOnce('scripts')
    {{-- Cargar nuestro script personalizado para mantenimiento en lugar del calendar.js --}}
    <script src="{{ asset('build/assets/maintenance-calendar.js') }}"></script>
@endPushOnce
