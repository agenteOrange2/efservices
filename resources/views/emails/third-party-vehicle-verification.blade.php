<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificación de Vehículo - EF Services</title>
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
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 200px;
        }
        h1 {
            color: #2563eb;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .vehicle-details {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .vehicle-details h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EF Services - Verificación de Vehículo</h1>
    </div>

    <div class="content">
        <p>Estimado(a) <strong>{{ $thirdPartyName }}</strong>,</p>

        <p>Por motivos de seguridad, necesitamos que verifique el registro del vehículo para su uso en la plataforma de EF Services TCP.</p>

        <p>El conductor <strong>{{ $driverName }}</strong> ha registrado un vehículo de su propiedad y necesitamos su consentimiento para continuar con el proceso.</p>

        <div class="vehicle-details">
            <h3>Detalles del Vehículo</h3>
            <p><strong>Marca:</strong> {{ $vehicleData['make'] }}</p>
            <p><strong>Modelo:</strong> {{ $vehicleData['model'] }}</p>
            <p><strong>Año:</strong> {{ $vehicleData['year'] }}</p>
            <p><strong>VIN:</strong> {{ $vehicleData['vin'] }}</p>
            <p><strong>Tipo:</strong> {{ ucfirst($vehicleData['type']) }}</p>
            <p><strong>Estado de Registro:</strong> {{ $vehicleData['registration_state'] }}</p>
            <p><strong>Número de Registro:</strong> {{ $vehicleData['registration_number'] }}</p>
        </div>

        <p>Por favor, haga clic en el botón a continuación para revisar y firmar el consentimiento:</p>

        <a href="{{ route('vehicle.verification.form', $verificationToken) }}" class="btn">Verificar Vehículo</a>

        <p>Este enlace expirará en 7 días. Si no reconoce esta solicitud, por favor ignore este correo electrónico.</p>
    </div>

    <div class="footer">
        <p>Este es un correo electrónico automático, por favor no responda a este mensaje.</p>
        <p>&copy; {{ date('Y') }} EF Services. Todos los derechos reservados.</p>
    </div>
</body>
</html>
