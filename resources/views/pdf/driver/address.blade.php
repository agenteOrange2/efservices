<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Información de Dirección</title>
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
        <div class="section-title">Dirección Actual</div>
        @if($userDriverDetail->application && $userDriverDetail->application->addresses)
            @php
                $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();
            @endphp
            @if($primaryAddress)
                <div class="field">
                    <span class="label">Dirección Línea 1:</span>
                    <span class="value">{{ $primaryAddress->address_line1 ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Dirección Línea 2:</span>
                    <span class="value">{{ $primaryAddress->address_line2 ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Ciudad:</span>
                    <span class="value">{{ $primaryAddress->city ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Estado:</span>
                    <span class="value">{{ $primaryAddress->state ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Código Postal:</span>
                    <span class="value">{{ $primaryAddress->zip_code ?? 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Desde:</span>
                    <span class="value">{{ $primaryAddress->from_date ? date('d/m/Y', strtotime($primaryAddress->from_date)) : 'N/A' }}</span>
                </div>
                <div class="field">
                    <span class="label">Hasta:</span>
                    <span class="value">{{ $primaryAddress->to_date ? date('d/m/Y', strtotime($primaryAddress->to_date)) : 'Presente' }}</span>
                </div>
                <div class="field">
                    <span class="label">¿Ha vivido aquí por 3 años o más?:</span>
                    <span class="value">{{ $primaryAddress->lived_three_years ? 'Sí' : 'No' }}</span>
                </div>
            @else
                <p>No se encontró información de dirección principal.</p>
            @endif
        @endif
    </div>
    
    @if($userDriverDetail->application && $userDriverDetail->application->addresses)
        @php
            $previousAddresses = $userDriverDetail->application->addresses->where('primary', false);
        @endphp
        @if(count($previousAddresses) > 0)
            <div class="section">
                <div class="section-title">Direcciones Anteriores</div>
                @foreach($previousAddresses as $index => $address)
                    <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                        <h4>Dirección Anterior #{{ $index + 1 }}</h4>
                        <div class="field">
                            <span class="label">Dirección Línea 1:</span>
                            <span class="value">{{ $address->address_line1 ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Dirección Línea 2:</span>
                            <span class="value">{{ $address->address_line2 ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Ciudad:</span>
                            <span class="value">{{ $address->city ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Estado:</span>
                            <span class="value">{{ $address->state ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Código Postal:</span>
                            <span class="value">{{ $address->zip_code ?? 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Desde:</span>
                            <span class="value">{{ $address->from_date ? date('d/m/Y', strtotime($address->from_date)) : 'N/A' }}</span>
                        </div>
                        <div class="field">
                            <span class="label">Hasta:</span>
                            <span class="value">{{ $address->to_date ? date('d/m/Y', strtotime($address->to_date)) : 'Presente' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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