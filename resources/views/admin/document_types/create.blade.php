@extends('../themes/' . $activeTheme)
@section('title', 'Create Document Type Carriers')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Document Type Carriers ', 'url' => route('admin.document-types.index')],
        ['label' => 'Create Document', 'active' => true],
    ];
@endphp

@section('subcontent')

    <h1>Crear Tipo de Documento</h1>
    {{-- Mostrar errores de validación --}}
    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12 sm:col-span-10 sm:col-start-2">
            <div class="mt-7">
                <div class="box box--stacked flex flex-col">

                    {{-- Formulario para crear un nuevo tipo de documento --}}
                    <form action="{{ route('admin.document-types.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="p-7">
                            <!-- Full Name -->
                            <div class="mt-5 block flex-col pt-5 first:mt-0 first:pt-0 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="flex items-center">
                                            <div class="font-medium">Document type name</div>
                                            <div
                                                class="ml-2.5 rounded-md border border-slate-200 bg-slate-100 px-2 py-0.5 text-xs text-slate-500">
                                                Required
                                            </div>
                                        </div>
                                        <div class="mt-1.5 text-xs leading-relaxed text-slate-500/80 xl:mt-3">
                                            Enter the name of the document type
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <x-base.form-input name="name" type="text" placeholder="Enter name" id="name"
                                        value="{{ old('name') }}" />
                                    @error('name')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Status -->
                            <div class="my-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Requirement</div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <select data-tw-merge aria-label="Default select example"
                                        class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2"
                                        id="requirement" name="requirement">
                                        <option value="1" {{ old('requirement') == 1 ? 'selected' : '' }}>Sí</option>
                                        <option value="0" {{ old('requirement') == 0 ? 'selected' : '' }}>No</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <!-- Default Document -->
                            <div class="my-5 block flex-col pt-5 sm:flex xl:flex-row xl:items-center">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Defaul Document</div>
                                    </div>
                                </div>
                                <div class="mt-3 w-full flex-1 xl:mt-0">
                                    <div class="w-full flex-1">
                                        <select id="allow_default_file" name="allow_default_file"
                                            class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1 mt-2 sm:mr-2 mt-2 sm:mr-2">
                                            <option value="1" {{ old('allow_default_file') == 1 ? 'selected' : '' }}>
                                                Yes
                                            </option>
                                            <option value="0" {{ old('allow_default_file') == 0 ? 'selected' : '' }}>No
                                            </option>
                                        </select>
                                        @error('allow_default_file')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <!-- Campo de archivo predeterminado -->
                            <div class="my-5 flex flex-col xl:flex-row xl:items-center hidden" id="default_file_wrapper">
                                <div class="mb-2 inline-block sm:mb-0 sm:mr-5 sm:text-right xl:mr-14 xl:w-60">
                                    <div class="text-left">
                                        <div class="font-medium">Upload File
                                            (Optional)</div>
                                    </div>
                                </div>

                                <div class="w-full flex-1">
                                    <div class="mt-3">
                                        <label data-tw-merge for="regular-form-6"
                                            class="inline-block mb-2 group-[.form-inline]:mb-2 group-[.form-inline]:sm:mb-0 group-[.form-inline]:sm:mr-5 group-[.form-inline]:sm:text-right">
                                            Input File
                                        </label>
                                        <input data-tw-merge id="regular-form-6" type="file" name="default_file"
                                            id="default_file" accept=".pdf,.jpg,.png" placeholder="Input file"
                                            class="disabled:bg-slate-100 disabled:cursor-not-allowed [&amp;[readonly]]:bg-slate-100 [&amp;[readonly]]:cursor-not-allowed [&amp;[readonly]]:dark:bg-darkmode-800/50 [&amp;[readonly]]:dark:border-transparent transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md placeholder:text-slate-400/90 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 [&amp;[type=&#039;file&#039;]]:border file:mr-4 file:py-2 file:px-4 file:rounded-l-md file:border-0 file:border-r-[1px] file:border-slate-100/10 file:text-sm file:font-semibold file:bg-slate-100 file:text-slate-500/70 hover:file:bg-200 group-[.form-inline]:flex-1 group-[.input-group]:rounded-none group-[.input-group]:[&amp;:not(:first-child)]:border-l-transparent group-[.input-group]:first:rounded-l group-[.input-group]:last:rounded-r group-[.input-group]:z-10">
                                        @error('default_file')
                                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex border-t border-slate-200/80 px-7 py-5 md:justify-end">
                                <x-base.button type="submit" class="w-full border-primary/50 px-10 md:w-auto"
                                    variant="outline-primary">
                                    <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                    Save User
                                </x-base.button>

                                <x-base.button as="a" href="{{ route('admin.document-types.index') }}"
                                    class="w-full border-primary/50 px-10 md:w-auto" variant="outline-primary">
                                    <x-base.lucide class="-ml-2 mr-2 h-4 w-4 stroke-[1.3]" icon="Pocket" />
                                    Cancel
                                </x-base.button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@pushOnce('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const allowDefaultFileSelect = document.getElementById('allow_default_file');
            const defaultFileWrapper = document.getElementById('default_file_wrapper');

            // Mostrar/Ocultar el campo de archivo predeterminado según la selección
            allowDefaultFileSelect.addEventListener('change', function() {
                if (this.value === '1') {
                    defaultFileWrapper.classList.remove('hidden');
                } else {
                    defaultFileWrapper.classList.add('hidden');
                }
            });

            // Comprobación inicial
            if (allowDefaultFileSelect.value === '1') {
                defaultFileWrapper.classList.remove('hidden');
            }
        });
    </script>
@endPushOnce
