<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employment Verification Document</title>
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
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24px;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .section h2 {
            margin-top: 0;
            font-size: 18px;
            color: #2563eb;
        }
        .info-group {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            display: block;
        }
        .signature-container {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-image {
            max-width: 300px;
            margin-top: 10px;
        }
        .verification-details {
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EMPLOYMENT VERIFICATION DOCUMENT</h1>
        <p>Verification Date: {{ now()->format('m/d/Y') }}</p>
    </div>

    <div class="section">
        <h2>APPLICANT INFORMATION</h2>
        <div class="info-group">
            <span class="info-label">Applicant Name:</span>
            {{ $driver->user->name }} {{ $driver->last_name }}
        </div>
        <div class="info-group">
            <span class="info-label">SSN:</span>
            @if(isset($ssn))
                @php
                    // Mostrar solo los últimos 4 dígitos del SSN
                    $length = strlen($ssn);
                    $visibleChars = 4; // Cantidad de caracteres a mostrar
                    $maskedLength = $length - $visibleChars;
                    $maskedSSN = '';
                    
                    // Si el formato tiene guiones (como 123-45-6789), mantenerlos
                    if (strpos($ssn, '-') !== false) {
                        $parts = explode('-', $ssn);
                        if(count($parts) === 3) {
                            // Formato típico xxx-xx-xxxx
                            $maskedSSN = 'XXX-XX-' . $parts[2];
                        } else {
                            // Otro formato con guiones, enmascarar todo excepto los últimos 4
                            $lastPart = end($parts);
                            $maskedSSN = str_repeat('*', $maskedLength) . substr($ssn, -$visibleChars);
                        }
                    } else {
                        // Sin guiones, simplemente enmascarar todo excepto los últimos 4
                        $maskedSSN = str_repeat('*', $maskedLength) . substr($ssn, -$visibleChars);
                    }
                @endphp
                {{ $maskedSSN }}
            @else
                Not available
            @endif
        </div>
    </div>

    <div class="section">
        <h2>EMPLOYMENT DETAILS</h2>
        <div class="info-group">
            <span class="info-label">Company Name:</span>
            {{ $employmentCompany->company->name }}
        </div>
        <div class="info-group">
            <span class="info-label">Employment Period:</span>
            {{ $employmentCompany->employed_from->format('m/d/Y') }} to {{ $employmentCompany->employed_to->format('m/d/Y') }}
        </div>
        <div class="info-group">
            <span class="info-label">Position(s) Held:</span>
            {{ $employmentCompany->positions_held }}
        </div>
        <div class="info-group">
            <span class="info-label">Reason for Leaving:</span>
            {{ $employmentCompany->reason_for_leaving }}
        </div>
    </div>

    <div class="section">
        <h2>SAFETY PERFORMANCE HISTORY</h2>
        <table>
            <tr>
                <th>Question</th>
                <th>Response</th>
            </tr>
            <tr>
                <td>Employment dates confirmed?</td>
                <td>{{ $safetyPerformanceData['dates_confirmed'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Did the applicant drive commercial motor vehicles?</td>
                <td>{{ $safetyPerformanceData['drove_commercial'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Was the applicant a safe and efficient driver?</td>
                <td>{{ $safetyPerformanceData['safe_driver'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Was the applicant involved in any vehicle accidents?</td>
                <td>{{ $safetyPerformanceData['had_accidents'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Reason for leaving confirmed?</td>
                <td>{{ $safetyPerformanceData['reason_confirmed'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Any positive drug tests?</td>
                <td>{{ $safetyPerformanceData['positive_drug_test'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Any positive alcohol tests?</td>
                <td>{{ $safetyPerformanceData['positive_alcohol_test'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Any refused tests?</td>
                <td>{{ $safetyPerformanceData['refused_test'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td>Completed rehabilitation program?</td>
                <td>{{ isset($safetyPerformanceData['completed_rehab']) ? ($safetyPerformanceData['completed_rehab'] ? 'Yes' : 'No') : 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h2>VERIFICATION NOTES</h2>
        <p>{{ $employmentCompany->verification_notes ?: 'No additional notes provided.' }}</p>
    </div>

    <div class="signature-container">
        <div class="info-group">
            <span class="info-label">Verified By:</span>
            {{ $verification_by ?? $verification->verification_by ?? 'Not specified' }}
        </div>
        <div class="info-group">
            <span class="info-label">Signature:</span><br>
            <img src="{{ $signature }}" alt="Signature" class="signature-image">
        </div>
        <div class="verification-details">
            <p>This document was electronically signed on {{ now()->format('m/d/Y \a\t h:i A') }}.</p>
            <p>Verification Token: {{ $verification->token }}</p>
        </div>
    </div>
</body>
</html>
