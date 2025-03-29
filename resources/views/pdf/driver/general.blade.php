{{-- resources/views/pdf/driver/general.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver application - General Information</title>
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

        /* Add this to your existing style block */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table-header {
            background-color: #333;
            color: white;
            font-weight: bold;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Driver Application Form</h1>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section">        
        <table>
            <tr>
                <td style="width: 75%"><strong>Applicant's Legal Name</strong><br>{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }} {{ $userDriverDetail->last_name ?? 'N/A' }}</td>
                <td style="width: 25%"><strong>Date of Application</strong><br>{{ $userDriverDetail->date_of_birth ? date('d/m/Y', strtotime($userDriverDetail->date_of_birth)) : 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Current Address</strong><br>{{ $address->address_line1 ?? 'N/A' }}</td>
                <td style="width: 16.66%"><strong>City</strong><br>{{ $address->city ?? 'N/A' }}</td>
                <td style="width: 16.66%"><strong>State</strong><br>{{ $address->state ?? 'N/A' }}</td>
                <td style="width: 16.66%"><strong>Zip</strong><br>{{ $address->zip_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Email Address</strong><br>{{ $userDriverDetail->user->email ?? 'N/A' }}</td>
                <td style="width: 25%"><strong>SSN</strong><br>{{ $userDriverDetail->medicalQualification->social_security_number ?? 'N/A' }}</td>
                <td style="width: 25%"><strong>Date of Birth</strong><br>{{ $userDriverDetail->date_of_birth ? date('m/d/Y', strtotime($userDriverDetail->date_of_birth)) : 'N/A' }}</td>
                <td style="width: 25%"><strong>Phone</strong><br>{{ $userDriverDetail->phone ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Agregar más secciones según sea necesario para este paso específico -->

    <!-- En tus vistas PDF (por ejemplo, pdf.driver.general.blade.php) -->
    <div class="signature-box">
        <div class="field">
            <span class="label">Signature:</span>
            <div>
                @if (!empty($signaturePath) && file_exists($signaturePath))
                    <img src="{{ $signaturePath }}" alt="Firma" style="max-width: 300px; max-height: 100px;" />
                @else
                    <p style="font-style: italic; color: #999;">Signature not available</p>
                @endif
            </div>
        </div>
        <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div>
    </div>
</body>

</html>
