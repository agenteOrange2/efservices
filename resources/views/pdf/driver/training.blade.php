{{-- resources/views/pdf/driver/training.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Training Schools</title>
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

        .school-item {
            margin-top: 15px;
        }

        ul {
            margin: 0;
            padding-left: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Driver Application Form</h1>
        <h2>{{ $title }}</h2>
    </div>

    <div class="section">
        <div class="section-title">Training School Information</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you attended commercial driver training school?</strong><br>{{ $userDriverDetail->trainingSchools && $userDriverDetail->trainingSchools->count() > 0 ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    @if($userDriverDetail->trainingSchools && $userDriverDetail->trainingSchools->count() > 0)
        <div class="section">
            <div class="section-title">Training Schools</div>
            @foreach($userDriverDetail->trainingSchools as $index => $school)
                <div class="school-item">
                    <h4>School #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td style="width: 50%"><strong>School Name</strong><br>{{ $school->school_name ?? 'N/A' }}</td>
                            <td style="width: 50%"><strong>Phone Number</strong><br>{{ $school->phone_number ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 33.33%"><strong>City</strong><br>{{ $school->city ?? 'N/A' }}</td>
                            <td style="width: 33.33%"><strong>State</strong><br>{{ $school->state ?? 'N/A' }}</td>
                            <td style="width: 33.33%"><strong>Graduated?</strong><br>{{ $school->graduated ? 'Yes' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Start Date</strong><br>{{ $school->date_start ? date('m/d/Y', strtotime($school->date_start)) : 'N/A' }}</td>
                            <td style="width: 50%"><strong>End Date</strong><br>{{ $school->date_end ? date('m/d/Y', strtotime($school->date_end)) : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Subject to Safety Regulations?</strong><br>{{ $school->subject_to_safety_regulations ? 'Yes' : 'No' }}</td>
                            <td style="width: 50%"><strong>Performed Safety Functions?</strong><br>{{ $school->performed_safety_functions ? 'Yes' : 'No' }}</td>
                        </tr>
                        @php
                            $skills = is_string($school->training_skills) ? json_decode($school->training_skills) : $school->training_skills;
                        @endphp
                        @if($skills && is_array($skills) && count($skills) > 0)
                            <tr>
                                <td colspan="2">
                                    <strong>Training Skills</strong><br>
                                    <ul>
                                        @foreach($skills as $skill)
                                            <li>{{ $skill }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endforeach
        </div>
    @elseif($userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_attended_training_school)
        <div class="section">
            <p>No training school data found.</p>
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