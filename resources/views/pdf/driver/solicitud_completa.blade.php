<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Solicitud Completa de Conductor</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            page-break-after: avoid;
        }

        .page-break {
            page-break-after: always;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
            background-color: #f0f0f0;
            padding: 5px;
        }

        .field {
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
            display: inline-block;
            width: 200px;
        }

        .value {
            display: inline-block;
        }

        .signature-box {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }

        .signature {
            max-height: 80px;
            max-width: 300px;
        }

        .date {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f0f0f0;
        }

        .toc {
            margin-bottom: 20px;
            width: 100%
        }

        .toc-item {
            margin-bottom: 5px;
        }

        .page-number {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Solicitud Completa de Conductor</h1>
        <h2>{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }}
            {{ $userDriverDetail->last_name ?? 'N/A' }}</h2>
        <p>ID de Solicitud: {{ $userDriverDetail->id }}</p>
        <p>Fecha de Envío: {{ $date }}</p>
    </div>

    <!-- Tabla de Contenido -->
    <div class="toc">
        <div class="section-title">Tabla de Contenido</div>
        <div class="toc-item">1. Información General ................................... Página 2</div>
        <div class="toc-item">2. Información de Dirección ........................... Página 3</div>
        <div class="toc-item">3. Detalles de Aplicación .............................. Página 4</div>
        <div class="toc-item">4. Licencias de Conductor ............................. Página 5</div>
        <div class="toc-item">5. Calificación Médica ................................. Página 6</div>
        <div class="toc-item">6. Escuelas de Entrenamiento ......................... Página 7</div>
        <div class="toc-item">7. Infracciones de Tráfico ............................. Página 8</div>
        <div class="toc-item">8. Registro de Accidentes ............................. Página 9</div>
        <div class="toc-item">9. Requisitos FMCSR .................................. Página 10</div>
        <div class="toc-item">10. Historial de Empleo ............................... Página 11</div>
        <div class="toc-item">11. Certificación ...................................... Página 12</div>
    </div>

    <div class="page-break"></div>

    <!-- Información General -->
    <div class="section">
        <div class="section-title">1. DRIVER APPLICANT INFORMATION</div>
        <div class="field">
            <span class="label">Applicant's Legal Name:</span>
            <span class="value">{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }}
                {{ $userDriverDetail->last_name ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Email:</span>
            <span class="value">{{ $userDriverDetail->user->email ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Phone:</span>
            <span class="value">{{ $userDriverDetail->phone ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Date of Birth:</span>
            <span
                class="value">{{ $userDriverDetail->date_of_birth ? date('d/m/Y', strtotime($userDriverDetail->date_of_birth)) : 'N/A' }}</span>
        </div>

    </div>

    <!-- Direcciones -->
    <div class="section">
        <div class="section-title">2. ADDRESSES FOR THE PAST THREE YEARS</div>
        @if ($userDriverDetail->application && $userDriverDetail->application->addresses)
            <div class="subsection">
                <h3>Dirección Principal</h3>
                @php
                    $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();
                @endphp
                @if ($primaryAddress)
                    <div class="field">
                        <span class="label">Address:</span>
                        <span class="value">{{ $primaryAddress->address_line1 }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Address 2:</span>
                        <span class="value">{{ $primaryAddress->address_line2 }}</span>
                    </div>
                    <div class="field">
                        <span class="label">City:</span>
                        <span class="value">{{ $primaryAddress->city }}, {{ $primaryAddress->state }}
                            {{ $primaryAddress->zip_code }}</span>
                    </div>
                @else
                    <p>No se encontró dirección principal</p>
                @endif
            </div>

            <div class="subsection">
                <h3>Direcciones Anteriores</h3>
                @php
                    $previousAddresses = $userDriverDetail->application->addresses->where('primary', false);
                @endphp
                @if ($previousAddresses->count() > 0)
                    @foreach ($previousAddresses as $address)
                        <div style="margin-bottom: 10px; border-bottom: 1px dotted #ccc; padding-bottom: 5px;">
                            <div class="field">
                                <span class="label">Dirección:</span>
                                <span class="value">{{ $address->address_line1 }}</span>
                            </div>
                            <div class="field">
                                <span class="label">Ciudad:</span>
                                <span class="value">{{ $address->city }}, {{ $address->state }}
                                    {{ $address->zip_code }}</span>
                            </div>
                            <div class="field">
                                <span class="label">Período:</span>
                                <span
                                    class="value">{{ $address->from_date ? date('d/m/Y', strtotime($address->from_date)) : 'N/A' }}
                                    -
                                    {{ $address->to_date ? date('d/m/Y', strtotime($address->to_date)) : 'Presente' }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p>No hay direcciones anteriores registradas</p>
                @endif
            </div>
        @else
            <p>No hay información de dirección disponible</p>
        @endif
    </div>

    {{-- Application --}}
    <div class="section">
        <div class="section-title">Detalles de la Aplicación</div>
        @if ($userDriverDetail->application && $userDriverDetail->application->details)
            @php
                $details = $userDriverDetail->application->details;
            @endphp
            <div class="field">
                <span class="label">Posición Solicitada:</span>
                <span class="value">
                    @if ($details->applying_position === 'other')
                        {{ $details->applying_position_other ?? 'N/A' }}
                    @else
                        {{ $details->applying_position ?? 'N/A' }}
                    @endif
                </span>
            </div>
            <div class="field">
                <span class="label">Ubicación Preferida:</span>
                <span class="value">{{ $details->applying_location ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Elegible para trabajar en EE.UU.?:</span>
                <span class="value">{{ $details->eligible_to_work ? 'Yes' : 'No' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Puede hablar inglés?:</span>
                <span class="value">{{ $details->can_speak_english ? 'Yes' : 'No' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Tiene tarjeta TWIC?:</span>
                <span class="value">{{ $details->has_twic_card ? 'Yes' : 'No' }}</span>
            </div>
            @if ($details->has_twic_card)
                <div class="field">
                    <span class="label">Fecha de expiración TWIC:</span>
                    <span
                        class="value">{{ $details->twic_expiration_date ? date('d/m/Y', strtotime($details->twic_expiration_date)) : 'N/A' }}</span>
                </div>
            @endif
            <div class="field">
                <span class="label">Salario Esperado:</span>
                <span class="value">{{ $details->expected_pay ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Cómo se enteró de nosotros?:</span>
                <span class="value">
                    @if ($details->how_did_hear === 'other')
                        {{ $details->how_did_hear_other ?? 'N/A' }}
                    @elseif($details->how_did_hear === 'employee_referral')
                        Referido por empleado: {{ $details->referral_employee_name ?? 'N/A' }}
                    @else
                        {{ $details->how_did_hear ?? 'N/A' }}
                    @endif
                </span>
            </div>
        @else
            <p>No se encontraron detalles de la aplicación.</p>
        @endif
    </div>

    @if ($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0)
        <div class="section">
            <div class="section-title">Historial de Trabajo con esta Empresa</div>
            @foreach ($userDriverDetail->workHistories as $index => $history)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Historial #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Compañía Anterior:</span>
                        <span class="value">{{ $history->previous_company ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Posición:</span>
                        <span class="value">{{ $history->position ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Inicio:</span>
                        <span
                            class="value">{{ $history->start_date ? date('d/m/Y', strtotime($history->start_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Fin:</span>
                        <span
                            class="value">{{ $history->end_date ? date('d/m/Y', strtotime($history->end_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Ubicación:</span>
                        <span class="value">{{ $history->location ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Razón de Salida:</span>
                        <span class="value">{{ $history->reason_for_leaving ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Contacto de Referencia:</span>
                        <span class="value">{{ $history->reference_contact ?? 'N/A' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="page-break"></div>


    <!-- Incluye más secciones según necesites, siguiendo el mismo patrón -->
    <!-- Por ejemplo: Licencias, Experiencia, etc. -->

    <!-- Sección de Certificación al final -->
    <div class="section">
        <div class="section-title">Certificación</div>
        <p>Certifico que toda la información proporcionada en esta solicitud es verdadera y completa a mi leal saber y
            entender.</p>

        <div class="signature-box">
            <div class="field">
                <span class="label">Firma:</span>
                <div>
                    @if (!empty($signaturePath) && file_exists($signaturePath))
                        <img src="{{ $signaturePath }}" alt="Firma"
                            style="max-width: 300px; max-height: 100px;" />
                    @else
                        <p style="font-style: italic; color: #999;">Firma no disponible</p>
                    @endif
                </div>
            </div>
            <div style="margin-top: 20px;">
                <span class="label">Fecha:</span>
                <span class="value">{{ $date }}</span>
            </div>
        </div>
    </div>
</body>

</html>
