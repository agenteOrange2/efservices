{{-- resources/views/pdf/driver/criminal_history.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Criminal History Investigation</title>
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

        .fcra-form {
            border: 2px solid #333;
            padding: 15px;
            margin: 20px 0;
            background-color: #fafafa;
        }

        .fcra-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
            text-decoration: underline;
        }

        .fcra-content {
            text-align: justify;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .fcra-signature {
            margin-top: 20px;
            border-top: 1px solid #333;
            padding-top: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Criminal History Investigation</h1>
        <h2>{{ $fullName ?? 'N/A' }}</h2>
        <p>Application ID: {{ $userDriverDetail->id }}</p>
        <p>Date: {{ $date }}</p>
    </div>

    <div class="section">
        <div class="section-title">Criminal Record</div>
        <table>
            <tr>
                <td><strong>Do you have any pending criminal charges?</strong></td>
                <td>{{ $userDriverDetail->criminalHistory->has_criminal_charges ?? false ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Felonies</div>
        <table>
            <tr>
                <td><strong>Have you ever been convicted of a felony?</strong></td>
                <td>{{ $userDriverDetail->criminalHistory->has_felony_conviction ?? false ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td><strong>Do you have a minister's permit to enter/exit Canada?</strong></td>
                <td>{{ $userDriverDetail->criminalHistory->has_minister_permit ?? false ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    <div class="fcra-form">
        <div class="fcra-title">
            FAIR CREDIT REPORTING ACT DISCLOSURE AND AUTHORIZATION FORM<br>
            [FOR EMPLOYMENT PURPOSES]
        </div>

        <div class="fcra-content">
            <p> Pursuant to the Federal Fair Credit Reporting Act (FCRA), I hereby authorize my prospective
                or current employer, {{ $carrier->name ?? 'EFCTS.' }}, EF Services LLC dba EF
                Trucking Compliance Services, (EFTCS) engaged with Checkr, Inc a Consumer Reporting
                Agency (CRA), to conduct a comprehensive review of my background through a consumer
                report and/or an investigative consumer report to be generated for employment,
                promotion, reassignment or retention as an employee. I understand that the scope of the
                consumer report/investigative consumer report may include, but is not limited to, the
                following areas: verification of Social Security number; current and previous residences;
                employment history, including all personnel files; education; references; credit history and
                reports; criminal history, including records from any criminal justice agency in any or all
                federal, state or county jurisdictions; birth records; motor vehicle records, including traffic
                citations and registration; and any other
            </p>
            <p>
                I, {{ $fullName ?? 'N/A' }}, authorize the complete release of , authorize the complete release of these records or data pertaining to
                me that an individual, company, firm, corporation or public agency may have. I hereby
                authorize and request any present or former employer, school, police department,
                financial institution or other persons having personal knowledge of me to furnish my
                prospective or current employer {{ $carrier->name ?? 'EFCTS' }}, EF Services LLC
                dba EF Trucking Compliance Services, (EFTCS), engaged with Checkr, Inc. with any and all
                information in their possession regarding me in connection with an application of
                employment. I am authorizing that a photocopy of this authorization be accepted with the
                same authority as the original.
            </p>
            <p>
                I understand that, pursuant to the federal Fair Credit Reporting Act (FCRA), if any adverse
                action is to be taken based upon the consumer report, a copy of the report and a summary
                of the consumer's rights will be provided to me.
            </p>

        </div>

        <div class="fcra-signature">
            <table style="border: none;">
                <tr style="border: none;">
                    <td style="border: none; width: 50%;">
                        <strong>FCRA Consent:</strong> {{ $userDriverDetail->criminalHistory->fcra_consent ?? false ? 'Yes' : 'No' }}
                    </td>
                    <td style="border: none; width: 50%;">
                        <strong>Background Info Consent:</strong> {{ $userDriverDetail->criminalHistory->background_info_consent ?? false ? 'Yes' : 'No' }}
                    </td>
                </tr>
            </table>
        </div>
    </div>
    <div class="signature-box">
        <div class="field">
            <span class="label">Signature:</span>
            <div>
                @if (!empty($signature) && file_exists($signature))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signature)) }}" alt="Signature" style="max-width: 300px; max-height: 100px;" />
                @elseif (!empty($signaturePath) && file_exists($signaturePath))
                <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signaturePath)) }}" alt="Signature" style="max-width: 300px; max-height: 100px;" />
                @else
                <p style="font-style: italic; color: #999;">Signature not available</p>
                @endif
            </div>
        </div>
        <!-- <div class="date">
            <span class="label">Date:</span>
            <span class="value">{{ $date }}</span>
        </div> -->
        <!-- Información adicional del conductor -->
        <div class="section">
            <div class="section-title">Additional Driver Information</div>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Legal Name</strong><br>{{ $userDriverDetail->user->name ?? 'N/A' }} {{ $userDriverDetail->middle_name ?? '' }} {{ $userDriverDetail->last_name ?? '' }}</td>
                    <td style="width: 50%"><strong>Carrier</strong><br>{{ $userDriverDetail->carrier->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 25%"><strong>Registration Date</strong><br>{{ isset($formatted_dates['created_at']) ? $formatted_dates['created_at'] : (isset($created_at) && $created_at ? $created_at->format('m/d/Y') : '') }}</td>
                    @if(isset($use_custom_dates) && $use_custom_dates && isset($formatted_dates['custom_created_at']) && $formatted_dates['custom_created_at'])
                    <td style="width: 25%"><strong>Custom Registration Date</strong><br>{{ $formatted_dates['custom_created_at'] }}</td>
                    @endif
                    <td style="width: 25%"><strong>Last Updated</strong><br>{{ isset($formatted_dates['updated_at']) ? $formatted_dates['updated_at'] : ($updated_at ? $updated_at->format('m/d/Y') : 'N/A') }}</td>
                    <td style="width: 25%"><strong>Document Date</strong><br>{{ $date }}</td>
                </tr>
            </table>
        </div>
    </div>
</body>

</html>