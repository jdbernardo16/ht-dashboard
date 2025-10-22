@extends('emails.administrative-alerts.Security')

@section('content')
    <!-- Critical Alert Banner -->
    <div style="background-color: #dc2626; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            🚨 CRITICAL SECURITY ALERT 🚨
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Immediate attention required - Critical security event detected
        </div>
    </div>

    <!-- Original Security Content -->
    @parent

    <!-- Critical Security Actions -->
    <div class="section">
        <div class="section-title">
            <span>🚨</span> Critical Security Actions
        </div>
        <div class="section-content">
            <div style="background-color: #dc2626; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    🔒 IMMEDIATE SECURITY RESPONSE 🔒
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    <li style="margin-bottom: 8px;">Review security logs immediately</li>
                    <li style="margin-bottom: 8px;">Verify system integrity and data safety</li>
                    <li style="margin-bottom: 8px;">Check for unauthorized access or data breaches</li>
                    <li style="margin-bottom: 8px;">Consider immediate system lockdown if necessary</li>
                    <li style="margin-bottom: 8px;">Document all findings and actions taken</li>
                    <li style="margin-bottom: 8px;">Notify security team and management</li>
                    <li style="margin-bottom: 8px;">Prepare incident response report</li>
                </ul>
            </div>

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" style="display: inline-block; padding: 16px 32px; background-color: #dc2626; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; transition: all 0.2s;">
                    🚨 TAKE IMMEDIATE SECURITY ACTION 🚨
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Emergency Contacts -->
    <div class="section">
        <div class="section-title">
            <span>📞</span> Emergency Contacts
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Security Team:</span>
                    <span style="color: #1e40af;">{{ config('security.email', 'security@hiddentreasures.com') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Emergency Phone:</span>
                    <span style="color: #1e40af;">{{ config('emergency.phone', '+1-555-EMERGENCY') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Incident Channel:</span>
                    <span style="color: #1e40af;">#security-incidents</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Footer Notice -->
    <div style="background-color: #fef2f2; border: 1px solid #fca5a5; border-radius: 6px; padding: 12px; margin-top: 24px; text-align: center;">
        <div style="color: #dc2626; font-weight: 600; font-size: 14px; margin-bottom: 4px;">
            🚨 CRITICAL SECURITY INCIDENT - IMMEDIATE RESPONSE REQUIRED
        </div>
        <div style="color: #991b1b; font-size: 12px;">
            This is a critical security event requiring immediate attention. Do not delay response.
        </div>
    </div>
@endsection