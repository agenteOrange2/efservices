<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Certification</title>
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

        .content {
            margin-bottom: 10px;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 4px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f0f0f0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Driver Application Form</h1>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section">
        <div class="section-title">Application Certification</div>
        <div class="content">
            <p>This certifies that this application was completed by me, and that all entries and information in it are true and complete to the best of my knowledge.</p>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Safety Performance History Investigation — Previous USDOT Regulated Employers</div>
        <div class="content">
            <p>I specifically authorize the release of the following information to the specified company and its agents for investigation purposes as required by §391.23 and §40.321(b) of the Federal Motor Carrier Safety Regulations. I hereby release you from all liability that may result from providing such information.</p>
        </div>

        @if($userDriverDetail->employmentCompanies && $userDriverDetail->employmentCompanies->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Company Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Zip Code</th>
                    <th>Employed From</th>
                    <th>Employed To</th>
                </tr>
            </thead>
            <tbody>
                @foreach($userDriverDetail->employmentCompanies as $company)
                <tr>
                    <td>{{ $company->company_name ?? ($company->masterCompany ? $company->masterCompany->company_name : 'N/A') }}</td>
                    <td>{{ $company->address ?? ($company->masterCompany ? $company->masterCompany->address : 'N/A') }}</td>
                    <td>{{ $company->city ?? ($company->masterCompany ? $company->masterCompany->city : 'N/A') }}</td>
                    <td>{{ $company->state ?? ($company->masterCompany ? $company->masterCompany->state : 'N/A') }}</td>
                    <td>{{ $company->zip ?? ($company->masterCompany ? $company->masterCompany->zip : 'N/A') }}</td>
                    <td>{{ $company->employed_from ? date('m/d/Y', strtotime($company->employed_from)) : 'N/A' }}</td>
                    <td>{{ $company->employed_to ? date('m/d/Y', strtotime($company->employed_to)) : 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No employment history available.</p>
        @endif
    </div>

    @if($userDriverDetail->certification)
    <div class="section">
        <div class="section-title">Certification Details</div>
        <div class="field">
            <span class="label">Signing Date:</span>
            <span class="value">{{ $userDriverDetail->certification->signed_at ? date('m/d/Y H:i:s', strtotime($userDriverDetail->certification->signed_at)) : 'N/A' }}</span>
        </div>
        <div class="field">
            <span class="label">Terms Accepted:</span>
            <span class="value">{{ $userDriverDetail->certification->is_accepted ? 'Yes' : 'No' }}</span>
        </div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">Electronic Signature Agreement</div>
        <div class="content">
            <p>By signing below, I agree to use an electronic signature and acknowledge that an electronic signature is as legally binding as an ink signature.</p>
        </div>
    </div>

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
        <!-- <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div> -->
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
    </div>
</body>

</html>