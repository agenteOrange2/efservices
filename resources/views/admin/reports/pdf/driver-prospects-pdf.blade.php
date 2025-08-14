<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Reporte de Prospectos de Conductores</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            margin: 0;
            padding: 0;
        }
        .header {
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
        }
        .filters {
            margin-bottom: 15px;
            padding: 8px;
            background-color: #f9f9f9;
            border-radius: 3px;
            border: 1px solid #eee;
        }
        .filter-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #555;
        }
        .filter-items {
            display: flex;
            flex-wrap: wrap;
        }
        .filter-item {
            margin-right: 15px;
        }
        .filter-label {
            font-weight: bold;
            color: #777;
        }
        .filter-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 10px;
        }
        th {
            background-color: #f2f2f2;
            border: 1px solid #ddd;
            padding: 5px;
            font-weight: bold;
            text-align: left;
            color: #333;
        }
        td {
            border: 1px solid #ddd;
            padding: 5px;
            vertical-align: top;
        }
        .footer {
            margin-top: 15px;
            font-size: 10px;
            color: #666;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-draft {
            background-color: #e5e5e5;
            color: #555;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }
        .type-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
        }
        .type-company {
            background-color: #cce5ff;
            color: #004085;
        }
        .type-owner {
            background-color: #fff3cd;
            color: #856404;
        }
        .type-third {
            background-color: #d6d8ff;
            color: #4a3f8a;
        }
        .progress-bar {
            position: relative;
            width: 100%;
            height: 12px;
            background-color: #e9ecef;
            border-radius: 6px;
            margin-top: 3px;
        }
        .progress-value {
            position: absolute;
            height: 12px;
            background-color: #007bff;
            border-radius: 6px;
        }
        .progress-text {
            position: absolute;
            width: 100%;
            text-align: center;
            font-size: 8px;
            font-weight: bold;
            color: #333;
            line-height: 12px;
        }
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        .empty-state p {
            margin: 5px 0 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%; border: none;">
            <tr style="border: none;">
                <td style="border: none; width: 70%;">
                    <h1>Reporte de Prospectos de Conductores</h1>
                    <p>Conductores en proceso de reclutamiento y verificación</p>
                </td>
                <td style="border: none; text-align: right;">
                    <p><strong>Fecha:</strong> {{ $date }}</p>
                    <p><strong>Total:</strong> {{ $totalProspects }} prospectos</p>
                </td>
            </tr>
        </table>
    </div>

    @if(count($filtros) > 0)
    <div class="filters">
        <div class="filter-title">Filtros aplicados:</div>
        <div class="filter-items">
            @foreach($filtros as $key => $value)
            <div class="filter-item">
                <span class="filter-label">{{ $key }}:</span>
                <span class="filter-value">{{ $value }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(count($prospects) > 0)
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Carrier</th>
                    <th>Tipo</th>
                    <th>Estado</th>

                    <th>Fecha Creación</th>
                </tr>
            </thead>
            <tbody>
                @foreach($prospects as $prospect)
                    <tr>
                        <td>{{ $prospect->id }}</td>
                        <td>
                            @if($prospect->user)
                                {{ $prospect->user->name }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($prospect->user)
                                {{ $prospect->user->email }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($prospect->userDriverDetail && $prospect->userDriverDetail->phone)
                                {{ $prospect->userDriverDetail->phone }}
                            @elseif($prospect->user && $prospect->user->phone)
                                {{ $prospect->user->phone }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($prospect->userDriverDetail && $prospect->userDriverDetail->carrier)
                                {{ $prospect->userDriverDetail->carrier->name }}
                            @else
                                No asignado
                            @endif
                        </td>
                        <td>
                            @if($prospect->isOwnerOperator())
                                <span class="type-badge type-owner">Owner Operator</span>
                            @elseif($prospect->isThirdPartyDriver())
                                <span class="type-badge type-third">Third Party</span>
                            @else
                                <span class="type-badge type-company">Company Driver</span>
                            @endif
                        </td>
                        <td>
                            @switch($prospect->status)
                                @case('draft')
                                    <span class="status-badge status-draft">Borrador</span>
                                    @break
                                @case('pending')
                                    <span class="status-badge status-pending">Pendiente</span>
                                    @break
                                @case('rejected')
                                    <span class="status-badge status-rejected">Rechazado</span>
                                    @break
                                @default
                                    {{ $prospect->status }}
                            @endswitch
                        </td>

                        <td>{{ $prospect->created_at->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="empty-state">
            <p>No se encontraron prospectos de conductores con los filtros aplicados.</p>
        </div>
    @endif

    <div class="footer">
        <p>© {{ date('Y') }} EFServices - Reporte generado el {{ $date }}</p>
    </div>
</body>
</html>
