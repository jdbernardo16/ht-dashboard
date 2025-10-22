@extends('emails.admin.base')

@section('content')
    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>🖥️</span> System Event Details
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
                <div class="detail-label">Server</div>
                <div class="detail-value">{{ $context['server_name'] ?? config('app.name', 'Production Server') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Environment</div>
                <div class="detail-value">{{ config('app.env', 'production') }}</div>
            </div>
            @if(isset($context['affected_service']))
            <div class="detail-row">
                <div class="detail-label">Affected Service</div>
                <div class="detail-value">{{ $context['affected_service'] }}</div>
            </div>
            @endif
            @if(isset($context['duration']))
            <div class="detail-row">
                <div class="detail-label">Duration</div>
                <div class="detail-value">{{ $context['duration'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- System Status -->
    <div class="section">
        <div class="section-title">
            <span>📊</span> System Status
        </div>
        <div class="section-content">
            <p>{{ $description }}</p>
            
            @if(isset($context['system_status']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Current System Status:</h4>
                <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                    @foreach($context['system_status'] as $component => $status)
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span style="font-weight: 500; color: #374151;">{{ $component }}:</span>
                        <span style="color: {{ $status === 'operational' ? '#16a34a' : ($status === 'degraded' ? '#ca8a04' : '#dc2626') }}; font-weight: 600;">
                            {{ ucfirst($status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Performance Impact -->
    @if(isset($context['performance_impact']))
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Performance Impact
        </div>
        <div class="section-content">
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #92400e;">Performance Metrics:</h4>
                @foreach($context['performance_impact'] as $metric => $value)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #92400e;">{{ $metric }}:</span>
                    <span style="color: #78350f; font-weight: 600;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Error Information -->
    @if(isset($context['error_message']) || isset($context['error_code']))
    <div class="section">
        <div class="section-title">
            <span>❌</span> Error Information
        </div>
        <div class="section-content">
            @if(isset($context['error_code']))
            <p><strong>Error Code:</strong> {{ $context['error_code'] }}</p>
            @endif
            
            @if(isset($context['error_message']))
            <p><strong>Error Message:</strong> {{ $context['error_message'] }}</p>
            @endif
            
            @if(isset($context['error_context']))
            <div style="margin-top: 12px;">
                <strong>Context:</strong>
                <ul style="margin-left: 20px; margin-top: 8px;">
                    @foreach($context['error_context'] as $key => $value)
                    <li style="margin-bottom: 4px;"><strong>{{ $key }}:</strong> {{ $value }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Recommended Actions -->
    <div class="section">
        <div class="section-title">
            <span>🔧</span> Recommended Actions
        </div>
        <div class="section-content">
            @if($severity === 'CRITICAL')
            <p><strong>Immediate action required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Investigate the system issue immediately</li>
                <li>Check system logs for related errors</li>
                <li>Verify system resources and availability</li>
                <li>Consider implementing emergency fixes</li>
                <li>Monitor system performance closely</li>
            </ul>
            @elseif($severity === 'HIGH')
            <p><strong>Priority attention required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review system logs and diagnostics</li>
                <li>Check affected services and dependencies</li>
                <li>Plan for system maintenance or fixes</li>
                <li>Monitor for escalation</li>
            </ul>
            @else
            <p><strong>Monitor and review:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Monitor system performance metrics</li>
                <li>Review during regular maintenance windows</li>
                <li>Document for future reference</li>
            </ul>
            @endif

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" class="action-button">View System Dashboard</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['stack_trace']) || isset($context['logs']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['logs']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>System Logs:</strong>
            <pre>{{ $context['logs'] }}</pre>
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