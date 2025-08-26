<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Reactivated - EFCTS</title>
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
            color: #3b82f6;
            margin-bottom: 10px;
        }
        .success-icon {
            width: 60px;
            height: 60px;
            background: #3b82f6;
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
            background: #eff6ff;
            border: 1px solid #bfdbfe;
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
            background: #3b82f6;
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
            background: #3b82f6;
            border-radius: 50%;
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }
        .warning-box {
            background: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">EFCTS</div>
            <div class="success-icon">🔄</div>
            <h1 class="title">Welcome Back to EFCTS!</h1>
        </div>

        <div class="content">
            <div class="highlight-box">
                <h2 style="color: #3b82f6; margin-top: 0;">🎉 Your Account Has Been Reactivated</h2>
                <p><strong>Dear {{ $carrier->name }},</strong></p>
                <p>Great news! Your carrier account has been successfully reactivated and you now have full access to the EFCTS platform once again.</p>
            </div>

            <div class="company-info">
                <h3 style="margin-top: 0; color: #374151;">Account Details:</h3>
                <p><strong>Company:</strong> {{ $carrier->name }}</p>
                <p><strong>DOT Number:</strong> {{ $carrier->dot_number ?? 'N/A' }}</p>
                <p><strong>MC Number:</strong> {{ $carrier->mc_number ?? 'N/A' }}</p>
                <p><strong>Status:</strong> <span style="color: #3b82f6; font-weight: 600;">REACTIVATED</span></p>
                <p><strong>Reactivation Date:</strong> {{ now()->format('F j, Y') }}</p>
            </div>

            <h3 style="color: #374151;">Your Full Access is Restored:</h3>
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

            <div class="warning-box">
                <h3 style="margin-top: 0; color: #d97706;">⚠️ Important Reminders:</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>Please review and update your company information if needed</li>
                    <li>Ensure all driver and vehicle information is current</li>
                    <li>Check for any pending compliance requirements</li>
                    <li>Review any notifications or messages that may have accumulated</li>
                </ul>
            </div>

            <div class="highlight-box">
                <h3 style="margin-top: 0; color: #1d4ed8;">Recommended Next Steps:</h3>
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Log in to your dashboard and review your account status</li>
                    <li>Update any outdated information in your profile</li>
                    <li>Check for any expired documents that need renewal</li>
                    <li>Review your driver and vehicle compliance status</li>
                    <li>Set up notifications to stay compliant going forward</li>
                </ol>
            </div>

            <p style="margin-top: 25px;">We're glad to have you back! If you have any questions about your reactivated account or need assistance with anything, our support team is ready to help. You can reach us through the support section in your dashboard or by replying to this email.</p>

            <p><strong>Thank you for continuing to choose EFCTS!</strong></p>
        </div>

        <div class="footer">
            <p>This email was sent to you because your carrier account was reactivated on our platform.</p>
            <p>EFCTS - Your Partner in Transportation Compliance</p>
            <p>© {{ date('Y') }} EFCTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>