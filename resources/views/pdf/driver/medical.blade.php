<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Calificación Médica</title>
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
    
    @if($userDriverDetail->medicalQualification)
        @php
            $medical = $userDriverDetail->medicalQualification;
        @endphp
        <div class="section">
            <div class="section-title">Información General</div>
            <div class="field">
                <span class="label">Número de Seguro Social:</span>
                <span class="value">{{ $medical->social_security_number ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">Fecha de Contratación:</span>
                <span class="value">{{ $medical->hire_date ? date('d/m/Y', strtotime($medical->hire_date)) : 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">Ubicación:</span>
                <span class="value">{{ $medical->location ?? 'N/A' }}</span>
            </div>
        </div>
        
        <div class="section">
            <div class="section-title">Estado del Conductor</div>
            <div class="field">
                <span class="label">¿Está Suspendido?:</span>
                <span class="value">{{ $medical->is_suspended ? 'Sí' : 'No' }}</span>
            </div>
            @if($medical->is_suspended)
                <div class="field">
                    <span class="label">Fecha de Suspensión:</span>
                    <span class="value">{{ $medical->suspension_date ? date('d/m/Y', strtotime($medical->suspension_date)) : 'N/A' }}</span>
                </div>
            @endif
            <div class="field">
                <span class="label">¿Está Terminado?:</span>
                <span class="value">{{ $medical->is_terminated ? 'Sí' : 'No' }}</span>
            </div>
            @if($medical->is_terminated)
                <div class="field">
                    <span class="label">Fecha de Terminación:</span>
                    <span class="value">{{ $medical->termination_date ? date('d/m/Y', strtotime($medical->termination_date)) : 'N/A' }}</span>
                </div>
            @endif
        </div>
        
        <div class="section">
            <div class="section-title">Calificación Médica</div>
            <div class="field">
                <span class="label">Nombre del Examinador Médico:</span>
                <span class="value">{{ $medical->medical_examiner_name ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">Número de Registro del Examinador:</span>
                <span class="value">{{ $medical->medical_examiner_registry_number ?? 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">Fecha de Expiración de Tarjeta Médica:</span>
                <span class="value">{{ $medical->medical_card_expiration_date ? date('d/m/Y', strtotime($medical->medical_card_expiration_date)) : 'N/A' }}</span>
            </div>
        </div>
    @else
        <div class="section">
            <p>No se encontraron datos de calificación médica.</p>
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