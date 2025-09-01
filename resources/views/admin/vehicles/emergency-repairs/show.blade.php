@extends('../themes/' . $activeTheme)
@section('title', 'Emergency Repair Details')
@php
    $breadcrumbLinks = [
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Emergency Repairs', 'url' => route('admin.vehicles.emergency-repairs.index')],
        ['label' => 'Details', 'active' => true],
    ];
@endphp
@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center">
                <div class="text-base font-medium group-[.mode--light]:text-white">
                    Emergency Repair Details: {{ $emergencyRepair->repair_name }}
                </div>
                <div class="flex flex-col gap-x-3 gap-y-2 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.edit', $emergencyRepair) }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="primary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Edit" />
                        Edit Repair
                    </x-base.button>
                    <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.index') }}"
                        class="group-[.mode--light]:!border-transparent group-[.mode--light]:!bg-white/[0.12] group-[.mode--light]:!text-slate-200"
                        variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                        Back to List
                    </x-base.button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-5">
                <!-- Main Details -->
                <div class="lg:col-span-2">
                    <div class="box box--stacked">
                        <div class="box-body p-5">
                            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                <div class="font-medium text-base truncate">Repair Information</div>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-warning/20 text-warning',
                                        'in_progress' => 'bg-primary/20 text-primary',
                                        'completed' => 'bg-success/20 text-success'
                                    ];
                                @endphp
                                <div class="ml-auto flex items-center {{ $statusClasses[$emergencyRepair->status] ?? 'bg-slate-100 text-slate-500' }} rounded-full px-3 py-1 text-sm font-medium">
                                    {{ ucfirst(str_replace('_', ' ', $emergencyRepair->status)) }}
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Repair Name</div>
                                    <div class="font-medium text-lg">{{ $emergencyRepair->repair_name }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Repair Date</div>
                                    <div class="font-medium">{{ $emergencyRepair->repair_date->format('F d, Y') }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Cost</div>
                                    <div class="font-medium text-lg text-success">${{ number_format($emergencyRepair->cost, 2) }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Created</div>
                                    <div class="font-medium">{{ $emergencyRepair->created_at->format('M d, Y g:i A') }}</div>
                                </div>

                                @if($emergencyRepair->updated_at != $emergencyRepair->created_at)
                                    <div>
                                        <div class="text-slate-500 text-sm mb-1">Last Updated</div>
                                        <div class="font-medium">{{ $emergencyRepair->updated_at->format('M d, Y g:i A') }}</div>
                                    </div>
                                @endif
                            </div>

                            @if($emergencyRepair->description)
                                <div class="mt-6">
                                    <div class="text-slate-500 text-sm mb-2">Description</div>
                                    <div class="bg-slate-50 dark:bg-darkmode-400 rounded-md p-4">
                                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $emergencyRepair->description }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($emergencyRepair->notes)
                                <div class="mt-6">
                                    <div class="text-slate-500 text-sm mb-2">Notes</div>
                                    <div class="bg-slate-50 dark:bg-darkmode-400 rounded-md p-4">
                                        <p class="text-slate-700 dark:text-slate-300 leading-relaxed">{{ $emergencyRepair->notes }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Files Section -->
                    @if($emergencyRepair->getMedia('emergency_repair_files')->count() > 0)
                        <div class="box box--stacked mt-6">
                            <div class="box-body p-5">
                                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                    <div class="font-medium text-base truncate">Attached Files</div>
                                    <div class="ml-auto text-slate-500 text-sm">
                                        {{ $emergencyRepair->getMedia('emergency_repair_files')->count() }} file(s)
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    @foreach($emergencyRepair->getMedia('emergency_repair_files') as $media)
                                        <div class="border border-slate-200/60 dark:border-darkmode-400 rounded-lg p-4 hover:bg-slate-50 dark:hover:bg-darkmode-400 transition-colors">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-shrink-0">
                                                    @if(str_starts_with($media->mime_type, 'image/'))
                                                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                            <x-base.lucide class="w-6 h-6 text-blue-600" icon="Image" />
                                                        </div>
                                                    @elseif($media->mime_type === 'application/pdf')
                                                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                                                            <x-base.lucide class="w-6 h-6 text-red-600" icon="FileText" />
                                                        </div>
                                                    @else
                                                        <div class="w-12 h-12 bg-slate-100 rounded-lg flex items-center justify-center">
                                                            <x-base.lucide class="w-6 h-6 text-slate-600" icon="File" />
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <div class="font-medium text-sm truncate">{{ $media->name }}</div>
                                                    <div class="text-xs text-slate-500 mt-1">{{ $media->human_readable_size }}</div>
                                                    <div class="text-xs text-slate-400 mt-1">{{ $media->created_at->format('M d, Y') }}</div>
                                                </div>
                                                <div class="flex-shrink-0">
                                                    <a href="{{ $media->getUrl() }}" target="_blank" 
                                                       class="inline-flex items-center justify-center w-8 h-8 text-slate-500 hover:text-primary hover:bg-primary/10 rounded-lg transition-colors">
                                                        <x-base.lucide class="w-4 h-4" icon="ExternalLink" />
                                                    </a>
                                                </div>
                                            </div>
                                            
                                            @if(str_starts_with($media->mime_type, 'image/'))
                                                <div class="mt-3">
                                                    <img src="{{ $media->getUrl() }}" alt="{{ $media->name }}" 
                                                         class="w-full h-32 object-cover rounded-lg cursor-pointer" 
                                                         onclick="openImageModal('{{ $media->getUrl() }}', '{{ $media->name }}')">
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="lg:col-span-1">
                    <!-- Vehicle Information -->
                    <div class="box box--stacked">
                        <div class="box-body p-5">
                            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                <div class="font-medium text-base truncate">Vehicle Information</div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Vehicle</div>
                                    <div class="font-medium">{{ $emergencyRepair->vehicle->make }} {{ $emergencyRepair->vehicle->model }}</div>
                                    <div class="text-sm text-slate-500">{{ $emergencyRepair->vehicle->year }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">Unit Number</div>
                                    <div class="font-medium">{{ $emergencyRepair->vehicle->company_unit_number ?? 'N/A' }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">VIN</div>
                                    <div class="font-medium text-xs break-all">{{ $emergencyRepair->vehicle->vin ?? 'N/A' }}</div>
                                </div>

                                <div>
                                    <div class="text-slate-500 text-sm mb-1">License Plate</div>
                                    <div class="font-medium">{{ $emergencyRepair->vehicle->license_plate ?? 'N/A' }}</div>
                                </div>

                                <div class="pt-3 border-t border-slate-200/60 dark:border-darkmode-400">
                                    <x-base.button as="a" href="{{ route('admin.vehicles.show', $emergencyRepair->vehicle) }}" 
                                        variant="outline-primary" class="w-full">
                                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Eye" />
                                        View Vehicle Details
                                    </x-base.button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carrier Information -->
                    @if($emergencyRepair->vehicle->carrier)
                        <div class="box box--stacked mt-6">
                            <div class="box-body p-5">
                                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                    <div class="font-medium text-base truncate">Carrier Information</div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <div class="text-slate-500 text-sm mb-1">Company Name</div>
                                        <div class="font-medium">{{ $emergencyRepair->vehicle->carrier->name }}</div>
                                    </div>

                                    @if($emergencyRepair->vehicle->carrier->dot_number)
                                        <div>
                                            <div class="text-slate-500 text-sm mb-1">DOT Number</div>
                                            <div class="font-medium">{{ $emergencyRepair->vehicle->carrier->dot_number }}</div>
                                        </div>
                                    @endif

                                    @if($emergencyRepair->vehicle->carrier->mc_number)
                                        <div>
                                            <div class="text-slate-500 text-sm mb-1">MC Number</div>
                                            <div class="font-medium">{{ $emergencyRepair->vehicle->carrier->mc_number }}</div>
                                        </div>
                                    @endif

                                    <div class="pt-3 border-t border-slate-200/60 dark:border-darkmode-400">
                                        <x-base.button as="a" href="{{ route('admin.carrier.show', $emergencyRepair->vehicle->carrier) }}" 
                                            variant="outline-primary" class="w-full">
                                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Building" />
                                            View Carrier Details
                                        </x-base.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Driver Information -->
                    @if($emergencyRepair->vehicle->driver)
                        <div class="box box--stacked mt-6">
                            <div class="box-body p-5">
                                <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                    <div class="font-medium text-base truncate">Driver Information</div>
                                </div>

                                <div class="space-y-4">
                                    <div>
                                        <div class="text-slate-500 text-sm mb-1">Driver Name</div>
                                        <div class="font-medium">{{ $emergencyRepair->vehicle->driver->first_name }} {{ $emergencyRepair->vehicle->driver->last_name }}</div>
                                    </div>

                                    @if($emergencyRepair->vehicle->driver->email)
                                        <div>
                                            <div class="text-slate-500 text-sm mb-1">Email</div>
                                            <div class="font-medium text-sm break-all">{{ $emergencyRepair->vehicle->driver->email }}</div>
                                        </div>
                                    @endif

                                    @if($emergencyRepair->vehicle->driver->phone)
                                        <div>
                                            <div class="text-slate-500 text-sm mb-1">Phone</div>
                                            <div class="font-medium">{{ $emergencyRepair->vehicle->driver->phone }}</div>
                                        </div>
                                    @endif

                                    <div class="pt-3 border-t border-slate-200/60 dark:border-darkmode-400">
                                        <x-base.button as="a" href="{{ route('admin.drivers.show', $emergencyRepair->vehicle->driver) }}" 
                                            variant="outline-primary" class="w-full">
                                            <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="User" />
                                            View Driver Details
                                        </x-base.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="box box--stacked mt-6">
                        <div class="box-body p-5">
                            <div class="flex items-center border-b border-slate-200/60 dark:border-darkmode-400 pb-5 mb-5">
                                <div class="font-medium text-base truncate">Actions</div>
                            </div>

                            <div class="space-y-3">
                                <x-base.button as="a" href="{{ route('admin.vehicles.emergency-repairs.edit', $emergencyRepair) }}" 
                                    variant="primary" class="w-full">
                                    <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Edit" />
                                    Edit Repair
                                </x-base.button>

                                <form action="{{ route('admin.vehicles.emergency-repairs.destroy', $emergencyRepair) }}" 
                                      method="POST" onsubmit="return confirm('Are you sure you want to delete this emergency repair? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <x-base.button type="submit" variant="outline-danger" class="w-full">
                                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="Trash2" />
                                        Delete Repair
                                    </x-base.button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-75 flex items-center justify-center p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-10">
                <x-base.lucide class="w-8 h-8" icon="X" />
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain">
            <div id="modalCaption" class="absolute bottom-4 left-4 text-white bg-black bg-opacity-50 px-3 py-1 rounded"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            function openImageModal(src, caption) {
                document.getElementById('modalImage').src = src;
                document.getElementById('modalCaption').textContent = caption;
                document.getElementById('imageModal').classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }

            function closeImageModal() {
                document.getElementById('imageModal').classList.add('hidden');
                document.body.style.overflow = 'auto';
            }

            // Close modal on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeImageModal();
                }
            });

            // Close modal on background click
            document.getElementById('imageModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeImageModal();
                }
            });
        </script>
    @endpush
@endsection