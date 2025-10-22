<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Goal Progress Update</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f8f9fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e9ecef;
        }
        .logo {
            max-width: 120px;
            margin-bottom: 15px;
        }
        .title {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }
        .content {
            margin-bottom: 30px;
        }
        .goal-details {
            background-color: #f8f9fa;
            border-left: 4px solid {{ $progressPercentage >= 100 ? '#28a745' : '#007bff' }};
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .goal-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .goal-meta {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
        }
        .progress-container {
            background-color: #e9ecef;
            border-radius: 10px;
            height: 20px;
            margin: 15px 0;
            overflow: hidden;
        }
        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #007bff, #0056b3);
            border-radius: 10px;
            transition: width 0.3s ease;
        }
        .progress-bar.completed {
            background: linear-gradient(90deg, #28a745, #1e7e34);
        }
        .progress-text {
            font-size: 18px;
            font-weight: 600;
            color: {{ $progressPercentage >= 100 ? '#28a745' : '#007bff' }};
            text-align: center;
            margin: 10px 0;
        }
        .milestone-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            background-color: {{ $progressPercentage >= 100 ? '#d4edda' : '#d1ecf1' }};
            color: {{ $progressPercentage >= 100 ? '#155724' : '#0c5460' }};
            margin: 10px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 15px 10px 15px 0;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #545b62;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 14px;
        }
        .goal-period {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .progress-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        .completed {
            color: #28a745;
        }
        .in-progress {
            color: #007bff;
        }
        .celebration {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            text-align: center;
        }
        .target-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/ht-logo.png') }}" alt="Hidden Treasures Logo" class="logo">
            <h1 class="title">Goal Progress Update</h1>
        </div>

        <div class="content">
            <div class="progress-icon {{ $progressPercentage >= 100 ? 'completed' : 'in-progress' }}">
                {{ $progressPercentage >= 100 ? '🎉' : '📈' }}
            </div>
            
            <p>Hello {{ $user->name }},</p>
            
            @if($progressPercentage >= 100)
                <p>Congratulations! You've achieved your goal in the Hidden Treasures Dashboard.</p>
            @else
                <p>Great progress! You've reached an important milestone for your goal.</p>
            @endif

            <div class="goal-details">
                <h3 class="goal-title">{{ $goal->title }}</h3>
                
                @if($goal->description)
                    <p>{{ $goal->description }}</p>
                @endif
                
                <div class="progress-text">{{ number_format($progressPercentage, 1) }}% Complete</div>
                
                <div class="progress-container">
                    <div class="progress-bar {{ $progressPercentage >= 100 ? 'completed' : '' }}" style="width: {{ min($progressPercentage, 100) }}%"></div>
                </div>
                
                <div style="text-align: center; margin-top: 10px;">
                    <span class="milestone-badge">{{ $milestone }}</span>
                </div>
            </div>

            <div class="target-info">
                <strong>Target:</strong> {{ $goal->target_value }} {{ $goal->target_unit }}<br>
                <strong>Current:</strong> {{ $goal->current_value }} {{ $goal->target_unit }}
            </div>

            <div class="goal-period">
                <strong>Period:</strong> {{ \Carbon\Carbon::parse($goal->start_date)->format('F j, Y') }} - {{ \Carbon\Carbon::parse($goal->end_date)->format('F j, Y') }}
            </div>

            @if($progressPercentage >= 100)
                <div class="celebration">
                    <strong>🎊 Congratulations! 🎊</strong><br>
                    You've successfully completed this goal. Your dedication and hard work have paid off!
                </div>
            @endif

            <p>Keep up the excellent work! Every step forward is progress toward your objectives.</p>

            <div>
                <a href="{{ $goalUrl }}" class="btn">View Goal</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about your goals, please contact your team manager.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>