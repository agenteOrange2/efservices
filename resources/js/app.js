import Pristine from "pristinejs";
import Toastify from 'toastify-js';
import TomSelect from 'tom-select';

// Evitar inicialización duplicada
if (!window.Livewire) {
    Livewire.start(); // Inicializa Livewire primero
}

if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();   // Luego inicializa Alpine
}

// Importar jQuery explícitamente
import $ from 'jquery';

// Exponer jQuery y Pristine globalmente
window.$ = window.jQuery = $;
window.Pristine = Pristine;

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
} catch (error) {
  console.warn("Error al cargar librerías opcionales:", error);
}