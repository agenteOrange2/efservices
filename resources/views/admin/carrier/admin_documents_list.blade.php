@extends('../themes/' . $activeTheme)

@section('title', 'Carrier Documents Overview')

@section('subcontent')
    <h1 class="text-2xl font-bold mb-6">Carriers Document Review</h1>
    {{-- <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="box box--stacked">
                <x-base.table class="border-b border-slate-200/60">
                    <x-base.table.thead>
                        <x-base.table.tr>
                            <x-base.table.td
                                class="w-5 border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                <x-base.form-check.input type="checkbox" />
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Carrier Name
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                User Carrier
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                               Updated Documents
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                Status
                            </x-base.table.td>
                            <x-base.table.td
                                class="border-t border-slate-200/60 bg-slate-50 py-4 font-medium text-slate-500">
                                Joined Date
                            </x-base.table.td>
                            <x-base.table.td
                                class="w-20 border-t border-slate-200/60 bg-slate-50 py-4 text-center font-medium text-slate-500">
                                Action
                            </x-base.table.td>
                        </x-base.table.tr>
                    </x-base.table.thead>
                    <x-base.table.tbody>
                        @foreach ($carriers as $carrier)
                            <x-base.table.tr>
                                <x-base.table.td>
                                    <x-base.form-check.input type="checkbox" />
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="flex items-center">
                                        <!-- Imagen del carrier -->
                                        <div class="image-fit zoom-in h-9 w-9">
                                            <img 
                                                class="rounded-full shadow-md"
                                                src="{{ $carrier->getFirstMediaUrl('logo_carrier') ?: asset('build/default_profile.png') }}"
                                                alt="Logo {{ $carrier->name }}">
                                        </div>
                                        <!-- Información del carrier -->
                                        <div class="ml-3.5">
                                            <a class="whitespace-nowrap font-medium text-primary hover:underline"
                                                href="{{ route('admin.carrier.documents', $carrier->slug) }}">
                                                {{ $carrier->name }}
                                            </a>
                                            <div class="text-xs text-slate-500">Carrier</div>
                                        </div>
                                    </div>
                                </x-base.table.td>
                                
                                <x-base.table.td>
                                    <a class="whitespace-nowrap font-medium text-primary hover:underline" href="">
                                        {{ optional($carrier->userCarriers->first())->name ?? 'N/A' }}
                                    </a>
                                </x-base.table.td>
                                <x-base.table.td class="border-dashed py-4">
                                    <div class="w-40">
                                        <div class="text-xs text-slate-500">
                                            {{ round($carrier->completion_percentage) }}%
                                        </div>
                                        <div class="mt-1.5 flex h-1 rounded-sm border bg-slate-50">
                                            <div @class([
                                                'first:rounded-l-sm last:rounded-r-sm border border-primary/20 -m-px bg-primary/40',
                                            ]) style="width: {{ $carrier->completion_percentage }}%;"></div>
                                        </div>
                                    </div>
                                </x-base.table.td>
                                

                                <x-base.table.td>
                                    <div class="flex items-center justify-center">
                                        @if ($carrier->document_status == 'active')
                                            <span
                                                class="px-3 py-1 text-sm font-semibold text-green-700 bg-green-100 rounded-full">Active</span>
                                        @elseif ($carrier->document_status == 'pending')
                                            <span
                                                class="px-3 py-1 text-sm font-semibold text-yellow-700 bg-yellow-100 rounded-full">Pending</span>
                                        @else
                                            <span
                                                class="px-3 py-1 text-sm font-semibold text-red-700 bg-red-100 rounded-full">Inactive</span>
                                        @endif
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td>
                                    <div class="text-xs text-slate-500">
                                        {{ $carrier->created_at->format('d M Y') }}
                                    </div>
                                </x-base.table.td>
                                <x-base.table.td>
                                    <div class="flex items-center justify-center">
                                        <x-base.menu class="h-5">
                                            <x-base.menu.button class="h-5 w-5 text-slate-500">
                                                <x-base.lucide class="h-5 w-5 stroke-current" icon="MoreVertical" />
                                            </x-base.menu.button>
                                            <x-base.menu.items class="w-40">
                                                <x-base.menu.item as="a"
                                                    href="{{ route('admin.carrier.admin_documents.review', $carrier->slug) }}">
                                                    <x-base.lucide class="mr-2 h-4 w-4" icon="Edit3" />
                                                    Review Documents
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
    </div> --}}

    <livewire:document.document-table/>
@endsection

{{-- @pushOnce('scripts')
    <script>
        async function refreshCarrierProgress() {
            const response = await fetch("{{ route('admin.carrier.admin_documents.refresh') }}", {
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });

            if (response.ok) {
                const data = await response.json();
                data.forEach(carrier => {
                    const progressBar = document.querySelector(`#carrier-${carrier.id} .progress-bar`);
                    const statusText = document.querySelector(`#carrier-${carrier.id} .status-text`);

                    // Actualizar barra de progreso
                    if (progressBar) {
                        progressBar.style.width = `${carrier.completion_percentage}%`;
                    }

                    // Actualizar estado
                    if (statusText) {
                        statusText.textContent = carrier.document_status;
                    }
                });
            }
        }
    </script>
@endPushOnce --}}
