@extends('emails.administrative-alerts.base')

@section('content')
    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>👤</span> User Action Details
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
            @if(isset($context['action_type']))
            <div class="detail-row">
                <div class="detail-label">Action Type</div>
                <div class="detail-value">{{ $context['action_type'] }}</div>
            </div>
            @endif
            @if(isset($context['affected_resource']))
            <div class="detail-row">
                <div class="detail-label">Affected Resource</div>
                <div class="detail-value">{{ $context['affected_resource'] }}</div>
            </div>
            @endif
            @if(isset($context['quantity']))
            <div class="detail-row">
                <div class="detail-label">Quantity</div>
                <div class="detail-value">{{ $context['quantity'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- User Information -->
    @if($initiated_by)
    <div class="section">
        <div class="section-title">
            <span>🔐</span> User Information
        </div>
        <div class="user-info">
            <div class="user-avatar">
                {{ strtoupper(substr($initiated_by->name, 0, 1)) }}
            </div>
            <div class="user-details">
                <div class="user-name">{{ $initiated_by->name }}</div>
                <div class="user-email">{{ $initiated_by->email }}</div>
                <div class="user-email">Role: {{ $initiated_by->role ?? 'Unknown' }}</div>
                @if(isset($context['session_duration']))
                <div class="user-email">Session Duration: {{ $context['session_duration'] }}</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Action Details -->
    <div class="section">
        <div class="section-title">
            <span>📋</span> Action Details
        </div>
        <div class="section-content">
            <p>{{ $description }}</p>
            
            @if(isset($context['action_details']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Additional Information:</h4>
                <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                    @foreach($context['action_details'] as $key => $value)
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span style="font-weight: 500; color: #374151;">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                        <span style="color: #6b7280;">{{ $value }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($context['affected_items']) && is_array($context['affected_items']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Affected Items:</h4>
                <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                    @foreach($context['affected_items'] as $item)
                    <div style="padding: 4px 0; border-bottom: 1px solid #fde68a;">
                        <strong>{{ $item['name'] ?? $item['id'] ?? 'Unknown' }}</strong>
                        @if(isset($item['description']))
                        <br><span style="color: #92400e; font-size: 12px;">{{ $item['description'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Impact Assessment -->
    @if(isset($context['impact_assessment']))
    <div class="section">
        <div class="section-title">
            <span>📊</span> Impact Assessment
        </div>
        <div class="section-content">
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                @foreach($context['impact_assessment'] as $aspect => $impact)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #92400e;">{{ ucfirst($aspect) }}:</span>
                    <span style="color: #78350f; font-weight: 600;">{{ $impact }}</span>
                </div>
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
                <li>Review the user action immediately</li>
                <li>Verify user authorization and intent</li>
                <li>Check for any data integrity issues</li>
                <li>Contact the user if clarification needed</li>
                <li>Document the incident for audit trail</li>
            </ul>
            @elseif($severity === 'HIGH')
            <p><strong>Priority attention required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review the user action within the next hour</li>
                <li>Verify the legitimacy of the action</li>
                <li>Check related system impacts</li>
                <li>Monitor for similar actions</li>
            </ul>
            @else
            <p><strong>Monitor and review:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review during regular audits</li>
                <li>Monitor user behavior patterns</li>
                <li>Update user training if needed</li>
            </ul>
            @endif

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" class="action-button">Review User Action</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Audit Trail -->
    @if(isset($context['audit_trail']))
    <div class="section">
        <div class="section-title">
            <span>📜</span> Audit Trail
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                @foreach($context['audit_trail'] as $entry)
                <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #374151;">{{ $entry['action'] ?? 'Unknown Action' }}</span>
                        <span style="color: #6b7280; font-size: 12px;">{{ $entry['timestamp'] ?? 'Unknown Time' }}</span>
                    </div>
                    @if(isset($entry['details']))
                    <div style="color: #6b7280; font-size: 12px;">{{ $entry['details'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['request_data']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['request_data']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Request Data:</strong>
            <pre>{{ json_encode($context['request_data'], JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
    </div>
    @endif
@endsection