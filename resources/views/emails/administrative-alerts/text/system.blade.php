@extends('emails.administrative-alerts.text.base')

@section('content')
SYSTEM EVENT DETAILS
--------------------
Event Type: {{ class_basename($event) }}
Severity: {{ $severity_display }}
Occurred: {{ $occurred_at->format('M j, Y g:i A T') }}

@if(isset($context['system_component']))
System Component: {{ $context['system_component'] }}
@endif

@if(isset($context['error_code']))
Error Code: {{ $context['error_code'] }}
@endif

@if(isset($context['affected_services']))
Affected Services:
@foreach($context['affected_services'] as $service)
• {{ $service }}
@endforeach
@endif


@if($initiated_by)
TRIGGERED BY USER
-----------------
Name: {{ $initiated_by->name }}
Email: {{ $initiated_by->email }}
Role: {{ $initiated_by->role ?? 'Unknown' }}
@endif


SYSTEM STATUS INFORMATION
-------------------------
{{ $description }}

@if(isset($context['system_status']))
Current Status: {{ $context['system_status'] }}
@endif

@if(isset($context['impact_assessment']))
Impact Assessment: {{ $context['impact_assessment'] }}
@endif

@if(isset($context['system_metrics']))
System Metrics:
@foreach($context['system_metrics'] as $metric => $value))
{{ ucfirst(str_replace('_', ' ', $metric)) }}: {{ $value }}
@endforeach
@endif


@if($severity === 'CRITICAL')
CRITICAL SYSTEM FAILURE - IMMEDIATE ACTION REQUIRED
==================================================
1. CRITICAL: System failure detected - immediate response required
2. Assess the scope and impact of the failure
3. Initiate disaster recovery procedures if applicable
4. Notify all affected stakeholders and users
5. Document all actions taken for post-mortem analysis
6. Monitor system recovery progress continuously

@elseif($severity === 'HIGH')
HIGH PRIORITY SYSTEM ALERT
==========================
1. System performance issue detected - investigate within 30 minutes
2. Check system logs and error reports for detailed information
3. Verify if any services need to be restarted
4. Monitor system performance metrics closely
5. Prepare contingency plans if condition worsens
6. Update team on resolution progress

@else
SYSTEM NOTIFICATION
==================
1. System event logged for review and monitoring
2. Add to system performance tracking
3. Review during next system maintenance window
4. Monitor for recurring patterns
5. Update system documentation if needed
@endif


@if(isset($context['troubleshooting_steps']))
TROUBLESHOOTING STEPS
---------------------
@foreach($context['troubleshooting_steps'] as $index => $step)
{{ $index + 1 }}. {{ $step }}
@endforeach
@endif


@if(isset($context['estimated_resolution']))
Estimated Resolution Time: {{ $context['estimated_resolution'] }}
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