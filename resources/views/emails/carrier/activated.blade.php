<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activated - EFCTS</title>
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
            color: #10b981;
            margin-bottom: 10px;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: #10b981;
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
        .highlight-box {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
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
            background: #10b981;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 5px;
            text-align: center;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .features {
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin: 10px 0;
            padding: 8px 0;
        }
        .feature-icon {
            width: 20px;
            height: 20px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">EFCTS</div>
            <div class="success-icon">✓</div>
            <h1 class="title">Welcome to EFCTS!</h1>
        </div>

        <div class="content">
            <div class="highlight-box">
                <h2 style="color: #10b981; margin-top: 0;">🎉 Congratulations! Your Account is Now Active</h2>
                <p><strong>Dear {{ $carrier->name }},</strong></p>
                <p>We're excited to inform you that your carrier account has been successfully activated and you now have full access to the EFCTS platform!</p>
            </div>

            <div class="company-info">
                <h3 style="margin-top: 0; color: #374151;">Account Details:</h3>
                <p><strong>Company:</strong> {{ $carrier->name }}</p>
                <p><strong>DOT Number:</strong> {{ $carrier->dot_number ?? 'N/A' }}</p>
                <p><strong>MC Number:</strong> {{ $carrier->mc_number ?? 'N/A' }}</p>
                <p><strong>Status:</strong> <span style="color: #10b981; font-weight: 600;">ACTIVE</span></p>
            </div>

            <h3 style="color: #374151;">What You Can Do Now:</h3>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">👥</div>
                    <span>Manage your driver roster and track their compliance</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🚛</div>
                    <span>Add and monitor your vehicle fleet</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📄</div>
                    <span>Upload and manage important documents</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <span>Access comprehensive reporting and analytics</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <span>Receive automated compliance notifications</span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $dashboardUrl }}" class="btn">Access Your Dashboard</a>
                <a href="{{ $supportUrl }}" class="btn btn-secondary">Contact Support</a>
            </div>

            <div class="highlight-box">
                <h3 style="margin-top: 0; color: #059669;">Next Steps:</h3>
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Log in to your dashboard to explore all available features</li>
                    <li>Complete your company profile if you haven't already</li>
                    <li>Add your drivers and vehicles to get started</li>
                    <li>Upload any required compliance documents</li>
                    <li>Set up notifications for important deadlines</li>
                </ol>
            </div>

            <p style="margin-top: 25px;">If you have any questions or need assistance getting started, our support team is here to help. You can reach us through the support section in your dashboard or by replying to this email.</p>

            <p><strong>Thank you for choosing EFCTS!</strong></p>
        </div>

        <div class="footer">
            <p>This email was sent to you because your carrier account was activated on our platform.</p>
            <p>EFCTS - Your Partner in Transportation Compliance</p>
            <p>© {{ date('Y') }} EFCTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>