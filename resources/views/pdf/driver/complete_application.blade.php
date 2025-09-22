{{-- resources/views/pdf/driver/solicitud_completa.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Complete Driver Application</title>
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
            page-break-after: avoid;
        }

        .page-break {
            page-break-after: always;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
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
            padding: 6px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .toc {
            margin-bottom: 20px;
            width: 100%
        }

        .toc-item {
            margin-bottom: 5px;
        }

        .page-number {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
        }

        .company-item,
        .school-item,
        .conviction-item,
        .accident-item,
        .license-item,
        .experience-item {
            margin-top: 15px;
            margin-bottom: 10px;
        }

        ul {
            margin: 0;
            padding-left: 15px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Complete Driver Application</h1>
        <h2>{{ $fullName ?? 'N/A' }}</h2>
        <p>Application ID: {{ $userDriverDetail->id }}</p>
        <p>Submission Date: {{ $date }}</p>
    </div>

    <!-- Table of Contents -->
    <div class="toc">
        <div class="section-title">Table of Contents</div>
        <div class="toc-item">1. General Information ............................... Page 2</div>
        <div class="toc-item">2. Address Information .............................. Page 3</div>
        <div class="toc-item">3. Application Details ............................. Page 4</div>
        <div class="toc-item">4. Driver's Licenses .............................. Page 5</div>
        <div class="toc-item">5. Medical Qualification .......................... Page 6</div>
        <div class="toc-item">6. Training Schools ............................... Page 7</div>
        <div class="toc-item">7. Driver Courses ................................. Page 8</div>
        <div class="toc-item">8. Traffic Violations ............................. Page 9</div>
        <div class="toc-item">9. Accident Record ............................... Page 10</div>
        <div class="toc-item">10. Criminal History Investigation ............... Page 11</div>
        <div class="toc-item">11. FMCSR Requirements ........................... Page 12</div>
        <div class="toc-item">12. Employment History ........................... Page 13</div>
        <div class="toc-item">13. Certification ................................ Page 14</div>
    </div>

    <div class="page-break"></div>

    <!-- 1. General Information -->
    <div class="section">
        <div class="section-title">1. GENERAL INFORMATION</div>
        <table>
            <tr>
                <td style="width: 75%"><strong>Applicant's Legal
                        Name</strong><br>{{ $fullName ?? 'N/A' }}</td>
                <td style="width: 25%"><strong>Date of Application</strong><br>{{ $date }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Current
                        Address</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->addresses ? $userDriverDetail->application->addresses->where('primary', true)->first()->address_line1 ?? 'N/A' : 'N/A' }}
                </td>
                <td style="width: 16.66%">
                    <strong>City</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->addresses ? $userDriverDetail->application->addresses->where('primary', true)->first()->city ?? 'N/A' : 'N/A' }}
                </td>
                <td style="width: 16.66%">
                    <strong>State</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->addresses ? $userDriverDetail->application->addresses->where('primary', true)->first()->state ?? 'N/A' : 'N/A' }}
                </td>
                <td style="width: 16.66%">
                    <strong>ZIP</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->addresses ? $userDriverDetail->application->addresses->where('primary', true)->first()->zip_code ?? 'N/A' : 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Email Address</strong><br>{{ $userDriverDetail->user->email ?? 'N/A' }}
                </td>
                <td style="width: 25%">
                    <strong>SSN</strong><br>{{ $userDriverDetail->medicalQualification->social_security_number ?? 'N/A' }}
                </td>
                <td style="width: 25%"><strong>Date of
                        Birth</strong><br>{{ $userDriverDetail->date_of_birth ? date('m/d/Y', strtotime($userDriverDetail->date_of_birth)) : 'N/A' }}
                </td>
                <td style="width: 25%"><strong>Phone</strong><br>{{ $userDriverDetail->phone ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 2. Address Information -->
    <div class="section">
        <div class="section-title">2. ADDRESS INFORMATION</div>
        <div class="section-title">Current Address</div>
        @if ($userDriverDetail->application && $userDriverDetail->application->addresses)
        @php
        $primaryAddress = $userDriverDetail->application->addresses->where('primary', true)->first();
        @endphp
        @if ($primaryAddress)
        <table>
            <tr>
                <td style="width: 50%"><strong>Address Line
                        1</strong><br>{{ $primaryAddress->address_line1 ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Address Line
                        2</strong><br>{{ $primaryAddress->address_line2 ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>City</strong><br>{{ $primaryAddress->city ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>State</strong><br>{{ $primaryAddress->state ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>ZIP Code</strong><br>{{ $primaryAddress->zip_code ?? 'N/A' }}
                </td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>From
                        Date</strong><br>{{ $primaryAddress->from_date ? date('m/d/Y', strtotime($primaryAddress->from_date)) : 'N/A' }}
                </td>
                <td style="width: 33.33%"><strong>To
                        Date</strong><br>{{ $primaryAddress->to_date ? date('m/d/Y', strtotime($primaryAddress->to_date)) : 'Present' }}
                </td>
                <td style="width: 33.33%"><strong>Lived Here 3+
                        Years</strong><br>{{ $primaryAddress->lived_three_years ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
        @else
        <p>No primary address information found.</p>
        @endif
        @endif

        @if ($userDriverDetail->application && $userDriverDetail->application->addresses)
        @php
        $previousAddresses = $userDriverDetail->application->addresses->where('primary', false);
        @endphp
        @if (count($previousAddresses) > 0)
        <div class="section-title">Previous Addresses</div>
        @foreach ($previousAddresses as $index => $address)
        <div class="previous-address">
            <h4>Previous Address #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Address Line
                            1</strong><br>{{ $address->address_line1 ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Address Line
                            2</strong><br>{{ $address->address_line2 ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 33.33%"><strong>City</strong><br>{{ $address->city ?? 'N/A' }}</td>
                    <td style="width: 33.33%"><strong>State</strong><br>{{ $address->state ?? 'N/A' }}</td>
                    <td style="width: 33.33%"><strong>ZIP
                            Code</strong><br>{{ $address->zip_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>From
                            Date</strong><br>{{ $address->from_date ? date('m/d/Y', strtotime($address->from_date)) : 'N/A' }}
                    </td>
                    <td style="width: 50%"><strong>To
                            Date</strong><br>{{ $address->to_date ? date('m/d/Y', strtotime($address->to_date)) : 'Present' }}
                    </td>
                </tr>
            </table>
        </div>
        @endforeach
        @endif
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 3. Application Details -->
    <div class="section">
        <div class="section-title">3. APPLICATION DETAILS</div>
        @if ($userDriverDetail->application && $userDriverDetail->application->details)
        @php
        $details = $userDriverDetail->application->details;
        @endphp
        <table>
            <tr>
                <td style="width: 50%"><strong>Applied Position</strong><br>
                    @if ($details->applying_position === 'other')
                    {{ $details->applying_position_other ?? 'N/A' }}
                    @else
                    {{ $details->applying_position ?? 'N/A' }}
                    @endif
                </td>
                <td style="width: 50%"><strong>Preferred
                        Location</strong><br>{{ $details->applying_location ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 33.33%"><strong>Eligible to work in
                        USA?</strong><br>{{ $details->eligible_to_work ? 'Yes' : 'No' }}</td>
                <td style="width: 33.33%"><strong>Can speak
                        English?</strong><br>{{ $details->can_speak_english ? 'Yes' : 'No' }}</td>
                <td style="width: 33.33%"><strong>Has TWIC
                        card?</strong><br>{{ $details->has_twic_card ? 'Yes' : 'No' }}</td>
            </tr>
            @if ($details->has_twic_card)
            <tr>
                <td colspan="3"><strong>TWIC Expiration
                        Date</strong><br>{{ $details->twic_expiration_date ? date('m/d/Y', strtotime($details->twic_expiration_date)) : 'N/A' }}
                </td>
            </tr>
            @endif
            <tr>
                <td><strong>Expected Salary</strong><br>${{ $details->expected_pay ?? 'N/A' }}</td>
                <td colspan="2"><strong>How did you hear about us?</strong><br>
                    @if ($details->how_did_hear === 'other')
                    {{ $details->how_did_hear_other ?? 'N/A' }}
                    @elseif($details->how_did_hear === 'employee_referral')
                    Employee Referral: {{ $details->referral_employee_name ?? 'N/A' }}
                    @else
                    {{ $details->how_did_hear ?? 'N/A' }}
                    @endif
                </td>
            </tr>
        </table>
        @else
        <p>No application details found.</p>
        @endif

        @if ($userDriverDetail->workHistories && $userDriverDetail->workHistories->count() > 0)
        <div class="section-title">Work History with this Company</div>
        @foreach ($userDriverDetail->workHistories as $index => $history)
        <div class="work-history">
            <h4>Work History #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Previous
                            Company</strong><br>{{ $history->previous_company ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Position</strong><br>{{ $history->position ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Start
                            Date</strong><br>{{ $history->start_date ? date('m/d/Y', strtotime($history->start_date)) : 'N/A' }}
                    </td>
                    <td><strong>End
                            Date</strong><br>{{ $history->end_date ? date('m/d/Y', strtotime($history->end_date)) : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Location</strong><br>{{ $history->location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Reason for Leaving</strong><br>{{ $history->reason_for_leaving ?? 'N/A' }}</td>
                    <td><strong>Reference Contact</strong><br>{{ $history->reference_contact ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>
        @endforeach
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 4. Driver's Licenses -->
    <div class="section">
        <div class="section-title">4. DRIVER'S LICENSES</div>
        <table>
            <tr>
                <td colspan="2"><strong>Current License
                        Number</strong><br>{{ $userDriverDetail->licenses->where('is_primary', true)->first()->current_license_number ?? 'N/A' }}
                </td>
            </tr>
        </table>

        @if ($userDriverDetail->licenses && $userDriverDetail->licenses->count() > 0)
        <div class="section-title">Licenses</div>
        @foreach ($userDriverDetail->licenses as $index => $license)
        <div class="license-item">
            <h4>License #{{ $index + 1 }}{{ $license->is_primary ? ' (Primary)' : '' }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>License
                            Number</strong><br>{{ $license->license_number ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>State of
                            Issue</strong><br>{{ $license->state_of_issue ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>License
                            Class</strong><br>{{ $license->license_class ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Expiration
                            Date</strong><br>{{ $license->expiration_date ? date('m/d/Y', strtotime($license->expiration_date)) : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><strong>Is CDL?</strong><br>{{ $license->is_cdl ? 'Yes' : 'No' }}</td>
                </tr>
                @if ($license->is_cdl && $license->endorsements && $license->endorsements->count() > 0)
                <tr>
                    <td colspan="2">
                        <strong>Endorsements</strong><br>
                        @foreach ($license->endorsements as $endorsement)
                        {{ $endorsement->code }}
                        ({{ $endorsement->name }}){{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </td>
                </tr>
                @endif
            </table>
        </div>
        @endforeach
        @endif

        @if ($userDriverDetail->experiences && $userDriverDetail->experiences->count() > 0)
        <div class="section-title">Driving Experience</div>
        @foreach ($userDriverDetail->experiences as $index => $experience)
        <div class="experience-item">
            <h4>Experience #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Equipment
                            Type</strong><br>{{ $experience->equipment_type ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Years of
                            Experience</strong><br>{{ $experience->years_experience ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Miles
                            Driven</strong><br>{{ number_format($experience->miles_driven ?? 0) }}</td>
                    <td style="width: 50%"><strong>Requires
                            CDL?</strong><br>{{ $experience->requires_cdl ? 'Yes' : 'No' }}</td>
                </tr>
            </table>
        </div>
        @endforeach
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 5. Medical Qualification -->
    <div class="section">
        <div class="section-title">5. MEDICAL QUALIFICATION</div>
        @if ($userDriverDetail->medicalQualification)
        @php
        $medical = $userDriverDetail->medicalQualification;
        @endphp
        <div class="section-title">General Information</div>
        <table>
            <tr>
                <td style="width: 33.33%"><strong>Social Security
                        Number</strong><br>{{ $medical->social_security_number ?? 'N/A' }}</td>
                <td style="width: 33.33%"><strong>Hire
                        Date</strong><br>{{ $medical->hire_date ? date('m/d/Y', strtotime($medical->hire_date)) : 'N/A' }}
                </td>
                <td style="width: 33.33%"><strong>Location</strong><br>{{ $medical->location ?? 'N/A' }}</td>
            </tr>
        </table>

        <div class="section-title">Driver Status</div>
        <table>
            <tr>
                <td style="width: 50%"><strong>Is
                        Suspended?</strong><br>{{ $medical->is_suspended ? 'Yes' : 'No' }}</td>
                @if ($medical->is_suspended)
                <td style="width: 50%"><strong>Suspension
                        Date</strong><br>{{ $medical->suspension_date ? date('m/d/Y', strtotime($medical->suspension_date)) : 'N/A' }}
                </td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Is
                        Terminated?</strong><br>{{ $medical->is_terminated ? 'Yes' : 'No' }}</td>
                @if ($medical->is_terminated)
                <td style="width: 50%"><strong>Termination
                        Date</strong><br>{{ $medical->termination_date ? date('m/d/Y', strtotime($medical->termination_date)) : 'N/A' }}
                </td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
        </table>

        <div class="section-title">Medical Qualification</div>
        <table>
            <tr>
                <td colspan="2"><strong>Medical Examiner
                        Name</strong><br>{{ $medical->medical_examiner_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td style="width: 50%"><strong>Examiner Registry
                        Number</strong><br>{{ $medical->medical_examiner_registry_number ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Medical Card Expiration
                        Date</strong><br>{{ $medical->medical_card_expiration_date ? date('m/d/Y', strtotime($medical->medical_card_expiration_date)) : 'N/A' }}
                </td>
            </tr>
        </table>
        @else
        <p>No medical qualification data found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 6. Training Schools -->
    <div class="section">
        <div class="section-title">6. TRAINING SCHOOLS</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you attended commercial driver training
                        school?</strong><br>{{ $userDriverDetail->trainingSchools && $userDriverDetail->trainingSchools->count() > 0 ? 'Yes' : 'No' }}
                </td>
            </tr>
        </table>

        @if ($userDriverDetail->trainingSchools && $userDriverDetail->trainingSchools->count() > 0)
        <div class="section-title">Training Schools</div>
        @foreach ($userDriverDetail->trainingSchools as $index => $school)
        <div class="school-item">
            <h4>School #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 100%"><strong>School Name</strong><br>{{ $school->school_name ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 33.33%"><strong>City</strong><br>{{ $school->city ?? 'N/A' }}</td>
                    <td style="width: 33.33%"><strong>State</strong><br>{{ $school->state ?? 'N/A' }}</td>
                    <td style="width: 33.33%">
                        <strong>Graduated?</strong><br>{{ $school->graduated ? 'Yes' : 'No' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Start
                            Date</strong><br>{{ $school->date_start ? date('m/d/Y', strtotime($school->date_start)) : 'N/A' }}
                    </td>
                    <td style="width: 50%"><strong>End
                            Date</strong><br>{{ $school->date_end ? date('m/d/Y', strtotime($school->date_end)) : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Subject to Safety
                            Regulations?</strong><br>{{ $school->subject_to_safety_regulations ? 'Yes' : 'No' }}
                    </td>
                    <td style="width: 50%"><strong>Performed Safety
                            Functions?</strong><br>{{ $school->performed_safety_functions ? 'Yes' : 'No' }}
                    </td>
                </tr>
                @php
                $skills = is_string($school->training_skills) ? json_decode($school->training_skills) : $school->training_skills;
                @endphp
                @if ($skills && is_array($skills) && count($skills) > 0)
                <tr>
                    <td colspan="2">
                        <strong>Training Skills</strong><br>
                        <ul>
                            @foreach ($skills as $skill)
                            <li>{{ $skill }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endif
            </table>
        </div>
        @endforeach
        @elseif(
        $userDriverDetail->application &&
        $userDriverDetail->application->details &&
        $userDriverDetail->application->details->has_attended_training_school)
        <p>No training school data found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 7. Driver Courses -->
    <div class="section">
        <div class="section-title">7. DRIVER COURSES</div>

        @if ($userDriverDetail->courses && $userDriverDetail->courses->count() > 0)
        <div class="section-title">Driver Courses</div>
        @foreach ($userDriverDetail->courses as $index => $course)
        <div class="course-item">
            <h4>Course #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Organization Name</strong><br>{{ $course->organization_name ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Course Type</strong><br>{{ $course->course_type ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Certification Date</strong><br>{{ $course->certification_date ? date('m/d/Y', strtotime($course->certification_date)) : 'N/A' }}</td>
                    <td style="width: 50%"><strong>Expiration Date</strong><br>{{ $course->expiration_date ? date('m/d/Y', strtotime($course->expiration_date)) : 'N/A' }}</td>
                </tr>
                @if ($course->experience)
                <tr>
                    <td colspan="2"><strong>Experience</strong><br>{{ $course->experience }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endforeach
        @else
        <p>No driver courses found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 8. Traffic Violations -->
    <div class="section">
        <div class="section-title">8. TRAFFIC VIOLATIONS</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you had any traffic violations in the last three
                        years?</strong><br>{{ $userDriverDetail->trafficConvictions && $userDriverDetail->trafficConvictions->count() > 0 ? 'Yes' : 'No' }}
                </td>
            </tr>
        </table>

        @if ($userDriverDetail->trafficConvictions && $userDriverDetail->trafficConvictions->count() > 0)
        <div class="section-title">Traffic Violations</div>
        @foreach ($userDriverDetail->trafficConvictions as $index => $conviction)
        <div class="conviction-item">
            <h4>Violation #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Violation
                            Date</strong><br>{{ $conviction->conviction_date ? date('m/d/Y', strtotime($conviction->conviction_date)) : 'N/A' }}
                    </td>
                    <td style="width: 50%"><strong>Location</strong><br>{{ $conviction->location ?? 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Charge</strong><br>{{ $conviction->charge ?? 'N/A' }}</td>
                    <td style="width: 50%"><strong>Penalty</strong><br>{{ $conviction->penalty ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>
        @endforeach
        @elseif(
        $userDriverDetail->application &&
        $userDriverDetail->application->details &&
        $userDriverDetail->application->details->has_traffic_convictions)
        <p>No traffic violation data found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 9. Accident Record -->
    <div class="section">
        <div class="section-title">9. ACCIDENT RECORD</div>
        <table>
            <tr>
                <td colspan="2"><strong>Have you had any accidents in the last three
                        years?</strong><br>{{ $userDriverDetail->accidents && $userDriverDetail->accidents->count() > 0 ? 'Yes' : 'No' }}
                </td>
            </tr>
        </table>

        @if ($userDriverDetail->accidents && $userDriverDetail->accidents->count() > 0)
        <div class="section-title">Accidents</div>
        @foreach ($userDriverDetail->accidents as $index => $accident)
        <div class="accident-item">
            <h4>Accident #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td style="width: 50%"><strong>Accident
                            Date</strong><br>{{ $accident->accident_date ? date('m/d/Y', strtotime($accident->accident_date)) : 'N/A' }}
                    </td>
                    <td style="width: 50%"><strong>Nature of
                            Accident</strong><br>{{ $accident->nature_of_accident ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="width: 25%"><strong>Injuries
                            Involved?</strong><br>{{ $accident->had_injuries ? 'Yes' : 'No' }}</td>
                    @if ($accident->had_injuries)
                    <td style="width: 25%"><strong>Number of
                            Injuries</strong><br>{{ $accident->number_of_injuries ?? '0' }}</td>
                    @else
                    <td style="width: 25%"></td>
                    @endif
                    <td style="width: 25%"><strong>Fatalities
                            Involved?</strong><br>{{ $accident->had_fatalities ? 'Yes' : 'No' }}</td>
                    @if ($accident->had_fatalities)
                    <td style="width: 25%"><strong>Number of
                            Fatalities</strong><br>{{ $accident->number_of_fatalities ?? '0' }}</td>
                    @else
                    <td style="width: 25%"></td>
                    @endif
                </tr>
                @if ($accident->comments)
                <tr>
                    <td colspan="4"><strong>Comments</strong><br>{{ $accident->comments }}</td>
                </tr>
                @endif
            </table>
        </div>
        @endforeach
        @elseif(
        $userDriverDetail->application &&
        $userDriverDetail->application->details &&
        $userDriverDetail->application->details->has_accidents)
        <p>No accident data found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 10. Criminal History Investigation -->
    <div class="section">
        <div class="section-title">10. CRIMINAL HISTORY INVESTIGATION</div>

        <div class="section-title">Criminal Record</div>
        <table>
            <tr>
                <td><strong>Do you have any pending criminal charges?</strong></td>
                <td>{{ isset($criminalHistory) && $criminalHistory['has_criminal_charges'] ? 'Yes' : 'No' }}</td>
            </tr>
        </table>

        <div class="section-title">Felonies</div>
        <table>
            <tr>
                <td><strong>Have you ever been convicted of a felony?</strong></td>
                <td>{{ isset($criminalHistory) && $criminalHistory['has_felony_conviction'] ? 'Yes' : 'No' }}</td>
            </tr>
            <tr>
                <td><strong>Do you have a minister's permit to enter/exit Canada?</strong></td>
                <td>{{ isset($criminalHistory) && $criminalHistory['has_minister_permit'] ? 'Yes' : 'No' }}</td>
            </tr>
        </table>

        <div class="section-title">FAIR CREDIT REPORTING ACT DISCLOSURE AND AUTHORIZATION FORM [FOR EMPLOYMENT PURPOSES]</div>
        <div style="border: 2px solid #333; padding: 15px; margin: 20px 0; background-color: #fafafa; text-align: justify; line-height: 1.6;">

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

        <table>
            <tr>
                <td style="width: 50%;"><strong>FCRA Consent:</strong> {{ isset($criminalHistory) && $criminalHistory['fcra_consent'] ? 'Yes' : 'No' }}</td>
                <td style="width: 50%;"><strong>Background Info Consent:</strong> {{ isset($criminalHistory) && $criminalHistory['background_info_consent'] ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
    </div>

    <div class="page-break"></div>

    <!-- 11. FMCSR Requirements -->
    <div class="section">
        <div class="section-title">11. FMCSR REQUIREMENTS</div>
        @if ($userDriverDetail->fmcsrData)
        @php
        $fmcsr = $userDriverDetail->fmcsrData;
        @endphp
        <table>
            <tr>
                <td style="width: 50%"><strong>Currently disqualified under FMCSR
                        391.15?</strong><br>{{ $fmcsr->is_disqualified ? 'Yes' : 'No' }}</td>
                @if ($fmcsr->is_disqualified)
                <td style="width: 50%"><strong>Disqualification
                        Details</strong><br>{{ $fmcsr->disqualified_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Has your license been suspended or
                        revoked?</strong><br>{{ $fmcsr->is_license_suspended ? 'Yes' : 'No' }}</td>
                @if ($fmcsr->is_license_suspended)
                <td style="width: 50%"><strong>Suspension
                        Details</strong><br>{{ $fmcsr->suspension_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Have you ever been denied a
                        license?</strong><br>{{ $fmcsr->is_license_denied ? 'Yes' : 'No' }}</td>
                @if ($fmcsr->is_license_denied)
                <td style="width: 50%"><strong>Denial
                        Details</strong><br>{{ $fmcsr->denial_details ?? 'N/A' }}</td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            <tr>
                <td style="width: 50%"><strong>Have you tested positive for drugs or
                        alcohol?</strong><br>{{ $fmcsr->has_positive_drug_test ? 'Yes' : 'No' }}</td>
                <td style="width: 50%"><strong>Consent to Release
                        Information?</strong><br>{{ $fmcsr->consent_to_release ? 'Yes' : 'No' }}</td>
            </tr>
            @if ($fmcsr->has_positive_drug_test)
            <tr>
                <td style="width: 50%"><strong>Substance Abuse
                        Professional</strong><br>{{ $fmcsr->substance_abuse_professional ?? 'N/A' }}</td>
                <td style="width: 50%"><strong>Professional
                        Phone</strong><br>{{ $fmcsr->sap_phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Return to Duty
                        Agency</strong><br>{{ $fmcsr->return_duty_agency ?? 'N/A' }}</td>
            </tr>
            @endif
            <tr>
                <td style="width: 50%"><strong>Have you been convicted of on-duty
                        offenses?</strong><br>{{ $fmcsr->has_duty_offenses ? 'Yes' : 'No' }}</td>
                @if ($fmcsr->has_duty_offenses)
                <td style="width: 50%"><strong>Most Recent Conviction
                        Date</strong><br>{{ $fmcsr->recent_conviction_date ? date('m/d/Y', strtotime($fmcsr->recent_conviction_date)) : 'N/A' }}
                </td>
                @else
                <td style="width: 50%"></td>
                @endif
            </tr>
            @if ($fmcsr->has_duty_offenses)
            <tr>
                <td colspan="2"><strong>Offense Details</strong><br>{{ $fmcsr->offense_details ?? 'N/A' }}
                </td>
            </tr>
            @endif
            <tr>
                <td colspan="2"><strong>Consent to Driving Record
                        Verification?</strong><br>{{ $fmcsr->consent_driving_record ? 'Yes' : 'No' }}</td>
            </tr>
        </table>
        @else
        <p>No FMCSR requirements data found.</p>
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 12. Employment History -->
    <div class="section">
        <div class="section-title">12. EMPLOYMENT HISTORY</div>
        <table>
            <tr>
                <td style="width: 50%"><strong>Have you been unemployed in the last 10
                        years?</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_unemployment_periods ? 'Yes' : 'No' }}
                </td>
                <td style="width: 50%"><strong>Have you completed your employment history
                        information?</strong><br>{{ $userDriverDetail->application && $userDriverDetail->application->details && $userDriverDetail->application->details->has_completed_employment_history ? 'Yes' : 'No' }}
                </td>
            </tr>
        </table>

        @if ($userDriverDetail->unemploymentPeriods && $userDriverDetail->unemploymentPeriods->count() > 0)
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
                @foreach ($userDriverDetail->unemploymentPeriods as $period)
                <tr>
                    <td>{{ $period->start_date ? date('m/d/Y', strtotime($period->start_date)) : 'N/A' }}</td>
                    <td>{{ $period->end_date ? date('m/d/Y', strtotime($period->end_date)) : 'N/A' }}</td>
                    <td>{{ $period->comments ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if ($userDriverDetail->employmentCompanies && $userDriverDetail->employmentCompanies->count() > 0)
        <div class="section-title">Employment Companies</div>
        @foreach ($userDriverDetail->employmentCompanies as $index => $company)
        <div class="company-item">
            <h4>Company #{{ $index + 1 }}</h4>
            <table>
                <tr>
                    <td colspan="2"><strong>Company
                            Name</strong><br>{{ $company->company_name ?? ($company->masterCompany->company_name ?? 'N/A') }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <strong>Address</strong><br>{{ $company->address ?? ($company->masterCompany->address ?? 'N/A') }}
                    </td>
                    <td style="width: 50%"><strong>City, State,
                            ZIP</strong><br>{{ $company->city ?? ($company->masterCompany->city ?? 'N/A') }},
                        {{ $company->state ?? ($company->masterCompany->state ?? '') }}
                        {{ $company->zip ?? ($company->masterCompany->zip ?? '') }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <strong>Contact</strong><br>{{ $company->contact ?? ($company->masterCompany->contact ?? 'N/A') }}
                    </td>
                    <td style="width: 50%">
                        <strong>Phone / Fax</strong><br>
                        Phone: {{ $company->phone ?? ($company->masterCompany->phone ?? 'N/A') }}<br>
                        Fax: {{ $company->fax ?? ($company->masterCompany->fax ?? 'N/A') }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Employed
                            From</strong><br>{{ $company->employed_from ? date('m/d/Y', strtotime($company->employed_from)) : 'N/A' }}
                    </td>
                    <td style="width: 50%"><strong>Employed
                            To</strong><br>{{ $company->employed_to ? date('m/d/Y', strtotime($company->employed_to)) : 'N/A' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%"><strong>Positions
                            Held</strong><br>{{ $company->positions_held ?? 'N/A' }}</td>
                    <td style="width: 50%">
                        <strong>FMCSR & Safety</strong><br>
                        Subject to FMCSR: {{ $company->subject_to_fmcsr ? 'Yes' : 'No' }}<br>
                        Safety Sensitive Function: {{ $company->safety_sensitive_function ? 'Yes' : 'No' }}
                    </td>
                </tr>
                <tr>
                    <td style="width: 50%">
                        <strong>Reason for Leaving</strong><br>
                        @if ($company->reason_for_leaving === 'other')
                        {{ $company->other_reason_description ?? 'Other' }}
                        @else
                        {{ $company->reason_for_leaving ?? 'N/A' }}
                        @endif
                    </td>
                    <td style="width: 50%">
                        <strong>Explanation</strong><br>{{ $company->explanation ?? 'N/A' }}
                    </td>
                </tr>
            </table>
        </div>
        @endforeach
        @endif
    </div>

    <div class="page-break"></div>

    <!-- 13. Certification -->
    <div class="section">
        <div class="section-title">13. CERTIFICATION</div>
        <p style="margin-bottom: 20px;">I certify that all information provided in this application is true and
            complete to the best of my knowledge. I understand that any false information or omission may disqualify me
            from further consideration for employment and may result in my dismissal if discovered at a later date.</p>

        <p style="margin-bottom: 20px;">I authorize the investigation of any or all statements contained in this
            application. I also authorize, whether listed or not, any person, school, current employer, past employers,
            and organizations to provide relevant information and opinions that may be useful in making a hiring
            decision. I release such persons and organizations from any legal liability in making such statements.</p>

        <p style="margin-bottom: 20px;">I understand that if I am extended an offer of employment it may be conditioned
            upon my successfully passing a pre-employment physical examination, drug screening, and background check.
        </p>

        <p style="margin-bottom: 20px;"><strong>By signing below, I certify that I have read, fully understand, and
                accept all terms of the foregoing statement.</strong></p>

        <div class="signature-box">
            <div class="field">
                <span class="label">Signature:</span>
                <div>
                    @if (!empty($signature) && file_exists($signature))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signature)) }}" alt="Signature"
                        style="max-width: 300px; max-height: 100px;" />
                    @elseif (!empty($signaturePath) && file_exists($signaturePath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signaturePath)) }}" alt="Signature"
                        style="max-width: 300px; max-height: 100px;" />
                    @else
                    <p style="font-style: italic; color: #999;">Signature not available</p>
                    @endif
                </div>
            </div>
            <div style="margin-top: 20px;">
                <span class="label">Date:</span>
                <span class="value">{{ $date }}</span>
            </div>
        </div>
    </div>
</body>

</html>