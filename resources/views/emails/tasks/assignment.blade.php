<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Task Assigned</title>
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
        .task-details {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .task-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .task-meta {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
        }
        .priority-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .priority-high {
            background-color: #f8d7da;
            color: #721c24;
        }
        .priority-medium {
            background-color: #fff3cd;
            color: #856404;
        }
        .priority-low {
            background-color: #d4edda;
            color: #155724;
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
        .assigned-by {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/ht-logo.png') }}" alt="Hidden Treasures Logo" class="logo">
            <h1 class="title">New Task Assigned</h1>
        </div>

        <div class="content">
            <p>Hello {{ $user->name }},</p>
            
            <p>You have been assigned a new task in the Hidden Treasures Dashboard. Please review the details below and take appropriate action.</p>

            <div class="task-details">
                <h3 class="task-title">{{ $task->title }}</h3>
                
                @if($task->description)
                    <p>{{ $task->description }}</p>
                @endif
                
                <div class="task-meta">
                    <strong>Priority:</strong> 
                    <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                </div>
                
                @if($task->due_date)
                    <div class="task-meta">
                        <strong>Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y') }}
                    </div>
                @endif
                
                <div class="task-meta">
                    <strong>Status:</strong> {{ ucfirst($task->status) }}
                </div>
            </div>

            @if($assignedBy)
                <div class="assigned-by">
                    <strong>Assigned by:</strong> {{ $assignedBy->name }}
                    @if($assignedBy->email)
                        ({{ $assignedBy->email }})
                    @endif
                </div>
            @endif

            <p>Please log in to the dashboard to view the complete task details and update the status as needed.</p>

            <div>
                <a href="{{ $taskUrl }}" class="btn">View Task</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about this task, please contact your team manager.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>