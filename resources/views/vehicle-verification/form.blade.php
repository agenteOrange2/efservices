<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vehicle Verification - EFCTS</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .signature-pad-container {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            position: relative;
            width: 100%;
            height: 220px;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }
        .signature-pad-container:hover {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        #signature-pad {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            border-radius: 10px;
        }
        .ef-gradient {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
        }
        .card-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen">
    <div class="max-w-5xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white card-shadow rounded-2xl overflow-hidden fade-in">
            <!-- Header -->
            <div class="ef-gradient px-8 py-8 relative overflow-hidden">
                <div class="absolute inset-0 bg-black opacity-5"></div>
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="bg-white bg-opacity-20 rounded-xl p-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-white text-3xl font-bold tracking-tight">EFCTS</h1>
                            <p class="text-blue-100 text-lg font-medium">Vehicle Verification Portal</p>
                        </div>
                    </div>
                    <div class="hidden md:block">
                        <div class="bg-white bg-opacity-10 rounded-lg px-4 py-2">
                            <span class="text-white text-sm font-medium">Secure Verification</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <div class="mb-10">
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-100">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Dear {{ $applicationDetails->third_party_name ?? 'Owner' }},</h2>
                        <div class="space-y-4 text-gray-700 leading-relaxed">
                            <p class="text-lg">
                                The driver <span class="font-semibold text-blue-700 bg-blue-50 px-2 py-1 rounded-md">{{ $application->user->name ?? 'Driver' }}</span> 
                                has registered a vehicle you own on the EFCTS TCP platform.
                            </p>
                            <p class="text-lg">
                                To continue with the process, we need your consent. Please review the vehicle details below and provide your digital signature if you agree.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Details -->
                <div class="bg-white border border-gray-200 rounded-xl p-8 mb-10 shadow-sm">
                    <div class="flex items-center mb-6">
                        <div class="bg-blue-100 rounded-lg p-2 mr-4">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Vehicle Information</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">Make / Brand</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $vehicle->make }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">Model</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $vehicle->model }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">Year</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $vehicle->year }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">VIN</p>
                            <p class="text-lg font-semibold text-gray-900 font-mono">{{ $vehicle->vin }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">Type</p>
                            <p class="text-lg font-semibold text-gray-900">{{ ucfirst($vehicle->type) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-1">Registration</p>
                            <p class="text-lg font-semibold text-gray-900">{{ $vehicle->registration_state }}</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $vehicle->registration_number }}</p>
                        </div>
                    </div>
                </div>

                <!-- Consent Agreement -->
                <div class="mb-10">
                    <div class="flex items-center mb-6">
                        <div class="bg-amber-100 rounded-lg p-2 mr-4">
                            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900">Consent Agreement</h3>
                    </div>
                    
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6 mb-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-amber-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h4 class="text-lg font-semibold text-amber-800 mb-2">Important Legal Notice</h4>
                                <p class="text-amber-700 leading-relaxed">
                                    By signing this form, you confirm that you are the rightful owner of the vehicle described above 
                                    and authorize <span class="font-semibold bg-amber-100 px-2 py-1 rounded-md">{{ $application->user->name ?? 'Driver' }}</span> 
                                    to use this vehicle on the EFCTS TCP platform for transportation purposes.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">
                        <div class="prose prose-gray max-w-none">
                            <p class="text-gray-700 mb-4 text-lg leading-relaxed">
                                I, <span class="font-bold text-gray-900 bg-gray-100 px-2 py-1 rounded-md">{{ $applicationDetails->third_party_name ?? 'Owner' }}</span>, 
                                declare that I am the legitimate owner of the vehicle described in this document and authorize its use on the EFCTS TCP platform.
                            </p>
                            <p class="text-gray-600 text-base">
                                I understand that this authorization will remain in effect until revoked in writing and that I am responsible for ensuring the vehicle meets all safety and regulatory requirements.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Signature Pad -->
                <form id="verification-form" class="mb-10">
                    @csrf
                    <div class="bg-white border border-gray-200 rounded-xl p-8 shadow-sm">
                        <div class="flex items-center mb-6">
                            <div class="bg-green-100 rounded-lg p-2 mr-4">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900">Digital Signature</h3>
                        </div>
                        
                        <p class="text-gray-600 mb-6 text-lg">
                            Please provide your digital signature below to confirm your consent and complete the verification process:
                        </p>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">Signature Area</label>
                            <div class="signature-pad-container relative">
                                <canvas id="signature-pad"></canvas>
                                <div class="absolute inset-0 flex items-center justify-center pointer-events-none" id="signature-placeholder">
                                    <p class="text-gray-400 text-lg font-medium">Sign here</p>
                                </div>
                            </div>
                            <input type="hidden" id="signature-data" name="signature">
                            <p class="text-sm text-gray-500 mt-2">Use your mouse, trackpad, or touch screen to sign above</p>
                        </div>
                        
                        <div class="mb-8">
                            <label class="flex items-start space-x-3 cursor-pointer group">
                                <input type="checkbox" id="agree-terms" name="agree_terms" class="h-5 w-5 text-blue-600 focus:ring-blue-500 border-gray-300 rounded mt-1 transition-all group-hover:border-blue-400">
                                <div class="flex-1">
                                    <span class="text-gray-700 font-medium group-hover:text-gray-900 transition-colors">
                                        I accept the terms and conditions
                                    </span>
                                    <p class="text-sm text-gray-500 mt-1">
                                        By checking this box, I confirm that I have read and agree to the vehicle verification terms and authorize the use of this vehicle on the EFCTS platform.
                                    </p>
                                </div>
                            </label>
                        </div>
                        
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" id="clear-signature" class="flex-1 sm:flex-none px-6 py-3 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition-all duration-200 font-medium border border-gray-200 hover:border-gray-300">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Clear Signature
                            </button>
                            <button type="submit" id="submit-btn" class="flex-1 px-8 py-3 ef-gradient text-white rounded-xl hover:shadow-lg transition-all duration-200 font-semibold text-lg">
                                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Confirm and Submit
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <p class="text-sm text-gray-500">
                    &copy; {{ date('Y') }} EFCTS. All rights reserved.
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
            const canvas = document.getElementById('signature-pad');
            const placeholder = document.getElementById('signature-placeholder');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'rgb(37, 99, 235)',
                minWidth: 2,
                maxWidth: 3,
                throttle: 16,
                minDistance: 5
            });

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                signaturePad.clear();
            }

            // Hide placeholder when user starts signing
            signaturePad.addEventListener('beginStroke', function() {
                placeholder.style.opacity = '0';
            });

            // Show placeholder if signature is cleared
            signaturePad.addEventListener('endStroke', function() {
                if (signaturePad.isEmpty()) {
                    placeholder.style.opacity = '1';
                }
            });

            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            document.getElementById('clear-signature').addEventListener('click', function() {
                signaturePad.clear();
                placeholder.style.opacity = '1';
                
                // Add visual feedback
                this.classList.add('animate-pulse');
                setTimeout(() => {
                    this.classList.remove('animate-pulse');
                }, 200);
            });

            document.getElementById('verification-form').addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = document.getElementById('submit-btn');
                const originalText = submitBtn.innerHTML;

                if (signaturePad.isEmpty()) {
                    showNotification('Please provide your signature.', 'error');
                    return;
                }

                if (!document.getElementById('agree-terms').checked) {
                    showNotification('Please accept the terms and conditions.', 'error');
                    return;
                }

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin w-5 h-5 inline-block mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                `;
                document.getElementById('loading-overlay').style.display = 'flex';

                const signatureData = signaturePad.toDataURL();
                document.getElementById('signature-data').value = signatureData;

                const formData = new FormData(this);

                fetch('{{ route("vehicle.verification.process", $verification->token) }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading-overlay').style.display = 'none';
                    if (data.success) {
                        showNotification('Verification completed successfully!', 'success');
                        setTimeout(() => {
                            window.location.href = '{{ route("vehicle.verification.thank-you", $verification->token) }}';
                        }, 1500);
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                        showNotification(data.message || 'An error occurred. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    document.getElementById('loading-overlay').style.display = 'none';
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                    console.error('Error:', error);
                    showNotification('An error occurred. Please try again.', 'error');
                });
            });

            // Enhanced notification system
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-lg transform transition-all duration-300 translate-x-full opacity-0 ${
                    type === 'success' ? 'bg-green-500 text-white' :
                    type === 'error' ? 'bg-red-500 text-white' :
                    'bg-blue-500 text-white'
                }`;
                notification.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            ${type === 'success' ? 
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>' :
                                type === 'error' ?
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>' :
                                '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>'
                            }
                        </svg>
                        <span class="font-medium">${message}</span>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full', 'opacity-0');
                }, 100);
                
                // Auto remove
                setTimeout(() => {
                    notification.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => {
                        document.body.removeChild(notification);
                    }, 300);
                }, 4000);
            }
        });
    </script>
</body>
</html>
