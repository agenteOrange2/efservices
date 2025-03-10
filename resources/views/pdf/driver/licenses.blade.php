<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Información de Licencias</title>
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
        <div class="section-title">Información de Licencia de Conducir</div>
        <div class="field">
            <span class="label">Número de Licencia Actual:</span>
            <span class="value">{{ $userDriverDetail->licenses->where('is_primary', true)->first()->current_license_number ?? 'N/A' }}</span>
        </div>
    </div>
    
    @if($userDriverDetail->licenses && $userDriverDetail->licenses->count() > 0)
        <div class="section">
            <div class="section-title">Licencias</div>
            @foreach($userDriverDetail->licenses as $index => $license)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Licencia #{{ $index + 1 }}{{ $license->is_primary ? ' (Principal)' : '' }}</h4>
                    <div class="field">
                        <span class="label">Número de Licencia:</span>
                        <span class="value">{{ $license->license_number ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Estado de Emisión:</span>
                        <span class="value">{{ $license->state_of_issue ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Clase de Licencia:</span>
                        <span class="value">{{ $license->license_class ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Expiración:</span>
                        <span class="value">{{ $license->expiration_date ? date('d/m/Y', strtotime($license->expiration_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Es CDL?:</span>
                        <span class="value">{{ $license->is_cdl ? 'Sí' : 'No' }}</span>
                    </div>
                    @if($license->is_cdl && $license->endorsements && $license->endorsements->count() > 0)
                        <div class="field">
                            <span class="label">Endosos:</span>
                            <span class="value">
                                @foreach($license->endorsements as $endorsement)
                                    {{ $endorsement->code }} ({{ $endorsement->name }}){{ !$loop->last ? ', ' : '' }}
                                @endforeach
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
    
    @if($userDriverDetail->experiences && $userDriverDetail->experiences->count() > 0)
        <div class="section">
            <div class="section-title">Experiencia de Conducción</div>
            @foreach($userDriverDetail->experiences as $index => $experience)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Experiencia #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Tipo de Equipo:</span>
                        <span class="value">{{ $experience->equipment_type ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Años de Experiencia:</span>
                        <span class="value">{{ $experience->years_experience ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Millas Conducidas:</span>
                        <span class="value">{{ $experience->miles_driven ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Requiere CDL?:</span>
                        <span class="value">{{ $experience->requires_cdl ? 'Sí' : 'No' }}</span>
                    </div>
                </div>
            @endforeach
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