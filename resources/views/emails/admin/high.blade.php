@extends('emails.admin.base')

@section('content')
    <!-- High Alert Banner -->
    <div style="background-color: #ea580c; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            ⚠️ HIGH PRIORITY ALERT ⚠️
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Priority attention required - This is a high-priority system event
        </div>
    </div>

    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>🔥</span> High Priority Event Details
        </div>
        <div class="event-details">
            <div class="detail-row">
                <div class="detail-label">Event Type</div>
                <div class="detail-value">{{ class_basename($event) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Severity</div>
                <div class="detail-value" style="color: #ea580c; font-weight: 600;">HIGH</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Occurred At</div>
                <div class="detail-value">{{ $occurred_at->format('M j, Y g:i A') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Time Since Event</div>
                <div class="detail-value">{{ $occurred_at->diffForHumans() }}</div>
            </div>
            @if(isset($context['priority_level']))
            <div class="detail-row">
                <div class="detail-label">Priority Level</div>
                <div class="detail-value" style="color: #ea580c; font-weight: 600;">{{ $context['priority_level'] }}</div>
            </div>
            @endif
            @if(isset($context['affected_components']))
            <div class="detail-row">
                <div class="detail-label">Affected Components</div>
                <div class="detail-value">{{ $context['affected_components'] }}</div>
            </div>
            @endif
            @if(isset($context['urgency']))
            <div class="detail-row">
                <div class="detail-label">Urgency</div>
                <div class="detail-value">{{ $context['urgency'] }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- High Priority Information -->
    <div class="section">
        <div class="section-title">
            <span>⚠️</span> High Priority Information
        </div>
        <div class="section-content">
            <div style="background-color: #fff7ed; border: 2px solid #ea580c; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; color: #ea580c; margin-bottom: 8px;">
                    {{ $title }}
                </div>
                <div style="color: #c2410c; line-height: 1.6;">
                    {{ $description }}
                </div>
            </div>

            @if(isset($context['high_priority_details']))
            <div style="background-color: #fff7ed; border-radius: 6px; padding: 12px; border: 1px solid #fdba74;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #ea580c;">High Priority Details:</h4>
                @foreach($context['high_priority_details'] as $detail)
                <div style="padding: 4px 0; border-bottom: 1px solid #fed7aa; color: #c2410c;">
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
            <div class="user-avatar" style="background-color: #ea580c; color: white;">
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

    <!-- Priority Actions Required -->
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Priority Actions Required
        </div>
        <div class="section-content">
            <div style="background-color: #ea580c; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    ⚠️ PRIORITY ATTENTION REQUIRED ⚠️
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @if($category === 'Security')
                    <li style="margin-bottom: 8px;">Review security event within the next hour</li>
                    <li style="margin-bottom: 8px;">Verify user authorization and activity legitimacy</li>
                    <li style="margin-bottom: 8px;">Check for potential security vulnerabilities</li>
                    <li style="margin-bottom: 8px;">Monitor for related security events</li>
                    <li style="margin-bottom: 8px;">Document findings and preventive measures</li>
                    @elseif($category === 'System')
                    <li style="margin-bottom: 8px;">Investigate system performance issues</li>
                    <li style="margin-bottom: 8px;">Check system resources and dependencies</li>
                    <li style="margin-bottom: 8px;">Review error logs and diagnostics</li>
                    <li style="margin-bottom: 8px;">Plan for system recovery or fixes</li>
                    <li style="margin-bottom: 8px;">Monitor for potential escalation</li>
                    @elseif($category === 'Business')
                    <li style="margin-bottom: 8px;">Review business impact and implications</li>
                    <li style="margin-bottom: 8px;">Verify transaction accuracy and compliance</li>
                    <li style="margin-bottom: 8px;">Contact relevant business stakeholders</li>
                    <li style="margin-bottom: 8px;">Update business records and reports</li>
                    <li style="margin-bottom: 8px;">Monitor for financial impacts</li>
                    @else
                    <li style="margin-bottom: 8px;">Investigate the high-priority event promptly</li>
                    <li style="margin-bottom: 8px;">Assess potential impact on operations</li>
                    <li style="margin-bottom: 8px;">Implement appropriate mitigation measures</li>
                    <li style="margin-bottom: 8px;">Monitor for related or escalating events</li>
                    <li style="margin-bottom: 8px;">Prepare documentation for review</li>
                    @endif
                </ul>
            </div>

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" style="display: inline-block; padding: 14px 28px; background-color: #ea580c; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; text-align: center; transition: all 0.2s;">
                    ⚡ TAKE PRIORITY ACTION ⚡
                </a>
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
            <div style="background-color: #fff7ed; border-radius: 6px; padding: 12px; border: 1px solid #fdba74;">
                @foreach($context['impact_assessment'] as $aspect => $impact)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #c2410c;">{{ ucfirst($aspect) }}:</span>
                    <span style="color: #9a3412; font-weight: 600;">{{ $impact }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Timeline Information -->
    @if(isset($context['timeline']))
    <div class="section">
        <div class="section-title">
            <span>⏰</span> Event Timeline
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                @foreach($context['timeline'] as $event)
                <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #374151;">{{ $event['action'] ?? 'Unknown' }}</span>
                        <span style="color: #6b7280; font-size: 12px;">{{ $event['time'] ?? 'Unknown' }}</span>
                    </div>
                    @if(isset($event['details']))
                    <div style="color: #6b7280; font-size: 12px;">{{ $event['details'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['error_message']) || isset($context['diagnostics']))
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
            
            @if(isset($context['diagnostics']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>System Diagnostics:</strong>
            <pre>{{ $context['diagnostics'] }}</pre>
            @endif
        </div>
    </div>
    @endif

    <!-- Contact Information -->
    <div class="section">
        <div class="section-title">
            <span>📞</span> Contact Information
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Primary Contact:</span>
                    <span style="color: #1e40af;">{{ config('admin.email', 'admin@hiddentreasures.com') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Support Channel:</span>
                    <span style="color: #1e40af;">#high-priority-alerts</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Response Time:</span>
                    <span style="color: #1e40af;">Within 1 hour</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Notice -->
    <div style="background-color: #fff7ed; border: 1px solid #fdba74; border-radius: 6px; padding: 12px; margin-top: 24px; text-align: center;">
        <div style="color: #ea580c; font-weight: 600; font-size: 14px; margin-bottom: 4px;">
            ⚠️ This is a HIGH PRIORITY alert requiring prompt attention
        </div>
        <div style="color: #c2410c; font-size: 12px;">
            Please review and respond within the next hour to prevent potential escalation.
        </div>
    </div>
@endsection