{{-- resources/views/pdf/driver/medical.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Medical Qualification</title>
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

    @if($userDriverDetail->medicalQualification)
    @php
    $medical = $userDriverDetail->medicalQualification;
    @endphp
    <div class="section">
        <div class="section-title">General Information</div>
        <table>
            <tr>
                <td style="width: 33.33%"><strong>Social Security Number</strong><br>{{ $medical->social_security_number ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>Hire Date</strong><br>{{ $medical->hire_date ? date('m/d/Y', strtotime($medical->hire_date)) : 'N/A' }}</td>
                <td style="width: 33.33%"><strong>Location</strong><br>{{ $medical->location ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Driver Status</div>
        <table>
            <tr>
                <td style="width: 50%"><strong>Is Suspended?</strong><br>{{ $medical->is_suspended ? 'Yes' : 'No' }}</td>
                @if($medical->is_suspended)
                <td style="width: 50%"><strong>Suspension Date</strong><br>{{ $medical->suspension_date ? date('m/d/Y', strtotime($medical->suspension_date)) : 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Is Terminated?</strong><br>{{ $medical->is_terminated ? 'Yes' : 'No' }}</td>
                @if($medical->is_terminated)
                <td style="width: 50%"><strong>Termination Date</strong><br>{{ $medical->termination_date ? date('m/d/Y', strtotime($medical->termination_date)) : 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Medical Qualification</div>
        <table>
            <tr>
                <td colspan="2"><strong>Medical Examiner Name</strong><br>{{ $medical->medical_examiner_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Examiner Registry Number</strong><br>{{ $medical->medical_examiner_registry_number ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Medical Card Expiration Date</strong><br>{{ $medical->medical_card_expiration_date ? date('m/d/Y', strtotime($medical->medical_card_expiration_date)) : 'N/A' }}</td>
            </tr>
        </table>
    </div>
    @else
    <div class="section">
        <p>No medical qualification data found.</p>
    </div>
    @endif

    <div class="signature-box">
        <div class="field">
            <span class="label">Signature:</span>
            <div>
                @if (!empty($signaturePath) && file_exists($signaturePath))
                <img src="{{ $signaturePath }}" alt="Signature" style="max-width: 300px; max-height: 100px;" />
                @else
                <p style="font-style: italic; color: #999;">Signature not available</p>
                @endif
            </div>
        </div>
        <!-- <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div> -->
        <!-- Document Information -->
        <div class="section">
            <div class="section-title">Document Information</div>
            <table>
                <tr>
                    <td style="width: 25%"><strong>Registration Date</strong><br>{{ isset($formatted_dates['created_at']) ? $formatted_dates['created_at'] : (isset($created_at) && $created_at ? $created_at->format('m/d/Y') : '') }}</td>
                    @if(isset($use_custom_dates) && $use_custom_dates && isset($formatted_dates['custom_created_at']) && $formatted_dates['custom_created_at'])
                    <td style="width: 25%"><strong>Custom Registration Date</strong><br>{{ $formatted_dates['custom_created_at'] }}</td>
                    @endif
                    <td style="width: 25%"><strong>Last Updated</strong><br>{{ isset($formatted_dates['updated_at']) ? $formatted_dates['updated_at'] : ($updated_at ? $updated_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 25%"><strong>Document Date</strong><br>{{ $date }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>