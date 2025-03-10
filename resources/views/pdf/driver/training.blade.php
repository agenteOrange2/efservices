<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Escuelas de Entrenamiento</title>
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
        ul {
            margin: 0;
            padding-left: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Formulario de Solicitud de Conductor</h1>
        <h2>{{ $title }}</h2>
    </div>
    
    <div class="section">
        <div class="section-title">Información de Escuelas de Entrenamiento</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
            <div class="field">
                <span class="label">¿Ha asistido a una escuela de entrenamiento para conductores comerciales?:</span>
                <span class="value">{{ $userDriverDetail->application->details->has_attended_training_school ? 'Sí' : 'No' }}</span>
            </div>
        @endif
    </div>
    
    @if($userDriverDetail->trainingSchools && $userDriverDetail->trainingSchools->count() > 0)
        <div class="section">
            <div class="section-title">Escuelas de Entrenamiento</div>
            @foreach($userDriverDetail->trainingSchools as $index => $school)
                <div style="margin-bottom: 15px; border-bottom: 1px dashed #ddd; padding-bottom: 10px;">
                    <h4>Escuela #{{ $index + 1 }}</h4>
                    <div class="field">
                        <span class="label">Nombre de la Escuela:</span>
                        <span class="value">{{ $school->school_name ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Ciudad:</span>
                        <span class="value">{{ $school->city ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Estado:</span>
                        <span class="value">{{ $school->state ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Número de Teléfono:</span>
                        <span class="value">{{ $school->phone_number ?? 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Inicio:</span>
                        <span class="value">{{ $school->date_start ? date('d/m/Y', strtotime($school->date_start)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">Fecha de Finalización:</span>
                        <span class="value">{{ $school->date_end ? date('d/m/Y', strtotime($school->date_end)) : 'N/A' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Se graduó?:</span>
                        <span class="value">{{ $school->graduated ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Sujeto a regulaciones de seguridad?:</span>
                        <span class="value">{{ $school->subject_to_safety_regulations ? 'Sí' : 'No' }}</span>
                    </div>
                    <div class="field">
                        <span class="label">¿Realizó funciones de seguridad?:</span>
                        <span class="value">{{ $school->performed_safety_functions ? 'Sí' : 'No' }}</span>
                    </div>
                    
                    @if($school->training_skills && count($school->training_skills) > 0)
                        <div class="field">
                            <span class="label">Habilidades entrenadas:</span>
                            <span class="value">
                                <ul>
                                    @foreach($school->training_skills as $skill)
                                        <li>{{ $skill }}</li>
                                    @endforeach
                                </ul>
                            </span>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_attended_training_school)
        <div class="section">
            <p>No se encontraron datos de escuelas de entrenamiento.</p>
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