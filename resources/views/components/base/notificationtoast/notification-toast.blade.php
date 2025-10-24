@if (session('notification'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notification = @json(session('notification'));
            
            // Crear dinámicamente el contenido de la notificación con estilos personalizados
            const notificationContent = document.createElement('div');
            notificationContent.className = 
                'py-5 pl-5 pr-14 bg-white border border-slate-200/60 rounded-lg shadow-xl flex';
            
            // Determinar el icono y color según el tipo
            let iconSvg = '';
            let textColor = '';
            
            if (notification.type === 'success') {
                textColor = 'text-success';
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle stroke-[1] w-5 h-5 text-success">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                `;
            } else if (notification.type === 'error') {
                textColor = 'text-danger';
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-circle stroke-[1] w-5 h-5 text-danger">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m15 9-6 6"></path>
                        <path d="m9 9 6 6"></path>
                    </svg>
                `;
            } else if (notification.type === 'warning') {
                textColor = 'text-warning';
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle stroke-[1] w-5 h-5 text-warning">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"></path>
                        <path d="M12 9v4"></path>
                        <path d="m12 17 .01 0"></path>
                    </svg>
                `;
            } else {
                textColor = 'text-primary';
                iconSvg = `
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info stroke-[1] w-5 h-5 text-primary">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="m12 16-4-4 4-4"></path>
                        <path d="m16 12H8"></path>
                    </svg>
                `;
            }
            
            notificationContent.innerHTML = `
                ${iconSvg}
                <div class="ml-4 mr-4">
                    <div class="font-medium">${notification.message}</div>
                    ${notification.details ? `<div class="mt-1 text-slate-500">${notification.details}</div>` : ''}
                </div>
            `;

            // Mostrar la notificación usando Toastify con el nodo dinámico
            Toastify({
                node: notificationContent,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
            }).showToast();
        });
    </script>
@endif