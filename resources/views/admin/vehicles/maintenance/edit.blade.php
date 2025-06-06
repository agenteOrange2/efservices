@extends('../themes/' . $activeTheme)
@section('title', 'Editar Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
    ['label' => 'Mantenimiento', 'url' => route('admin.maintenance.index')],
    ['label' => 'Editar Mantenimiento', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Editar Registro de Mantenimiento
            </div>
            <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                <x-base.button as="a" href="{{ route('admin.maintenance.index') }}"
                    class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                    variant="outline-secondary">
                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                    Volver a la Lista
                </x-base.button>
            </div>
        </div>
        
        <div class="intro-y box p-5 mt-5">
            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                <div class="font-medium text-base truncate">Información del Mantenimiento</div>
                <div class="flex items-center ml-auto">
                    <div class="dropdown ml-auto sm:ml-0">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="settings" class="w-4 h-4 mr-2"></i> Acciones
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Exportar PDF
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file" class="w-4 h-4 mr-2"></i> Exportar Excel
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="printer" class="w-4 h-4 mr-2"></i> Imprimir
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Renderizar el componente Livewire para editar mantenimiento -->
            <livewire:admin.vehicle.maintenance-form :id="$id" />
            
            <!-- Sección de documentos adjuntos -->
            <div class="mt-8 pt-5 border-t border-slate-200/60 dark:border-darkmode-400">
                <h3 class="text-lg font-medium mb-5">Documentos Adjuntos</h3>
                <div class="intro-y grid grid-cols-12 gap-3 sm:gap-6 mt-3">
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
                            <div class="absolute left-0 top-0 mt-3 ml-3">
                                <input class="form-check-input border border-slate-500" type="checkbox">
                            </div>
                            <a href="" class="w-3/5 file__icon file__icon--empty-directory mx-auto"></a>
                            <a href="" class="block font-medium mt-4 text-center truncate">Factura.pdf</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">1.2 MB</div>
                            <div class="absolute top-0 right-0 mr-2 mt-2 dropdown ml-auto">
                                <a class="dropdown-toggle w-5 h-5 block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="more-vertical" class="w-5 h-5 text-slate-500"></i>
                                </a>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a href="" class="dropdown-item">
                                                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Descargar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="" class="dropdown-item">
                                                <i data-lucide="trash" class="w-4 h-4 mr-2"></i> Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
                            <div class="absolute left-0 top-0 mt-3 ml-3">
                                <input class="form-check-input border border-slate-500" type="checkbox">
                            </div>
                            <a href="" class="w-3/5 file__icon file__icon--image mx-auto">
                                <div class="file__icon--image__preview image-fit">
                                    <img alt="" src="/dist/images/preview-5.jpg" data-action="zoom">
                                </div>
                            </a>
                            <a href="" class="block font-medium mt-4 text-center truncate">Foto.jpg</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">3.4 MB</div>
                            <div class="absolute top-0 right-0 mr-2 mt-2 dropdown ml-auto">
                                <a class="dropdown-toggle w-5 h-5 block" href="javascript:;" aria-expanded="false" data-tw-toggle="dropdown">
                                    <i data-lucide="more-vertical" class="w-5 h-5 text-slate-500"></i>
                                </a>
                                <div class="dropdown-menu w-40">
                                    <ul class="dropdown-content">
                                        <li>
                                            <a href="" class="dropdown-item">
                                                <i data-lucide="download" class="w-4 h-4 mr-2"></i> Descargar
                                            </a>
                                        </li>
                                        <li>
                                            <a href="" class="dropdown-item">
                                                <i data-lucide="trash" class="w-4 h-4 mr-2"></i> Eliminar
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 sm:px-5 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--file mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="" class="block font-medium mt-4 text-center truncate">Agregar Documento</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir nuevo archivo</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection