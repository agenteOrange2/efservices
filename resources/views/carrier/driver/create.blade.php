@extends('../themes/' . $activeTheme)

@section('subhead')
    <title>Crear Nuevo Conductor - EF Services</title>
@endsection

@section('subcontent')
    <div class="py-5">
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('carrier.drivers.index') }}" class="btn btn-outline-secondary mr-4">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i> Volver
                </a>
                <h2 class="text-2xl font-medium">Registrar Nuevo Conductor</h2>
            </div>
            <div class="mt-2 text-slate-500">
                Complete el formulario para registrar un nuevo conductor en su empresa.
            </div>
        </div>
        
        @if(session('error'))
            <div class="alert alert-danger mb-4">
                {{ session('error') }}
            </div>
        @endif
        
        <div class="box p-5">
            <!-- Componente Livewire para registro por pasos -->
            <livewire:carrier.step.carrier-driver-registration-manager />
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Incluir IMask para las máscaras -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <script defer src="https://unpkg.com/@alpinejs/validate@3.x.x/dist/cdn.min.js"></script>
    <script src="https://unpkg.com/imask"></script>

    <script>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Máscara para el teléfono
            const phoneMask = IMask(document.querySelector('input[name="phone"]'), {
                mask: '(000) 000-0000'
            });

            // Máscara para la licencia (si la necesitas; ajustar formato)
            const licInput = document.querySelector('input[name="license_number"]');
            if (licInput) {
                IMask(licInput, {
                    // Ajusta la máscara según tu formato real
                    mask: 'AA-000000'
                });
            }
        });
    </script>
@endpush

