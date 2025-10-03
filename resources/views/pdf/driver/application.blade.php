{{-- resources/views/pdf/driver/application.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Application Details</title>
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

        .work-history {
            margin-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Driver Application Form</h1>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section">
        <div class="section-title">Application Details</div>
        @if($userDriverDetail->application && $userDriverDetail->application->details)
        @php
        $details = $userDriverDetail->application->details;
        @endphp
        <table>
            <tr>
                <td style="width: 50%"><strong>Applied Position</strong><br>
                    @if($details->applying_position === 'other')
                    {{ $details->applying_position_other ?? 'N/A' }}
                    @else
                    {{ $details->applying_position ?? 'N/A' }}
                    @endif
                </td>
                <td style="width: 50%"><strong>Preferred Location</strong><br>{{ $details->applying_location ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>Eligible to work in USA?</strong><br>{{ $details->eligible_to_work ? 'Yes' : 'No' }}</td>
                <td style="width: 33.33%"><strong>Can speak English?</strong><br>{{ $details->can_speak_english ? 'Yes' : 'No' }}</td>
                <td style="width: 33.33%"><strong>Has TWIC card?</strong><br>{{ $details->has_twic_card ? 'Yes' : 'No' }}</td>
            </tr>
            @if($details->has_twic_card)
            <tr>
                <td colspan="3"><strong>TWIC Expiration Date</strong><br>{{ $details->twic_expiration_date ? date('m/d/Y', strtotime($details->twic_expiration_date)) : 'N/A' }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Expected Salary</strong><br>${{ $details->expected_pay ?? 'N/A' }}</td>
                <td colspan="2"><strong>How did you hear about us?</strong><br>
                    @if($details->how_did_hear === 'other')
                    {{ $details->how_did_hear_other ?? 'N/A' }}
                    @elseif($details->how_did_hear === 'employee_referral')
                    Employee Referral: {{ $details->referral_employee_name ?? 'N/A' }}
                    @else
                    {{ $details->how_did_hear ?? 'N/A' }}
                    @endif
                </td>
            </tr>
        </table>
        @else
        <p>No application details found.</p>
        @endif
    </div>

    @if($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0)
    <div class="section">
        <div class="section-title">Work History with this Company</div>
        @foreach($userDriverDetail->workHistories as $index => $history)
        <div class="work-history">
            <h4>Work History #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Previous Company</strong><br>{{ $history->previous_company ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Position</strong><br>{{ $history->position ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Start Date</strong><br>{{ $history->start_date ? date('m/d/Y', strtotime($history->start_date)) : 'N/A' }}</td>
                    <td><strong>End Date</strong><br>{{ $history->end_date ? date('m/d/Y', strtotime($history->end_date)) : 'N/A' }}</td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Location</strong><br>{{ $history->location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Reason for Leaving</strong><br>{{ $history->reason_for_leaving ?? 'N/A' }}</td>
                    <td><strong>Reference Contact</strong><br>{{ $history->reference_contact ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        @endforeach
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