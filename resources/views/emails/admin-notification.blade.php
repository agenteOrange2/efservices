<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 12px;
        }
        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .info-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            color: #212529;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
    </div>
    
    <div class="content">
        <p>{{ $message }}</p>
        
        <div class="info-box">
            <h3>Detalles de la Notificación</h3>
            
            <div class="info-row">
                <span class="label">Tipo de Evento:</span>
                <span class="value">{{ ucfirst(str_replace('_', ' ', $eventType)) }}</span>
            </div>
            
            <div class="info-row">
                <span class="label">Usuario:</span>
                <span class="value">{{ $user->name }} ({{ $user->email }})</span>
            </div>
            
            @if($carrier)
            <div class="info-row">
                <span class="label">Carrier:</span>
                <span class="value">{{ $carrier->company_name }}</span>
            </div>
            @endif
            
            @if($step)
            <div class="info-row">
                <span class="label">Paso:</span>
                <span class="value">{{ $step }}</span>
            </div>
            @endif
            
            <div class="info-row">
                <span class="label">Fecha y Hora:</span>
                <span class="value">{{ now()->format('d/m/Y H:i:s') }}</span>
            </div>
        </div>
        
        @if(!empty($data))
        <div class="info-box">
            <h3>Información Adicional</h3>
            @foreach($data as $key => $value)
                @if(!is_array($value) && !is_object($value))
                <div class="info-row">
                    <span class="label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span class="value">{{ $value }}</span>
                </div>
                @endif
            @endforeach
        </div>
        @endif
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="{{ config('app.url') }}/admin/notifications" class="btn">
                Ver Notificaciones en el Sistema
            </a>
        </div>
    </div>
    
    <div class="footer">
        <p>Este es un correo automático del sistema EFCTS.</p>
        <p>No responda a este correo.</p>
        <p>&copy; {{ date('Y') }} EFCTS - Todos los derechos reservados</p>
    </div>
</body>
</html>