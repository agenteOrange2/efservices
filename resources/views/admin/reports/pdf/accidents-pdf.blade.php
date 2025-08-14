<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accident Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            font-size: 14px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            color: #6c757d;
        }
        .filters {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .filter-label {
            font-weight: bold;
            display: inline-block;
            margin-right: 15px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .summary-item {
            display: inline-block;
            margin-right: 20px;
            text-align: center;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            display: block;
        }
        .summary-label {
            font-size: 12px;
            color: #6c757d;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .footer {
            margin-top: 20px;
            padding: 10px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Accident Report</div>
        <div class="subtitle">Generated on {{ $date }}</div>
    </div>

    @if(count($filtros) > 0)
    <div class="filters">
        <div class="filter-label">Filters:</div>
        @foreach($filtros as $filtro)
            <span>{{ $filtro }}</span>
            @if(!$loop->last) | @endif
        @endforeach
    </div>
    @endif

    <div class="summary">
        <div class="summary-item">
            <span class="summary-value">{{ $totalAccidents }}</span>
            <span class="summary-label">Total Accidents</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Driver</th>
                <th>Carrier</th>
                <th>Date</th>
                <th>Location</th>
                <th>Nature of Accident</th>
                <th>Fatalities</th>
                <th>Injuries</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accidents as $accident)
                <tr>
                    <td>
                        {{ $accident->userDriverDetail->user->name ?? 'N/A' }}
                        {{ $accident->userDriverDetail->last_name ?? '' }}
                    </td>
                    <td>
                        {{ $accident->carrier->name ?? 'N/A' }}
                        <br>
                        <small>DOT: {{ $accident->carrier->dot_number ?? 'N/A' }}</small>
                    </td>
                    <td>{{ $accident->accident_date ? $accident->accident_date->format('m/d/Y') : 'N/A' }}</td>
                    <td>{{ $accident->location ?? 'N/A' }}</td>
                    <td>{{ $accident->nature_of_accident ?? 'N/A' }}</td>
                    <td style="text-align: center;">
                        @if($accident->had_fatalities)
                            <strong style="color: #dc3545;">{{ $accident->number_of_fatalities }}</strong>
                        @else
                            0
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($accident->had_injuries)
                            <strong style="color: #fd7e14;">{{ $accident->number_of_injuries }}</strong>
                        @else
                            0
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">No accidents found matching the specified criteria</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is an automatically generated report. All times are in local time zone.</p>
        <p>&copy; {{ date('Y') }} EF Services. All rights reserved.</p>
    </div>
</body>
</html>
