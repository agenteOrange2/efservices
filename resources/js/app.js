import Pristine from "pristinejs";
import Toastify from 'toastify-js';
import TomSelect from 'tom-select';
import Pikaday from 'pikaday';
import moment from 'moment';
import 'pikaday/css/pikaday.css';

// Alpine.js se carga desde CDN en el layout guest
// Solo configurar si no está ya disponible
if (!window.Alpine) {
    import('alpinejs').then(Alpine => {
        window.Alpine = Alpine.default;
        Alpine.default.start();
    });
}

// Inicializar Livewire si está disponible
if (typeof Livewire !== 'undefined') {
    Livewire.start();
}

// Importar jQuery explícitamente
import $ from 'jquery';

// Exponer jQuery, Pristine, Pikaday y moment globalmente
window.$ = window.jQuery = $;
window.Pristine = Pristine;
window.Pikaday = Pikaday;
window.moment = moment;

// Validación en consola
window.Toastify = Toastify;
if (typeof $ === "undefined" || typeof Pristine === "undefined") {
  console.error("jQuery o Pristine no están disponibles.");
} else {
  import("@left4code/tw-starter/dist/js/svg-loader");
  import("@left4code/tw-starter/dist/js/accordion");
  import("@left4code/tw-starter/dist/js/alert");
  import("@left4code/tw-starter/dist/js/dropdown");
  import("@left4code/tw-starter/dist/js/modal");
  import("@left4code/tw-starter/dist/js/tab");
}

// Otros scripts
try {
  import("./vendors/chartjs");
  import("./vendors/tiny-slider");
  import("./vendors/tippy");
  import("./vendors/litepicker");
  import("./vendors/tom-select");
  import("./vendors/dropzone");
  import("./pages/notification");
  import("./vendors/tabulator");
  import("./vendors/lucide");
  import("./vendors/calendar/calendar");
  import("./vendors/select2");
  import("./vendors/calendar/plugins/interaction.js");
  import("./vendors/calendar/plugins/day-grid.js");
  import("./vendors/calendar/plugins/time-grid.js");
  import("./vendors/calendar/plugins/list.js");
  import("./ckeditor-classic");
} catch (error) {
  console.warn("Error al cargar librerías opcionales:", error);
}