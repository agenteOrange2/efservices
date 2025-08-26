<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Validated - EFCTS</title>
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
        .success-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #059669, #10b981);
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
            background: linear-gradient(135deg, #ecfdf5, #f0fdf4);
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
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin: 25px 0;
            padding: 0 10px;
        }
        .step {
            text-align: center;
            flex: 1;
            position: relative;
        }
        .step-icon {
            width: 40px;
            height: 40px;
            background: #10b981;
            border-radius: 50%;
            margin: 0 auto 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .step-label {
            font-size: 12px;
            color: #059669;
            font-weight: 600;
        }
        .step-line {
            position: absolute;
            top: 20px;
            left: 50%;
            width: 100%;
            height: 2px;
            background: #10b981;
            z-index: -1;
        }
        .step:last-child .step-line {
            display: none;
        }
        .features {
            margin: 20px 0;
        }
        .feature-item {
            display: flex;
            align-items: center;
            margin: 12px 0;
            padding: 10px;
            background: #f9fafb;
            border-radius: 6px;
        }
        .feature-icon {
            width: 24px;
            height: 24px;
            background: #10b981;
            border-radius: 50%;
            margin-right: 15px;
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
            <div class="success-icon">💳</div>
            <h1 class="title">Payment Validated Successfully!</h1>
        </div>

        <div class="content">
            <div class="highlight-box">
                <h2 style="color: #059669; margin-top: 0;">🎉 Congratulations! Your Payment Has Been Validated</h2>
                @if($user)
                    <p><strong>Dear {{ $user->first_name }} {{ $user->last_name }},</strong></p>
                @else
                    <p><strong>Dear {{ $carrier->name }},</strong></p>
                @endif
                <p>Excellent news! Your banking information has been successfully validated and your carrier account is now fully active with complete access to all EFCTS features.</p>
            </div>

            <div class="progress-steps">
                <div class="step">
                    <div class="step-icon">✓</div>
                    <div class="step-label">Registration</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-icon">✓</div>
                    <div class="step-label">Company Info</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-icon">✓</div>
                    <div class="step-label">Membership</div>
                    <div class="step-line"></div>
                </div>
                <div class="step">
                    <div class="step-icon">✓</div>
                    <div class="step-label">Payment Validated</div>
                </div>
            </div>

            <div class="company-info">
                <h3 style="margin-top: 0; color: #374151;">Account Details:</h3>
                @if($user)
                    <p><strong>Contact Person:</strong> {{ $user->first_name }} {{ $user->last_name }}</p>
                    <p><strong>Email:</strong> {{ $user->email }}</p>
                @endif
                <p><strong>Company:</strong> {{ $carrier->name }}</p>
                <p><strong>DOT Number:</strong> {{ $carrier->dot_number ?? 'N/A' }}</p>
                <p><strong>MC Number:</strong> {{ $carrier->mc_number ?? 'N/A' }}</p>
                <p><strong>Status:</strong> <span style="color: #10b981; font-weight: 600;">ACTIVE</span></p>
                <p><strong>Validation Date:</strong> {{ now()->format('F j, Y') }}</p>
            </div>

            <h3 style="color: #374151;">You Now Have Full Access To:</h3>
            <div class="features">
                <div class="feature-item">
                    <div class="feature-icon">👥</div>
                    <span><strong>Driver Management:</strong> Add, manage, and track your driver roster with full compliance monitoring</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🚛</div>
                    <span><strong>Vehicle Fleet:</strong> Monitor your entire vehicle fleet with maintenance and inspection tracking</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📄</div>
                    <span><strong>Document Management:</strong> Upload, organize, and track all your compliance documents</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">📊</div>
                    <span><strong>Analytics & Reports:</strong> Access comprehensive reporting and business intelligence tools</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">🔔</div>
                    <span><strong>Smart Notifications:</strong> Receive automated alerts for renewals and compliance deadlines</span>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">💼</div>
                    <span><strong>Premium Support:</strong> Access to priority customer support and dedicated account management</span>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $dashboardUrl }}" class="btn">Access Your Dashboard</a>
                <a href="{{ $paymentValidatedUrl }}" class="btn btn-secondary">View Validation Details</a>
            </div>

            <div class="highlight-box">
                <h3 style="margin-top: 0; color: #047857;">🚀 Ready to Get Started?</h3>
                <p style="margin-bottom: 15px;">Your account is now fully operational! Here's what we recommend doing first:</p>
                <ol style="margin: 0; padding-left: 20px;">
                    <li><strong>Complete Your Profile:</strong> Add any remaining company details and preferences</li>
                    <li><strong>Add Your Drivers:</strong> Import or manually add your driver roster</li>
                    <li><strong>Register Your Vehicles:</strong> Add your fleet information for comprehensive tracking</li>
                    <li><strong>Upload Documents:</strong> Ensure all compliance documents are up to date</li>
                    <li><strong>Set Up Notifications:</strong> Configure alerts for important deadlines and renewals</li>
                </ol>
            </div>

            <p style="margin-top: 25px; padding: 15px; background: #f0fdf4; border-radius: 6px; border-left: 4px solid #10b981;">
                <strong>🎯 Pro Tip:</strong> Take advantage of our onboarding checklist in your dashboard to ensure you're getting the most out of all available features!
            </p>

            <p style="margin-top: 25px;">If you have any questions about your newly activated account or need assistance with any features, our support team is here to help. You can reach us through the support section in your dashboard or by replying to this email.</p>

            <p><strong>Welcome to the EFCTS family!</strong></p>
        </div>

        <div class="footer">
            <p>This email was sent to you because your payment information was successfully validated.</p>
            <p>EFCTS - Your Partner in Transportation Compliance</p>
            <p>© {{ date('Y') }} EFCTS. All rights reserved.</p>
        </div>
    </div>
</body>
</html>