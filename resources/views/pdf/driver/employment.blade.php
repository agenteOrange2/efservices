<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Historial de Empleo</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Formulario de Solicitud de Conductor</h1>
        <h2>{{ $title }}</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Información de Historial de Empleo</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
            <div class="field">
                <span class="label">¿Ha estado desempleado en los últimos 10 años?:</span>
                <span class="value">{{ $userDriverDetail->application->details->has_unemployment_periods ? 'Sí' : 'No' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Ha completado la información de historial de empleo?:</span>
                <span class="value">{{ $userDriverDetail->application->details->has_completed_employment_history ? 'Sí' : 'No' }}</span>
            </div>
        @endif
    </div>
    
    @if($userDriverDetail->unemploymentPeriods && $userDriverDetail->unemploymentPeriods->count() > 0)
        <div class="section">
            <div class="section-title">Períodos de Desempleo</div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha de Inicio</th>
                        <th>Fecha de Fin</th>
                        <th>Comentarios</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userDriverDetail->unemploymentPeriods as $period)
                        <tr>
                            <td>{{ $period->start_date ? date('d/m/Y', strtotime($period->start_date)) : 'N/A' }}</td>
                            <td>{{ $period->end_date ? date('d/m/Y', strtotime($period->end_date)) : 'N/A' }}</td>
                            <td>{{ $period->comments ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
    
    @if($userDriverDetail->employmentCompanies && $userDriverDetail->employmentCompanies->count() > 0)
        <div class="section">
            <div class="section-title">Empresas de Empleo</div>
            @foreach($userDriverDetail->employmentCompanies as $index => $company)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Empresa #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Nombre de la Empresa:</span>
                        <span class="value">{{ $company->company_name ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Dirección:</span>
                        <span class="value">{{ $company->address ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Ciudad:</span>
                        <span class="value">{{ $company->city ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Estado:</span>
                        <span class="value">{{ $company->state ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Código Postal:</span>
                        <span class="value">{{ $company->zip ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Contacto:</span>
                        <span class="value">{{ $company->contact ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Teléfono:</span>
                        <span class="value">{{ $company->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fax:</span>
                        <span class="value">{{ $company->fax ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Empleado Desde:</span>
                        <span class="value">{{ $company->employed_from ? date('d/m/Y', strtotime($company->employed_from)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Empleado Hasta:</span>
                        <span class="value">{{ $company->employed_to ? date('d/m/Y', strtotime($company->employed_to)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Posiciones Ocupadas:</span>
                        <span class="value">{{ $company->positions_held ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Sujeto a FMCSR?:</span>
                        <span class="value">{{ $company->subject_to_fmcsr ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Función sensible de seguridad?:</span>
                        <span class="value">{{ $company->safety_sensitive_function ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Razón de Salida:</span>
                        <span class="value">
                            @if($company->reason_for_leaving === 'other')
                                {{ $company->other_reason_description ?? 'Otra' }}
                            @else
                                {{ $company->reason_for_leaving ?? 'N/A' }}
                            @endif
                        </span>
                    </div>
                    <div class="field">
                        <span class="label">Explicación:</span>
                        <span class="value">{{ $company->explanation ?? 'N/A' }}</span>
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