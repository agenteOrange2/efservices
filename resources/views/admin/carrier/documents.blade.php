@extends('../themes/' . $activeTheme)
@section('title', 'Documents for ' . $carrier->name)
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Documents for ' . $carrier->name, 'active' => true],
    ];
@endphp

@pushOnce('styles')
    @vite('resources/css/vendors/toastify.css')
@endPushOnce

@section('subcontent')
    <!-- Componente de notificación toast -->
    <x-base.notificationtoast.notification-toast :notification="session('notification')" />

    <div class="flex flex-col gap-5 sm:flex-row justify-between items-center mb-4">
        <h1 class="text-xl font-semibold">
            Documents for {{ $carrier->name }}
        </h1>
        
        <!-- Botón para generar documentos faltantes -->
        <form action="{{ route('admin.carrier.generate-missing-documents', $carrier->slug) }}" method="POST" class="inline">
            @csrf
            <x-base.button type="submit"  variant="primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Generar Documentos Faltantes
            </x-base.button>
        </form>
    </div>

    <div class="col-span-12">
        <div class="mt-2 overflow-auto lg:overflow-visible">
            <x-base.table class="border-separate border-spacing-y-[10px]">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap text-sm font-medium text-center bg-white text-gray-500 dark:text-gray-400">
                        <!-- Tab Carrier -->
                        <li class="flex-grow">
                            <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                        {{ request()->routeIs('admin.carrier.edit') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">

                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.edit') ? 'text-primary dark:text-primary' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M18 20a6 6 0 0 0-12 0" />
                                    <circle cx="12" cy="10" r="4" />
                                    <circle cx="12" cy="12" r="10" />
                                </svg>
                                Profile Carrier
                            </a>
                        </li>
                        <!-- Tab Users -->
                        <li class="flex-grow">
                            <a href="{{ route('admin.carrier.user_carriers.index', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                        {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary border-primary dark:text-primary' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                                Users
                            </a>
                        </li>
                        <!-- Tab Drivers -->
                        <li class="flex-grow">

                            <a href="{{ route('admin.carrier.user_drivers.index', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary border-primary ' : '' }}">
                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_drivers.*') ? 'text-primary dark:text-primary' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <rect width="16" height="16" x="4" y="4" rx="2" />
                                    <path d="M12 3v18" />
                                    <path d="M3 12h18" />
                                    <path d="m13 8-2-2-2 2" />
                                    <path d="m13 16-2 2-2-2" />
                                    <path d="m8 13-2-2 2-2" />
                                    <path d="m16 13 2-2-2-2" />
                                </svg>
                                Drivers
                            </a>
                        </li>
                        <!-- Tab Documents -->
                        {{-- Uncomment if needed --}}
                        <li class="flex-grow">
                            <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2  rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                        {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary dark:border-primary' : '' }}">
                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-primary dark:text-primary' : '' }}"
                                    xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4" />
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                    <path d="m3 15 2 2 4-4" />
                                </svg>
                                Documents
                            </a>
                        </li>
                    </ul>
                </div>
                <x-base.table.tbody>
                    @foreach ($documents as $document)
                        <x-base.table.tr>
                            <x-base.table.td
                                class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="flex items-center">
                                    <x-base.lucide class="h-6 w-6 fill-primary/10 stroke-[0.8] text-theme-1"
                                        icon="FileText" />
                                    <div class="ml-3.5">
                                        <a class="font-medium whitespace-nowrap" href="">
                                            {{ $document->documentType->name }}
                                        </a>
                                        <div class="mt-1 text-xs whitespace-nowrap">
                                            @if ($document->documentType->requirement)
                                                <span class="text-red-500 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    Obligatory
                                                </span>
                                            @else
                                                <span class="text-blue-500 flex items-center">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1"
                                                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                        stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12h6m2 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Optional
                                                </span>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-60 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    File
                                </div>
                                <div class="flex items-center text-primary">
                                    <div class="ml-1.5 whitespace-nowrap">
                                        {{-- <div class="flex items-center text-primary"> --}}
                                        @if ($document->getFirstMediaUrl('carrier_documents'))
                                            <div class="flex items-center text-primary">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                                <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}"
                                                    target="_blank" class="ml-3 text-primary underline ">
                                                    View File
                                                </a>
                                            </div>
                                        @elseif ($document->documentType->getFirstMediaUrl('default_documents'))
                                            <!-- Archivo predeterminado -->
                                            <div class="flex items-center text-primary mb-5">
                                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                                <a href="{{ $document->documentType->getFirstMediaUrl('default_documents') }}"
                                                    target="_blank" class="ml-3 text-primary underline">View Default
                                                    File</a>
                                            </div>
                                            @if ($document->documentType->getFirstMediaUrl('default_documents') && !$document->getFirstMediaUrl('carrier_documents'))
                                                <div class="flex items-center">
                                                    <input type="checkbox" id="approve-{{ $document->id }}"
                                                        {{ $document->status === \App\Models\CarrierDocument::STATUS_APPROVED ? 'checked' : '' }}
                                                        onchange="toggleApproval('{{ route('admin.carrier.approveDefaultDocument', [$carrier, $document]) }}', this)"
                                                        class="transition-all duration-100 ease-in-out shadow-sm border-slate-200 cursor-pointer rounded focus:ring-4 focus:ring-offset-0 focus:ring-primary focus:ring-opacity-20 [&[type='radio']]:checked:bg-primary [&[type='radio']]:checked:border-primary [&[type='radio']]:checked:border-opacity-10 [&[type='checkbox']]:checked:bg-primary [&[type='checkbox']]:checked:border-primary [&[type='checkbox']]:checked:border-opacity-10 [&:disabled:not(:checked)]:bg-slate-100 [&:disabled:not(:checked)]:cursor-not-allowed [&:disabled:not(:checked)]:dark:bg-darkmode-800/50 [&:disabled:checked]:opacity-70 [&:disabled:checked]:cursor-not-allowed [&:disabled:checked]:dark:bg-darkmode-800/50">

                                                    <div class="flex items-center">
                                                        <label for="approve-{{ $document->id }}" class="ml-3">
                                                            Approve Default
                                                        </label>
                                                        <x-base.tippy variant="primary"
                                                            content="If you have your own {{ $document->documentType->name }} file please upload it otherwise if you agree with the default file, just check the checkbox">
                                                            <div class="col-span-6 sm:col-span-3 lg:col-span-2 ml-4">
                                                                <i data-tw-merge data-lucide="alert-circle"
                                                                    class="stroke-[1] w-5 h-5 mx-auto block mx-auto block"></i>
                                                            </div>
                                                        </x-base.tippy>
                                                    </div>
                                                </div>
                                            @elseif ($document->documentType->getFirstMediaUrl('default_documents') && $document->getFirstMediaUrl('carrier_documents'))
                                                <div class="flex items-center">
                                                    <span class="text-xs font-medium bg-blue-100 text-blue-800 px-2 py-1 rounded-full ml-3">
                                                        Usuario usa documento personalizado
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-gray-500">No file uploaded</span>
                                        @endif
                                        {{-- </div> --}}
                                    </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1.5 whitespace-nowrap text-xs text-slate-500">
                                    Notes
                                </div>
                                <div class="relative group">
                                    <div class="flex items-center">
                                        <button type="button" class="cursor-pointer text-primary text-decoration-none" 
                                            onclick="showNotesModal('{{ $document->id }}', '{{ $document->documentType->name }}', '{{ $document->notes ?? 'No notes available' }}')">
                                            View Notes
                                        </button>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    Status
                                </div>
                                <div
                                    class="flex items-center 
                                    {{ $document->status_name == 'Pending'
                                        ? 'text-orange-500'
                                        : ($document->status_name == 'Approved'
                                            ? 'text-green-500'
                                            : ($document->status_name == 'In Process'
                                                ? 'text-blue-500'
                                                : 'text-red-500')) }}">
                                    <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                        icon="{{ $document->status_name == 'Pending'
                                            ? 'AlertCircle'
                                            : ($document->status_name == 'Approved'
                                                ? 'CheckCircle'
                                                : ($document->status_name == 'In Process'
                                                    ? 'RefreshCw'
                                                    : 'XCircle')) }}" />
                                    <div class="ml-1.5 whitespace-nowrap">
                                        {{ $document->status_name }}
                                    </div>
                                </div>

                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    Date
                                </div>
                                <div class="whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($document->updated_at)->format('d M Y') }}
                                </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box relative w-20 rounded-l-none rounded-r-none border-x-0 py-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="flex items-center justify-center">
                                    <x-base.menu class="h-5">
                                        <x-base.menu.button class="w-5 h-5 text-slate-500">
                                            <x-base.lucide class="w-5 h-5 fill-slate-400/70 stroke-slate-400/70"
                                                icon="MoreVertical" />
                                        </x-base.menu.button>
                                        <x-base.menu.items class="w-40">
                                            <x-base.menu.item
                                                data-modal-target="uploadModal-{{ $document->documentType->id }}"
                                                data-modal-toggle="uploadModal-{{ $document->documentType->id }}">
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="FileSignature" />
                                                Upload/Replace
                                            </x-base.menu.item>
                                        </x-base.menu.items>
                                    </x-base.menu>
                                </div>
                            </x-base.table.td>
                        </x-base.table.tr>
                    @endforeach
                </x-base.table.tbody>
            </x-base.table>
        </div>
    </div>

    <!-- Modals -->
    @foreach ($documents as $document)
        <!-- Modal -->
        <div id="uploadModal-{{ $document->documentType->id }}" tabindex="-1"
            class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
                <!-- Botón para cerrar el modal -->
                <button type="button" data-modal-toggle="uploadModal-{{ $document->documentType->id }}"
                    class="absolute top-2 right-2 text-gray-400 hover:text-gray-600 text-2xl font-semibold">
                    &times;
                </button>

                <!-- Título -->
                <h3 class="text-lg font-semibold mb-4 text-gray-900">Upload or Replace File</h3>

                <!-- Diseño de Input para subir archivos -->
                <form id="upload-form-{{ $document->documentType->id }}"
                    action="{{ route('admin.carrier.admin_documents.upload', [$carrier->slug, $document->documentType->id]) }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="w-full mb-5">
                        <label for="file-input-{{ $document->documentType->id }}"
                            class="flex flex-col items-center justify-center py-9 w-full border border-gray-300 border-dashed rounded-2xl cursor-pointer bg-gray-50 hover:border-indigo-600 transition">
                            <!-- Icono -->
                            <div class="mb-3 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none"
                                    stroke="#4F46E5" stroke-width="1.6">
                                    <path
                                        d="M16.296 25.3935L19.9997 21.6667L23.7034 25.3935M19.9997 35V21.759M10.7404 27.3611H9.855C6.253 27.3611 3.33301 24.4411 3.33301 20.8391C3.33301 17.2371 6.253 14.3171 9.855 14.3171V14.3171C10.344 14.3171 10.736 13.9195 10.7816 13.4326C11.2243 8.70174 15.1824 5 19.9997 5C25.1134 5 29.2589 9.1714 29.2589 14.3171H30.1444C33.7463 14.3171 36.6663 17.2371 36.6663 20.8391C36.6663 24.4411 33.7463 27.3611 30.1444 27.3611H29.2589" />
                                </svg>
                            </div>
                            <!-- Texto de ayuda -->
                            <h2 class="text-center text-gray-400 text-xs font-normal mb-1">
                                PNG, JPG, PDF, smaller than 15MB
                            </h2>
                            <h4 class="text-center text-gray-900 text-sm font-medium leading-snug">
                                Drag and Drop your file here or
                                <span class="text-indigo-600 underline">click to upload</span>
                            </h4>
                            <!-- Input oculto -->
                            <input id="file-input-{{ $document->documentType->id }}" type="file" name="document"
                                class="hidden">
                        </label>
                        <!-- Área para mostrar el nombre del archivo -->
                        <p id="file-name-{{ $document->documentType->id }}"
                            class="mt-2 text-gray-600 text-sm italic text-center hidden"></p>
                    </div>

                    <!-- Botón de Acción -->
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                            Upload File
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endsection

@pushOnce('scripts')
    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const toggleButtons = document.querySelectorAll("[data-modal-toggle]");

            // Abrir/Cerrar Modales
            toggleButtons.forEach(button => {
                const modalId = button.getAttribute("data-modal-toggle");
                const modal = document.getElementById(modalId);

                button.addEventListener("click", () => {
                    modal.classList.toggle("hidden");
                });
            });

            // Mostrar nombre del archivo
            const fileInputs = document.querySelectorAll("input[type='file']");
            fileInputs.forEach(input => {
                input.addEventListener("change", (e) => {
                    const fileName = e.target.files[0]?.name || "No file selected";
                    const fileNameDisplay = document.getElementById(
                        `file-name-${input.id.split("file-input-")[1]}`
                    );
                    fileNameDisplay.textContent = `Selected File: ${fileName}`;
                    fileNameDisplay.classList.remove("hidden");
                });
            });

            window.toggleApproval = async (url, checkbox) => {
                // Determina si el documento está aprobado o pendiente
                const approved = checkbox.checked ? 1 : 0;
                
                // Obtener el contenedor de estado del documento (elemento padre del checkbox)
                const statusContainer = checkbox.closest('tr').querySelector('div[class*="text-orange-500"], div[class*="text-green-500"], div[class*="text-blue-500"], div[class*="text-red-500"]');
                
                try {
                    const response = await fetch(url, {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .content,
                        },
                        body: JSON.stringify({
                            approved
                        }),
                    });

                    if (response.ok) {
                        const result = await response.json();
                        console.log(result); // Log para depuración
                        
                        // Actualizar el estado visualmente sin recargar la página
                        if (statusContainer) {
                            // Eliminar todas las clases de color existentes
                            statusContainer.classList.remove(
                                'text-orange-500', 
                                'text-green-500', 
                                'text-blue-500', 
                                'text-red-500'
                            );
                            
                            // Añadir la clase de color correcta según el nuevo estado
                            if (result.statusName === 'Approved') {
                                statusContainer.classList.add('text-green-500');
                            } else if (result.statusName === 'Pending') {
                                statusContainer.classList.add('text-orange-500');
                            } else if (result.statusName === 'In Process') {
                                statusContainer.classList.add('text-blue-500');
                            } else {
                                statusContainer.classList.add('text-red-500');
                            }
                            
                            // Actualizar el icono
                            const icon = statusContainer.querySelector('svg');
                            if (icon) {
                                // Eliminar la clase actual del icono
                                while (icon.firstChild) {
                                    icon.removeChild(icon.firstChild);
                                }
                                
                                // Actualizar con el nuevo icono según el estado
                                if (result.statusName === 'Approved') {
                                    icon.setAttribute('data-lucide', 'CheckCircle');
                                } else if (result.statusName === 'Pending') {
                                    icon.setAttribute('data-lucide', 'AlertCircle');
                                } else if (result.statusName === 'In Process') {
                                    icon.setAttribute('data-lucide', 'RefreshCw');
                                } else {
                                    icon.setAttribute('data-lucide', 'XCircle');
                                }
                                
                                // Volver a dibujar el icono si es necesario
                                if (window.lucide && typeof window.lucide.createIcons === 'function') {
                                    window.lucide.createIcons();
                                }
                            }
                            
                            // Actualizar el texto del estado
                            const statusText = statusContainer.querySelector('div.ml-1\.5') || 
                                               statusContainer.querySelector('div.whitespace-nowrap');
                            if (statusText) {
                                statusText.textContent = result.statusName;
                            }
                            
                            // Mostrar notificación de éxito
                            const successContent = document
                                .getElementById("success-notification-content")
                                .cloneNode(true);
                            successContent.classList.remove("hidden");
                            successContent.querySelector('.font-medium').textContent = 'Estado actualizado';
                            successContent.querySelector('.text-slate-500').textContent = 
                                'El estado del documento ha sido actualizado a ' + result.statusName;

                            Toastify({
                                node: successContent,
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                stopOnFocus: true,
                            }).showToast();
                        } else {
                            // Si no podemos actualizar dinámicamente, recargamos la página
                            location.reload();
                        }
                    } else {
                        throw new Error("Failed to update document status.");
                    }
                } catch (error) {
                    console.error(error);
                    // Revertir el estado del checkbox si ocurre un error
                    checkbox.checked = !checkbox.checked;
                }
            };
        });
        // Función para mostrar el modal con las notas
        function showNotesModal(documentId, documentName, notes) {
            // Crear el modal dinámicamente
            const modalHtml = `
                <div id="notesModal-${documentId}" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
                    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md mx-auto">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium">Notes for ${documentName}</h3>
                            <button type="button" class="text-gray-400 hover:text-gray-500" onclick="closeNotesModal('${documentId}')">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">${notes}</p>
                        </div>
                        <div class="mt-4 flex justify-end">
                            <button type="button" class="inline-flex justify-center rounded-md border border-transparent bg-blue-100 px-4 py-2 text-sm font-medium text-blue-900 hover:bg-blue-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2" onclick="closeNotesModal('${documentId}')">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            // Agregar el modal al body
            document.body.insertAdjacentHTML('beforeend', modalHtml);
        }
        
        // Función para cerrar el modal
        function closeNotesModal(documentId) {
            const modal = document.getElementById(`notesModal-${documentId}`);
            if (modal) {
                modal.remove();
            }
        }
    </script>
@endPushOnce
