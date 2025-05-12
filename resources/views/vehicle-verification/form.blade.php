<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Verification - EF Services</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        .signature-pad-container {
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            position: relative;
            width: 100%;
            height: 200px;
            background-color: white;
        }
        #signature-pad {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-xl rounded-lg overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-600 px-6 py-4">
                <h1 class="text-white text-2xl font-bold">EF Services - Vehicle Verification</h1>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">Dear {{ $applicationDetails->third_party_name ?? 'Owner' }},</h2>
                    <p class="text-gray-600 mb-4">
                        The driver <span class="font-semibold">{{ $application->user->name ?? 'Driver' }}</span> 
                        you have registered a vehicle you own on the EF Services TCP platform.
                    </p>
                    <p class="text-gray-600 mb-4">
                        To continue with the process, we need your consent. Please review the vehicle details and sign the form if you agree.
                    </p>
                </div>

                <!-- Vehicle Details -->
                <div class="bg-gray-50 rounded-lg p-6 mb-8">
                    <h3 class="text-lg font-semibold text-blue-800 mb-4">Vehicle Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm text-gray-500">Make / Brand</p>
                            <p class="font-medium">{{ $vehicle->make }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Model</p>
                            <p class="font-medium">{{ $vehicle->model }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Yer</p>
                            <p class="font-medium">{{ $vehicle->year }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">VIN</p>
                            <p class="font-medium">{{ $vehicle->vin }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Type</p>
                            <p class="font-medium">{{ ucfirst($vehicle->type) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Status</p>
                            <p class="font-medium">{{ $vehicle->registration_state }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Registration Number</p>
                            <p class="font-medium">{{ $vehicle->registration_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Consent Agreement -->
                <div class="mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Consent Agreement</h3>
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                        <p class="text-yellow-700">
                            By signing this form, you confirm that you are the rightful owner of the vehicle described above. 
                            and authorizes <span class="font-semibold">{{ $application->user->name ?? 'Driver' }}</span> 
                            to use this vehicle on the EF Services TCP platform for transportation purposes.
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-6">
                        <p class="text-gray-600 mb-4">
                            I, <span class="font-semibold">{{ $applicationDetails->third_party_name ?? 'Owner' }}</span>, declaro que soy el propietario legítimo del vehículo descrito en este documento y autorizo su uso en la plataforma de EF Services TCP.
                        </p>
                        <p class="text-gray-600 mb-4">
                            I understand that this authorization will remain in effect until revoked in writing.
                        </p>
                    </div>
                </div>

                <!-- Signature Pad -->
                <form id="verification-form" class="mb-8">
                    @csrf
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Digital Signature</h3>
                    <p class="text-gray-600 mb-4">
                        Please sign in the space below to confirm your consent:
                    </p>
                    <div class="mb-4">
                        <div class="signature-pad-container">
                            <canvas id="signature-pad"></canvas>
                        </div>
                        <input type="hidden" id="signature-data" name="signature">
                    </div>
                    <div class="mb-4">
                        <label class="flex items-center">
                            <input type="checkbox" id="agree-terms" name="agree_terms" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="ml-2 text-gray-700">I accept the terms and conditions</span>
                        </label>
                    </div>
                    <div class="flex space-x-4">
                        <button type="button" id="clear-signature" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition">
                            Delete Signature
                        </button>
                        <button type="submit" id="submit-btn" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                            Confirm and Send
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} EF Services. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 hidden" style="display: none;">
        <div class="bg-white p-6 rounded-lg shadow-xl">
            <div class="flex items-center">
                <svg class="animate-spin h-8 w-8 text-blue-600 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-lg font-medium">Processing...</span>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Signature Pad
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)'
            });

            // Handle window resize
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext("2d").scale(ratio, ratio);
                signaturePad.clear(); // Clear the canvas
            }

            window.addEventListener("resize", resizeCanvas);
            resizeCanvas(); // Initial setup

            // Clear signature button
            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
            });

            // Form submission
            document.getElementById('verification-form').addEventListener('submit', function(e) {
                e.preventDefault();

                if (signaturePad.isEmpty()) {
                    alert('Por favor, firme el documento antes de continuar.');
                    return;
                }
                
                // Verificar que se hayan aceptado los términos
                const agreeTerms = document.getElementById('agree-terms');
                if (!agreeTerms.checked) {
                    alert('Por favor, acepte los términos y condiciones para continuar.');
                    return;
                }

                // Show loading overlay
                const loadingOverlay = document.getElementById('loading-overlay');
                loadingOverlay.style.display = 'flex';

                // Get signature data as PNG image
                const signatureData = signaturePad.toDataURL('image/png');
                
                // Verificar que la firma se ha capturado correctamente
                console.log('Longitud de la firma:', signatureData.length);
                console.log('Primeros 50 caracteres de la firma:', signatureData.substring(0, 50) + '...');
                
                // Crear el objeto FormData
                const formData = new FormData();
                formData.append('signature', signatureData);
                formData.append('agree_terms', '1'); // Valor booleano true
                formData.append('_token', '{{ csrf_token() }}');

                // Submit form via AJAX
                fetch('{{ route("vehicle.verification.process", $verification->token) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.href = '{{ route("vehicle.verification.thank-you", $verification->token) }}';
                    } else {
                        alert(data.message || 'Ha ocurrido un error. Por favor, intente nuevamente.');
                        loadingOverlay.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Ha ocurrido un error al procesar la verificación. Por favor, inténtelo de nuevo.');
                    loadingOverlay.style.display = 'none';
                });
            });
        });
    </script>
</body>
</html>
