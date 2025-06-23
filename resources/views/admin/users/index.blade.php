@extends('../themes/' . $activeTheme)
@section('title', 'Super Admins')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Users', 'url' => route('admin.users.index')],
        ['label' => 'Super Admins', 'active' => true],
    ];
@endphp
@section('subcontent')
    {{-- <livewire:notifications-panel /> --}}
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Users
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.users.create') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="PenLine" />
                        Add New User
                    </x-base.button>
                </div>
            </div>
            <div class="box box--stacked flex flex-col mt-5">
                <div class="flex flex-col gap-y-2 p-5 sm:flex-row sm:items-center">
                    <div class="relative">
                        <livewire:search-bar placeholder="Search users..." />
                    </div>

                    <div class="flex flex-col gap-x-3 gap-y-2 sm:ml-auto sm:flex-row">
                        <livewire:menu-export :exportExcel="true" :exportPdf="true" :exportExcelRoute="'admin.users.export.excel'" :exportPdfRoute="'admin.users.export.pdf'" />
                        <livewire:filter-popover :filter-options="[
                            'status' => [
                                'type' => 'select',
                                'label' => 'Status',
                                'options' => [
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                ],
                            ],
                        ]" />
                    </div>
                </div>
                <livewire:generic-table class="p-0" 
                    model="App\Models\User" 
                    :columns="['name', 'email', 'status', 'created_at']" 
                    :searchableFields="['name', 'email', 'status', 'created_at']"
                    editRoute="admin.users.edit" 
                    exportExcelRoute="admin.users.export.excel"
                    exportPdfRoute="admin.users.export.pdf" 
                    :customFilters="[
                        'status' => [
                            'type' => 'select',
                            'label' => 'Status',
                            'options' => [
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ],
                        ],
                    ]" />
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Cargar scripts Vite primero -->
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')

    <!-- Asegurarnos que el script se ejecuta después de cargar Livewire -->
    <script>
        // Usar evento livewire:initialized para Livewire v3
        document.addEventListener('livewire:initialized', function() {
            console.log('Livewire is initialized and listening for notify and downloadExport events');

            Livewire.on('notify', notification => {                
                // Verificar que Toastify está disponible
                if (typeof Toastify === 'function') {
                    Toastify({
                        text: notification.message,
                        duration: 3000,
                        gravity: "top",
                        position: "right",
                        backgroundColor: notification.type === 'success' ? "#28a745" : 
                                        notification.type === 'error' ? "#dc3545" : 
                                        notification.type === 'warning' ? "#ffc107" : "#17a2b8",
                    }).showToast();
                } else {
                    console.log('Toastify not available, notification was:', notification);
                    alert(notification.message);
                }
            });
            
            // Listener para manejar las descargas de exportación
            Livewire.on('downloadExport', params => {
                console.log('Iniciando descarga desde URL:', params.url);
                // Crear un iframe temporal para descargar el archivo sin cambiar de página
                const downloadIframe = document.createElement('iframe');
                downloadIframe.style.display = 'none';
                downloadIframe.src = params.url;
                document.body.appendChild(downloadIframe);
                
                // Eliminar el iframe después de un tiempo
                setTimeout(() => {
                    document.body.removeChild(downloadIframe);
                }, 5000);
            });
        });
    </script>
@endpush
