import Toastify from 'toastify-js';

/**
 * Mostrar notificación dinámica basada en el componente Blade.
 * @param {string} id - ID del nodo de notificación.
 * @param {boolean} [sticky=false] - Si la notificación debe persistir.
 * @param {number} [duration=3000] - Duración en milisegundos (si no es persistente).
 */
export function showNotification(id, sticky = false, duration = 3000) {
    const notification = document.getElementById(id);

    if (!notification) {
        console.error(`Notification with ID "${id}" not found.`);
        return;
    }

    const clonedNode = notification.cloneNode(true);
    clonedNode.classList.remove('hidden');

    Toastify({
        node: clonedNode,
        duration: sticky ? -1 : duration,
        newWindow: true,
        close: true,
        gravity: 'top',
        position: 'right',
        stopOnFocus: true,
    }).showToast();
}
