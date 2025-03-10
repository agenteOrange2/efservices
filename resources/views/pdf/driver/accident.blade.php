<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Registro de Accidentes</title>
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
        <div class="section-title">Información de Registro de Accidentes</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
            <div class="field">
                <span class="label">¿Ha tenido accidentes en los últimos tres años?:</span>
                <span class="value">{{ $userDriverDetail->application->details->has_accidents ? 'Sí' : 'No' }}</span>
            </div>
        @endif
    </div>
    
    @if($userDriverDetail->accidents && $userDriverDetail->accidents->count() > 0)
        <div class="section">
            <div class="section-title">Accidentes</div>
            @foreach($userDriverDetail->accidents as $index => $accident)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Accidente #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Fecha del Accidente:</span>
                        <span class="value">{{ $accident->accident_date ? date('d/m/Y', strtotime($accident->accident_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Naturaleza del Accidente:</span>
                        <span class="value">{{ $accident->nature_of_accident ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Hubo Lesiones?:</span>
                        <span class="value">{{ $accident->had_injuries ? 'Sí' : 'No' }}</span>
                    </div>
                    @if($accident->had_injuries)
                        <div class="field">
                            <span class="label">Número de Lesiones:</span>
                            <span class="value">{{ $accident->number_of_injuries ?? '0' }}</span>
                        </div>
                    @endif
                    <div class="field">
                        <span class="label">¿Hubo Fatalidades?:</span>
                        <span class="value">{{ $accident->had_fatalities ? 'Sí' : 'No' }}</span>
                    </div>
                    @if($accident->had_fatalities)
                        <div class="field">
                            <span class="label">Número de Fatalidades:</span>
                            <span class="value">{{ $accident->number_of_fatalities ?? '0' }}</span>
                        </div>
                    @endif
                    <div class="field">
                        <span class="label">Comentarios:</span>
                        <span class="value">{{ $accident->comments ?? 'N/A' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_accidents)
        <div class="section">
            <p>No se encontraron datos de accidentes.</p>
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