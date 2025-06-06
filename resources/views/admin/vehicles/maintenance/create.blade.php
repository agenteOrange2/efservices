@extends('../themes/' . $activeTheme)
@section('title', 'Nuevo Mantenimiento')
@php
$breadcrumbLinks = [
    ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
    ['label' => 'Vehículos', 'url' => route('admin.vehicles.index')],
    ['label' => 'Mantenimiento', 'url' => route('admin.maintenance.index')],
    ['label' => 'Nuevo Mantenimiento', 'active' => true],
];
@endphp
@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12">
        <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
            <div class="text-base font-medium group-[.mode--light]:text-white">
                Nuevo Registro de Mantenimiento
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
                <div class="ml-auto flex items-center">
                    <div class="dropdown">
                        <button class="dropdown-toggle btn btn-outline-secondary" aria-expanded="false" data-tw-toggle="dropdown">
                            <i data-lucide="help-circle" class="w-4 h-4 mr-2"></i> Ayuda
                        </button>
                        <div class="dropdown-menu w-40">
                            <ul class="dropdown-content">
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="file-text" class="w-4 h-4 mr-2"></i> Guía
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:;" class="dropdown-item">
                                        <i data-lucide="info" class="w-4 h-4 mr-2"></i> Acerca de
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Renderizar el componente Livewire para crear mantenimiento -->
            <livewire:admin.vehicle.maintenance-form />
            
            <!-- Sección de documentos adjuntos -->
            <div class="mt-8 pt-5 border-t border-slate-200/60 dark:border-darkmode-400">
                <h3 class="text-lg font-medium mb-5">Documentos Adjuntos</h3>
                <div class="intro-y grid grid-cols-12 gap-3 sm:gap-6 mt-3">
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--file mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Agregar Factura</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir archivo PDF</div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--image mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Agregar Foto</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir imagen</div>
                        </div>
                    </div>
                    <div class="intro-y col-span-6 sm:col-span-4 md:col-span-3 2xl:col-span-2">
                        <div class="file box rounded-md pt-8 pb-5 px-3 relative zoom-in">
                            <div class="w-3/5 file__icon file__icon--empty-directory mx-auto">
                                <div class="file__icon__file-name">+</div>
                            </div>
                            <a href="javascript:;" class="block font-medium mt-4 text-center truncate">Otro Documento</a>
                            <div class="text-slate-500 text-xs text-center mt-0.5">Subir cualquier archivo</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection