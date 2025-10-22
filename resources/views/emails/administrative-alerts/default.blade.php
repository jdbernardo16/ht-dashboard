@extends('emails.administrative-alerts.base')

@section('content')
    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>📋</span> Event Details
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
                <div class="detail-label">Category</div>
                <div class="detail-value">{{ $category }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Occurred At</div>
                <div class="detail-value">{{ $occurred_at->format('M j, Y g:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Time Since Event</div>
                <div class="detail-value">{{ $occurred_at->diffForHumans() }}</div>
            </div>
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

    <!-- Event Description -->
    <div class="section">
        <div class="section-title">
            <span>📝</span> Event Description
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 16px; border: 1px solid #e5e7eb;">
                <p style="margin-bottom: 0;">{{ $description }}</p>
            </div>
        </div>
    </div>

    <!-- Additional Context -->
    @if(!empty($context))
    <div class="section">
        <div class="section-title">
            <span>🔍</span> Additional Context
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                @foreach($context as $key => $value)
                @if(!in_array($key, ['ip_address', 'user_agent', 'location', 'server_name', 'business_type', 'transaction_id', 'amount', 'action_type', 'affected_resource', 'quantity']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151; font-size: 13px;">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                    <span style="color: #6b7280; font-size: 13px;">
                        @if(is_array($value))
                            {{ json_encode($value) }}
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
                @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recommended Actions -->
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Recommended Actions
        </div>
        <div class="section-content">
            @if($severity === 'CRITICAL')
            <p><strong>Immediate action required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review this event immediately</li>
                <li>Assess potential impact on operations</li>
                <li>Take appropriate mitigation measures</li>
                <li>Document findings and actions taken</li>
            </ul>
            @elseif($severity === 'HIGH')
            <p><strong>Priority attention required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review this event within the next hour</li>
                <li>Assess potential impact and risks</li>
                <li>Monitor for related events</li>
                <li>Document for future reference</li>
            </ul>
            @else
            <p><strong>Monitor and review:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review during regular checks</li>
                <li>Monitor for patterns or trends</li>
                <li>Update procedures if needed</li>
                <li>Document for audit purposes</li>
            </ul>
            @endif

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" class="action-button">Review Event Details</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['stack_trace']) || isset($context['error_message']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['error_message']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Error Message:</strong>
            <pre>{{ $context['error_message'] }}</pre>
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