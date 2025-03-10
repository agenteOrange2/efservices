<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Infracciones de Tráfico</title>
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
        <div class="section-title">Información de Infracciones de Tráfico</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
            <div class="field">
                <span class="label">¿Ha tenido infracciones de tráfico en los últimos tres años?:</span>
                <span class="value">{{ $userDriverDetail->application->details->has_traffic_convictions ? 'Sí' : 'No' }}</span>
            </div>
        @endif
    </div>
    
    @if($userDriverDetail->trafficConvictions && $userDriverDetail->trafficConvictions->count() > 0)
        <div class="section">
            <div class="section-title">Infracciones de Tráfico</div>
            @foreach($userDriverDetail->trafficConvictions as $index => $conviction)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Infracción #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Fecha de Infracción:</span>
                        <span class="value">{{ $conviction->conviction_date ? date('d/m/Y', strtotime($conviction->conviction_date)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Ubicación:</span>
                        <span class="value">{{ $conviction->location ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Cargo:</span>
                        <span class="value">{{ $conviction->charge ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Penalidad:</span>
                        <span class="value">{{ $conviction->penalty ?? 'N/A' }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_traffic_convictions)
        <div class="section">
            <p>No se encontraron datos de infracciones de tráfico.</p>
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