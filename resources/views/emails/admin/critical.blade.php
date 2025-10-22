@extends('emails.admin.base')

@section('content')
    <!-- Critical Alert Banner -->
    <div style="background-color: #dc2626; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            🚨 CRITICAL ALERT 🚨
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Immediate attention required - This is a critical system event
        </div>
    </div>

    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>🔥</span> Critical Event Details
        </div>
        <div class="event-details">
            <div class="detail-row">
                <div class="detail-label">Event Type</div>
                <div class="detail-value">{{ class_basename($event) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Severity</div>
                <div class="detail-value" style="color: #dc2626; font-weight: 600;">CRITICAL</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Occurred At</div>
                <div class="detail-value">{{ $occurred_at->format('M j, Y g:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Time Since Event</div>
                <div class="detail-value">{{ $occurred_at->diffForHumans() }}</div>
            </div>
            @if(isset($context['impact_level']))
            <div class="detail-row">
                <div class="detail-label">Impact Level</div>
                <div class="detail-value" style="color: #dc2626; font-weight: 600;">{{ $context['impact_level'] }}</div>
            </div>
            @endif
            @if(isset($context['affected_systems']))
            <div class="detail-row">
                <div class="detail-label">Affected Systems</div>
                <div class="detail-value">{{ $context['affected_systems'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Critical Information -->
    <div class="section">
        <div class="section-title">
            <span>⚠️</span> Critical Information
        </div>
        <div class="section-content">
            <div style="background-color: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; color: #dc2626; margin-bottom: 8px;">
                    {{ $title }}
                </div>
                <div style="color: #991b1b; line-height: 1.6;">
                    {{ $description }}
                </div>
            </div>

            @if(isset($context['critical_details']))
            <div style="background-color: #fef2f2; border-radius: 6px; padding: 12px; border: 1px solid #fca5a5;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #dc2626;">Critical Details:</h4>
                @foreach($context['critical_details'] as $detail)
                <div style="padding: 4px 0; border-bottom: 1px solid #fecaca; color: #991b1b;">
                    {{ $detail }}
                </div>
                @endforeach
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
            <div class="user-avatar" style="background-color: #dc2626; color: white;">
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

    <!-- Immediate Actions Required -->
    <div class="section">
        <div class="section-title">
            <span>🚨</span> Immediate Actions Required
        </div>
        <div class="section-content">
            <div style="background-color: #dc2626; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    ⚡ IMMEDIATE RESPONSE REQUIRED ⚡
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @if($category === 'Security')
                    <li style="margin-bottom: 8px;">Review security logs immediately</li>
                    <li style="margin-bottom: 8px;">Verify system integrity and data safety</li>
                    <li style="margin-bottom: 8px;">Check for unauthorized access or data breaches</li>
                    <li style="margin-bottom: 8px;">Consider immediate system lockdown if necessary</li>
                    <li style="margin-bottom: 8px;">Document all findings and actions taken</li>
                    @elseif($category === 'System')
                    <li style="margin-bottom: 8px;">Check system status and availability</li>
                    <li style="margin-bottom: 8px;">Verify data integrity and backups</li>
                    <li style="margin-bottom: 8px;">Implement emergency fixes if available</li>
                    <li style="margin-bottom: 8px;">Monitor system performance closely</li>
                    <li style="margin-bottom: 8px;">Prepare for potential service disruption</li>
                    @elseif($category === 'Business')
                    <li style="margin-bottom: 8px;">Review business impact and financial exposure</li>
                    <li style="margin-bottom: 8px;">Contact relevant stakeholders immediately</li>
                    <li style="margin-bottom: 8px;">Verify transaction accuracy and compliance</li>
                    <li style="margin-bottom: 8px;">Document for audit and legal purposes</li>
                    <li style="margin-bottom: 8px;">Consider business continuity measures</li>
                    @else
                    <li style="margin-bottom: 8px;">Investigate the critical event immediately</li>
                    <li style="margin-bottom: 8px;">Assess impact on operations and users</li>
                    <li style="margin-bottom: 8px;">Implement mitigation strategies</li>
                    <li style="margin-bottom: 8px;">Monitor for escalation or related events</li>
                    <li style="margin-bottom: 8px;">Communicate with affected parties</li>
                    @endif
                </ul>
            </div>

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" style="display: inline-block; padding: 16px 32px; background-color: #dc2626; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; transition: all 0.2s;">
                    🚨 TAKE IMMEDIATE ACTION 🚨
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Contact Information -->
    <div class="section">
        <div class="section-title">
            <span>📞</span> Emergency Contacts
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">System Administrator:</span>
                    <span style="color: #1e40af;">{{ config('admin.email', 'admin@hiddentreasures.com') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Emergency Phone:</span>
                    <span style="color: #1e40af;">{{ config('emergency.phone', '+1-555-EMERGENCY') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Slack Channel:</span>
                    <span style="color: #1e40af;">#critical-alerts</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['stack_trace']) || isset($context['error_logs']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['error_logs']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Error Logs:</strong>
            <pre>{{ $context['error_logs'] }}</pre>
            @endif
            
            @if(isset($context['stack_trace']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Stack Trace:</strong>
            <pre>{{ $context['stack_trace'] }}</pre>
            @endif
        </div>
    </div>
    @endif

    <!-- Footer Notice -->
    <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; padding: 12px; margin-top: 24px; text-align: center;">
        <div style="color: #dc2626; font-weight: 600; font-size: 14px; margin-bottom: 4px;">
            ⚠️ This is a CRITICAL alert requiring immediate attention
        </div>
        <div style="color: #991b1b; font-size: 12px;">
            If you are unable to respond, please escalate to your supervisor or the system administrator immediately.
        </div>
    </div>
@endsection