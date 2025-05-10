/*
(function () {
    "use strict";

    // Basic non sticky notification
    $("#basic-non-sticky-notification-toggle").on("click", function () {
        Toastify({
            node: $("#basic-non-sticky-notification-content")
                .clone()
                .removeClass("hidden")[0],
            duration: 3000,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();
    });

    // Basic sticky notification
    $("#basic-sticky-notification-toggle").on("click", function () {
        Toastify({
            node: $("#basic-non-sticky-notification-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();
    });

    // Success notification
    $("#success-notification-toggle").on("click", function () {
        Toastify({
            node: $("#success-notification-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();
    });

    // Notification with actions
    $("#notification-with-actions-toggle").on("click", function () {
        Toastify({
            node: $("#notification-with-actions-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();
    });

    // Notification with avatar
    $("#notification-with-avatar-toggle").on("click", function () {
        // Init toastify
        let avatarNotification = Toastify({
            node: $("#notification-with-avatar-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: false,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();

        // Close notification event
        $(avatarNotification.toastElement)
            .find('[data-dismiss="notification"]')
            .on("click", function () {
                avatarNotification.hideToast();
            });
    });

    // Notification with split buttons
    $("#notification-with-split-buttons-toggle").on("click", function () {
        // Init toastify
        let splitButtonsNotification = Toastify({
            node: $("#notification-with-split-buttons-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: false,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();

        // Close notification event
        $(splitButtonsNotification.toastElement)
            .find('[data-dismiss="notification"]')
            .on("click", function () {
                splitButtonsNotification.hideToast();
            });
    });

    // Notification with buttons below
    $("#notification-with-buttons-below-toggle").on("click", function () {
        // Init toastify
        Toastify({
            node: $("#notification-with-buttons-below-content")
                .clone()
                .removeClass("hidden")[0],
            duration: -1,
            newWindow: true,
            close: true,
            gravity: "top",
            position: "right",
            stopOnFocus: true,
        }).showToast();
    });
})();
*/

import Toastify from 'toastify-js';

document.addEventListener('DOMContentLoaded', () => {
  // Definir comportamientos de las notificaciones
  const createNotification = (node, { sticky = false, duration = 3000 } = {}) => {
    if (!node) return;
    Toastify({
      node: node.cloneNode(true),
      duration: sticky ? -1 : duration,
      newWindow: true,
      close: true,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
    }).showToast();
  };

  const createDismissableNotification = (node) => {
    if (!node) return;
    const toast = Toastify({
      node: node.cloneNode(true),
      duration: -1,
      newWindow: true,
      close: false,
      gravity: "top",
      position: "right",
      stopOnFocus: true,
    }).showToast();

    // Agregar manejo para cerrar manualmente
    toast.toastElement
      .querySelector('[data-dismiss="notification"]')
      ?.addEventListener('click', () => toast.hideToast());
  };

  // Vincular acciones
  document
    .getElementById("basic-non-sticky-notification-toggle")
    ?.addEventListener("click", () => {
      const content = document.getElementById("basic-non-sticky-notification-content");
      createNotification(content);
    });

  document
    .getElementById("basic-sticky-notification-toggle")
    ?.addEventListener("click", () => {
      const content = document.getElementById("basic-non-sticky-notification-content");
      createNotification(content, { sticky: true });
    });

  document
    .getElementById("success-notification-toggle")
    ?.addEventListener("click", () => {
      const content = document.getElementById("success-notification-content");
      createNotification(content, { sticky: true });
    });

  // Notificaciones avanzadas
  document
    .getElementById("notification-with-avatar-toggle")
    ?.addEventListener("click", () => {
      const content = document.getElementById("notification-with-avatar-content");
      createDismissableNotification(content);
    });
});



