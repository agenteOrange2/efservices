<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking Information Under Review</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #fff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            background-color: #ffc107;
            color: #212529;
            border-radius: 4px;
            font-weight: bold;
            margin: 10px 0;
        }
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Banking Information Under Review</h1>
    </div>

    <div class="content">
        @if($user)
            <p>Dear {{ $user->name }},</p>
        @else
            <p>Dear Carrier Representative,</p>
        @endif

        <p>We have received your banking information for <strong>{{ $carrier->name }}</strong> and it is currently under review by our team.</p>

        <div class="info-section">
            <h3>Carrier Information:</h3>
            <p><strong>Company Name:</strong> {{ $carrier->name }}</p>
            @if($carrier->mc_number)
                <p><strong>MC Number:</strong> {{ $carrier->mc_number }}</p>
            @endif
            @if($carrier->dot_number)
                <p><strong>DOT Number:</strong> {{ $carrier->dot_number }}</p>
            @endif
            <p><strong>Status:</strong> <span class="status-badge">Under Review</span></p>
        </div>

        <h3>What happens next?</h3>
        <ul>
            <li>Our team will review your banking information within 1-2 business days</li>
            <li>You will receive an email notification once the review is complete</li>
            <li>If approved, your account will be activated and you can start using our services</li>
            <li>If additional information is needed, we will contact you directly</li>
        </ul>

        <p><strong>Important:</strong> Please ensure all banking information is accurate and up-to-date. Any discrepancies may delay the approval process.</p>

        <p>If you have any questions or need to update your information, please contact our support team.</p>

        <p>Thank you for choosing our services!</p>
    </div>

    <div class="footer">
        <p>This is an automated message. Please do not reply to this email.</p>
        <p>&copy; {{ date('Y') }} EFCTS. All rights reserved.</p>
    </div>
</body>
</html>