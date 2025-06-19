<x-guest-layout>
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
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="bg-blue-600 px-6 py-4">
            <h1 class="text-white text-2xl font-bold">Employment Verification Request</h1>
        </div>
        
        <div class="p-6">
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Dear {{ $masterCompany ? $masterCompany->company_name : $employmentCompany->company_name ?? $verification->company_name }},</h2>
                
                <p class="mb-4">
                    {{ $driver->user->name }} {{ $driver->last_name }} has listed your company as a previous employer in their employment history.
                    As part of our verification process, we kindly request your confirmation of the following employment details:
                </p>
                
                <div class="bg-gray-100 p-4 rounded-lg mb-6">
                    <h2 class="text-lg font-bold mb-4 text-center">SAFETY PERFORMANCE HISTORY INVESTIGATION</h2>
                    <h3 class="font-semibold mb-2 text-center">PREVIOUS USDOT REGULATED EMPLOYERS</h3>
                    <p class="mb-4 text-sm">In accordance with 49 CFR 40.25 and 391.23, we are hereby requesting that you supply us with the Safety Performance History of this individual. Under DOT rule 391.23(g), you must respond to this inquiry within 30 days of receipt.</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <p><span class="font-medium">Applicant Name:</span><br>{{ $driver->user->name }} {{ $driver->last_name }}</p>
                        </div>
                        <div>
                            <p><span class="font-medium">SSN:</span><br>{{ $ssn ?? 'Not available' }}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <p><span class="font-medium">Employment Dates:</span><br>{{ $employmentCompany->employed_from }} - {{ $employmentCompany->employed_to }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p><span class="font-medium">Position Held:</span> {{ $employmentCompany->positions_held }}</p>
                    </div>
                    
                    @if($employmentCompany->subject_to_fmcsr)
                    <p class="mt-3">
                        <span class="font-medium">FMCSR:</span> The driver has indicated that they were subject to Federal Motor Carrier Safety Regulations (FMCSR) while employed at your company.
                    </p>
                    @endif
                    
                    @if($employmentCompany->safety_sensitive_function)
                    <p class="mt-1">
                        <span class="font-medium">Safety-Sensitive Functions:</span> The driver has indicated that they performed safety-sensitive functions subject to drug and alcohol testing requirements while employed at your company.
                    </p>
                    @endif
                </div>
            </div>
            
            <form action="{{ route('employment-verification.process', $token) }}" method="POST" id="verificationForm" class="space-y-6">
                @csrf
                
                <div class="space-y-6">
                    <h3 class="text-lg font-semibold">Safety Performance History Questions:</h3>
                    
                    <!-- Pregunta 1 - Fechas de empleo -->                    
                    <div class="space-y-2">
                        <label class="block font-medium">1. Are the dates of employment correct as stated above?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="dates_confirmed" id="dates_confirmed_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="dates_confirmed_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="dates_confirmed" id="dates_confirmed_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="dates_confirmed_no" class="ml-2">No</label>
                            </div>
                        </div>
                        <div id="correct_dates_container" class="mt-2 hidden">
                            <label for="correct_dates" class="block text-sm">If no, please provide the correct dates of employment:</label>
                            <input type="text" name="correct_dates" id="correct_dates" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <!-- Pregunta 2 - Conducción de vehículos comerciales -->
                    <div class="space-y-2">
                        <label class="block font-medium">2. Did the applicant drive commercial vehicles for your company?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="drove_commercial" id="drove_commercial_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="drove_commercial_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="drove_commercial" id="drove_commercial_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="drove_commercial_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 3 - Conductor seguro y eficiente -->
                    <div class="space-y-2">
                        <label class="block font-medium">3. Was the applicant a safe and efficient driver?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="safe_driver" id="safe_driver_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="safe_driver_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="safe_driver" id="safe_driver_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="safe_driver_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 4 - Accidentes -->
                    <div class="space-y-2">
                        <label class="block font-medium">4. Was the applicant involved in any vehicle accidents while employed with your company?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="had_accidents" id="had_accidents_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="had_accidents_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="had_accidents" id="had_accidents_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="had_accidents_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 5 - Razón para dejar el empleo -->
                    <div class="space-y-2">
                        <label class="block font-medium">5. Reason for leaving your employment:</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="reason_confirmed" id="reason_confirmed_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="reason_confirmed_yes" class="ml-2">Confirm: {{ $employmentCompany->reason_for_leaving }}</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="reason_confirmed" id="reason_confirmed_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="reason_confirmed_no" class="ml-2">Different reason</label>
                            </div>
                        </div>
                        <div id="different_reason_container" class="mt-2 hidden">
                            <label for="different_reason" class="block text-sm">Please specify the correct reason:</label>
                            <input type="text" name="different_reason" id="different_reason" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                    </div>
                    
                    <!-- Pregunta 6 - Test positivo de sustancias controladas -->
                    <div class="space-y-2">
                        <label class="block font-medium">6. Has the applicant tested positive for a controlled substance in the last three (3) years?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="positive_drug_test" id="positive_drug_test_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="positive_drug_test_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="positive_drug_test" id="positive_drug_test_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="positive_drug_test_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 7 - Test de alcohol -->
                    <div class="space-y-2">
                        <label class="block font-medium">7. Has the applicant had an alcohol test with a B.A.C. of 0.04 or greater in the last three (3) years?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="positive_alcohol_test" id="positive_alcohol_test_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="positive_alcohol_test_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="positive_alcohol_test" id="positive_alcohol_test_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="positive_alcohol_test_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 8 - Rechazo de prueba -->
                    <div class="space-y-2">
                        <label class="block font-medium">8. Has the applicant refused a required test for drugs or alcohol in the last three (3) years?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="refused_test" id="refused_test_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="refused_test_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="refused_test" id="refused_test_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="refused_test_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 9 - Programa de rehabilitación -->
                    <div class="space-y-2">
                        <label class="block font-medium">9. Did the applicant complete a substance abuse rehabilitation program, if required?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="completed_rehab" id="completed_rehab_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="completed_rehab_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="completed_rehab" id="completed_rehab_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="completed_rehab_no" class="ml-2">No</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="completed_rehab" id="completed_rehab_na" value="2" class="w-4 h-4 text-blue-600">
                                <label for="completed_rehab_na" class="ml-2">N/A</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Pregunta 10 - Otras violaciones -->
                    <div class="space-y-2">
                        <label class="block font-medium">10. Has this person ever violated any other DOT agency drug and alcohol testing regulations?</label>
                        <div class="flex space-x-6">
                            <div class="flex items-center">
                                <input type="radio" name="other_violations" id="other_violations_yes" value="1" class="w-4 h-4 text-blue-600" required>
                                <label for="other_violations_yes" class="ml-2">Yes</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" name="other_violations" id="other_violations_no" value="0" class="w-4 h-4 text-blue-600">
                                <label for="other_violations_no" class="ml-2">No</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Confirmación de empleo -->
                    <div class="flex items-center">
                        <input type="checkbox" name="employment_confirmed" id="employment_confirmed" value="1" class="w-4 h-4 text-blue-600" required>
                        <label for="employment_confirmed" class="ml-2">I confirm that this person was employed at our company</label>
                    </div>
                    
                    <!-- Verification Status -->
                    <div class="space-y-2">
                        <label for="verification_status" class="block font-medium">Verification Status:</label>
                        <select name="verification_status" id="verification_status" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                            <option value="verified">Verified - Information is correct</option>
                            <option value="rejected">Rejected - Information is incorrect</option>
                        </select>
                    </div>
                    
                    <!-- Comentarios -->
                    <div class="space-y-2">
                        <label for="verification_notes" class="block font-medium">Comments:</label>
                        <textarea name="verification_notes" id="verification_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
                    </div>
                    
                    <!-- Verificado por -->
                    <div class="space-y-2">
                        <label for="verification_by" class="block font-medium">Verified By (Full Name):</label>
                        <input type="text" name="verification_by" id="verification_by" class="w-full px-3 py-2 border border-gray-300 rounded-md" required placeholder="Enter your full name">
                    </div>
                </div>
                
                <div class="space-y-2">
                    <label class="block font-medium">Signature:</label>
                    <div class="signature-pad-container">
                        <canvas id="signature-pad"></canvas>
                        <input type="hidden" name="signature" id="signature-data">
                    </div>
                    <div class="flex space-x-2 mt-2">
                        <button type="button" id="clear-signature" class="px-3 py-1 bg-gray-200 text-gray-700 rounded-md text-sm">Clear</button>
                    </div>
                </div>
                
                <div class="pt-4">
                    <button type="button" id="submit-verification" class="w-full px-4 py-2 bg-blue-600 text-white font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Submit Verification
                    </button>
                </div>
            </form>
            
            <!-- Loading overlay -->
            <div id="loading-overlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="display: none;">
                <div class="text-center text-white">
                    <svg class="animate-spin h-10 w-10 text-white mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p>Processing verification...</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Función de ayuda para verificar si un elemento existe
    function elementExists(id) {
        const element = document.getElementById(id);
        if (!element) {
            console.warn(`Elemento con ID '${id}' no encontrado en el DOM`);
            return false;
        }
        return element;
    }
    
    // Verificar que la biblioteca SignaturePad esté cargada
    if (typeof SignaturePad === 'undefined') {
        console.error('SignaturePad library not loaded');
        return;
    }
        
    // Inicializar SignaturePad
    let signaturePad = null;
    const canvas = elementExists('signature-pad');
    
    if (canvas) {
        try {
            signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgb(255, 255, 255)',
                penColor: 'rgb(0, 0, 0)'
            });
            console.log('SignaturePad initialized successfully');
        } catch (error) {
            console.error('Error initializing SignaturePad:', error);
        }
    } else {
        console.error('Canvas element not found');
    }
    
    const loadingOverlay = elementExists('loading-overlay');
    
    // Función para redimensionar el canvas
    function resizeCanvas() {
        try {
            if (canvas && signaturePad) {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const rect = canvas.getBoundingClientRect();
                const oldData = signaturePad.isEmpty() ? null : signaturePad.toDataURL();
                
                // Redimensionar el canvas
                canvas.width = rect.width * ratio;
                canvas.height = rect.height * ratio;
                canvas.style.width = rect.width + 'px';
                canvas.style.height = rect.height + 'px';
                
                const ctx = canvas.getContext('2d');
                ctx.scale(ratio, ratio);
                
                // Limpiar el signaturePad y configurar de nuevo
                signaturePad.clear();
                
                // Si había una firma, restaurarla
                if (oldData) {
                    signaturePad.fromDataURL(oldData);
                }
                
                console.log('Canvas redimensionado correctamente');
            }
        } catch (error) {
            console.error('Error al redimensionar el canvas:', error);
        }
    }
    
    // Redimensionar el canvas al cargar
    if (canvas && signaturePad) {
        resizeCanvas();
        
        // Verificar que window exista antes de agregar el event listener
        if (typeof window !== 'undefined') {
            window.addEventListener('resize', resizeCanvas);
            console.log('Event listener de resize agregado correctamente');
        }
    }
    
    // Botón para limpiar firma
    const clearButton = elementExists('clear-signature');
    if (clearButton) {
        try {
            clearButton.addEventListener('click', function() {
                if (signaturePad) {
                    signaturePad.clear();
                    console.log('Firma limpiada correctamente');
                }
            });
        } catch (error) {
            console.error('Error al agregar evento al botón de limpiar firma:', error);
        }
    }
    
    // Manejar campos condicionales
    const datesConfirmedNo = elementExists('dates_confirmed_no');
    const datesConfirmedYes = elementExists('dates_confirmed_yes');
    const reasonConfirmedNo = elementExists('reason_confirmed_no');
    const reasonConfirmedYes = elementExists('reason_confirmed_yes');
    const correctDatesContainer = elementExists('correct_dates_container');
    const differentReasonContainer = elementExists('different_reason_container');
    
    // Inicializar los contenedores como ocultos
    if (correctDatesContainer) correctDatesContainer.style.display = 'none';
    if (differentReasonContainer) differentReasonContainer.style.display = 'none';
    
    // Agregar event listeners solo si los elementos existen
    if (datesConfirmedNo && correctDatesContainer) {
        datesConfirmedNo.addEventListener('change', function() {
            correctDatesContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    if (datesConfirmedYes && correctDatesContainer) {
        datesConfirmedYes.addEventListener('change', function() {
            if (this.checked) {
                correctDatesContainer.style.display = 'none';
            }
        });
    }
    
    if (reasonConfirmedNo && differentReasonContainer) {
        reasonConfirmedNo.addEventListener('change', function() {
            differentReasonContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    if (reasonConfirmedYes && differentReasonContainer) {
        reasonConfirmedYes.addEventListener('change', function() {
            if (this.checked) {
                differentReasonContainer.style.display = 'none';
            }
        });
    }
    
    // Submit form
    const submitButton = elementExists('submit-verification');
    if (submitButton) {
        submitButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevenir envío múltiple
            
            try {
                // Validar campos requeridos
                const employmentConfirmed = elementExists('employment_confirmed');
                if (employmentConfirmed && !employmentConfirmed.checked) {
                    alert('Please confirm employment');
                    return;
                }
                
                // Validar radio buttons requeridos
                const requiredRadioGroups = [
                    'dates_confirmed',
                    'drove_commercial',
                    'safe_driver',
                    'had_accidents',
                    'reason_confirmed',
                    'positive_drug_test',
                    'positive_alcohol_test',
                    'refused_test',
                    'completed_rehab',
                    'other_violations'
                ];
                
                let missingFields = [];
                
                requiredRadioGroups.forEach(function(groupName) {
                    if (!document.querySelector(`input[name="${groupName}"]:checked`)) {
                        missingFields.push(groupName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()));
                    }
                });
                
                if (missingFields.length > 0) {
                    alert('Please answer all required questions: ' + missingFields.join(', '));
                    return;
                }
                
                // Verificar firma
                if (signaturePad && signaturePad.isEmpty()) {
                    alert('Please provide a signature');
                    return;
                }
                
                // Mostrar overlay de carga
                if (loadingOverlay) loadingOverlay.style.display = 'flex';
                
                // Deshabilitar botón de envío
                submitButton.disabled = true;
                
                // Get signature data as PNG image
                let signatureData = '';
                if (signaturePad) {
                    signatureData = signaturePad.toDataURL('image/png');
                }
                
                // Guardar la firma en el campo oculto
                const signatureField = elementExists('signature-data');
                if (signatureField) signatureField.value = signatureData;
                
                // Crear el objeto FormData con el formulario actual
                const form = document.querySelector('form');
                if (!form) {
                    throw new Error('Form not found');
                }
            
                const formData = new FormData(form);
                
                // Asegurarse de que la firma esté incluida
                if (signatureData) formData.set('signature', signatureData);

                // Submit form via AJAX
                const url = form.getAttribute('action');
                console.log('Enviando formulario a:', url);
                
                fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    console.log('Respuesta recibida:', response.status);
                    if (!response.ok) {
                        if (response.status === 422) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Validation error');
                            });
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Datos recibidos:', data);
                    if (data.success) {
                        console.log('Redirigiendo a página de agradecimiento');
                        // Usar una URL absoluta o relativa válida
                        window.location.href = data.redirect_url || '/employment-verification/thank-you';
                    } else {
                        throw new Error(data.message || 'Unknown error occurred');
                    }
                })
                .catch(error => {
                    console.error('Error en la petición:', error);
                    alert('Ha ocurrido un error al procesar la verificación: ' + error.message);
                })
                .finally(() => {
                    // Ocultar overlay y rehabilitar botón
                    if (loadingOverlay) loadingOverlay.style.display = 'none';
                    submitButton.disabled = false;
                });
                
            } catch (error) {
                console.error('Error en el proceso de envío:', error);
                alert('Ha ocurrido un error al preparar el formulario: ' + error.message);
                if (loadingOverlay) loadingOverlay.style.display = 'none';
                submitButton.disabled = false;
            }
        });
    }
});
</script>
</x-guest-layout>
