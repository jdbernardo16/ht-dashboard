@extends('emails.administrative-alerts.text.security')

@section('content')
🔐 FAILED LOGIN ATTEMPT 🔐
===========================
Unauthorized login attempt detected - Security verification required

{{ parent }}

FAILED LOGIN DETAILS
--------------------
@if(isset($context['username']))
Username/Email: {{ $context['username'] }}
@endif

@if(isset($context['failure_reason']))
Failure Reason: {{ $context['failure_reason'] }}
@endif

@if(isset($context['attempts_count']))
Consecutive Failures: {{ $context['attempts_count'] }}
@endif

@if(isset($context['last_successful_login']))
Last Successful Login: {{ $context['last_successful_login'] }}
@endif


@if(isset($context['suspicious_indicators']) && !empty($context['suspicious_indicators']))
🚨 SUSPICIOUS INDICATORS DETECTED
---------------------------------
@foreach($context['suspicious_indicators'] as $indicator)
⚠️ {{ $indicator }}
@endforeach
@endif


@if(isset($context['location']))
GEOGRAPHIC INFORMATION
----------------------
Location: {{ $context['location'] }}

@if(isset($context['country']))
Country: {{ $context['country'] }}
@endif

@if(isset($context['isp']))
ISP: {{ $context['isp'] }}
@endif

@if(isset($context['is_unusual_location']) && $context['is_unusual_location'])
⚠️ UNUSUAL LOCATION DETECTED - This location is not typical for this user
@endif
@endif


@if(isset($context['ip_analysis']))
IP ADDRESS ANALYSIS
-------------------
@foreach($context['ip_analysis'] as $aspect => $analysis)
{{ ucfirst(str_replace('_', ' ', $aspect)) }}: {{ $analysis }}
@endforeach
@endif


🔒 IMMEDIATE SECURITY ACTIONS
=============================
@if(isset($context['attempts_count']) && $context['attempts_count'] >= 5)
🚨 CRITICAL: MULTIPLE FAILED ATTEMPTS DETECTED
1. BLOCK THE IP ADDRESS IMMEDIATELY
2. Enable account lockout policies
@endif
3. Verify if this is a legitimate user attempt
4. Check for other suspicious activities from this IP
5. Review account security settings
6. Consider enabling two-factor authentication
7. Monitor for additional failed attempts


@if(isset($context['user_account']) && $context['user_account'])
👤 USER ACCOUNT ACTIONS
------------------------
1. Contact the user to verify their identity
2. Check if user recently changed password
3. Verify user's current location and activity
4. Consider temporary password reset if suspicious
@endif


@if(isset($context['recent_attempts']) && !empty($context['recent_attempts']))
📊 RECENT FAILED ATTEMPTS
-------------------------
@foreach($context['recent_attempts'] as $attempt)
IP: {{ $attempt['ip'] ?? 'Unknown IP' }}
Time: {{ $attempt['time'] ?? 'Unknown Time' }}
@if(isset($attempt['details']))
Details: {{ $attempt['details'] }}
@endif

@endforeach
@endif


@if(isset($context['additional_security_info']))
ℹ️ ADDITIONAL SECURITY INFORMATION
----------------------------------
@foreach($context['additional_security_info'] as $key => $info)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $info }}
@endforeach
@endif


SECURITY RECOMMENDATIONS
========================
1. Review IP reputation and threat intelligence
2. Check for brute force attack patterns
3. Verify user account security settings
4. Monitor for successful login from suspicious IP
5. Update firewall rules if necessary
6. Document incident for security audit trail
7. Consider implementing rate limiting if not already active
@endsection