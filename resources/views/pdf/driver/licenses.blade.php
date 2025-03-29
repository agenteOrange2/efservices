{{-- resources/views/pdf/driver/licenses.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - License Information</title>
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

        .license-item, .experience-item {
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
        <div class="section-title">Driver's License Information</div>
        <table>
            <tr>
                <td colspan="2"><strong>Current License Number</strong><br>{{ $userDriverDetail->licenses->where('is_primary', true)->first()->current_license_number ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    @if($userDriverDetail->licenses && $userDriverDetail->licenses->count() > 0)
        <div class="section">
            <div class="section-title">Licenses</div>
            @foreach($userDriverDetail->licenses as $index => $license)
                <div class="license-item">
                    <h4>License #{{ $index + 1 }}{{ $license->is_primary ? ' (Primary)' : '' }}</h4>
                    <table>
                        <tr>
                            <td style="width: 50%"><strong>License Number</strong><br>{{ $license->license_number ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>State of Issue</strong><br>{{ $license->state_of_issue ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>License Class</strong><br>{{ $license->license_class ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>Expiration Date</strong><br>{{ $license->expiration_date ? date('m/d/Y', strtotime($license->expiration_date)) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td colspan="2"><strong>Is CDL?</strong><br>{{ $license->is_cdl ? 'Yes' : 'No' }}</td>
                        </tr>
                        @if($license->is_cdl && $license->endorsements && $license->endorsements->count() > 0)
                            <tr>
                                <td colspan="2">
                                    <strong>Endorsements</strong><br>
                                    @foreach($license->endorsements as $endorsement)
                                        {{ $endorsement->code }} ({{ $endorsement->name }}){{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    @if($userDriverDetail->experiences && $userDriverDetail->experiences->count() > 0)
        <div class="section">
            <div class="section-title">Driving Experience</div>
            @foreach($userDriverDetail->experiences as $index => $experience)
                <div class="experience-item">
                    <h4>Experience #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td style="width: 50%"><strong>Equipment Type</strong><br>{{ $experience->equipment_type ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>Years of Experience</strong><br>{{ $experience->years_experience ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Miles Driven</strong><br>{{ number_format($experience->miles_driven ?? 0) }}</td>
                            <td style="width: 50%"><strong>Requires CDL?</strong><br>{{ $experience->requires_cdl ? 'Yes' : 'No' }}</td>
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
        <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div>
    </div>
</body>

</html>