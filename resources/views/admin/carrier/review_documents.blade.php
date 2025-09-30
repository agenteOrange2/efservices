@extends('../themes/' . $activeTheme)

@section('title', 'Review Documents for ' . $carrier->name)

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Carriers', 'url' => route('admin.carrier.index')],
        ['label' => 'Documents for' . $carrier->name, 'active' => true],
    ];
@endphp

@section('subcontent')
    <h1 class="text-2xl font-bold mb-4">Documents for {{ $carrier->name }}</h1>

    <div class="box box--stacked flex flex-col">
        <div class="overflow-auto xl:overflow-visible">
            <x-base.table class="border-b border-slate-200/60">
                <x-base.table.thead>
                    <x-base.table.tr>
                        <x-base.table.td class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Document Type
                        </x-base.table.td>
                        <x-base.table.td class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Notes
                        </x-base.table.td>
                        <x-base.table.td
                            class="w-52 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Uploaded File
                        </x-base.table.td>
                        <x-base.table.td
                            class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                            Status
                        </x-base.table.td>
                        <x-base.table.td class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                            Date Updated
                        </x-base.table.td>
                        <x-base.table.td
                            class="w-20 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                            Action
                        </x-base.table.td>
                    </x-base.table.tr>
                </x-base.table.thead>
                <x-base.table.tbody>
                    {{-- @foreach ($users->take(10) as $fakerKey => $faker) --}}
                    @foreach ($documents as $document)
                        <x-base.table.tr class="[&_td]:last:border-b-0 bg-white">
                            <x-base.table.td class="border-dashed py-4">
                                <div class="flex items-center">
                                    <x-base.lucide class="h-6 w-6 fill-primary/10 stroke-[0.8] text-theme-1"
                                        icon="FileText" />
                                    <div class="ml-3.5">
                                        {{ $document->documentType->name }}
                                        <div class="mt-1 text-xs font-medium text-slate-500">
                                            {{-- {{ $document->documentType->requirement ? 'Obligatory' : 'Optional' }} --}}
                                        </div>
                                    </div>
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="mt-0.5 font-medium text-slate-500">
                                    @if ($document->notes)
                                        {{-- <button data-modal-target="viewNoteModal-{{ $document->id }}"
                                            data-modal-toggle="viewNoteModal-{{ $document->id }}"
                                            class="text-blue-500 underline">
                                            View Notes
                                        </button> --}}
                                        <x-base.button data-tw-toggle="modal"
                                            data-tw-target="#viewNoteModal-{{ $document->id }}" as="a"
                                            variant="primary">
                                            View Notes
                                        </x-base.button>
                                    @else
                                        <span>No notes</span>
                                    @endif
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="w-40">
                                    @if ($document->getFirstMediaUrl('carrier_documents'))
                                        <!-- Archivo subido por el carrier -->
                                        <a href="{{ $document->getFirstMediaUrl('carrier_documents') }}" target="_blank"
                                            class="text-blue-500 underline">
                                            View Uploaded File
                                        </a>
                                    @elseif ($document->documentType->getFirstMediaUrl('default_documents'))
                                        <!-- Archivo predeterminado -->
                                        <a href="{{ $document->documentType->getFirstMediaUrl('default_documents') }}"
                                            target="_blank" class="text-blue-500 underline">
                                            View Default File
                                        </a>
                                    @else
                                        <!-- Ningún archivo disponible -->
                                        <span class="text-gray-500">N/A</span>
                                    @endif
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div
                                class="flex items-center 
                                    {{ $document->status_name == 'Pending' ? 'text-orange-500' : 
                                       ($document->status_name == 'Approved' ? 'text-green-500' : 
                                       ($document->status_name == 'In Process' ? 'text-blue-500' : 'text-red-500')) }}">
                                <x-base.lucide class="h-3.5 w-3.5 stroke-[1.7]"
                                    icon="{{ $document->status_name == 'Pending' ? 'AlertCircle' : 
                                           ($document->status_name == 'Approved' ? 'CheckCircle' : 
                                           ($document->status_name == 'In Process' ? 'RefreshCw' : 'XCircle')) }}" />
                                <div class="ml-1.5 whitespace-nowrap">
                                    {{ $document->status_name }}
                                </div>
                            </div>
                            
                            </x-base.table.td>
                            <x-base.table.td class="border-dashed py-4">
                                <div class="whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($document->updated_at)->format('d M Y') }}
                                </div>
                            </x-base.table.td>
                            <x-base.table.td class="relative border-dashed py-4">
                                <div class="flex items-center justify-center">
                                    <x-base.menu class="h-5">
                                        <x-base.menu.button class="h-5 w-5 text-slate-500">
                                            <x-base.lucide class="h-5 w-5 fill-slate-400/70 stroke-slate-400/70"
                                                icon="MoreVertical" />
                                        </x-base.menu.button>
                                        <x-base.menu.items class="w-40">
                                            <x-base.menu.item 
                                            data-tw-toggle="modal" 
                                            data-tw-target="#noteModal-{{ $document->id }}">
                                            <x-base.lucide class="mr-2 h-4 w-4" icon="CheckSquare" />
                                            Add/Edit Note
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



    {{-- Modal Notes --}}
    @foreach ($documents as $document)
        <x-base.dialog id="viewNoteModal-{{ $document->id }}">
            <x-base.dialog.panel class="p-10 text-center">
                <h3 class="text-lg font-semibold mb-4">Notes for {{ $document->documentType->name }}</h3>
                <p class="text-gray-700">{{ $document->notes ?? 'No notes available.' }}</p>
            </x-base.dialog.panel>
        </x-base.dialog>
    @endforeach



    @foreach ($documents as $document)
        <x-base.dialog id="noteModal-{{ $document->id }}">
            <x-base.dialog.panel class="p-10">
                <h3 class="text-lg font-semibold mb-4">Update Notes and Status</h3>
                <form
                    action="{{ route('admin.carriers.documents.update', ['carrier' => $carrier->slug, 'document' => $document->id]) }}"
                    method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-4">
                        <label for="notes-{{ $document->id }}"
                            class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea id="notes-{{ $document->id }}" name="notes" rows="4"
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm">{{ $document->notes }}</textarea>
                    </div>
                    <div class="mb-4">
                        <label for="status-{{ $document->id }}"
                            class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status-{{ $document->id }}" name="status"
                            class="disabled:bg-slate-100 disabled:cursor-not-allowed disabled:dark:bg-darkmode-800/50 [&[readonly]]:bg-slate-100 [&[readonly]]:cursor-not-allowed [&[readonly]]:dark:bg-darkmode-800/50 transition duration-200 ease-in-out w-full text-sm border-slate-200 shadow-sm rounded-md py-2 px-3 pr-8 focus:ring-4 focus:ring-primary focus:ring-opacity-20 focus:border-primary focus:border-opacity-40 group-[.form-inline]:flex-1">
                            <option value="1" {{ $document->status == 1 ? 'selected' : '' }}>Approved</option>
                            <option value="2" {{ $document->status == 2 ? 'selected' : '' }}>Rejected</option>
                            <option value="3" {{ $document->status == 3 ? 'selected' : '' }}>In Process</option>
                            <option value="0" {{ $document->status == 0 ? 'selected' : '' }}>Pending</option>
                        </select>
                    </div>
                    <div class="flex justify-end">
                        <x-base.dialog.footer>
                            <x-base.button class="mr-1 w-20" data-tw-dismiss="modal"
                                type="button" variant="outline-secondary">
                                Cancel
                            </x-base.button>
                            <x-base.button class="w-20" type="submit" variant="primary">
                                Send
                            </x-base.button>
                        </x-base.dialog.footer>
                        {{-- <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Save
                        </button> --}}
                    </div>
                </form>
            </x-base.dialog.panel>
        </x-base.dialog>
    @endforeach


@endsection

@pushOnce('scripts')
    @vite('resources/js/pages/modal.js')
@endPushOnce
