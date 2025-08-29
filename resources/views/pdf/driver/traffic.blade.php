{{-- resources/views/pdf/driver/traffic.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Traffic Violations</title>
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

        .conviction-item {
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
        <div class="section-title">Traffic Violations Information</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you had any traffic violations in the last three years?</strong><br>{{ $userDriverDetail->trafficConvictions && $userDriverDetail->trafficConvictions->count() > 0 ? 'Yes' : 'No' }}</td>                
            </tr>
        </table>
    </div>

    @if($userDriverDetail->trafficConvictions && $userDriverDetail->trafficConvictions->count() > 0)
        <div class="section">
            <div class="section-title">Traffic Violations</div>
            @foreach($userDriverDetail->trafficConvictions as $index => $conviction)
                <div class="conviction-item">
                    <h4>Violation #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td style="width: 50%"><strong>Violation Date</strong><br>{{ $conviction->conviction_date ? date('m/d/Y', strtotime($conviction->conviction_date)) : 'N/A' }}</td>
                            <td style="width: 50%"><strong>Location</strong><br>{{ $conviction->location ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Charge</strong><br>{{ $conviction->charge ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>Penalty</strong><br>{{ $conviction->penalty ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_traffic_convictions)
        <div class="section">
            <p>No traffic violation data found.</p>
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