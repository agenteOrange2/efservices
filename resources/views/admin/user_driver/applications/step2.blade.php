@extends('../themes/' . $activeTheme)
@section('title', 'New Driver Application - Step 2')

@section('subcontent')
<div class="grid grid-cols-12 gap-x-6 gap-y-10">
    <div class="col-span-12 sm:col-span-10 sm:col-start-2">
        <div class="mb-6">
            <h2 class="text-2xl font-medium">Step 2: Address History</h2>
            <div class="mt-2 text-slate-500">
                Please provide the current address and any previous addresses (covering at least 3 years).
            </div>
        </div>

        <div class="box box--stacked flex flex-col">
            {{-- 
                Ajusta la ruta según tu definición:
                route('admin.carrier.user_drivers.application.step2.store', [$carrier, $driver])
                o si usas {application} en la ruta, algo como:
                route('admin.carrier.user_drivers.application.step2.store', $application->id)
            --}}
            <form action="{{ route('admin.carrier.user_drivers.application.step2.store', [$carrier, $driver]) }}"
                  method="POST">
                @csrf

                <!-- Dirección actual -->
                <div class="p-7">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Current Address</h3>
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Address Line 1</label>
                            <x-base.form-input 
                                name="address_line1" 
                                value="{{ old('address_line1', $currentAddress->address_line1 ?? '') }}"
                                required />
                            @error('address_line1')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">Address Line 2</label>
                            <x-base.form-input 
                                name="address_line2" 
                                value="{{ old('address_line2', $currentAddress->address_line2 ?? '') }}" />
                            @error('address_line2')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">City</label>
                            <x-base.form-input 
                                name="city" 
                                value="{{ old('city', $currentAddress->city ?? '') }}" 
                                required />
                            @error('city')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div>
                            <label class="form-label">State</label>
                            <x-base.form-input 
                                name="state" 
                                value="{{ old('state', $currentAddress->state ?? '') }}" 
                                required />
                            @error('state')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">ZIP Code</label>
                            <x-base.form-input 
                                name="zip_code" 
                                value="{{ old('zip_code', $currentAddress->zip_code ?? '') }}" 
                                required />
                            @error('zip_code')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div>
                            <label class="form-label">Lived From</label>
                            <x-base.form-input
                                type="date"
                                name="from_date"
                                value="{{ old('from_date', optional($currentAddress->from_date)->format('Y-m-d')) }}"
                                required
                            />
                            @error('from_date')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Lived To (if still living here, leave blank)</label>
                            <x-base.form-input
                                type="date"
                                name="to_date"
                                value="{{ old('to_date', optional($currentAddress->to_date)->format('Y-m-d')) }}"
                            />
                            @error('to_date')
                                <div class="text-red-500 mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="flex items-center mt-6">
                            <input 
                                type="checkbox"
                                name="lived_three_years"
                                id="lived_three_years"
                                value="1"
                                class="mr-2"
                                {{-- 
                                    Si en BD lived_three_years = true, lo marcamos.
                                    old() es importante para mantener estado en validación fallida
                                --}}
                                {{ old('lived_three_years', $currentAddress->lived_three_years ?? false) ? 'checked' : '' }}
                            />
                            <label for="lived_three_years">
                                I have lived here for 3 years (or more).
                            </label>
                        </div>
                    </div>
                    @error('lived_three_years')
                        <div class="text-red-500 mt-1">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Direcciones anteriores si NO ha vivido 3 años --}}
                <div class="p-7 border-t" 
                     id="previous-addresses-section" 
                     style="display: none;"> 
                    <div class="mb-6">
                        <h3 class="text-lg font-medium">Previous Addresses</h3>
                        <p class="text-slate-500 text-sm">
                            Please list all previous addresses in which you have lived in the last 3 years.
                        </p>
                    </div>

                    {{-- Contenedor dinámico de direcciones --}}
                    <div id="previous-addresses-container">
                        <!-- 
                            Aquí inyectaremos "bloques" de direcciones previas vía JavaScript 
                            si el usuario no ha vivido 3 años en la dirección actual.
                        -->
                    </div>

                    <div>
                        <x-base.button 
                            type="button"
                            variant="outline-primary"
                            id="add-previous-address-btn"
                            class="mt-4">
                            <x-base.lucide icon="Plus" class="w-4 h-4 mr-2" />
                            Add Another Previous Address
                        </x-base.button>
                    </div>
                </div>

                <div class="flex justify-end mt-6 pt-6 border-t">
                    <x-base.button type="submit" variant="primary">
                        Continue to Step 3
                        <x-base.lucide icon="ArrowRight" class="w-4 h-4 ml-2" />
                    </x-base.button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Pequeño script para mostrar/ocultar el bloque de "Previous Addresses" 
    // y clonar dinámicamente los campos cuando se da click en "Add Another Previous Address".
    document.addEventListener('DOMContentLoaded', function() {
        const livedThreeYearsCheckbox = document.getElementById('lived_three_years');
        const previousAddressesSection = document.getElementById('previous-addresses-section');
        const addPreviousAddressBtn = document.getElementById('add-previous-address-btn');
        const previousAddressesContainer = document.getElementById('previous-addresses-container');

        // Plantilla HTML para un bloque de dirección previa
        function getPreviousAddressTemplate(index) {
            // Usamos "previous_addresses[index]" para que coincida con 
            // el array esperado en la validación: previous_addresses.*.campo
            return `
                <div class="border rounded-md p-4 mb-4 bg-slate-50">
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="form-label">Address Line 1</label>
                            <input type="text" name="previous_addresses[${index}][address_line1]" class="form-input" required />
                        </div>
                        <div>
                            <label class="form-label">Address Line 2</label>
                            <input type="text" name="previous_addresses[${index}][address_line2]" class="form-input" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">City</label>
                            <input type="text" name="previous_addresses[${index}][city]" class="form-input" required />
                        </div>
                        <div>
                            <label class="form-label">State</label>
                            <input type="text" name="previous_addresses[${index}][state]" class="form-input" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">ZIP Code</label>
                            <input type="text" name="previous_addresses[${index}][zip_code]" class="form-input" required />
                        </div>
                        <div>
                            <label class="form-label">Lived From</label>
                            <input type="date" name="previous_addresses[${index}][from_date]" class="form-input" required />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-6 mt-4">
                        <div>
                            <label class="form-label">Lived To</label>
                            <input type="date" name="previous_addresses[${index}][to_date]" class="form-input" required />
                        </div>
                    </div>
                </div>
            `;
        }

        // Inicialmente, mostrar/ocultar según el checkbox
        togglePreviousAddressesSection(livedThreeYearsCheckbox.checked);

        livedThreeYearsCheckbox.addEventListener('change', function(e) {
            togglePreviousAddressesSection(e.target.checked);
        });

        function togglePreviousAddressesSection(isChecked) {
            if (isChecked) {
                // Si ha vivido 3 años, ocultamos la sección de direcciones previas
                previousAddressesSection.style.display = 'none';
            } else {
                // Si NO ha vivido 3 años, mostrar y permitir agregar direcciones previas
                previousAddressesSection.style.display = 'block';
            }
        }

        // Contador para indexar las direcciones previas
        let addressIndex = 0;

        addPreviousAddressBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const template = getPreviousAddressTemplate(addressIndex);
            previousAddressesContainer.insertAdjacentHTML('beforeend', template);
            addressIndex++;
        });

        // Si en el old() de la validación había errores y se recargó la vista,
        // podrías re-construir las direcciones previas automáticamente,
        // pero eso implica guardar en session un array con `old('previous_addresses')`.
        // Aquí se muestra la lógica básica.
    });
</script>
@endpush
