{{-- resources/views/pdf/driver/general.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Solicitud de Conductor - Información General</title>
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
    </div>
    
    <!-- Agregar más secciones según sea necesario para este paso específico -->
    
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