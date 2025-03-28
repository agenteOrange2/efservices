{{-- resources/views/pdf/driver/accident.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Accident Record</title>
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

        .accident-item {
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
        <div class="section-title">Accident Record Information</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you had any accidents in the last three years?</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_accidents ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    @if($userDriverDetail->accidents && $userDriverDetail->accidents->count() > 0)
        <div class="section">
            <div class="section-title">Accidents</div>
            @foreach($userDriverDetail->accidents as $index => $accident)
                <div class="accident-item">
                    <h4>Accident #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td style="width: 50%"><strong>Accident Date</strong><br>{{ $accident->accident_date ? date('m/d/Y', strtotime($accident->accident_date)) : 'N/A' }}</td>
                            <td style="width: 50%"><strong>Nature of Accident</strong><br>{{ $accident->nature_of_accident ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 25%"><strong>Injuries Involved?</strong><br>{{ $accident->had_injuries ? 'Yes' : 'No' }}</td>
                            @if($accident->had_injuries)
                                <td style="width: 25%"><strong>Number of Injuries</strong><br>{{ $accident->number_of_injuries ?? '0' }}</td>
                            @else
                                <td style="width: 25%"></td>
                            @endif
                            <td style="width: 25%"><strong>Fatalities Involved?</strong><br>{{ $accident->had_fatalities ? 'Yes' : 'No' }}</td>
                            @if($accident->had_fatalities)
                                <td style="width: 25%"><strong>Number of Fatalities</strong><br>{{ $accident->number_of_fatalities ?? '0' }}</td>
                            @else
                                <td style="width: 25%"></td>
                            @endif
                        </tr>
                        @if($accident->comments)
                            <tr>
                                <td colspan="4"><strong>Comments</strong><br>{{ $accident->comments }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_accidents)
        <div class="section">
            <p>No accident data found.</p>
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