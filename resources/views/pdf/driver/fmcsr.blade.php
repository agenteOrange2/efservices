{{-- resources/views/pdf/driver/fmcsr.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - FMCSR Requirements</title>
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

    @if($userDriverDetail->fmcsrData)
    @php
    $fmcsr = $userDriverDetail->fmcsrData;
    @endphp
    <div class="section">
        <div class="section-title">FMCSR Requirements</div>
        <table>
            <tr>
                <td style="width: 50%"><strong>Currently disqualified under FMCSR 391.15?</strong><br>{{ $fmcsr->is_disqualified ? 'Yes' : 'No' }}</td>
                @if($fmcsr->is_disqualified)
                <td style="width: 50%"><strong>Disqualification Details</strong><br>{{ $fmcsr->disqualified_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Has your license been suspended or revoked?</strong><br>{{ $fmcsr->is_license_suspended ? 'Yes' : 'No' }}</td>
                @if($fmcsr->is_license_suspended)
                <td style="width: 50%"><strong>Suspension Details</strong><br>{{ $fmcsr->suspension_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Have you ever been denied a license?</strong><br>{{ $fmcsr->is_license_denied ? 'Yes' : 'No' }}</td>
                @if($fmcsr->is_license_denied)
                <td style="width: 50%"><strong>Denial Details</strong><br>{{ $fmcsr->denial_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Have you tested positive for drugs or alcohol?</strong><br>{{ $fmcsr->has_positive_drug_test ? 'Yes' : 'No' }}</td>
                <td style="width: 50%"><strong>Consent to Release Information?</strong><br>{{ $fmcsr->consent_to_release ? 'Yes' : 'No' }}</td>
            </tr>
            @if($fmcsr->has_positive_drug_test)
            <tr>
                <td style="width: 50%"><strong>Substance Abuse Professional</strong><br>{{ $fmcsr->substance_abuse_professional ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Professional Phone</strong><br>{{ $fmcsr->sap_phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Return to Duty Agency</strong><br>{{ $fmcsr->return_duty_agency ?? 'N/A' }}</td>
            </tr>
            @endif
            <tr>
                <td style="width: 50%"><strong>Have you been convicted of on-duty offenses?</strong><br>{{ $fmcsr->has_duty_offenses ? 'Yes' : 'No' }}</td>
                @if($fmcsr->has_duty_offenses)
                <td style="width: 50%"><strong>Most Recent Conviction Date</strong><br>{{ $fmcsr->recent_conviction_date ? date('m/d/Y', strtotime($fmcsr->recent_conviction_date)) : 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            @if($fmcsr->has_duty_offenses)
            <tr>
                <td colspan="2"><strong>Offense Details</strong><br>{{ $fmcsr->offense_details ?? 'N/A' }}</td>
            </tr>
            @endif
            <tr>
                <td colspan="2"><strong>Consent to Driving Record Verification?</strong><br>{{ $fmcsr->consent_driving_record ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>
    @else
    <div class="section">
        <p>No FMCSR requirements data found.</p>
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
                    <td style="width: 33.33%"><strong>Creation Date</strong><br>{{ isset($formatted_dates['created_at']) ? $formatted_dates['created_at'] : ($created_at ? $created_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 33.33%"><strong>Last Updated</strong><br>{{ isset($formatted_dates['updated_at']) ? $formatted_dates['updated_at'] : ($updated_at ? $updated_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 33.33%"><strong>Document Date</strong><br>{{ $date }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>