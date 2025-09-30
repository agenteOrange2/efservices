{{-- resources/views/pdf/driver/address.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Address Information</title>
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

        .previous-address {
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
        <div class="section-title">Current Address</div>
        @if($userDriverDetail->application && $userDriverDetail->application->addresses)
        @php
        $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();
        @endphp
        @if($primaryAddress)
        <table>
            <tr>
                <td style="width: 50%"><strong>Address Line 1</strong><br>{{ $primaryAddress->address_line1 ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Address Line 2</strong><br>{{ $primaryAddress->address_line2 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>City</strong><br>{{ $primaryAddress->city ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>State</strong><br>{{ $primaryAddress->state ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>Zip Code</strong><br>{{ $primaryAddress->zip_code ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>From Date</strong><br>{{ $primaryAddress->from_date ? date('m/d/Y', strtotime($primaryAddress->from_date)) : 'N/A' }}</td>
                <td style="width: 33.33%"><strong>To Date</strong><br>{{ $primaryAddress->to_date ? date('m/d/Y', strtotime($primaryAddress->to_date)) : 'Present' }}</td>
                <td style="width: 33.33%"><strong>Lived Here 3+ Years</strong><br>{{ $primaryAddress->lived_three_years ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
        @else
        <p>No primary address information found.</p>
        @endif
        @endif
    </div>

    @if($userDriverDetail->application && $userDriverDetail->application->addresses)
    @php
    $previousAddresses = $userDriverDetail->application->addresses->where('primary', false);
    @endphp
    @if(count($previousAddresses) > 0)
    <div class="section">
        <div class="section-title">Previous Addresses</div>
        @foreach($previousAddresses as $index => $address)
        <div class="previous-address">
            <h4>Previous Address #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Address Line 1</strong><br>{{ $address->address_line1 ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Address Line 2</strong><br>{{ $address->address_line2 ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 33.33%"><strong>City</strong><br>{{ $address->city ?? 'N/A' }}</td>
                    <td style="width: 33.33%"><strong>State</strong><br>{{ $address->state ?? 'N/A' }}</td>
                    <td style="width: 33.33%"><strong>Zip Code</strong><br>{{ $address->zip_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>From Date</strong><br>{{ $address->from_date ? date('m/d/Y', strtotime($address->from_date)) : 'N/A' }}</td>
                    <td style="width: 50%"><strong>To Date</strong><br>{{ $address->to_date ? date('m/d/Y', strtotime($address->to_date)) : 'Present' }}</td>
                </tr>
            </table>
        </div>
        @endforeach
    </div>
    @endif
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

        <!-- Document Information -->
        <div class="section">
            <div class="section-title">Document Information</div>
            <table>
                <tr>
                    <td style="width: 33.33%"><strong>Creation Date</strong><br>{{ isset($formatted_dates['created_at']) ? $formatted_dates['created_at'] : ($created_at ? $created_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 33.33%"><strong>Last Updated</strong><br>{{ isset($formatted_dates['updated_at']) ? $formatted_dates['updated_at'] : ($updated_at ? $updated_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 33.33%"><strong>Document Date</strong><br>{{ $date }}</td>
                </tr>
            </table>
        </div>
        <!-- <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div> -->
    </div>
</body>

</html>