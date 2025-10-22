@extends('emails.admin.base')

@section('content')
    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>🔒</span> Security Event Details
        </div>
        <div class="event-details">
            <div class="detail-row">
                <div class="detail-label">Event Type</div>
                <div class="detail-value">{{ class_basename($event) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Severity</div>
                <div class="detail-value">{{ $severity_display }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Occurred At</div>
                <div class="detail-value">{{ $occurred_at->format('M j, Y g:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">IP Address</div>
                <div class="detail-value">{{ $context['ip_address'] ?? 'Unknown' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">User Agent</div>
                <div class="detail-value">{{ $context['user_agent'] ?? 'Unknown' }}</div>
            </div>
            @if(isset($context['location']))
            <div class="detail-row">
                <div class="detail-label">Location</div>
                <div class="detail-value">{{ $context['location'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- User Information -->
    @if($initiated_by)
    <div class="section">
        <div class="section-title">
            <span>👤</span> User Information
        </div>
        <div class="user-info">
            <div class="user-avatar">
                {{ strtoupper(substr($initiated_by->name, 0, 1)) }}
            </div>
            <div class="user-details">
                <div class="user-name">{{ $initiated_by->name }}</div>
                <div class="user-email">{{ $initiated_by->email }}</div>
                <div class="user-email">Role: {{ $initiated_by->role ?? 'Unknown' }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- Security Details -->
    <div class="section">
        <div class="section-title">
            <span>🛡️</span> Security Information
        </div>
        <div class="section-content">
            <p>{{ $description }}</p>
            
            @if(isset($context['security_details']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Additional Details:</h4>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @foreach($context['security_details'] as $detail)
                    <li style="margin-bottom: 4px;">{{ $detail }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Recommended Actions -->
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Recommended Actions
        </div>
        <div class="section-content">
            @if($severity === 'CRITICAL')
            <p><strong>Immediate action required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review the security event details immediately</li>
                <li>Verify the user's identity and authorization</li>
                <li>Check for any unauthorized access or data breaches</li>
                <li>Consider temporarily restricting access if suspicious</li>
            </ul>
            @elseif($severity === 'HIGH')
            <p><strong>Priority attention required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Investigate the security event within the next hour</li>
                <li>Review user activity logs for related events</li>
                <li>Validate the legitimacy of the action</li>
            </ul>
            @else
            <p><strong>Monitor and review:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review the event during regular security checks</li>
                <li>Monitor for similar patterns or repeated events</li>
                <li>Update security policies if needed</li>
            </ul>
            @endif

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" class="action-button">Review Security Event</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['stack_trace']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['stack_trace']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Stack Trace:</strong>
            <pre>{{ $context['stack_trace'] }}</pre>
            @endif
        </div>
    </div>
    @endif
@endsection