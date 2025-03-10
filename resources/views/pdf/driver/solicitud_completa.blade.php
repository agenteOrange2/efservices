<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
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
        th, td {
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
        <h2>{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }} {{ $userDriverDetail->last_name ?? 'N/A' }}</h2>
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
    <div class="header">
        <h2>1. Información General</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Información Personal</div>
        <div class="field">
            <span class="label">Nombre:</span>
            <span class="value">{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }} {{ $userDriverDetail->last_name ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Email:</span>
            <span class="value">{{ $userDriverDetail->user->email ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Teléfono:</span>
            <span class="value">{{ $userDriverDetail->phone ?? 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Fecha de Nacimiento:</span>
            <span class="value">{{ $userDriverDetail->date_of_birth ? date('d/m/Y', strtotime($userDriverDetail->date_of_birth)) : 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Estado:</span>
            <span class="value">{{ $userDriverDetail->status_name ?? 'N/A' }}</span>
        </div>
    </div>
    
    <div class="page-number">Página 2</div>
    
    <div class="page-break"></div>
    
    <!-- Información de Direcciones -->
    <div class="header">
        <h2>2. Información de Dirección</h2>
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
            @endif
        @endif
    </div>
    
    <!-- Si hay direcciones anteriores, se incluyen aquí -->
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
                            <span class="value">{{ $address->to_date ? date('d/m/Y', strtotime($address->to_date)) : 'N/A' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
    
    <div class="page-number">Página 3</div>
    
    <!-- Continúa con este patrón para cada sección, creando una página nueva para cada una con un encabezado y contenido específico -->
    
    <!-- Última página con la certificación y firma -->
    <div class="page-break"></div>
    
    <div class="header">
        <h2>11. Certificación</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Certificación de la Aplicación</div>
        <p>Esto certifica que esta solicitud fue completada por mí, y que todas las entradas e información en ella son verdaderas y completas según mi mejor conocimiento.</p>
        
        <p>Al firmar abajo, acepto usar una firma electrónica y reconozco que una firma electrónica es tan legalmente vinculante como una firma en tinta.</p>
    </div>
    
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
    
    <div class="page-number">Página 12</div>
</body>
</html>