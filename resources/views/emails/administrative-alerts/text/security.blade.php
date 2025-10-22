@extends('emails.administrative-alerts.text.base')

@section('content')
SECURITY EVENT DETAILS
----------------------
Event Type: {{ class_basename($event) }}
Severity: {{ $severity_display }}
Occurred: {{ $occurred_at->format('M j, Y g:i A T') }}

@if(isset($context['ip_address']))
IP Address: {{ $context['ip_address'] }}
@endif

@if(isset($context['user_agent']))
User Agent: {{ $context['user_agent'] }}
@endif

@if(isset($context['location']))
Location: {{ $context['location'] }}
@endif


@if($initiated_by)
USER INFORMATION
----------------
Name: {{ $initiated_by->name }}
Email: {{ $initiated_by->email }}
Role: {{ $initiated_by->role ?? 'Unknown' }}
@endif


SECURITY INFORMATION
--------------------
{{ $description }}

@if(isset($context['security_details']))
Additional Details:
@foreach($context['security_details'] as $detail)
• {{ $detail }}
@endforeach
@endif


@if($severity === 'CRITICAL')
CRITICAL SECURITY ALERT - IMMEDIATE ACTION REQUIRED
==================================================
1. Review the security event immediately
2. Verify the user's identity and authorization
3. Check for any unauthorized access or data breaches
4. Consider temporarily restricting access if suspicious
5. Document all findings and actions taken

@elseif($severity === 'HIGH')
HIGH PRIORITY SECURITY ALERT
============================
1. Investigate the security event within the next hour
2. Review user activity logs for related events
3. Validate the legitimacy of the action
4. Monitor for additional suspicious activities
5. Update security protocols if needed

@else
SECURITY NOTICE
===============
1. Review the event during regular security checks
2. Monitor for similar patterns or repeated events
3. Update security policies if needed
4. Add to security log for trend analysis
@endif


@if(isset($context['technical_details']) || isset($context['stack_trace']))
TECHNICAL DETAILS
-----------------
@if(isset($context['technical_details']))
{{ $context['technical_details'] }}
@endif

@if(isset($context['stack_trace']))

Stack Trace:
{{ $context['stack_trace'] }}
@endif
@endif
@endsection