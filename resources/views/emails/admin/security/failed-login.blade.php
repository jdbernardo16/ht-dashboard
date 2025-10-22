@extends('emails.admin.security')

@section('content')
    <!-- Failed Login Specific Banner -->
    <div style="background-color: #dc2626; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            🔐 FAILED LOGIN ATTEMPT 🔐
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Unauthorized login attempt detected - Security verification required
        </div>
    </div>

    <!-- Original Security Content -->
    @parent

    <!-- Failed Login Details -->
    <div class="section">
        <div class="section-title">
            <span>🚫</span> Failed Login Details
        </div>
        <div class="section-content">
            <div style="background-color: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                @if(isset($context['username']))
                <p><strong>Username/Email:</strong> {{ $context['username'] }}</p>
                @endif
                
                @if(isset($context['failure_reason']))
                <p><strong>Failure Reason:</strong> {{ $context['failure_reason'] }}</p>
                @endif
                
                @if(isset($context['attempts_count']))
                <p><strong>Consecutive Failures:</strong> {{ $context['attempts_count'] }}</p>
                @endif
                
                @if(isset($context['last_successful_login']))
                <p><strong>Last Successful Login:</strong> {{ $context['last_successful_login'] }}</p>
                @endif
            </div>

            @if(isset($context['suspicious_indicators']) && !empty($context['suspicious_indicators']))
            <div style="background-color: #fef2f2; border-radius: 6px; padding: 12px; border: 1px solid #fca5a5; margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #dc2626;">🚨 Suspicious Indicators:</h4>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @foreach($context['suspicious_indicators'] as $indicator)
                    <li style="margin-bottom: 4px; color: #991b1b;">{{ $indicator }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Geographic Information -->
    @if(isset($context['location']))
    <div class="section">
        <div class="section-title">
            <span>🌍</span> Geographic Information
        </div>
        <div class="section-content">
            <div style="background-color: #f0f9ff; border-radius: 6px; padding: 12px; border: 1px solid #0ea5e9;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Location:</span>
                    <span style="color: #075985;">{{ $context['location'] }}</span>
                </div>
                @if(isset($context['country']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Country:</span>
                    <span style="color: #075985;">{{ $context['country'] }}</span>
                </div>
                @endif
                @if(isset($context['isp']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">ISP:</span>
                    <span style="color: #075985;">{{ $context['isp'] }}</span>
                </div>
                @endif
                
                @if(isset($context['is_unusual_location']) && $context['is_unusual_location'])
                <div style="background-color: #fef2f2; border-radius: 4px; padding: 8px; margin-top: 12px; border: 1px solid #fca5a5;">
                    <div style="color: #dc2626; font-weight: 600; font-size: 12px; text-align: center;">
                        ⚠️ UNUSUAL LOCATION DETECTED
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- IP Analysis -->
    @if(isset($context['ip_analysis']))
    <div class="section">
        <div class="section-title">
            <span>🔍</span> IP Address Analysis
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                @foreach($context['ip_analysis'] as $aspect => $analysis)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">{{ ucfirst(str_replace('_', ' ', $aspect)) }}:</span>
                    <span style="color: #6b7280; font-weight: {{ in_array($aspect, ['risk_score', 'threat_level']) ? '600' : '400' }};">
                        {{ $analysis }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Security Recommendations -->
    <div class="section">
        <div class="section-title">
            <span>🛡️</span> Security Recommendations
        </div>
        <div class="section-content">
            <div style="background-color: #dc2626; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    🔒 IMMEDIATE SECURITY ACTIONS 🔒
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @if(isset($context['attempts_count']) && $context['attempts_count'] >= 5)
                    <li style="margin-bottom: 8px;">🚨 BLOCK THE IP ADDRESS IMMEDIATELY</li>
                    <li style="margin-bottom: 8px;">Enable account lockout policies</li>
                    @endif
                    <li style="margin-bottom: 8px;">Verify if this is a legitimate user attempt</li>
                    <li style="margin-bottom: 8px;">Check for other suspicious activities from this IP</li>
                    <li style="margin-bottom: 8px;">Review account security settings</li>
                    <li style="margin-bottom: 8px;">Consider enabling two-factor authentication</li>
                    <li style="margin-bottom: 8px;">Monitor for additional failed attempts</li>
                </ul>
            </div>

            @if(isset($context['user_account']) && $context['user_account'])
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24; margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #92400e;">👤 User Account Actions:</h4>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    <li style="margin-bottom: 4px; color: #78350f;">Contact the user to verify their identity</li>
                    <li style="margin-bottom: 4px; color: #78350f;">Check if user recently changed password</li>
                    <li style="margin-bottom: 4px; color: #78350f;">Verify user's current location and activity</li>
                    <li style="margin-bottom: 4px; color: #78350f;">Consider temporary password reset if suspicious</li>
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Recent Failed Attempts -->
    @if(isset($context['recent_attempts']) && !empty($context['recent_attempts']))
    <div class="section">
        <div class="section-title">
            <span>📊</span> Recent Failed Attempts
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                @foreach($context['recent_attempts'] as $attempt)
                <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #374151; font-size: 12px;">
                            {{ $attempt['ip'] ?? 'Unknown IP' }}
                        </span>
                        <span style="color: #6b7280; font-size: 11px;">
                            {{ $attempt['time'] ?? 'Unknown Time' }}
                        </span>
                    </div>
                    @if(isset($attempt['details']))
                    <div style="color: #6b7280; font-size: 11px;">{{ $attempt['details'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Additional Security Information -->
    @if(isset($context['additional_security_info']))
    <div class="section">
        <div class="section-title">
            <span>ℹ️</span> Additional Security Information
        </div>
        <div class="section-content">
            <div style="background-color: #f0f9ff; border-radius: 6px; padding: 12px; border: 1px solid #0ea5e9;">
                @foreach($context['additional_security_info'] as $key => $info)
                <div style="padding: 4px 0; color: #0c4a6e; font-size: 12px;">
                    <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $info }}
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
@endsection