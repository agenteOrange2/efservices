@extends('../themes/' . $activeTheme)
@section('title', 'Driver Carriers for: ' . $carrier->name)

@php
$breadcrumbLinks = [
['label' => 'App', 'url' => route('admin.dashboard')],
['label' => 'Carriers', 'url' => route('admin.carrier.index')],
['label' => 'Driver Carriers: ' . $carrier->name, 'active' => true],
];
@endphp

@section('subcontent')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Crear Nuevo Conductor</h1>
                    <p class="text-muted mb-0">Transportista: {{ $carrier->name }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.carrier.user_drivers.index', $carrier) }}"
                        class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver a Lista
                    </a>
                </div>
            </div>

            <!-- Driver Limit Info -->
            @php
            $maxDrivers = $carrier->membership->max_drivers ?? 1;
            $currentDriversCount = App\Models\UserDriverDetail::where('carrier_id', $carrier->id)->count();
            @endphp

            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle"></i>
                <strong>Límite de Conductores:</strong>
                {{ $currentDriversCount }} de {{ $maxDrivers }} conductores utilizados
                @if($currentDriversCount >= $maxDrivers)
                <span class="text-danger">(Límite alcanzado)</span>
                @endif
            </div>

            <!-- Main Form Card -->
            <div class="box box--stacked flex flex-col">
                <div class="box-body">
                    <div class="card-header p-5">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-user-plus"></i> Información del Conductor
                        </h6>
                    </div>
                </div>
                <div class="p-5">                    
                        <!-- Livewire Component -->
                        @livewire('admin.admin-driver-form', [
                        'carrier' => $carrier,
                        'mode' => 'create'
                        ])                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .nav-tabs .nav-link {
        border: 1px solid transparent;
        border-top-left-radius: 0.25rem;
        border-top-right-radius: 0.25rem;
    }

    .nav-tabs .nav-link.active {
        color: #495057;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .tab-content {
        border: 1px solid #dee2e6;
        border-top: none;
        padding: 1.5rem;
        background-color: #fff;
    }

    .form-group label {
        font-weight: 600;
        color: #374151;
    }

    .required::after {
        content: " *";
        color: #e53e3e;
    }
</style>
@endpush
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to format date input as MM/DD/YYYY
    function formatDateInput(input) {
        let value = input.value.replace(/\D/g, ''); // Remove non-digits
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2);
        }
        if (value.length >= 5) {
            value = value.substring(0, 5) + '/' + value.substring(5, 9);
        }
        
        input.value = value;
        
        // Trigger Livewire update
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }
    
    // Function to validate date format
    function isValidDate(dateString) {
        const regex = /^(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])\/(19|20)\d{2}$/;
        if (!regex.test(dateString)) return false;
        
        const [month, day, year] = dateString.split('/').map(Number);
        const date = new Date(year, month - 1, day);
        
        return date.getFullYear() === year &&
               date.getMonth() === month - 1 &&
               date.getDate() === day;
    }
    
    // Apply date formatting to existing date inputs
    function applyDateFormatting() {
        const dateInputs = document.querySelectorAll('input[placeholder="MM/DD/YYYY"]');
        
        dateInputs.forEach(input => {
            // Remove existing event listeners to avoid duplicates
            input.removeEventListener('input', input._dateFormatHandler);
            input.removeEventListener('blur', input._dateValidateHandler);
            
            // Add input event listener for formatting
            input._dateFormatHandler = function(e) {
                formatDateInput(e.target);
            };
            input.addEventListener('input', input._dateFormatHandler);
            
            // Add blur event listener for validation
            input._dateValidateHandler = function(e) {
                const value = e.target.value;
                if (value && !isValidDate(value)) {
                    e.target.classList.add('border-red-500');
                    // Show error message if not already present
                    let errorMsg = e.target.parentNode.querySelector('.date-error-msg');
                    if (!errorMsg) {
                        errorMsg = document.createElement('span');
                        errorMsg.className = 'text-red-500 text-sm date-error-msg';
                        errorMsg.textContent = 'Please enter a valid date in MM/DD/YYYY format';
                        e.target.parentNode.appendChild(errorMsg);
                    }
                } else {
                    e.target.classList.remove('border-red-500');
                    // Remove error message
                    const errorMsg = e.target.parentNode.querySelector('.date-error-msg');
                    if (errorMsg) {
                        errorMsg.remove();
                    }
                }
            };
            input.addEventListener('blur', input._dateValidateHandler);
        });
    }
    
    // Apply formatting on page load
    applyDateFormatting();
    
    // Reapply formatting when Livewire updates the DOM
    document.addEventListener('livewire:navigated', applyDateFormatting);
    document.addEventListener('livewire:load', applyDateFormatting);
    
    // Listen for Livewire updates and reapply formatting
    Livewire.hook('message.processed', (message, component) => {
        setTimeout(applyDateFormatting, 100);
    });
    
    // Show auto-save feedback
    function showAutoSaveFeedback() {
        const feedback = document.getElementById('auto-save-feedback');
        if (feedback) {
            feedback.classList.remove('hidden');
            setTimeout(() => {
                feedback.classList.add('hidden');
            }, 3000);
        }
    }
    
    // Listen for auto-save events from Livewire
    window.addEventListener('auto-save-success', function() {
        showAutoSaveFeedback();
    });
    
    // Listen for Livewire events that might indicate auto-save
    Livewire.on('autoSaveSuccess', function() {
        showAutoSaveFeedback();
    });
});
</script>
@endpush