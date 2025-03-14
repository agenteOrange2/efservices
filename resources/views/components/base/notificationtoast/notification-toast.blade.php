@if (session('notification'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Crear dinámicamente el contenido de la notificación con estilos personalizados
            const notificationContent = document.createElement('div');
            notificationContent.className = 
                `py-5 pl-5 pr-14 bg-white border border-slate-200/60 rounded-lg shadow-xl flex`;
            notificationContent.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-circle stroke-[1] w-5 h-5 text-{{ session('notification')['type'] === 'success' ? 'success' : 'error' }}">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <div class="ml-4 mr-4">
                    <div class="font-medium">{{ session('notification')['message'] }}</div>
                    @if (session('notification')['details'] ?? false)
                        <div class="mt-1 text-slate-500">{{ session('notification')['details'] }}</div>
                    @endif
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