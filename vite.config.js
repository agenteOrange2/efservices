import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',                
                'resources/js/components/user/image-preview.js',
                'resources/js/components/base/dropzone-file-upload.js',
                'resources/js/pages/users/user-delete.js',                               
                'resources/css/app.css',
                'resources/css/vendors/tippy.css',
                'resources/css/vendors/tom-select.css',
                'resources/css/vendors/simplebar.css',
                'resources/css/vendors/zoom-vanilla.css',
                'resources/css/vendors/full-calendar.css',
                'resources/css/themes/raze.css',
                'resources/css/vendors/dropzone.css',
                'resources/css/vendors/toastify.css',
                'resources/css/vendors/leaflet.css',
                'resources/css/vendors/ckeditor.css',
                'resources/css/vendors/highlight.css',
                'resources/css/vendors/tabulator.css',                
                'resources/js/vendors/ckeditor/classic.js',
                'resources/js/components/base/classic-editor.js',                
                'resources/js/themes/raze.js',
                'resources/js/components/base/theme-color.js',
                'resources/js/components/base/leaflet-map-loader.js',


                //plugins
                'resources/js/vendors/calendar/plugins/interaction.js',
                'resources/js/vendors/calendar/plugins/day-grid.js',
                'resources/js/vendors/calendar/plugins/time-grid.js',
                'resources/js/vendors/calendar/plugins/list.js',

        
                // Archivos de componentes base
                'resources/js/components/base/litepicker.js',
                'resources/js/components/base/tiny-slider.js',
                'resources/js/components/base/tippy.js',
                'resources/js/components/base/dropzone.js',
                'resources/js/components/base/highlight.js',
                'resources/js/components/base/lucide.js',
                'resources/js/components/base/tom-select.js',                                
                'resources/js/components/base/source.js',
                'resources/js/components/base/tippy-content.js',
                'resources/js/components/report-donut-chart-3.js',
                'resources/js/components/report-donut-chart-4.js',
                'resources/js/components/report-donut-chart-5.js',
                'resources/js/components/report-donut-chart-6.js',
                'resources/js/components/report-donut-chart-7.js',
                'resources/js/components/simple-line-chart-1.js',
                'resources/js/components/report-line-chart.js',
                'resources/js/components/report-line-chart-1.js',
                'resources/js/components/report-line-chart-2.js',
                'resources/js/components/report-line-chart-3.js',
                'resources/js/components/report-line-chart-4.js',
                'resources/js/components/report-bar-chart-6.js',
                'resources/js/components/report-radar-chart.js',
                'resources/js/components/report-bar-chart.js',                
                'resources/js/components/report-bar-chart-3.js',
                'resources/js/components/report-bar-chart-4.js',                
                'resources/js/components/report-bar-chart-5.js',
                'resources/js/components/report-bar-chart-6.js',                
                'resources/js/components/base/calendar/draggable.js',
                'resources/js/components/base/calendar/calendar.js',
                'resources/js/components/quick-search.js',
                'resources/js/components/base/preview-component.js',
                'resources/js/components/vertical-bar-chart.js',
                'resources/js/components/horizontal-bar-chart.js',
                'resources/js/components/donut-chart.js',
                'resources/js/components/stacked-bar-chart.js',
                'resources/js/components/line-chart.js',
                'resources/js/components/pie-chart.js',
                
                // Archivos de vendors específicos
                'node_modules/jquery/dist/jquery.min.js',
                'node_modules/select2/dist/js/select2.full.min.js',
                'resources/js/vendors/dom.js',   
                'resources/js/vendors/tailwind-merge.js',
                'resources/js/vendors/tab.js',
                'resources/js/vendors/lodash.js',
                'resources/js/vendors/leaflet-map.js',
                'resources/js/vendors/axios.js',
                'resources/js/vendors/simplebar.js',
                'resources/js/vendors/dayjs.js',
                'resources/js/vendors/transition.js',
                'resources/js/vendors/popper.js',
                'resources/js/vendors/dropdown.js',               
                'resources/js/vendors/modal.js', 
                'resources/js/vendors/alert.js',
                'resources/js/vendors/xlsx.js',
                'resources/js/vendors/highlight.js',
                'resources/js/vendors/toastify.js',
                'resources/js/vendors/accordion.js',
                'resources/js/vendors/pristine.js',

                //Pages
                'resources/js/pages/tabulator.js',
                'resources/js/pages/modal.js',
                'resources/js/pages/slideover.js',
                'resources/js/pages/validation.js',

                'resources/js/modules/notification.js', // Módulo reutilizable
                'resources/js/pages/notification.js',  // Script para la página
                
                // Archivo utilitario de colores
                'resources/js/utils/helper.js',
                'resources/js/utils/colors.js', 
        
                // Otros archivos CSS necesarios
                'resources/css/vendors/litepicker.css',
                'resources/css/vendors/tiny-slider.css',
                'node_modules/select2/dist/css/select2.min.css',
                'resources/css/vendors/select2-custom.css',
                // Agrega más archivos CSS si es necesario


                'resources/js/vendors/chartjs.js',
                'resources/js/vendors/lucide.js',
                'resources/js/vendors/litepicker.js',
                'resources/js/vendors/tippy.js',
              ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            'tailwind-config': path.resolve(__dirname, 'tailwind.config.js'),
            $: "jquery", // Resolver jQuery correctamente
            Pristine: "pristinejs", // Resolver Pristine correctamente
        }
    },
});