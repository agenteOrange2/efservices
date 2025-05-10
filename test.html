<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consent of Owner Third Party Company Driver</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header h1 {
            color: #2563eb;
            margin-top: 0;
            margin-bottom: 5px;
        }

        .header p {
            color: #6b7280;
            font-size: 14px;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #1e40af;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 10px;
            font-size: 18px;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
            /* Smaller font size for tables */
        }

        .details-table th,
        .details-table td {
            border: 1px solid #e5e7eb;
            padding: 10px;
            text-align: left;
        }

        .details-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 13px;
        }

        .consent-text {
            background-color: #f9fafb;
            padding: 15px;
            border: 1px solid #e5e7eb;
            border-radius: 5px;
            margin-bottom: 30px;
        }

        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-container {
            position: relative;
            height: 100px;
            display: flex;
            align-items: flex-end;
            margin-bottom: 10px;
        }

        .signature-container:after {
            content: "";
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
            border-bottom: 1px solid #000;
            width: 100%;
        }

        .signature-container img {
            max-width: 200px;
            max-height: 80px;
            margin-bottom: 5px;
        }

        .signature-info {
            display: flex;
            justify-content: space-between;
        }

        .signature-name,
        .signature-date {
            width: 45%;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>EF Services TCP</h1>
        <p>Consent of Owner Third Party Company Driver</p>
    </div>

    <div class="section">
        <h2>Owner Information</h2>
        <table class="details-table">
            <tr>
                <th>Name</th>
                <td>{{ $verification->third_party_name }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $verification->third_party_phone }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $verification->third_party_email }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Driver Information</h2>
        <table class="details-table">
            <tr>
                <th>Name</th>
                <td>{{ $driverDetails->user->name }} {{ $driverDetails->middle_name }} {{ $driverDetails->last_name }}
                </td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $driverDetails->user->email }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $driverDetails->phone ?? 'No disponible' }}</td>
            </tr>
            {{-- <tr>
                <th>ID de Conductor</th>
                <td>{{ $driverDetails->id }}</td>
            </tr> --}} 
        </table>
    </div>

    <div class="section">
        <h2>Vehicle Details</h2>
        <table class="details-table">
            <tr>
                <th>Brand</th>
                <td>{{ $vehicle->make }}</td>
            </tr>
            <tr>
                <th>Model</th>
                <td>{{ $vehicle->model }}</td>
            </tr>
            <tr>
                <th>Year</th>
                <td>{{ $vehicle->year }}</td>
            </tr>
            <tr>
                <th>VIN</th>
                <td>{{ $vehicle->vin }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>{{ ucfirst($vehicle->type) }}</td>
            </tr>
            <tr>
                <th>Registration Status</th>
                <td>{{ $vehicle->registration_state }}</td>
            </tr>
            <tr>
                <th>Registration Number</th>
                <td>{{ $vehicle->registration_number }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Statement of Consent</h2>
        <div class="consent-text">
            <p>Yo, <strong>{{ $verification->third_party_name }}</strong>, declaro que soy el propietario legítimo del
                vehículo descrito en este documento y autorizo a <strong>{{ $driverDetails->user->name }}
                    {{ $driverDetails->middle_name }} {{ $driverDetails->last_name }}</strong> a utilizar este
                vehículo en la plataforma de EF Services TCP para fines de transporte.</p>
            <p>Entiendo que esta autorización permanecerá vigente hasta que sea revocada por escrito.</p>
            <p>Confirmo que el vehículo cumple con todos los requisitos legales y de seguridad necesarios para su
                operación en la plataforma de EF Services TCP.</p>
        </div>
    </div>

    <div class="signature-section">
        <h2>Firma Digital</h2>
        <div class="signature-container">
            {{-- Primero intentar usar la ruta física de la firma (como en certification.blade.php) --}}
            @if (!empty($signaturePath) && file_exists($signaturePath))
                <img src="{{ $signaturePath }}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
                {{-- Si no hay ruta física, intentar usar los datos base64 --}}
            @elseif(isset($signatureData) && !empty($signatureData))
                @php
                    // Depurar la información de la firma
                    $signatureType = 'desconocido';
                    $signatureLength = strlen($signatureData);
                    $signatureStart = substr($signatureData, 0, 30);

                    if (strpos($signatureData, 'data:image') === 0) {
                        $signatureType = 'base64';
                        // Asegurarse de que la firma base64 esté limpia
                        $cleanSignature = $signatureData;
                    } elseif (filter_var($signatureData, FILTER_VALIDATE_URL)) {
                        $signatureType = 'url';
                        $cleanSignature = $signatureData;
                    } else {
                        $signatureType = 'raw';
                        // Convertir a base64 si no es base64 ni URL
                        $cleanSignature = 'data:image/png;base64,' . base64_encode($signatureData);
                    }
                @endphp

                {{-- Mostrar la firma según su tipo --}}
                <img src="{!! $cleanSignature !!}" alt="Firma Digital" style="max-width: 100%; max-height: 100px;">
            @else
                <div
                    style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center;">
                    <p style="color: #999;">Firma no disponible</p>
                </div>
            @endif
        </div>
        <div class="signature-info">
            <div class="signature-name">
                <p><strong>Nombre:</strong> {{ $verification->third_party_name }}</p>
            </div>
            <div class="signature-date">
                <p><strong>Fecha:</strong> {{ $date }}</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Este documento fue generado electrónicamente y es válido sin firma manuscrita.</p>
        <p>ID de Verificación: {{ $verification->token }}</p>
        <p>&copy; {{ date('Y') }} EF Services. Todos los derechos reservados.</p>
    </div>
</body>

</html>
