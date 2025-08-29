{{-- resources/views/pdf/driver/employment.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Employment History</title>
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

        .company-item {
            margin-top: 20px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Driver Application Form</h1>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section">
        <div class="section-title">Employment History Information</div>
        <table>
            <tr>
                <td style="width: 50%"><strong>Have you been unemployed in the last 10 years?</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_unemployment_periods ? 'Yes' : 'No' }}</td>
                <td style="width: 50%"><strong>Have you completed your employment history information?</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_completed_employment_history ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    @if($userDriverDetail->unemploymentPeriods && $userDriverDetail->unemploymentPeriods->count() > 0)
        <div class="section">
            <div class="section-title">Unemployment Periods</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 30%">Start Date</th>
                        <th style="width: 30%">End Date</th>
                        <th style="width: 40%">Comments</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($userDriverDetail->unemploymentPeriods as $period)
                        <tr>
                            <td>{{ $period->start_date ? date('m/d/Y', strtotime($period->start_date)) : 'N/A' }}</td>
                            <td>{{ $period->end_date ? date('m/d/Y', strtotime($period->end_date)) : 'N/A' }}</td>
                            <td>{{ $period->comments ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($userDriverDetail->employmentCompanies && $userDriverDetail->employmentCompanies->count() > 0)
        <div class="section">
            <div class="section-title">Employment Companies</div>
            @foreach($userDriverDetail->employmentCompanies as $index => $company)
                <div class="company-item">
                    <h4>Company #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td colspan="2"><strong>Company Name</strong><br>{{ $company->masterCompany->company_name ?? $company->company_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Address</strong><br>{{ $company->masterCompany->address ?? $company->address ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>City, State, ZIP</strong><br>{{ $company->masterCompany->city ?? $company->city ?? 'N/A' }}, {{ $company->masterCompany->state ?? $company->state ?? '' }} {{ $company->masterCompany->zip ?? $company->zip ?? '' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Contact</strong><br>{{ $company->masterCompany->contact ?? $company->contact ?? 'N/A' }}</td>
                            <td style="width: 50%">
                                <strong>Phone / Fax</strong><br>
                                Phone: {{ $company->masterCompany->phone ?? $company->phone ?? 'N/A' }}<br>
                                Fax: {{ $company->masterCompany->fax ?? $company->fax ?? 'N/A' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Employed From</strong><br>{{ $company->employed_from ? date('m/d/Y', strtotime($company->employed_from)) : 'N/A' }}</td>
                            <td style="width: 50%"><strong>Employed To</strong><br>{{ $company->employed_to ? date('m/d/Y', strtotime($company->employed_to)) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Positions Held</strong><br>{{ $company->positions_held ?? 'N/A' }}</td>
                            <td style="width: 50%">
                                <strong>FMCSR & Safety</strong><br>
                                Subject to FMCSR: {{ $company->subject_to_fmcsr ? 'Yes' : 'No' }}<br>
                                Safety Sensitive Function: {{ $company->safety_sensitive_function ? 'Yes' : 'No' }}
                            </td>
                        </tr>
                        <tr>
                            <td style="width: 50%">
                                <strong>Reason for Leaving</strong><br>
                                @if($company->reason_for_leaving === 'other')
                                    {{ $company->other_reason_description ?? 'Other' }}
                                @else
                                    {{ $company->reason_for_leaving ?? 'N/A' }}
                                @endif
                            </td>
                            <td style="width: 50%"><strong>Explanation</strong><br>{{ $company->explanation ?? 'N/A' }}</td>
                        </tr>
                    </table>
                </div>
            @endforeach
        </div>
    @endif

    @if(($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0) || ($userDriverDetail->driver_related_employments && $userDriverDetail->driver_related_employments->count() > 0))
        <div class="section">
            <div class="section-title">Other Employment</div>
            
            @if($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0)
                @foreach($userDriverDetail->workHistories as $index => $workHistory)
                    <div class="company-item">
                        <h4>Work History #{{ $index + 1 }}</h4>
                        <table>                            
                            <tr>
                                <td style="width: 50%"><strong>Start Date</strong><br>{{ $workHistory->start_date ? date('m/d/Y', strtotime($workHistory->start_date)) : 'N/A' }}</td>
                                <td style="width: 50%"><strong>End Date</strong><br>{{ $workHistory->end_date ? date('m/d/Y', strtotime($workHistory->end_date)) : 'N/A' }}</td>
                            </tr>
                            <tr>                                
                                <td style="width: 50%"><strong>Position</strong><br>{{ $workHistory->position ?? 'N/A' }}</td>
                            </tr>                            
                            
                            @if($workHistory->description)
                            <tr>
                                <td colspan="2"><strong>Description</strong><br>{{ $workHistory->description }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                @endforeach
            @endif
            
            @if($userDriverDetail->driver_related_employments && $userDriverDetail->driver_related_employments->count() > 0)
                @foreach($userDriverDetail->driver_related_employments as $index => $relatedEmployment)
                    <div class="company-item">
                        <h4>Related Employment #{{ $index + 1 }}</h4>
                        <table>
                            <tr>
                                <td style="width: 50%"><strong>Start Date</strong><br>{{ $relatedEmployment->start_date ? date('m/d/Y', strtotime($relatedEmployment->start_date)) : 'N/A' }}</td>
                                <td style="width: 50%"><strong>End Date</strong><br>{{ $relatedEmployment->end_date ? date('m/d/Y', strtotime($relatedEmployment->end_date)) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td style="width: 50%"><strong>Position</strong><br>{{ $relatedEmployment->position ?? 'N/A' }}</td>
                                <td style="width: 50%"></td>
                            </tr>
                            @if($relatedEmployment->comments)
                            <tr>
                                <td colspan="2"><strong>Comments</strong><br>{{ $relatedEmployment->comments }}</td>
                            </tr>
                            @endif
                        </table>
                    </div>
                @endforeach
            @endif
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