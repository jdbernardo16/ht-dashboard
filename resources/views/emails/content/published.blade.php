<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Published</title>
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
        .content-details {
            background-color: #f8f9fa;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .content-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        .content-meta {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
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
        .platform-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            background-color: #d1ecf1;
            color: #0c5460;
            margin-left: 5px;
        }
        .category-tag {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 11px;
            background-color: #e9ecef;
            color: #495057;
            margin: 2px 4px 2px 0;
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
        .publish-date {
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
        .content-excerpt {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-style: italic;
            color: #6c757d;
        }
        .platform-info {
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
            <h1 class="title">Content Published</h1>
        </div>

        <div class="content">
            <div class="success-icon">📝</div>
            
            <p>Hello {{ $user->name }},</p>
            
            <p>Great news! New content has been published in the Hidden Treasures Dashboard.</p>

            <div class="content-details">
                <h3 class="content-title">{{ $contentPost->title }}</h3>
                
                <div class="content-meta">
                    <strong>Status:</strong> 
                    <span class="status-badge">{{ ucfirst($contentPost->status) }}</span>
                    <span class="platform-badge">{{ ucfirst($contentPost->platform) }}</span>
                </div>

                @if($contentPost->content)
                    <div class="content-excerpt">
                        {{ Str::limit(strip_tags($contentPost->content), 150) }}
                        @if(Str::length(strip_tags($contentPost->content)) > 150)
                            ... <a href="{{ $contentUrl }}">Read more</a>
                        @endif
                    </div>
                @endif
                
                <div class="content-meta">
                    <strong>Categories:</strong><br>
                    @if($categories->count() > 0)
                        @foreach($categories as $category)
                            <span class="category-tag">{{ $category->name }}</span>
                        @endforeach
                    @else
                        <em>No categories assigned</em>
                    @endif
                </div>
            </div>

            <div class="platform-info">
                <strong>Platform:</strong> {{ ucfirst($contentPost->platform) }}<br>
                @if($contentPost->scheduled_date)
                    <strong>Scheduled for:</strong> {{ \Carbon\Carbon::parse($contentPost->scheduled_date)->format('F j, Y \a\t g:i A') }}<br>
                @endif
                <strong>Content Type:</strong> {{ ucfirst($contentPost->content_type) }}
            </div>

            <div class="publish-date">
                <strong>Published on:</strong> {{ $contentPost->published_at ? \Carbon\Carbon::parse($contentPost->published_at)->format('F j, Y \a\t g:i A') : $contentPost->updated_at->format('F j, Y \a\t g:i A') }}
            </div>

            <p>This content is now live and available to your audience. Please monitor engagement and performance metrics through the dashboard.</p>

            <div>
                <a href="{{ $contentUrl }}" class="btn">View Content</a>
                <a href="{{ $dashboardUrl }}" class="btn btn-secondary">Go to Dashboard</a>
            </div>
        </div>

        <div class="footer">
            <p>This is an automated notification from the Hidden Treasures Dashboard. If you have any questions about this content, please contact your content manager.</p>
            <p>&copy; {{ date('Y') }} Hidden Treasures. All rights reserved.</p>
        </div>
    </div>
</body>
</html>