<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $subject ?? 'Administrative Alert' }}</title>
    <style>
        /* Reset styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #374151;
            background-color: #f9fafb;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        /* Email container */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Header styles */
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3730a3 100%);
            padding: 24px;
            text-align: center;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .logo {
            width: 40px;
            height: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: #1e40af;
            font-size: 18px;
        }

        .header-text {
            color: #ffffff;
        }

        .header-text h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .header-text p {
            font-size: 14px;
            opacity: 0.9;
        }

        /* Severity indicator */
        .severity-indicator {
            padding: 16px 24px;
            border-left: 4px solid {{ $severity_color ?? '#6b7280' }};
            background-color: {{ $severity_color ?? '#6b7280' }}15;
        }

        .severity-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: {{ $severity_color ?? '#6b7280' }};
            color: #ffffff;
            margin-bottom: 8px;
        }

        .severity-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 8px;
        }

        .severity-description {
            font-size: 14px;
            color: #6b7280;
        }

        /* Content styles */
        .content {
            padding: 24px;
        }

        .section {
            margin-bottom: 24px;
        }

        .section:last-child {
            margin-bottom: 0;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-content {
            font-size: 14px;
            color: #374151;
            line-height: 1.6;
        }

        /* Event details */
        .event-details {
            background-color: #f9fafb;
            border-radius: 6px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #6b7280;
            font-size: 13px;
        }

        .detail-value {
            font-weight: 400;
            color: #111827;
            font-size: 13px;
            text-align: right;
            max-width: 60%;
        }

        /* User info */
        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: #f9fafb;
            border-radius: 6px;
            border: 1px solid #e5e7eb;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6b7280;
            font-size: 14px;
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-weight: 600;
            color: #111827;
            font-size: 14px;
        }

        .user-email {
            color: #6b7280;
            font-size: 12px;
        }

        /* Action button */
        .action-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #1e40af;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            text-align: center;
            transition: background-color 0.2s;
        }

        .action-button:hover {
            background-color: #1e3a8a;
        }

        /* Technical details */
        .technical-details {
            background-color: #1f2937;
            color: #f9fafb;
            border-radius: 6px;
            padding: 16px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            line-height: 1.4;
            overflow-x: auto;
        }

        .technical-details pre {
            margin: 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Footer */
        .footer {
            background-color: #f9fafb;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer-content {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        .footer-links {
            margin-top: 12px;
        }

        .footer-link {
            color: #1e40af;
            text-decoration: none;
            margin: 0 8px;
        }

        .footer-link:hover {
            text-decoration: underline;
        }

        /* Responsive styles */
        @media screen and (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }

            .header, .severity-indicator, .content, .footer {
                padding: 16px;
            }

            .header-content {
                flex-direction: column;
                gap: 8px;
            }

            .header-text h1 {
                font-size: 20px;
            }

            .severity-title {
                font-size: 18px;
            }

            .detail-row {
                flex-direction: column;
                gap: 4px;
            }

            .detail-value {
                text-align: left;
                max-width: 100%;
            }

            .user-info {
                flex-direction: column;
                text-align: center;
            }

            .action-button {
                display: block;
                width: 100%;
            }
        }

        /* Outlook-specific fixes */
        .outlook-fix {
            mso-line-height-rule: exactly;
            line-height: 0;
            font-size: 0;
        }

        /* High contrast mode support */
        @media (prefers-contrast: high) {
            .email-container {
                border: 2px solid #000000;
            }

            .severity-indicator {
                border-left-width: 6px;
            }

            .action-button {
                border: 2px solid #000000;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            body {
                background-color: #111827;
            }

            .email-container {
                background-color: #1f2937;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            }

            .severity-title {
                color: #f9fafb;
            }

            .section-title {
                color: #f9fafb;
            }

            .section-content {
                color: #d1d5db;
            }

            .event-details, .user-info {
                background-color: #374151;
                border-color: #4b5563;
            }

            .detail-label {
                color: #9ca3af;
            }

            .detail-value {
                color: #f9fafb;
            }

            .user-name {
                color: #f9fafb;
            }

            .footer {
                background-color: #374151;
                border-color: #4b5563;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="logo">HT</div>
                <div class="header-text">
                    <h1>Hidden Treasures Dashboard</h1>
                    <p>Administrative Alert System</p>
                </div>
            </div>
        </div>

        <!-- Severity Indicator -->
        <div class="severity-indicator">
            <div class="severity-badge">{{ $severity_display ?? 'Alert' }}</div>
            <div class="severity-title">{{ $title ?? 'System Alert' }}</div>
            <div class="severity-description">{{ $description ?? 'An important event requires your attention.' }}</div>
        </div>

        <!-- Content -->
        <div class="content">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-content">
                <p>This alert was sent from the Hidden Treasures Dashboard administrative system.</p>
                <p>If you believe this was sent in error, please contact your system administrator.</p>
                <div class="footer-links">
                    <a href="{{ config('app.url') }}" class="footer-link">Dashboard</a>
                    <a href="{{ config('app.url') }}/settings/notifications" class="footer-link">Notification Settings</a>
                    <a href="{{ config('app.url') }}/support" class="footer-link">Support</a>
                </div>
                <p style="margin-top: 16px; font-size: 11px; color: #9ca3af;">
                    Alert ID: {{ $event->occurredAt->format('Y-m-d_H-i-s') }}_{{ $category ?? 'unknown' }}
                </p>
            </div>
        </div>
    </div>
</body>
</html>