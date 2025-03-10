<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Requisitos FMCSR</title>
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
    
    @if($userDriverDetail->fmcsrData)
        @php
            $fmcsr = $userDriverDetail->fmcsrData;
        @endphp
        <div class="section">
            <div class="section-title">Requisitos FMCSR</div>
            <div class="field">
                <span class="label">¿Actualmente descalificado según FMCSR 391.15?:</span>
                <span class="value">{{ $fmcsr->is_disqualified ? 'Sí' : 'No' }}</span>
            </div>
            @if($fmcsr->is_disqualified)
                <div class="field">
                    <span class="label">Detalles de descalificación:</span>
                    <span class="value">{{ $fmcsr->disqualified_details ?? 'N/A' }}</span>
                </div>
            @endif
            
            <div class="field">
                <span class="label">¿Su licencia ha sido suspendida o revocada?:</span>
                <span class="value">{{ $fmcsr->is_license_suspended ? 'Sí' : 'No' }}</span>
            </div>
            @if($fmcsr->is_license_suspended)
                <div class="field">
                    <span class="label">Detalles de suspensión:</span>
                    <span class="value">{{ $fmcsr->suspension_details ?? 'N/A' }}</span>
                </div>
            @endif
            
            <div class="field">
                <span class="label">¿Alguna vez le han negado una licencia?:</span>
                <span class="value">{{ $fmcsr->is_license_denied ? 'Sí' : 'No' }}</span>
            </div>
            @if($fmcsr->is_license_denied)
                <div class="field">
                    <span class="label">Detalles de negación:</span>
                    <span class="value">{{ $fmcsr->denial_details ?? 'N/A' }}</span>
                </div>
            @endif
            
            <div class="field">
                <span class="label">¿Ha dado positivo en pruebas de drogas o alcohol?:</span>
                <span class="value">{{ $fmcsr->has_positive_drug_test ? 'Sí' : 'No' }}</span>
            </div>
            @if($fmcsr->has_positive_drug_test)
                <div class="field">
                    <span class="label">Profesional de Abuso de Sustancias:</span>
                    <span class="value">{{ $fmcsr->substance_abuse_professional ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Teléfono del Profesional:</span>
                    <span class="value">{{ $fmcsr->sap_phone ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Agencia de Prueba de Retorno:</span>
                    <span class="value">{{ $fmcsr->return_duty_agency ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">¿Consiente a la divulgación de información?:</span>
                    <span class="value">{{ $fmcsr->consent_to_release ? 'Sí' : 'No' }}</span>
                </div>
            @endif
            
            <div class="field">
                <span class="label">¿Ha sido condenado por delitos en servicio?:</span>
                <span class="value">{{ $fmcsr->has_duty_offenses ? 'Sí' : 'No' }}</span>
            </div>
            @if($fmcsr->has_duty_offenses)
                <div class="field">
                    <span class="label">Fecha de condena más reciente:</span>
                    <span class="value">{{ $fmcsr->recent_conviction_date ? date('d/m/Y', strtotime($fmcsr->recent_conviction_date)) : 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Detalles de delitos:</span>
                    <span class="value">{{ $fmcsr->offense_details ?? 'N/A' }}</span>
                </div>
            @endif
            
            <div class="field">
                <span class="label">¿Consiente a la verificación de historial de conducción?:</span>
                <span class="value">{{ $fmcsr->consent_driving_record ? 'Sí' : 'No' }}</span>
            </div>
        </div>
    @else
        <div class="section">
            <p>No se encontraron datos de requisitos FMCSR.</p>
        </div>
    @endif
    
    <div class="signature-box">
        <div class="field">
            <span class="label">Firma:</span>
            <div>
                <img src="{{ $signature }}" class="signature" alt="Firma">
            </div>
        </div>
        <div class="date">
            <span class="label">Fecha:</span>
            <span class="value">{{ $date }}</span>
        </div>
    </div>
</body>
</html>