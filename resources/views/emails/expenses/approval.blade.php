<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense {{ ucfirst($expense->status) }}</title>
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
        .expense-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 6px;
            border-left: 4px solid {{ $expense->status === 'approved' ? '#28a745' : '#dc3545' }};
        }
        .expense-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .expense-meta {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: 700;
            color: {{ $expense->status === 'approved' ? '#28a745' : '#dc3545' }};
            margin: 10px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            background-color: {{ $expense->status === 'approved' ? '#d4edda' : '#f8d7da' }};
            color: {{ $expense->status === 'approved' ? '#155724' : '#721c24' }};
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
        .category-info {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .approval-date {
            background-color: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .status-icon {
            font-size: 48px;
            text-align: center;
            margin: 20px 0;
        }
        .approved {
            color: #28a745;
        }
        .rejected {
            color: #dc3545;
        }
        .notes-section {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
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
            <h1 class="title">Expense {{ ucfirst($expense->status) }}</h1>
        </div>

        <div class="content">
            <div class="status-icon {{ $expense->status }}">
                {{ $expense->status === 'approved' ? '✓' : '✗' }}
            </div>
            
            <p>Hello {{ $user->name }},</p>
            
            @if($expense->status === 'approved')
                <p>Good news! Your expense has been approved in the Hidden Treasures Dashboard.</p>
            @else
                <p>Your expense has been reviewed and could not be approved at this time.</p>
            @endif

            <div class="expense-details">
                <h3 class="expense-title">{{ $expense->description }}</h3>
                
                <div class="amount">${{ number_format($expense->amount, 2) }}</div>
                
                <div class="expense-meta">
                    <strong>Status:</strong> 
                    <span class="status-badge">{{ ucfirst($expense->status) }}</span>
                </div>
                
                <div class="expense-meta">
                    <strong>Expense Date:</strong> {{ \Carbon\Carbon::parse($expense->expense_date)->format('F j, Y') }}
                </div>
                
                <div class="expense-meta">
                    <strong>Submitted:</strong> {{ $expense->created_at->format('F j, Y \a\t g:i A') }}
                </div>
            </div>

            @if($category)
                <div class="category-info">
                    <strong>Category:</strong> {{ $category }}
                </div>
            @endif

            @if($expense->notes)
                <div class="notes-section">
                    <strong>Notes:</strong><br>
                    {{ $expense->notes }}
                </div>
            @endif

            <div class="approval-date">
                <strong>{{ ucfirst($expense->status) }} on:</strong> {{ $expense->updated_at->format('F j, Y \a\t g:i A') }}
            </div>

            @if($expense->status === 'approved')
                <p>This expense will be processed according to the regular reimbursement schedule. If you have any questions about the payment timeline, please contact your manager.</p>
            @else
                <p>If you have questions about this decision or would like to provide additional information, please contact your manager.</p>
            @endif

            <div>
                <a href="{{ $expenseUrl }}" class="btn">View Expense</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about this expense, please contact your manager.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>