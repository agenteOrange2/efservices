<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Detalles de Aplicación</title>
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
        }
        .section {
            margin-bottom: 15px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Formulario de Solicitud de Conductor</h1>
        <h2>{{ $title }}</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Detalles de la Aplicación</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
            @php
                $details = $userDriverDetail->application->details;
            @endphp
            <div class="field">
                <span class="label">Posición Solicitada:</span>
                <span class="value">
                    @if($details->applying_position === 'other')
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
                <span class="value">{{ $details->eligible_to_work ? 'Sí' : 'No' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Puede hablar inglés?:</span>
                <span class="value">{{ $details->can_speak_english ? 'Sí' : 'No' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Tiene tarjeta TWIC?:</span>
                <span class="value">{{ $details->has_twic_card ? 'Sí' : 'No' }}</span>
            </div>
            @if($details->has_twic_card)
                <div class="field">
                    <span class="label">Fecha de expiración TWIC:</span>
                    <span class="value">{{ $details->twic_expiration_date ? date('d/m/Y', strtotime($details->twic_expiration_date)) : 'N/A' }}</span>
                </div>
            @endif
            <div class="field">
                <span class="label">Salario Esperado:</span>
                <span class="value">{{ $details->expected_pay ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Cómo se enteró de nosotros?:</span>
                <span class="value">
                    @if($details->how_did_hear === 'other')
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
    
    @if($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0)
        <div class="section">
            <div class="section-title">Historial de Trabajo con esta Empresa</div>
            @foreach($userDriverDetail->workHistories as $index => $history)
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
                        <span class="value">{{ $history->start_date ? date('d/m/Y', strtotime($history->start_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Fin:</span>
                        <span class="value">{{ $history->end_date ? date('d/m/Y', strtotime($history->end_date)) : 'N/A' }}</span>
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
    
    <div class="signature-box">
        <div class="field">
            <span class="label">Firma:</span>
            <div>
                @if (!empty($signaturePath) && file_exists($signaturePath))
                    <img src="{{ $signaturePath }}" alt="Firma" style="max-width: 300px; max-height: 100px;" />
                @else
                    <p style="font-style: italic; color: #999;">Firma no disponible</p>
                @endif
            </div>
        </div>
        <div class="date">
            <span class="label">Fecha:</span>
            <span class="value">{{ $date }}</span>
        </div>
    </div>
</body>
</html>