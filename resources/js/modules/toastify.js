import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

export function createNotification({ node, duration = 3000, sticky = false }) {
    return Toastify({
        node: node.cloneNode(true),
        duration: sticky ? -1 : duration,
        newWindow: true,
        close: true,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
    }).showToast();
}

export function createDismissableNotification({ node }) {
    const toast = Toastify({
        node: node.cloneNode(true),
        duration: -1,
        newWindow: true,
        close: false,
        gravity: "top",
        position: "right",
        stopOnFocus: true,
    });
    const toastInstance = toast.showToast();

    // Agregar evento para cerrar manualmente
    toastInstance.toastElement
        .querySelector('[data-dismiss="notification"]')
        .addEventListener('click', () => toast.hideToast());

    return toastInstance;
}
