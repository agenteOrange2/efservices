<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carriers Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #4F46E5;
        }

        .header h1 {
            color: #4F46E5;
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 10px;
        }

        .analytics {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }

        .analytics-item {
            text-align: center;
            flex: 1;
        }

        .analytics-item .number {
            font-size: 16px;
            font-weight: bold;
            color: #4F46E5;
        }

        .analytics-item .label {
            font-size: 8px;
            color: #666;
            margin-top: 2px;
        }

        .filters {
            margin-bottom: 15px;
            padding: 8px;
            background: #e5e7eb;
            border-radius: 3px;
        }

        .filters h3 {
            font-size: 11px;
            margin-bottom: 5px;
            color: #374151;
        }

        .filters p {
            font-size: 9px;
            color: #6b7280;
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 8px;
        }

        th {
            background-color: #4F46E5;
            color: white;
            font-weight: bold;
            text-align: center;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status {
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: bold;
            text-align: center;
        }

        .status.active {
            background-color: #dcfce7;
            color: #166534;
        }

        .status.pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status.inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background-color: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(to right, #10b981, #059669);
            transition: width 0.3s ease;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #666;
            padding: 10px;
            border-top: 1px solid #ddd;
        }

        .page-break {
            page-break-after: always;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-sm {
            font-size: 9px;
        }

        .text-xs {
            font-size: 7px;
        }

        .mb-2 {
            margin-bottom: 8px;
        }

        .mt-2 {
            margin-top: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Carriers Report</h1>
        <p>Generated on {{ $generated_at }}</p>
        <p>Total records: {{ $total_carriers }}</p>
    </div>

    <!-- Analytics -->
    <div class="analytics">
        <div class="analytics-item">
            <div class="number">{{ $analytics['total_carriers'] }}</div>
            <div class="label">Total Carriers</div>
        </div>
        <div class="analytics-item">
            <div class="number">{{ $analytics['active_carriers'] }}</div>
            <div class="label">Active</div>
        </div>
        <div class="analytics-item">
            <div class="number">{{ $analytics['pending_carriers'] }}</div>
            <div class="label">Pending</div>
        </div>
        <div class="analytics-item">
            <div class="number">{{ $analytics['incomplete_carriers'] }}</div>
            <div class="label">Incomplete</div>
        </div>
        <div class="analytics-item">
            <div class="number">{{ $analytics['completion_rate'] }}%</div>
            <div class="label">Completion Rate</div>
        </div>
    </div>

    <!-- Filtros aplicados -->
    @if(!empty($filters) && array_filter($filters))
    <div class="filters">
        <h3>Filtros Aplicados:</h3>
        @if(!empty($filters['status']))
            <p><strong>Status:</strong> {{ ucfirst($filters['status']) }}</p>
        @endif
        @if(!empty($filters['date_range']['start']) && !empty($filters['date_range']['end']))
            <p><strong>Date Range:</strong> {{ $filters['date_range']['start'] }} - {{ $filters['date_range']['end'] }}</p>
        @endif
        @if(!empty($filters['expiring_soon']))
            <p><strong>Documents expiring soon:</strong> Yes</p>
        @endif
    </div>
    @endif

    <!-- Tabla de carriers -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">ID</th>
                <th style="width: 20%;">Carrier</th>
                <th style="width: 15%;">User Assign</th>
                <th style="width: 12%;">Progress</th>
                <th style="width: 8%;">Status</th>
                <th style="width: 10%;">Documents</th>
                <th style="width: 8%;">Expires</th>
                <th style="width: 12%;">Register Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($carriers as $carrier)
            <tr>
                <td class="text-center">{{ $carrier->id }}</td>
                <td>
                    <div class="font-bold">{{ $carrier->name }}</div>
                    <div class="text-xs">{{ $carrier->email ?? 'No email' }}</div>
                </td>
                <td>
                    @if($carrier->userCarriers->first())
                        <div class="text-sm">{{ $carrier->userCarriers->first()->user->name ?? 'N/A' }}</div>
                        <div class="text-xs">{{ $carrier->userCarriers->first()->user->email ?? '' }}</div>
                    @else
                        <span class="text-xs">Unassigned</span>
                    @endif
                </td>
                <td>
                    <div class="text-center mb-2">{{ $carrier->completion_percentage }}%</div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: {{ $carrier->completion_percentage }}%;"></div>
                    </div>
                </td>
                <td class="text-center">
                    @if($carrier->document_status == 'active')
                        <span class="status active">Active</span>
                    @elseif($carrier->document_status == 'pending')
                        <span class="status pending">Pending</span>
                    @else
                        <span class="status inactive">Incomplete</span>
                    @endif
                </td>
                <td class="text-center">
                    <div class="text-sm">{{ $carrier->documents_summary['approved'] ?? 0 }}/{{ $carrier->documents_summary['total'] ?? 0 }}</div>
                    <div class="text-xs">Aprobados/Total</div>
                </td>
                <td class="text-center">
                    @if(($carrier->expiring_documents ?? 0) > 0)
                        <span style="color: #dc2626; font-weight: bold;">{{ $carrier->expiring_documents }}</span>
                    @else
                        <span>0</span>
                    @endif
                </td>
                <td class="text-center text-xs">
                    {{ $carrier->created_at->format('d/m/Y') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">No carriers to display</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Report generated by EF Services - {{ now()->format('Y') }}</p>
    </div>
</body>
</html>