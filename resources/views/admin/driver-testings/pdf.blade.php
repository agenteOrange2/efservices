<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Drug Test Report #{{ $driverTesting->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            text-align: center;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .header h1 {
            margin: 10px 0;
            font-size: 24px;
            color: #333;
        }
        .subheader {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        .section {
            margin-bottom: 20px;
            clear: both;
        }
        .section-title {
            background-color: #f0f0f0;
            padding: 5px 10px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f7f7f7;
            padding: 5px 10px;
            text-align: left;
            width: 30%;
            font-weight: bold;
        }
        td {
            padding: 5px 10px;
        }
        .col-2 {
            width: 50%;
            float: left;
            box-sizing: border-box;
            padding-right: 15px;
        }
        .clearfix:after {
            content: "";
            display: table;
            clear: both;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }
        .badge-primary {
            background-color: #4a6cf7;
        }
        .badge-warning {
            background-color: #f6c000;
        }
        .badge-danger {
            background-color: #f1416c;
        }
        .status-passed {
            color: #1d9d74;
            font-weight: bold;
        }
        .status-failed {
            color: #f1416c;
            font-weight: bold;
        }
        .status-pending {
            color: #f6c000;
            font-weight: bold;
        }
        .footer {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            position: absolute;
            bottom: 20px;
            width: 100%;
        }
        .page-break {
            page-break-before: always;
        }
        .signature-box {
            margin-top: 50px;
            border-top: 1px solid #333;
            padding-top: 5px;
            width: 40%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DRUG & ALCOHOL TESTING REPORT</h1>
        <div>Test ID: #{{ $driverTesting->id }} | Generated on: {{ now()->format('m/d/Y h:i A') }}</div>
    </div>
    
    <div class="subheader">
        CONFIDENTIAL MEDICAL INFORMATION
    </div>

    <div class="section">
        <div class="section-title">TEST INFORMATION</div>
        <table>
            <tr>
                <th>Test Date</th>
                <td>{{ $driverTesting->test_date->format('m/d/Y') }}</td>
            </tr>
            <tr>
                <th>Test Type</th>
                <td>{{ $driverTesting->test_type }}</td>
            </tr>
            <tr>
                <th>Test Categories</th>
                <td>
                    @if($driverTesting->is_random_test)
                        <span style="color: #4a6cf7; font-weight: bold; margin-right: 5px;">RANDOM</span>
                    @endif
                    @if($driverTesting->is_post_accident_test)
                        <span style="color: #f6c000; font-weight: bold; margin-right: 5px;">POST-ACCIDENT</span>
                    @endif
                    @if($driverTesting->is_reasonable_suspicion_test)
                        <span style="color: #f1416c; font-weight: bold; margin-right: 5px;">REASONABLE SUSPICION</span>
                    @endif
                    @if(!$driverTesting->is_random_test && !$driverTesting->is_post_accident_test && !$driverTesting->is_reasonable_suspicion_test)
                        <span>None specified</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Test Location</th>
                <td>{{ $driverTesting->location ?: 'Not specified' }}</td>
            </tr>
            <tr>
                <th>Administered By</th>
                <td>{{ $driverTesting->administered_by ?: 'Not specified' }}</td>
            </tr>
            <tr>
                <th>Test Result</th>
                <td>
                    @if($driverTesting->test_result == 'passed')
                        <span class="status-passed">PASSED</span>
                    @elseif($driverTesting->test_result == 'failed')
                        <span class="status-failed">FAILED</span>
                    @else
                        <span class="status-pending">PENDING</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    {{ \App\Models\Admin\Driver\DriverTesting::getStatuses()[$driverTesting->status] }}
                </td>
            </tr>
            <tr>
                <th>Next Test Due</th>
                <td>{{ $driverTesting->next_test_due ? $driverTesting->next_test_due->format('m/d/Y') : 'Not scheduled' }}</td>
            </tr>
            <tr>
                <th>Bill To</th>
                <td>{{ $driverTesting->bill_to ?: 'Not specified' }}</td>
            </tr>
        </table>
    </div>
    
    <div class="section clearfix">
        <div class="section-title">PERSONAL INFORMATION</div>
        
        <div class="col-2">
            <table>
                <tr>
                    <th colspan="2">Driver Information</th>
                </tr>
                <tr>
                    <th>Name</th>
                    <td>{{ $driverTesting->userDriverDetail->user->name }} {{ $driverTesting->userDriverDetail->last_name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $driverTesting->userDriverDetail->user->email }}</td>
                </tr>
                <tr>
                    <th>Phone</th>
                    <td>{{ $driverTesting->userDriverDetail->phone ?: 'Not available' }}</td>
                </tr>
                <tr>
                    <th>Driver ID</th>
                    <td>{{ $driverTesting->userDriverDetail->id }}</td>
                </tr>
            </table>
        </div>
        
        <div class="col-2">
            <table>
                <tr>
                    <th colspan="2">Carrier Information</th>
                </tr>
                <tr>
                    <th>Carrier Name</th>
                    <td>{{ $driverTesting->carrier->name }}</td>
                </tr>
                <tr>
                    <th>Carrier ID</th>
                    <td>{{ $driverTesting->carrier->id }}</td>
                </tr>
                <tr>
                    <th>Requested By</th>
                    <td>{{ $driverTesting->requester_name ?: 'Not specified' }}</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="section">
        <div class="section-title">ADDITIONAL INFORMATION</div>
        <table>
            <tr>
                <th>Notes</th>
                <td>{{ $driverTesting->notes ?: 'No notes available' }}</td>
            </tr>
        </table>
    </div>

    <div class="section" style="margin-top: 50px;">
        <div style="float: left; width: 45%;">
            <div class="signature-box">
                Driver Signature
            </div>
        </div>
        <div style="float: right; width: 45%;">
            <div class="signature-box">
                Administrator Signature
            </div>
        </div>
    </div>
    
    <div class="footer">
        <p>This document is confidential and contains private health information. Unauthorized distribution is prohibited.</p>
        <p>Generated by EF Services Testing Drugs Module | Report #{{ $driverTesting->id }} | {{ now()->format('Y-m-d H:i:s') }}</p>
    </div>
</body>
</html>
