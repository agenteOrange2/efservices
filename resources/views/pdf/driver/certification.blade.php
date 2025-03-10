<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Certificación</title>
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
        .content {
            margin-bottom: 10px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>Formulario de Solicitud de Conductor</h1>
        <h2>{{ $title }}</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Certificación de la Aplicación</div>
        <div class="content">
            <p>Esto certifica que esta solicitud fue completada por mí, y que todas las entradas e información en ella son verdaderas y completas según mi mejor conocimiento.</p>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">Investigación de Historial de Desempeño de Seguridad — Empleadores Anteriores Regulados por USDOT</div>
        <div class="content">
            <p>Por la presente, autorizo específicamente la divulgación de la siguiente información a la compañía especificada y sus agentes con fines de investigación según lo requerido por §391.23 y §40.321(b) de las Regulaciones Federales de Seguridad de Vehículos Motorizados. Por la presente, se le libera de toda responsabilidad que pueda resultar de proporcionar dicha información.</p>
        </div>
        
        @if($userDriverDetail->employmentCompanies && $userDriverDetail->employmentCompanies->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Nombre de la Empresa</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Código Postal</th>
                        <th>Empleado Desde</th>
                        <th>Empleado Hasta</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userDriverDetail->employmentCompanies as $company)
                        <tr>
                            <td>{{ $company->company_name ?? 'N/A' }}</td>
                            <td>{{ $company->address ?? 'N/A' }}</td>
                            <td>{{ $company->city ?? 'N/A' }}</td>
                            <td>{{ $company->state ?? 'N/A' }}</td>
                            <td>{{ $company->zip ?? 'N/A' }}</td>
                            <td>{{ $company->employed_from ? date('d/m/Y', strtotime($company->employed_from)) : 'N/A' }}</td>
                            <td>{{ $company->employed_to ? date('d/m/Y', strtotime($company->employed_to)) : 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No hay historial de empleo disponible.</p>
        @endif
    </div>
    
    @if($userDriverDetail->certification)
        <div class="section">
            <div class="section-title">Detalles de Certificación</div>
            <div class="field">
                <span class="label">Fecha de Firma:</span>
                <span class="value">{{ $userDriverDetail->certification->signed_at ? date('d/m/Y H:i:s', strtotime($userDriverDetail->certification->signed_at)) : 'N/A' }}</span>
            </div>
            <div class="field">
                <span class="label">¿Aceptó los términos?:</span>
                <span class="value">{{ $userDriverDetail->certification->is_accepted ? 'Sí' : 'No' }}</span>
            </div>
        </div>
    @endif
    
    <div class="section">
        <div class="section-title">Acuerdo de Firma Electrónica</div>
        <div class="content">
            <p>Al firmar a continuación, acepto usar una firma electrónica y reconozco que una firma electrónica es tan legalmente vinculante como una firma en tinta.</p>
        </div>
    </div>
    
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