<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sale Recorded</title>
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
        .sale-details {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .sale-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .sale-meta {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
        }
        .amount {
            font-size: 24px;
            font-weight: 700;
            color: #28a745;
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
        .client-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .sale-date {
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
        .commission-info {
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
            <h1 class="title">New Sale Recorded</h1>
        </div>

        <div class="content">
            <div class="success-icon">💰</div>
            
            <p>Hello {{ $user->name }},</p>
            
            <p>Excellent news! A new sale has been recorded in the Hidden Treasures Dashboard.</p>

            <div class="sale-details">
                <h3 class="sale-title">Sale to {{ $client->name }}</h3>
                
                <div class="amount">${{ number_format($sale->amount, 2) }}</div>
                
                @if($sale->description)
                    <p><strong>Description:</strong> {{ $sale->description }}</p>
                @endif
                
                <div class="sale-meta">
                    <strong>Sale Date:</strong> {{ \Carbon\Carbon::parse($sale->sale_date)->format('F j, Y') }}
                </div>
                
                <div class="sale-meta">
                    <strong>Status:</strong> {{ ucfirst($sale->status) }}
                </div>
            </div>

            <div class="client-info">
                <strong>Client Information:</strong><br>
                {{ $client->name }}<br>
                @if($client->email)
                    {{ $client->email }}<br>
                @endif
                @if($client->phone)
                    {{ $client->phone }}
                @endif
            </div>

            @if($sale->commission_rate || $sale->commission_amount)
                <div class="commission-info">
                    <strong>Commission Details:</strong><br>
                    @if($sale->commission_rate)
                        Rate: {{ $sale->commission_rate }}%<br>
                    @endif
                    @if($sale->commission_amount)
                        Amount: ${{ number_format($sale->commission_amount, 2) }}
                    @endif
                </div>
            @endif

            <div class="sale-date">
                <strong>Recorded on:</strong> {{ $sale->created_at->format('F j, Y \a\t g:i A') }}
            </div>

            <p>Please review the sale details and ensure all information is accurate.</p>

            <div>
                <a href="{{ $saleUrl }}" class="btn">View Sale</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about this sale, please contact your sales team.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>