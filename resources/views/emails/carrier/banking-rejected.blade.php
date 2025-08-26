<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking Information Rejected - EFCTS</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .logo {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            margin-bottom: 10px;
        }
        .warning-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            border-radius: 50%;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }
        .title {
            color: #1f2937;
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }
        .content {
            margin-bottom: 30px;
        }
        .warning-box {
            background: linear-gradient(135deg, #fef2f2, #fef7f7);
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .reason-box {
            background: #f8fafc;
            border-left: 4px solid #dc2626;
            border-radius: 0 8px 8px 0;
            padding: 15px;
            margin: 15px 0;
        }
        .company-info {
            background: #f8fafc;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #059669, #10b981);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(5, 150, 105, 0.2);
        }
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280, #9ca3af);
            box-shadow: 0 2px 4px rgba(107, 114, 128, 0.2);
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .next-steps {
            margin: 20px 0;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            margin: 12px 0;
            padding: 10px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .step-number {
            width: 24px;
            height: 24px;
            background: #059669;
            border-radius: 50%;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
            font-weight: bold;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">EFCTS</div>
            <div class="warning-icon">⚠️</div>
            <h1 class="title">Banking Information Rejected</h1>
        </div>

        <div class="content">
            <div class="warning-box">
                <h2 style="color: #dc2626; margin-top: 0;">❌ Action Required: Banking Information Needs Correction</h2>
                <p><strong>Dear {{ $carrier->name }},</strong></p>
                <p>We have reviewed your banking information and unfortunately, we cannot approve it at this time. Please review the reason below and resubmit your corrected information.</p>
            </div>

            <div class="reason-box">
                <h3 style="margin-top: 0; color: #dc2626;">Rejection Reason:</h3>
                <p style="margin-bottom: 0; font-weight: 500;">{{ $rejectionReason }}</p>
            </div>

            <div class="company-info">
                <h3 style="margin-top: 0; color: #374151;">Account Details:</h3>
                <p><strong>Company:</strong> {{ $carrier->name }}</p>
                <p><strong>DOT Number:</strong> {{ $carrier->dot_number ?? 'N/A' }}</p>
                <p><strong>MC Number:</strong> {{ $carrier->mc_number ?? 'N/A' }}</p>
                <p><strong>Status:</strong> Pending Validation</p>
            </div>

            <div class="next-steps">
                <h3 style="color: #374151;">Next Steps:</h3>
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div>
                        <strong>Review the rejection reason</strong><br>
                        <span style="color: #6b7280;">Carefully read the feedback provided above to understand what needs to be corrected.</span>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">2</div>
                    <div>
                        <strong>Gather correct information</strong><br>
                        <span style="color: #6b7280;">Prepare the accurate banking details and any required documentation.</span>
                    </div>
                </div>
                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>
                        <strong>Contact our support team</strong><br>
                        <span style="color: #6b7280;">Our support team will help you resolve the issue and guide you through the correction process.</span>
                    </div>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="mailto:support@efcts.com" class="btn">Contact Support</a>
            </div>

            <div style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 15px; margin: 20px 0;">
                <h4 style="color: #0369a1; margin-top: 0;">💡 Need Help?</h4>
                <p style="margin-bottom: 0; color: #374151;">If you have questions about the rejection reason or need assistance with your banking information, please contact our support team. We're here to help you get approved quickly.</p>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated message from EFCTS.</p>
            <p>If you have any questions, please contact our support team at support@efcts.com.</p>
            <p style="margin-top: 15px; font-size: 12px;">© {{ date('Y') }} EFCTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>