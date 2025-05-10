<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Report - {{ $dateRange }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        h1 {
            font-size: 24px;
            color: #333;
            margin: 0 0 5px;
        }
        h2 {
            font-size: 18px;
            color: #666;
            margin: 0 0 20px;
            font-weight: normal;
        }
        .summary-boxes {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
        }
        .summary-box {
            flex: 1;
            min-width: 200px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .summary-box h3 {
            font-size: 14px;
            margin: 0 0 10px;
            color: #555;
        }
        .summary-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #3F51B5;
        }
        .status-pills {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }
        .pill {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
        }
        .pill.active {
            background-color: rgba(76, 175, 80, 0.2);
            color: #388E3C;
        }
        .pill.pending {
            background-color: rgba(255, 193, 7, 0.2);
            color: #F57F17;
        }
        .pill.inactive, .pill.out-of-service {
            background-color: rgba(244, 67, 54, 0.2);
            color: #D32F2F;
        }
        .pill.upcoming {
            background-color: rgba(255, 152, 0, 0.2);
            color: #E65100;
        }
        .pill.completed {
            background-color: rgba(76, 175, 80, 0.2);
            color: #388E3C;
        }
        .pill.overdue {
            background-color: rgba(244, 67, 54, 0.2);
            color: #D32F2F;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        table th {
            background-color: #f5f5f5;
            padding: 10px;
            text-align: left;
            font-weight: bold;
            color: #555;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 50px;
            padding-top: 15px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EF Services Dashboard Report</h1>
        <h2>{{ $dateRange }}</h2>
        <p>Generated on: {{ $generatedAt }}</p>
    </div>

    <div class="summary-boxes">
        <div class="summary-box">
            <h3>Vehicles Summary</h3>
            <div class="value">{{ number_format($totalVehicles) }}</div>
            <div class="status-pills">
                <span class="pill active">Active: {{ number_format($activeVehicles) }}</span>
                <span class="pill pending">Suspended: {{ number_format($suspendedVehicles) }}</span>
                <span class="pill out-of-service">Out of Service: {{ number_format($outOfServiceVehicles) }}</span>
            </div>
        </div>
        
        <div class="summary-box">
            <h3>Maintenance Summary</h3>
            <div class="value">{{ number_format($totalMaintenance) }}</div>
            <div class="status-pills">
                <span class="pill completed">Completed: {{ number_format($completedMaintenance) }}</span>
                <span class="pill pending">Pending: {{ number_format($pendingMaintenance) }}</span>
                <span class="pill upcoming">Upcoming: {{ number_format($upcomingMaintenance) }}</span>
                <span class="pill overdue">Overdue: {{ number_format($overdueMaintenance) }}</span>
            </div>
        </div>
    </div>

    <h3>Recent Vehicles</h3>
    <table>
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Year</th>
                <th>VIN</th>
                <th>Carrier</th>
                <th>Status</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentVehicles as $vehicle)
            <tr>
                <td>{{ $vehicle['make'] . ' ' . $vehicle['model'] }}</td>
                <td>{{ $vehicle['year'] }}</td>
                <td>{{ \Illuminate\Support\Str::limit($vehicle['vin'], 10) }}</td>
                <td>{{ $vehicle['carrier'] }}</td>
                <td><span class="pill {{ strtolower(str_replace(' ', '-', $vehicle['status']['label'])) }}">{{ $vehicle['status']['label'] }}</span></td>
                <td>{{ $vehicle['created_at'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <h3>Recent Maintenance</h3>
    <table>
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Service Date</th>
                <th>Next Service</th>
                <th>Cost</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentMaintenance as $maintenance)
            <tr>
                <td>{{ $maintenance['vehicle'] }}</td>
                <td>{{ $maintenance['service_date'] }}</td>
                <td>{{ $maintenance['next_service_date'] }}</td>
                <td>{{ $maintenance['cost'] }}</td>
                <td><span class="pill {{ strtolower(str_replace(' ', '-', $maintenance['status']['label'])) }}">{{ $maintenance['status']['label'] }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>© {{ date('Y') }} EF Services. All rights reserved.</p>
        <p>This report is generated automatically and provides an overview of your fleet's current status.</p>
    </div>
</body>
</html>
