@extends('../themes/' . $activeTheme)
@section('title', 'Gestión de Destinatarios de Notificaciones')
@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Notificaciones', 'url' => '#'],
        ['label' => 'Destinatarios', 'active' => true],
    ];
@endphp

@section('subcontent')
<div class="max-w-6xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Gestión de Destinatarios</h1>
        <p class="text-gray-600">Configura quién recibe las notificaciones del sistema</p>
    </div>

    <!-- Alert Messages -->
    <div id="alertContainer" class="mb-6"></div>

    <!-- Main Content -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Form Section -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-6">Agregar Destinatario</h2>
                
                <form id="addRecipientForm">
                    @csrf
                    
                    <!-- Notification Type -->
                    <div class="mb-4">
                        <label for="notification_type" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipo de Notificación
                        </label>
                        <select id="notification_type" name="notification_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="user_carrier">Registro de Transportista</option>
                            <option value="carrier_step">Paso Completado</option>
                        </select>
                    </div>

                    <!-- User Selection -->
                    <div class="mb-6">
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Usuario (Superadmin o User Carrier)
                        </label>
                        <select id="user_id" name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                            <option value="">Seleccionar usuario...</option>
                            @foreach($users as $user)
                                <option value="{{ $user['id'] }}">
                                    {{ $user['name'] }} ({{ $user['email'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="submitBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                        Agregar Destinatario
                    </button>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h2 class="text-xl font-semibold text-gray-800">Destinatarios Configurados</h2>
                        <button onclick="refreshPage()" class="px-4 py-2 text-gray-600 hover:text-gray-800 transition-colors duration-200">
                            <i class="fas fa-sync-alt mr-1"></i> Actualizar
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo de Notificación</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($recipients as $recipient)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $recipient->name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        {{ $recipient->email }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $recipient->notification_type === 'user_carrier' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                        {{ $recipient->notification_type === 'user_carrier' ? 'Registro Transportista' : 'Paso Completado' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                        {{ $recipient->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $recipient->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="toggleRecipient({{ $recipient->id }})" 
                                            class="text-blue-600 hover:text-blue-900 mr-3 transition-colors duration-200">
                                        {{ $recipient->is_active ? 'Desactivar' : 'Activar' }}
                                    </button>
                                    <button onclick="deleteRecipient({{ $recipient->id }})" 
                                            class="text-red-600 hover:text-red-900 transition-colors duration-200">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                                    <p>No hay destinatarios configurados</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
    <div class="text-center text-white">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-white mx-auto mb-4"></div>
        <p class="text-lg">Procesando...</p>
    </div>
</div>
@endsection

@pushOnce('scripts')
<script>
// Global variables
let isSubmitting = false;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeForm();
});

function initializeForm() {
    const form = document.getElementById('addRecipientForm');
    form.addEventListener('submit', handleFormSubmit);
}

function handleFormSubmit(e) {
    e.preventDefault();
    
    if (isSubmitting) return;
    
    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = document.getElementById('submitBtn');
    
    // Validate form
    const notificationType = document.getElementById('notification_type').value;
    const userId = document.getElementById('user_id').value;
    
    if (!notificationType || !userId) {
        showAlert('Por favor completa todos los campos', 'error');
        return;
    }
    
    isSubmitting = true;
    showLoading(true);
    
    // Update button state
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Agregando...';
    
    fetch('{{ route("admin.notification-recipients.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('¡Destinatario agregado exitosamente!', 'success');
            form.reset();
            setTimeout(() => {
                location.reload();
            }, 1500);
        } else {
            showAlert('Error: ' + (data.message || 'No se pudo agregar el destinatario'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
    })
    .finally(() => {
        isSubmitting = false;
        showLoading(false);
        
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Agregar Destinatario';
    });
}

function toggleRecipient(id) {
    showLoading(true);
    
    fetch(`{{ url('admin/notification-recipients') }}/${id}/toggle`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Estado actualizado correctamente', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert('Error: ' + (data.message || 'No se pudo cambiar el estado'), 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
    })
    .finally(() => {
        showLoading(false);
    });
}

function deleteRecipient(id) {
    if (confirm('¿Estás seguro de que quieres eliminar este destinatario?\nEsta acción no se puede deshacer.')) {
        showLoading(true);
        
        fetch(`{{ url('admin/notification-recipients') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('¡Destinatario eliminado exitosamente!', 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showAlert('Error: ' + (data.message || 'No se pudo eliminar el destinatario'), 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error de conexión. Por favor, intenta nuevamente.', 'error');
        })
        .finally(() => {
            showLoading(false);
        });
    }
}

function refreshPage() {
    showLoading(true);
    setTimeout(() => {
        location.reload();
    }, 500);
}

function showLoading(show = true) {
    const overlay = document.getElementById('loadingOverlay');
    if (show) {
        overlay.classList.remove('hidden');
    } else {
        overlay.classList.add('hidden');
    }
}

function showAlert(message, type = 'success') {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-notification');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
    alertDiv.className = `alert-notification fixed top-4 right-4 z-50 px-4 py-3 border rounded-lg shadow-lg ${bgColor}`;
    alertDiv.innerHTML = `
        <div class="flex items-center justify-between">
            <span>${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-lg font-bold">&times;</button>
        </div>
    `;
    
    // Insert into body
    document.body.appendChild(alertDiv);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>
@endPushOnce

@push('styles')
<style>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style>
@endpush