<div>
    <div class="intro-y flex items-center mt-8">
        <h2 class="text-lg font-medium mr-auto">
            {{ $isEditing ? 'Editar Registro de Mantenimiento' : 'Nuevo Registro de Mantenimiento' }}
        </h2>
    </div>
    <div class="grid grid-cols-12 gap-6 mt-5">
        <div class="intro-y col-span-12 lg:col-span-8">
            <div class="intro-y box p-5">
                <form wire:submit.prevent="save">
                    <div class="mt-3">
                        <label for="vehicle_id" class="form-label">Vehículo</label>
                        <select id="vehicle_id" wire:model="vehicle_id" class="form-select w-full @error('vehicle_id') border-danger @enderror">
                            <option value="">Seleccionar vehículo</option>
                            @foreach($vehicles as $vehicle)
                                <option value="{{ $vehicle->id }}">
                                    {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->company_unit_number ?? $vehicle->vin }})
                                </option>
                            @endforeach
                        </select>
                        @error('vehicle_id') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-3">
                        <label for="service_tasks" class="form-label">Tipo de Mantenimiento</label>
                        <select id="service_tasks" wire:model="service_tasks" class="form-select w-full @error('service_tasks') border-danger @enderror">
                            <option value="">Seleccionar tipo de mantenimiento</option>
                            @foreach($maintenanceTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('service_tasks') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="service_date" class="form-label">Fecha de Mantenimiento</label>
                            <input id="service_date" type="datetime-local" wire:model="service_date" class="form-control w-full @error('service_date') border-danger @enderror">
                            @error('service_date') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                        
                        <div>
                            <label for="next_service_date" class="form-label">Fecha Próximo Mantenimiento</label>
                            <input id="next_service_date" type="datetime-local" wire:model="next_service_date" class="form-control w-full @error('next_service_date') border-danger @enderror">
                            @error('next_service_date') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="unit" class="form-label">Unidad</label>
                            <input id="unit" type="text" wire:model="unit" class="form-control w-full @error('unit') border-danger @enderror" placeholder="Número de unidad o identificador">
                            @error('unit') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                        
                        <div>
                            <label for="vendor_mechanic" class="form-label">Proveedor/Mecánico</label>
                            <input id="vendor_mechanic" type="text" wire:model="vendor_mechanic" class="form-control w-full @error('vendor_mechanic') border-danger @enderror" placeholder="Ej: Taller Automotriz XYZ">
                            @error('vendor_mechanic') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="cost" class="form-label">Costo</label>
                            <div class="input-group">
                                <div class="input-group-text">$</div>
                                <input id="cost" type="number" step="0.01" min="0" wire:model="cost" class="form-control @error('cost') border-danger @enderror" placeholder="0.00">
                            </div>
                            @error('cost') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                        
                        <div>
                            <label for="odometer" class="form-label">Lectura de Odómetro</label>
                            <input id="odometer" type="number" min="0" wire:model="odometer" class="form-control w-full @error('odometer') border-danger @enderror" placeholder="Ej: 50000">
                            @error('odometer') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="description" class="form-label">Descripción</label>
                        <textarea id="description" wire:model="description" class="form-control w-full @error('description') border-danger @enderror" rows="4" placeholder="Detalles adicionales del mantenimiento"></textarea>
                        @error('description') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-3">
                        <div class="form-check">
                            <input id="status" type="checkbox" wire:model="status" class="form-check-input">
                            <label for="status" class="form-check-label">Marcar como Completado</label>
                        </div>
                        @error('status') <div class="text-danger mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="text-right mt-5">
                        <a href="{{ route('admin.maintenance.index') }}" class="btn btn-outline-secondary w-24 mr-1">Cancelar</a>
                        <button type="submit" class="btn btn-primary w-24">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        <div class="intro-y col-span-12 lg:col-span-4">
            <div class="intro-y box p-5">
                <div class="text-base font-medium">Información de Mantenimiento</div>
                <div class="text-slate-500 mt-2">
                    <p>Utilice este formulario para {{ $isEditing ? 'actualizar' : 'crear' }} un registro de mantenimiento para un vehículo.</p>
                    <ul class="list-disc list-inside mt-3">
                        <li>Seleccione el vehículo al que se le realizó mantenimiento</li>
                        <li>Elija el tipo de mantenimiento realizado</li>
                        <li>Establezca la fecha del mantenimiento</li>
                        <li>Indique cuándo debe realizarse el próximo mantenimiento</li>
                        <li>Proporcione detalles sobre proveedor y costos</li>
                        <li>Marque como completado si el mantenimiento ya se realizó</li>
                    </ul>
                </div>
                @if($isEditing)
                    <div class="mt-5 pt-5 border-t border-slate-200/60 dark:border-darkmode-400">
                        <div class="flex items-center">
                            <div class="font-medium">Creado:</div>
                            <div class="ml-auto">{{ $maintenance->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="flex items-center mt-2">
                            <div class="font-medium">Última Actualización:</div>
                            <div class="ml-auto">{{ $maintenance->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>