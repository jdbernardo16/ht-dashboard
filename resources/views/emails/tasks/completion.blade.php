<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Completed</title>
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
            border-left: 4px solid #28a745;
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
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
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
        .completed-by {
            background-color: #d4edda;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .completion-date {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .success-icon {
            color: #28a745;
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/ht-logo.png') }}" alt="Hidden Treasures Logo" class="logo">
            <h1 class="title">Task Completed</h1>
        </div>

        <div class="content">
            <div class="success-icon">✓</div>
            
            <p>Hello {{ $user->name }},</p>
            
            <p>Great news! A task has been marked as completed in the Hidden Treasures Dashboard.</p>

            <div class="task-details">
                <h3 class="task-title">{{ $task->title }}</h3>
                
                @if($task->description)
                    <p>{{ $task->description }}</p>
                @endif
                
                <div class="task-meta">
                    <strong>Status:</strong> 
                    <span class="status-badge">{{ ucfirst($task->status) }}</span>
                </div>
                
                <div class="task-meta">
                    <strong>Priority:</strong> 
                    <span class="priority-badge priority-{{ $task->priority }}">{{ $task->priority }}</span>
                </div>
                
                @if($task->due_date)
                    <div class="task-meta">
                        <strong>Original Due Date:</strong> {{ \Carbon\Carbon::parse($task->due_date)->format('F j, Y') }}
                    </div>
                @endif
            </div>

            @if($completedBy)
                <div class="completed-by">
                    <strong>Completed by:</strong> {{ $completedBy->name }}
                    @if($completedBy->email)
                        ({{ $completedBy->email }})
                    @endif
                </div>
            @endif

            <div class="completion-date">
                <strong>Completed on:</strong> {{ $task->updated_at->format('F j, Y \a\t g:i A') }}
            </div>

            <p>Please review the completed task and provide any necessary feedback or next steps.</p>

            <div>
                <a href="{{ $taskUrl }}" class="btn">View Task</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about this task completion, please contact the team member who completed it.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>