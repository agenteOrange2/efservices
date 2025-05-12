<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Vehicle Verification - EF Services</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            max-width: 200px;
        }
        h1 {
            color: #2563eb;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .vehicle-details {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .vehicle-details h3 {
            margin-top: 0;
            color: #1e40af;
        }
        .btn {
            display: inline-block;
            background-color: #2563eb;
            color: #fff;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 5px;
            font-weight: bold;
            margin: 20px 0;
        }
        .btn a{
            color: #fff;
            text-decoration: none;
        }
        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>EF Services - Vehicle Verification</h1>
    </div>

    <div class="content">
        <p>Dear <strong>{{ $thirdPartyName }}</strong>,</p>

        <p>For security reasons, we need you to verify vehicle registration for use on the EF Services TCP platform.</p>

        <p>The client <strong>{{ $driverName }}</strong> has registered a vehicle owned by you and we need your consent to continue with the process.</p>

        <div class="vehicle-details">
            <h3>Vehicle Details</h3>
            <p><strong>Make | Brand:</strong> {{ $vehicleData['make'] }}</p>
            <p><strong>Model:</strong> {{ $vehicleData['model'] }}</p>
            <p><strong>Year:</strong> {{ $vehicleData['year'] }}</p>
            <p><strong>VIN:</strong> {{ $vehicleData['vin'] }}</p>
            <p><strong>Type:</strong> {{ ucfirst($vehicleData['type']) }}</p>
            <p><strong>Registration Status:</strong> {{ $vehicleData['registration_state'] }}</p>
            <p><strong>Registration Number:</strong> {{ $vehicleData['registration_number'] }}</p>
        </div>

        <p>Please click the button below to review and sign the consent form:</p>

        <a href="{{ route('vehicle.verification.form', $verificationToken) }}" class="btn">Verify Vehicle</a>

        <p>This link will expire in 7 days. If you do not recognize this request, please ignore this email.</p>
    </div>

    <div class="footer">
        <p>This is an automated email, please do not reply to this message.</p>
        <p>&copy; {{ date('Y') }} EF Services. All rights reserved.</p>
    </div>
</body>
</html>
