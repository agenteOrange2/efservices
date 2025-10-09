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
                <td>{{ $thirdPartyName }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $thirdPartyPhone }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $thirdPartyEmail }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Driver Information</h2>
        <table class="details-table">
            <tr>
                <th>Name</th>
                <td>{{ $driverName }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $driverEmail }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $driverPhone ?? 'No disponible' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>Vehicle Details</h2>
        @if($vehicle)
            <table class="details-table">
                <tr>
                    <th>Brand</th>
                    <td>{{ $vehicle->make ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Model</th>
                    <td>{{ $vehicle->model ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Year</th>
                    <td>{{ $vehicle->year ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>VIN</th>
                    <td>{{ $vehicle->vin ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>{{ $vehicle ? ucfirst($vehicle->type) : 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Registration Status</th>
                    <td>{{ $vehicle->registration_state ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Registration Number</th>
                    <td>{{ $vehicle->registration_number ?? 'N/A' }}</td>
                </tr>
            </table>
        @else
            <div class="consent-text">
                <p><em>No vehicle information available for this driver.</em></p>
            </div>
        @endif
    </div>

    <div class="section">
        <h2>Statement of Consent</h2>
        <div class="consent-text">
            <p>I, <strong>{{ $thirdPartyName }}</strong>, declare that I am the lawful owner of the vehicle described at
                    this document and authorize <strong>{{ $driverName }}</strong> to use this vehicle on the EF Services TCP platform
                     for transportation purposes.</p>
            <p>I understand that this authorization will remain in effect until revoked by in writing.</p>
            <p>I confirm that the vehicle complies with all legal and safety requirements necessary for operation on the EF Services TCP platform.</p>
        </div>
    </div>

    <div class="signature-section">
        <h2>Digital Signature</h2>
        <div class="signature-container">
            {{-- Primero intentar usar la ruta física de la firma (como en certification.blade.php) --}}
            @if (!empty($signaturePath) && file_exists($signaturePath))
                <img src="{{ $signaturePath }}" alt="Digital Signature" style="max-width: 100%; max-height: 100px;">
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
                <img src="{!! $cleanSignature !!}" alt="Digital Signature" style="max-width: 100%; max-height: 100px;">
            @else
                <div
                    style="border: 1px dashed #ccc; height: 100px; display: flex; align-items: center; justify-content: center;">
                    <p style="color: #999;">Signature not available</p>
                </div>
            @endif
        </div>
        <div class="signature-info">
            <div class="signature-name">
                <p><strong>Name:</strong> {{ $thirdPartyName }}</p>
            </div>
            <div class="signature-date">
                <p><strong>Date:</strong> {{ $date }}</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>This document was generated electronically and is valid without a handwritten signature.</p>
        <p>Generated on: {{ $signedDate }}</p>
        <p>&copy; {{ date('Y') }} EF Services. All rights reserved.</p>
    </div>
</body>

</html>
