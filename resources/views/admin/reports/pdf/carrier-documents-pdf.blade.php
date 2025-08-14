<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Carrier Documents Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
        .filters {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .filters h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        .filter-item {
            display: inline-block;
            margin-right: 20px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 11px;
        }
        td {
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .status-active {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #ffc107;
            font-weight: bold;
        }
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        .progress-bar {
            width: 50px;
            height: 8px;
            background-color: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            display: inline-block;
        }
        .progress-fill {
            height: 100%;
            background-color: #28a745;
            border-radius: 4px;
        }
        .document-stats {
            font-size: 9px;
            color: #666;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Carrier Documents Report</h1>
        <p>Generated on: {{ $date }}</p>
        @if(isset($filtros) && count($filtros) > 0)
            <p>Applied Filters: {{ implode(', ', $filtros) }}</p>
        @endif
    </div>

    <!-- Statistics -->
    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $totalCarriers }}</div>
            <div class="stat-label">Total Carriers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->where('status', 1)->count() }}</div>
            <div class="stat-label">Active Carriers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->where('status', 2)->count() }}</div>
            <div class="stat-label">Pending Carriers</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->sum('documents_count') }}</div>
            <div class="stat-label">Total Documents</div>
        </div>
    </div>

    <!-- Document Statistics -->
    <div class="stats">
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->sum('approved_documents_count') }}</div>
            <div class="stat-label">Approved Documents</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->sum('pending_documents_count') }}</div>
            <div class="stat-label">Pending Documents</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $carriers->sum('rejected_documents_count') }}</div>
            <div class="stat-label">Rejected Documents</div>
        </div>
        <div class="stat-item">
            <div class="stat-number">{{ $documentTypes->count() }}</div>
            <div class="stat-label">Document Types</div>
        </div>
    </div>

    <!-- Carriers Table -->
    <table>
        <thead>
            <tr>
                <th>Carrier Name</th>
                <th class="text-center">DOT</th>
                <th class="text-center">MC</th>
                <th class="text-center">EIN</th>
                <th class="text-center">Status</th>
                <th class="text-center">Documents</th>
                <th class="text-center">Progress</th>
                <th class="text-center">Completion</th>
            </tr>
        </thead>
        <tbody>
            @foreach($carriers as $carrier)
                <tr>
                    <td>{{ $carrier->name }}</td>
                    <td class="text-center">{{ $carrier->dot_number ?: 'N/A' }}</td>
                    <td class="text-center">{{ $carrier->mc_number ?: 'N/A' }}</td>
                    <td class="text-center">{{ $carrier->ein_number ?: 'N/A' }}</td>
                    <td class="text-center">
                        @if($carrier->status == 1)
                            <span class="status-active">Active</span>
                        @elseif($carrier->status == 2)
                            <span class="status-pending">Pending</span>
                        @else
                            <span class="status-inactive">Inactive</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <strong>{{ $carrier->documents_count }}</strong>
                        <div class="document-stats">
                            A: {{ $carrier->approved_documents_count }} |
                            P: {{ $carrier->pending_documents_count }}
                            @if($carrier->rejected_documents_count > 0)
                                | R: {{ $carrier->rejected_documents_count }}
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        @php
                            $totalDocumentTypes = $documentTypes->count();
                            $progress = $totalDocumentTypes > 0 ? ($carrier->approved_documents_count / $totalDocumentTypes) * 100 : 0;
                        @endphp
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $progress }}%"></div>
                        </div>
                    </td>
                    <td class="text-center">{{ number_format($progress, 1) }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if($carriers->count() == 0)
        <div style="text-align: center; padding: 50px; color: #666;">
            <p>No carriers found with the applied filters.</p>
        </div>
    @endif

    <div class="footer">
        <p>EF Services - Carrier Documents Report | Page 1 of 1</p>
        <p>This report contains {{ $carriers->count() }} carriers out of {{ $totalCarriers }} total carriers.</p>
    </div>
</body>
</html>