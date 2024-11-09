// Carga de archivos estáticos
import.meta.glob(["../images/**"]);
import Alpine from 'alpinejs';


window.Alpine = Alpine;
Alpine.start();

/*
|-------------------------------------------------------------------------- 
| Tailwise Built-in Components 
|-------------------------------------------------------------------------- 
*/
import "@left4code/tw-starter/dist/js/svg-loader";
import "@left4code/tw-starter/dist/js/accordion";
import "@left4code/tw-starter/dist/js/alert";
import "@left4code/tw-starter/dist/js/dropdown";
import "@left4code/tw-starter/dist/js/modal";
import "@left4code/tw-starter/dist/js/tab";

/*
|-------------------------------------------------------------------------- 
| 3rd Party Libraries 
|-------------------------------------------------------------------------- 
*/
try {
  import("./vendors/chartjs");
  import("./vendors/tiny-slider");
  import("./vendors/tippy");
  import("./vendors/litepicker"); 
  import("./vendors/tom-select");
  import("./vendors/dropzone");
  import("./vendors/pristine");
  import("./vendors/image-zoom");
  import("./pages/notification");
  import("./vendors/tabulator");
  import("./vendors/lucide");
  import("./vendors/calendar/calendar");
} catch (error) {
  console.warn("Asegúrate de que todas las librerías existan en `resources/js/`. Error:", error);
}

/*
|-------------------------------------------------------------------------- 
| Custom Components 
|-------------------------------------------------------------------------- 
*/

try {
  // import("./maps");
  // import("./chat");
  // import("./show-modal");
  // import("./show-slide-over");
  // import("./show-dropdown");
  // import("./search");
  // import("./copy-code");
  // import("./show-code");
  // import("./side-menu");
  // import("./mobile-menu");
  // import("./side-menu-tooltip");
  // import("./dark-mode-switcher");
  import("../js/components/base/lucide") 
  import("../js/components/base/tippy")   
  import("../js/components/base/litepicker")

} catch (error) {
  console.warn("Asegúrate de que todos los componentes personalizados existan en `resources/js/`. Error:", error);
}


