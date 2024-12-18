@extends('../themes/' . $activeTheme)

@section('title', 'Documents for ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.user_carriers.index', $carrier->slug)],
        ['label' => 'Documents for ' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')
    <h1 class="text-xl font-semibold">
        Documents for {{ $carrier->name }}</h1>
    <h1>hey</h1>

    {{-- 
    <div class="px-7">
        <div class="box box--stacked flex flex-col">
            <div class="overflow-auto xl:overflow-visible">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap text-sm font-medium text-center text-gray-500 dark:text-gray-400">
                        <!-- Tab Carrier -->
                        <li class="flex-grow">
                            <a href="{{ route('admin.carrier.edit', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2 rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
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
                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.user_carriers.*') ? 'text-primary dark:text-primary' : '' }}"
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
                        <!-- Tab Documents -->                        
                        <li class="flex-grow">
                            <a href="{{ route('admin.carrier.documents', $carrier->slug) }}"
                                class="inline-flex items-center justify-center w-full p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300 group
                            {{ request()->routeIs('admin.carrier.documents') ? 'text-primary border-blue-600 dark:text-primary dark:border-primary' : '' }}">
                                <svg class="w-6 h-6 me-2 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-300 {{ request()->routeIs('admin.carrier.documents') ? 'text-primary dark:text-primary' : '' }}"
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
                <table class="w-full text-left border-b border-slate-200/60">
                    <thead>
                        <tr>
                            <th
                                class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Name</th>
                            <th
                                class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Status</th>
                            <th
                                class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Notes</th>
                            <th
                                class="px-5 border-b border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr>
                                <td
                                    class="px-5 py-3 border-b box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                    {{ $document->documentType->name }}</td>
                                <td class="px-5 border-b border-dashed py-4">{{ $document->status_name }}</td>
                                <td class="px-5 border-b border-dashed py-4">
                                    @if ($document->notes)
                                        <p class="text-gray-600">Notes: {{ $document->notes }}</p>
                                    @endif
                                </td>
                                <td class="px-5 border-b border-dashed py-4">
                                    @if (!$document->getFirstMediaUrl('carrier_documents'))
                                        <!-- Mostrar formulario si no hay archivo subido -->
                                        <form action="{{ route('admin.carrier.admin_documents.upload', [$carrier->slug, $document->documentType->id]) }}"
                                            method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <input type="file" name="document" class="block mb-2" required>
                                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload
                                                File
                                            </button>
                                        </form>
                                    @else
                                        <!-- Mostrar enlace si el archivo está subido -->
                                        <a href="{{ $document->getFirstMediaUrl('document') }}" target="_blank"
                                            class="text-blue-500 underline">
                                            View Uploaded File
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 border-b border-dashed py-4 text-center">
                                    No documents available for this carrier.</td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    --}}
    <!-- Modal Trigger -->

    <table class="w-full text-left border-separate border-spacing-y-[10px]">
        <thead>
            <tr>
                <th class="px-5 py-4 font-medium text-slate-500">Document Type</th>
                <th class="px-5 py-4 font-medium text-slate-500">Status</th>
                <th class="px-5 py-4 font-medium text-slate-500">File</th>
                <th class="px-5 py-4 font-medium text-slate-500">Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($documents as $document)
                <tr>
                    <td>{{ $document->documentType->name }}</td>
                    <td>{{ $document->status_name }}</td>
                    <td>
                        @if ($document->getFirstMediaUrl('carrier_documents'))
                            <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank"
                                class="text-blue-500 underline">
                                View File
                            </a>
                        @else
                            <span class="text-gray-500">No file uploaded</span>
                        @endif
                    </td>
                    <td>
                        <!-- Formulario dinámico por fila -->
                        <form
                            action="{{ route('admin.carrier.admin_documents.upload', [$carrier->slug, $document->documentType->id]) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="file" name="document" required class="block mb-2">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-700">
                                {{ $document->getFirstMediaUrl('carrier_documents') ? 'Replace File' : 'Upload File' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="col-span-12">
        <h1>Performance Insights</h1>
        <div class="mt-2 overflow-auto lg:overflow-visible">
            <x-base.table class="border-separate border-spacing-y-[10px]">
                <x-base.table.tbody>
                    @foreach ($documents as $document)
                        <x-base.table.tr>
                            <x-base.table.td
                                class="box rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="flex items-center">
                                    {{-- <x-base.lucide class="h-6 w-6 fill-primary/10 stroke-[0.8] text-theme-1"
                                        icon="{{ $faker['category']['icon'] }}" /> --}}
                                    <div class="ml-3.5">
                                        <a class="font-medium whitespace-nowrap" href="">
                                            {{ $document->documentType->name }}
                                        </a>
                                        <div class="mt-1 text-xs whitespace-nowrap text-slate-500">
                                            {{ $document->documentType->name }}
                                        </div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-60 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    File
                                </div>
                                {{-- Corregir --}}
                                <a class="flex items-center text-primary" href="">
                                    <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]" icon="ExternalLink" />
                                    <div class="ml-1.5 whitespace-nowrap">
                                        @if ($document->getFirstMediaUrl('carrier_documents'))
                                        <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank"
                                            class="text-blue-500 underline">
                                            View File
                                        </a>
                                    </div>
                                    @else
                                    <div class="ml-1.5 whitespace-nowrap">
                                        <span class="text-gray-500">No file uploaded</span>
                                    </div>
                                    @endif
                                        {{-- {{ $faker['user']['name'] }} --}}
                                    </div>
                                </a>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1.5 whitespace-nowrap text-xs text-slate-500">
                                    Purchased Items
                                </div>
                                <div class="flex mb-1">
                                    <div class="w-5 h-5 image-fit zoom-in">
                                        {{-- <x-base.tippy
                                            class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                            src="{{ Vite::asset($faker['products'][0]['images'][0]['path']) }}"
                                            alt="Tailwise - Admin Dashboard Template"
                                            as="img"
                                            content="{{ $faker['products'][0]['name'] }}"
                                        /> --}}
                                    </div>
                                    <div class="image-fit zoom-in -ml-1.5 h-5 w-5">
                                        {{-- <x-base.tippy
                                            class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                            src="{{ Vite::asset($faker['products'][1]['images'][0]['path']) }}"
                                            alt="Tailwise - Admin Dashboard Template"
                                            as="img"
                                            content="{{ $faker['products'][1]['name'] }}"
                                        /> --}}
                                    </div>
                                    <div class="image-fit zoom-in -ml-1.5 h-5 w-5">
                                        {{-- <x-base.tippy
                                            class="rounded-full shadow-[0px_0px_0px_2px_#fff,_1px_1px_5px_rgba(0,0,0,0.32)]"
                                            src="{{ Vite::asset($faker['products'][2]['images'][0]['path']) }}"
                                            alt="Tailwise - Admin Dashboard Template"
                                            as="img"
                                            content="{{ $faker['products'][2]['name'] }}"
                                        /> --}}
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    Status
                                </div>
                                {{-- <div @class(['flex items-center', $faker['orderStatus']['textColor']])>
                                    <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                        icon="{{ $faker['orderStatus']['icon'] }}" />
                                        <div class="ml-1.5 whitespace-nowrap">
                                            {{ $faker['orderStatus']['name'] }}
                                        </div>
                                    </div> 
                                    --}}
                            </x-base.table.td>
                            <x-base.table.td
                                class="box w-44 rounded-l-none rounded-r-none border-x-0 shadow-[5px_3px_5px_#00000005] first:rounded-l-[0.6rem] first:border-l last:rounded-r-[0.6rem] last:border-r">
                                <div class="mb-1 text-xs whitespace-nowrap text-slate-500">
                                    Date
                                </div>
                                <div class="whitespace-nowrap">{{ $document->created_at }}</div>
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
                                            <x-base.menu.item>
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="WalletCards" />
                                                View Details
                                            </x-base.menu.item>
                                            <x-base.menu.item>
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="FileSignature" />
                                                Edit Order
                                            </x-base.menu.item>
                                            <x-base.menu.item>
                                                <x-base.lucide class="w-4 h-4 mr-2" icon="Printer" />
                                                Print Invoice
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
@endsection
