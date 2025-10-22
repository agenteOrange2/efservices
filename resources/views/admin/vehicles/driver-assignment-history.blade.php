@extends('../themes/' . $activeTheme)
@section('title', 'Driver Assignment History')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Vehicles', 'url' => route('admin.vehicles.index')],
        ['label' => 'Vehicle Details', 'url' => route('admin.vehicles.show', $vehicle->id)],
        ['label' => 'Driver Assignment History', 'active' => true],
    ];
@endphp

@section('subcontent')
    <div class="grid grid-cols-12 gap-x-6 gap-y-10">
        <div class="col-span-12">
            <!-- Header Section -->
            <div class="flex flex-col gap-y-3 md:h-10 md:flex-row md:items-center mb-6">
                <div class="text-base font-medium">
                    Driver Assignment History - {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }})
                </div>
                <div class="flex flex-col gap-x-3 gap-y-6 sm:flex-row md:ml-auto">
                    <x-base.button as="a" href="{{ route('admin.vehicles.show', $vehicle->id) }}"
                        class="w-full sm:w-44" variant="outline-secondary">
                        <x-base.lucide class="mr-2 h-4 w-4 stroke-[1.3]" icon="ArrowLeft" />
                        Back to Details
                    </x-base.button>
                </div>
            </div>

            <!-- Vehicle Info Card -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-1">Vehicle</h3>
                        <p class="text-base font-semibold">{{ $vehicle->make }} {{ $vehicle->model }}</p>
                        <p class="text-sm text-slate-600">{{ $vehicle->year }} - {{ $vehicle->vin }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-1">Carrier</h3>
                        <p class="text-base font-semibold">{{ $vehicle->carrier->name ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-slate-500 mb-1">Status</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $vehicle->out_of_service ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ $vehicle->out_of_service ? 'Out of Service' : 'Active' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Assignment History Table -->
            <div class="bg-white rounded-lg border border-slate-200 shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-800">Driver Assignment History</h2>
                    <p class="text-sm text-slate-600 mt-1">All driver assignments for this vehicle</p>
                </div>

                @if($assignmentHistory->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-slate-50 border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Driver
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Start Date
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        End Date    
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Duration
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Notes
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($assignmentHistory as $assignment)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10">
                                                    <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center">
                                                        <x-base.lucide class="h-5 w-5 text-slate-500" icon="User" />
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">
                                                        @switch($assignment->driver_type)
                                                            @case('company_driver')
                                                                {{ $assignment->user->name ?? 'Unknown' }}
                                                                @break
                                                            @case('owner_operator')
                                                                {{ $assignment->ownerOperatorDetail->owner_name ?? 'Unknown' }}
                                                                @break
                                                            @case('third_party')
                                                                @if($assignment->user)
                                                                    <div class="font-semibold">Driver: {{ $assignment->user->name }}</div>
                                                                    @if($assignment->thirdPartyDetail)
                                                                        <div class="mt-1">Company: {{ $assignment->thirdPartyDetail->third_party_name ?? 'Unknown' }}</div>
                                                                    @endif
                                                                @else
                                                                    {{ $assignment->thirdPartyDetail->third_party_name ?? 'Unknown' }}
                                                                @endif
                                                                @break
                                                            @default
                                                                {{ $assignment->user->name ?? 'Unknown' }}
                                                        @endswitch
                                                    </div>
                                                    <div class="text-sm text-slate-500">
                                                        @switch($assignment->driver_type)
                                                            @case('company_driver')
                                                                {{ $assignment->user->email ?? '' }}
                                                                @break
                                                            @case('owner_operator')
                                                                {{ $assignment->ownerOperatorDetail->owner_email ?? '' }}
                                                                @break
                                                            @case('third_party')
                                                                @if($assignment->user)
                                                                    <div>{{ $assignment->user->email }}</div>
                                                                    @if($assignment->thirdPartyDetail)
                                                                        <div class="mt-1">{{ $assignment->thirdPartyDetail->third_party_email ?? '' }}</div>
                                                                    @endif
                                                                @else
                                                                    {{ $assignment->thirdPartyDetail->third_party_email ?? '' }}
                                                                @endif
                                                                @break
                                                            @default
                                                                {{ $assignment->user->email ?? '' }}
                                                        @endswitch
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @switch($assignment->driver_type)
                                                    @case('company_driver')
                                                        bg-blue-100 text-blue-800
                                                        @break
                                                    @case('owner_operator')
                                                        bg-green-100 text-green-800
                                                        @break
                                                    @case('third_party')
                                                        bg-purple-100 text-purple-800
                                                        @break
                                                    @default
                                                        bg-gray-100 text-gray-800
                                                @endswitch">
                                                @switch($assignment->driver_type)
                                                    @case('company_driver')
                                                        Company Driver
                                                        @break
                                                    @case('owner_operator')
                                                        Owner Operator
                                                        @break
                                                    @case('third_party')
                                                        Third Party
                                                        @break
                                                    @default
                                                        {{ ucfirst(str_replace('_', ' ', $assignment->driver_type)) }}
                                                @endswitch
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            {{ $assignment->start_date ? \Carbon\Carbon::parse($assignment->start_date)->format('M d, Y') : 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                            {{ $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date)->format('M d, Y') : 'Activo' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                            @if($assignment->start_date)
                                @php
                                    try {
                                        $startDate = \Carbon\Carbon::parse($assignment->start_date);
                                        $endDate = $assignment->end_date ? \Carbon\Carbon::parse($assignment->end_date) : \Carbon\Carbon::now();
                                        
                                        // Validar que las fechas sean válidas
                                        if ($startDate->isValid() && $endDate->isValid()) {
                                            // Asegurar que la fecha de inicio no sea posterior a la fecha de fin
                                            if ($startDate->lte($endDate)) {
                                                $diffInDays = $startDate->diffInDays($endDate);
                                                
                                                // Formatear la duración de manera legible
                                                if ($diffInDays == 0) {
                                                    $durationText = 'Less than 1 day';
                                                } elseif ($diffInDays < 30) {
                                                    $durationText = number_format($diffInDays) . ' day' . ($diffInDays > 1 ? 's' : '');
                                                } elseif ($diffInDays < 365) {
                                                    $months = floor($diffInDays / 30);
                                                    $remainingDays = $diffInDays % 30;
                                                    $durationText = $months . ' month' . ($months > 1 ? 'es' : '');
                                                    if ($remainingDays > 0) {
                                                        $durationText .= ' y ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
                                                    }
                                                } else {
                                                    $years = floor($diffInDays / 365);
                                                    $remainingDays = $diffInDays % 365;
                                                    $durationText = $years . ' año' . ($years > 1 ? 's' : '');
                                                    if ($remainingDays > 0) {
                                                        $months = floor($remainingDays / 30);
                                                        if ($months > 0) {
                                                            $durationText .= ' y ' . $months . ' month' . ($months > 1 ? 's' : '');
                                                        }
                                                    }
                                                }
                                            } else {
                                                $durationText = 'Fechas inválidas';
                                            }
                                        } else {
                                            $durationText = 'Fechas inválidas';
                                        }
                                    } catch (\Exception $e) {
                                        $durationText = 'Error en cálculo';
                                    }
                                @endphp
                                {{ $durationText }}
                            @else
                                N/A
                            @endif
                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $assignment->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ ucfirst($assignment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-900">
                                            {{ $assignment->notes ?? 'Sin notas' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($assignmentHistory->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200">
                            {{ $assignmentHistory->links() }}
                        </div>
                    @endif
                @else
                    <div class="px-6 py-12 text-center">
                        <x-base.lucide class="mx-auto h-12 w-12 text-slate-400" icon="Users" />
                        <h3 class="mt-2 text-sm font-medium text-slate-900">No Driver Assignments</h3>
                        <p class="mt-1 text-sm text-slate-500">This vehicle does not have any driver assignments history.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection