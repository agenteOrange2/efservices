<div class="bg-white rounded-lg border border-slate-200 shadow-sm">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b border-slate-200">
        <button wire:click="toggleSection" 
                class="flex items-center justify-between w-full text-left hover:bg-slate-50 rounded-lg p-2 -m-2 transition-colors">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">
                        Historial Completo de Asignaciones de Conductores
                    </h3>
                    <p class="text-sm text-slate-500">
                        Registro detallado de todas las asignaciones de conductores para este vehículo
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                @if($showSection && $assignments->total() > 0)
                    <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                        {{ $assignments->total() }} registro{{ $assignments->total() !== 1 ? 's' : '' }}
                    </span>
                @endif
                <svg class="w-5 h-5 text-slate-400 transform transition-transform {{ $showSection ? 'rotate-180' : '' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </div>
        </button>
    </div>

    <!-- Content Section -->
    @if($showSection)
        <div class="p-6">
            @if($assignments->count() > 0)
                <!-- Table Header -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Conductor
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Tipo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Período
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Duración
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                    Estado
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($assignments as $assignment)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <!-- Driver Name -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-slate-200 flex items-center justify-center">
                                                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900">
                                                    {{ $this->getDriverName($assignment) }}
                                                </div>
                                                @if($assignment->driver_type === 'company_driver' && $assignment->user?->email)
                                                    <div class="text-sm text-slate-500">
                                                        {{ $assignment->user->email }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Driver Type -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $assignment->driver_type === 'company_driver' ? 'bg-blue-100 text-blue-800' : 
                                               ($assignment->driver_type === 'owner_operator' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                            {{ $this->getDriverType($assignment) }}
                                        </span>
                                    </td>

                                    <!-- Period -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        <div class="space-y-1">
                                            <div class="flex items-center text-sm">
                                                <span class="text-slate-500 mr-2">Inicio:</span>
                                                {{ $assignment->start_date ? $assignment->start_date->format('d/m/Y') : 'N/A' }}
                                            </div>
                                            <div class="flex items-center text-sm">
                                                <span class="text-slate-500 mr-2">Fin:</span>
                                                {{ $assignment->end_date ? $assignment->end_date->format('d/m/Y') : 'Activo' }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Duration -->
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ $this->getDuration($assignment->start_date, $assignment->end_date) }}
                                    </td>

                                    <!-- Status -->
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusBadgeClass($assignment->status) }}">
                                            {{ $this->getStatusText($assignment->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($assignments->hasPages())
                    <div class="mt-6 border-t border-slate-200 pt-4">
                        {{ $assignments->links() }}
                    </div>
                @endif
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-slate-400">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-slate-900">Sin historial de asignaciones</h3>
                    <p class="mt-1 text-sm text-slate-500">
                        Este vehículo no tiene historial de asignaciones de conductores registradas.
                    </p>
                </div>
            @endif
        </div>
    @endif
</div>