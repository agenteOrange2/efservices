@extends('../themes/' . $activeTheme)
@section('title', 'Add User Driver')

@php
    $breadcrumbLinks = [
        ['label' => 'App', 'url' => route('admin.dashboard')],
        ['label' => 'Drivers', 'url' => route('admin.carrier.user_drivers.index', $carrier->slug)],
        ['label' => 'Create Driver', 'active' => true],
    ];

    // Definir los pasos y sus correspondientes pestañas
    $steps = [
        \App\Services\Admin\DriverStepService::STEP_GENERAL => ['label' => 'general', 'title' => 'General Information'],
        \App\Services\Admin\DriverStepService::STEP_LICENSES => [
            'label' => 'licenses',
            'title' => 'Licenses & Experience',
        ],
        \App\Services\Admin\DriverStepService::STEP_MEDICAL => ['label' => 'medical', 'title' => 'Medical Information'],
        \App\Services\Admin\DriverStepService::STEP_TRAINING => ['label' => 'training', 'title' => 'Training History'],
        \App\Services\Admin\DriverStepService::STEP_TRAFFIC => ['label' => 'traffic', 'title' => 'Traffic Record'],
        \App\Services\Admin\DriverStepService::STEP_ACCIDENT => ['label' => 'accident', 'title' => 'Accident History'],
    ];

    // Si estamos editando, obtener el estado actual de los pasos
    $stepsStatus =
        isset($userDriverDetail) && $userDriverDetail->id
            ? app(\App\Services\Admin\DriverStepService::class)->getStepsStatus($userDriverDetail)
            : array_fill(1, 6, \App\Services\Admin\DriverStepService::STATUS_MISSING);

    // Obtener el paso actual
    $currentStep =
        isset($userDriverDetail) && $userDriverDetail->id
            ? $userDriverDetail->current_step
            : \App\Services\Admin\DriverStepService::STEP_GENERAL;

    // Calcular el porcentaje de completitud
    $completionPercentage =
        isset($userDriverDetail) && $userDriverDetail->id
            ? app(\App\Services\Admin\DriverStepService::class)->calculateCompletionPercentage($userDriverDetail)
            : 0;
@endphp

@section('subcontent')
<livewire:admin.driver.driver-registration-wizard :carrier="$carrier" />
@endsection


@push('scripts')
<script>
    console.log('Driver Registration Wizard');

    function imagePreview() {
            return {
                previewUrl: null,
                hasImage: false,
                originalSrc: '{{ $userDriverDetail->profile_photo_url ?? asset('build/default_profile.png') }}',

                handleFileChange(e) {
                    const file = e.target.files[0];
                    if (!file) return;

                    // Validar tipo de archivo
                    if (!file.type.startsWith('image/')) {
                        alert('Please select an image file');
                        e.target.value = '';
                        return;
                    }

                    // Crear URL de previsualización
                    this.previewUrl = URL.createObjectURL(file);
                    this.hasImage = true;
                },

                removeImage() {
                    // Limpiar input file
                    const input = document.getElementById('photo');
                    input.value = '';

                    // Restaurar imagen original o default
                    this.previewUrl = this.originalSrc;
                    this.hasImage = false;

                    // Si es edición y hay una foto existente, puedes hacer una llamada AJAX para eliminarla
                    @if (isset($userDriverDetail) && $userDriverDetail->id)
                        if (confirm('Are you sure you want to remove the profile photo?')) {
                            fetch(`{{ route('admin.driver.delete-photo', ['driver' => $userDriverDetail->id]) }}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json',
                                    }
                                })
                                .then(response => response.json())
                                .then(data => {
                                    if (data.success) {
                                        this.originalSrc = '{{ asset('build/default_profile.png') }}';
                                        this.previewUrl = this.originalSrc;
                                    }
                                });
                        }
                    @endif
                }
            }
        }

</script>
    
@endpush

@pushOnce('scripts')
    {{-- @vite('resources/js/admin/driverRegistration.js') --}}

    @vite('resources/js/app.js')
    @vite('resources/js/pages/notification.js')
@endPushOnce
