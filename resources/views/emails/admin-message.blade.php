<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $adminMessage->subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px!important;            
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container-mail{
            max-width: 800px!important;
            margin: 0 auto!important;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background: #03045E;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            color: #f8f9fa;
            font-size: 24px;
            font-weight: 600;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 10px;
        }
        .priority-high {
            background-color: #dc3545;
            color: white;
        }
        .priority-normal {
            background-color: #28a745;
            color: white;
        }
        .priority-low {
            background-color: #6c757d;
            color: white;
        }
        .content {
            padding: 30px 20px;
        }
        .greeting {
            font-size: 16px;
            margin-bottom: 20px;
            color: #495057;
        }
        .message-content {
            background-color: #f8f9fa;
            border-left: 4px solid #03045E;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 5px 5px 0;
            font-size: 15px;
            line-height: 1.7;
        }
        .message-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 13px;
        }
        .info-row {
            margin: 5px 0;
        }
        .label {
            font-weight: bold;
            color: #495057;
            display: inline-block;
            width: 120px;
        }
        .footer {
            background-color: #03045E;
            color: #adb5bd;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        .footer a {
            color: #007bff;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .company-logo {
            margin-bottom: 10px;
        }
        @media (max-width: 600px) {
            body {
                padding: 10px;
            }
            .header, .content, .footer {
                padding: 20px 15px;
            }
            .label {
                width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container container-mail">
        <!-- Header -->
        <div class="header">
            <div class="company-logo">
                <strong>EFCTS</strong>
            </div>
            <h1>{{ $adminMessage->subject }}</h1>
            @if($adminMessage->priority)
                <span class="priority-badge priority-{{ $adminMessage->priority }}">
                    Prioridad {{ ucfirst($adminMessage->priority) }}
                </span>
            @endif
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Hi {{ $recipient->name }},
            </div>

            <p>You have received a new message from the EFCTS administrative team:</p>

            <div class="message-content">
                {!! nl2br(e($adminMessage->message)) !!}
            </div>

            <div class="message-info">
                <div class="info-row">
                    <span class="label">Sent by:</span>
                    {{ $adminMessage->sender->name ?? 'Administrator' }}
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    {{ $adminMessage->created_at->format('d/m/Y H:i') }}
                </div>
                @if($adminMessage->priority)
                <div class="info-row">
                    <span class="label">Priority:</span>
                    {{ ucfirst($adminMessage->priority) }}
                </div>
                @endif
            </div>

            <p style="margin-top: 30px; color: #6c757d; font-size: 14px;">
                This is an automated message from the EFCTS system. 
                @if($recipient->recipient_type === 'driver')
                    If you have any questions, you can contact us through your driver panel.
                @else
                    If you have any questions, you can reply to this email.
                @endif
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>EFCTS</strong></p>
            <p>Transport Driver Management System</p>
            <p>
                This email was sent automatically. 
                <br>
                © {{ date('Y') }} EFCTS. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>