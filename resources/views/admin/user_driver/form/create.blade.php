@extends('../themes/' . $activeTheme)
@section('title', 'Add User Driver')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
        ['label' => 'Create Driver', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Crear Nuevo Driver</h1>
        
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-6">
            <nav class="-mb-px flex space-x-8">
                <button onclick="showTab('personal')" id="tab-personal" class="tab-button active py-2 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    Información Personal
                </button>
                <button onclick="showTab('address')" id="tab-address" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Dirección
                </button>
                <button onclick="showTab('details')" id="tab-details" class="tab-button py-2 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    Detalles de Aplicación
                </button>
            </nav>
        </div>

        <form id="driver-form" action="{{ route('admin.carrier.user_drivers.store', $carrier->slug) }}" method="POST">
            @csrf
            
            <!-- Tab Content: Información Personal -->
            <div id="content-personal" class="tab-content">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">Nombre</label>
                        <input type="text" id="first_name" name="first_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Apellido</label>
                        <input type="text" id="last_name" name="last_name" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">Teléfono</label>
                        <input type="tel" id="phone" name="phone" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="license_number" class="block text-sm font-medium text-gray-700 mb-2">Número de Licencia</label>
                        <input type="text" id="license_number" name="license_number" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="date_of_birth" class="block text-sm font-medium text-gray-700 mb-2">Fecha de Nacimiento</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Dirección -->
            <div id="content-address" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label for="street_address" class="block text-sm font-medium text-gray-700 mb-2">Dirección</label>
                        <input type="text" id="street_address" name="street_address" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-2">Ciudad</label>
                        <input type="text" id="city" name="city" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="state" class="block text-sm font-medium text-gray-700 mb-2">Estado</label>
                        <input type="text" id="state" name="state" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="zip_code" class="block text-sm font-medium text-gray-700 mb-2">Código Postal</label>
                        <input type="text" id="zip_code" name="zip_code" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-2">País</label>
                        <select id="country" name="country" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Seleccionar País</option>
                            <option value="US">Estados Unidos</option>
                            <option value="MX">México</option>
                            <option value="CA">Canadá</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tab Content: Detalles de Aplicación -->
            <div id="content-details" class="tab-content hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">Años de Experiencia</label>
                        <input type="number" id="experience_years" name="experience_years" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    </div>
                    <div>
                        <label for="vehicle_type" class="block text-sm font-medium text-gray-700 mb-2">Tipo de Vehículo</label>
                        <select id="vehicle_type" name="vehicle_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Seleccionar Tipo</option>
                            <option value="truck">Camión</option>
                            <option value="van">Van</option>
                            <option value="car">Auto</option>
                        </select>
                    </div>
                    <div>
                        <label for="availability" class="block text-sm font-medium text-gray-700 mb-2">Disponibilidad</label>
                        <select id="availability" name="availability" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="">Seleccionar Disponibilidad</option>
                            <option value="full_time">Tiempo Completo</option>
                            <option value="part_time">Medio Tiempo</option>
                            <option value="weekends">Fines de Semana</option>
                        </select>
                    </div>
                    <div>
                        <label for="salary_expectation" class="block text-sm font-medium text-gray-700 mb-2">Expectativa Salarial</label>
                        <input type="number" id="salary_expectation" name="salary_expectation" min="0" step="0.01" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="md:col-span-2">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notas Adicionales</label>
                        <textarea id="notes" name="notes" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.carrier.user_drivers.index', $carrier->slug) }}" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors">
                    Cancelar
                </a>
                <div class="space-x-3">
                    <button type="button" id="prev-btn" onclick="previousTab()" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300 transition-colors hidden">
                        Anterior
                    </button>
                    <button type="button" id="next-btn" onclick="nextTab()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                        Siguiente
                    </button>
                    <button type="submit" id="submit-btn" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors hidden">
                        Crear Driver
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
let currentTab = 'personal';
const tabs = ['personal', 'address', 'details'];

function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById('content-' + tabName).classList.remove('hidden');
    
    // Add active class to selected tab button
    const activeButton = document.getElementById('tab-' + tabName);
    activeButton.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    
    currentTab = tabName;
    updateNavigationButtons();
}

function nextTab() {
    const currentIndex = tabs.indexOf(currentTab);
    if (currentIndex < tabs.length - 1) {
        showTab(tabs[currentIndex + 1]);
    }
}

function previousTab() {
    const currentIndex = tabs.indexOf(currentTab);
    if (currentIndex > 0) {
        showTab(tabs[currentIndex - 1]);
    }
}

function updateNavigationButtons() {
    const currentIndex = tabs.indexOf(currentTab);
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    // Show/hide previous button
    if (currentIndex === 0) {
        prevBtn.classList.add('hidden');
    } else {
        prevBtn.classList.remove('hidden');
    }
    
    // Show/hide next and submit buttons
    if (currentIndex === tabs.length - 1) {
        nextBtn.classList.add('hidden');
        submitBtn.classList.remove('hidden');
    } else {
        nextBtn.classList.remove('hidden');
        submitBtn.classList.add('hidden');
    }
}

// Initialize the form
document.addEventListener('DOMContentLoaded', function() {
    showTab('personal');
});
</script>
</div>
@endsection