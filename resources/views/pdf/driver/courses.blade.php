{{-- resources/views/pdf/driver/courses.blade.php --}}
<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Driver Application - Driver Courses</title>
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

        .course-item {
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

    @if($userDriverDetail->courses && $userDriverDetail->courses->count() > 0)
        <div class="section">
            <div class="section-title">Driver Courses</div>
            @foreach($userDriverDetail->courses as $index => $course)
                <div class="course-item">
                    <h4>Course #{{ $index + 1 }}</h4>
                    <table>
                        <tr>
                            <td colspan="2"><strong>Organization Name</strong><br>{{ $course->organization_name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 33.33%"><strong>City</strong><br>{{ $course->city ?? 'N/A' }}</td>
                            <td style="width: 33.33%"><strong>State</strong><br>{{ $course->state ?? 'N/A' }}</td>
                            <td style="width: 33.33%"><strong>Status</strong><br>{{ $course->status ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td style="width: 50%"><strong>Certification Date</strong><br>{{ $course->certification_date ? date('m/d/Y', strtotime($course->certification_date)) : 'N/A' }}</td>
                            <td style="width: 50%"><strong>Expiration Date</strong><br>{{ $course->expiration_date ? date('m/d/Y', strtotime($course->expiration_date)) : 'N/A' }}</td>
                        </tr>
                        @if($course->experience)
                            <tr>
                                <td colspan="2">
                                    <strong>Experience</strong><br>
                                    {{ $course->experience }}
                                </td>
                            </tr>
                        @endif
                    </table>
                </div>
            @endforeach
        </div>
    @else
        <div class="section">
            <div class="section-title">Driver Courses</div>
            <p>No driver courses found.</p>
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