@extends('emails.administrative-alerts.text.base')

@section('content')
USER ACTION EVENT DETAILS
-------------------------
Event Type: {{ class_basename($event) }}
Severity: {{ $severity_display }}
Occurred: {{ $occurred_at->format('M j, Y g:i A T') }}

@if(isset($context['action_type']))
Action Type: {{ $context['action_type'] }}
@endif

@if(isset($context['target_resource']))
Target Resource: {{ $context['target_resource'] }}
@endif

@if(isset($context['affected_items_count']))
Affected Items: {{ $context['affected_items_count'] }}
@endif


USER INFORMATION
----------------
@if($initiated_by)
Name: {{ $initiated_by->name }}
Email: {{ $initiated_by->email }}
Role: {{ $initiated_by->role ?? 'Unknown' }}
User ID: {{ $initiated_by->id }}
@else
User: Anonymous / System
@endif

@if(isset($context['user_session_id']))
Session ID: {{ $context['user_session_id'] }}
@endif


ACTION DETAILS
--------------
{{ $description }}

@if(isset($context['action_summary']))
Action Summary: {{ $context['action_summary'] }}
@endif

@if(isset($context['reason']))
Reason Provided: {{ $context['reason'] }}
@endif

@if(isset($context['affected_resources']))
Affected Resources:
@foreach($context['affected_resources'] as $resource)
• {{ $resource }}
@endforeach
@endif


@if($severity === 'CRITICAL')
CRITICAL USER ACTION - IMMEDIATE REVIEW REQUIRED
================================================
1. CRITICAL: High-impact user action detected
2. Verify user authorization for this action
3. Review scope of changes or deletions
4. Contact user to confirm action legitimacy
5. Prepare rollback procedures if necessary
6. Document incident for compliance and audit

@elseif($severity === 'HIGH')
HIGH PRIORITY USER ACTION
=========================
1. Significant user action requires review
2. Verify action aligns with user responsibilities
3. Check for potential data or system impact
4. Review user's recent activity patterns
5. Monitor for follow-up actions
6. Update team if action affects workflows

@else
USER ACTION NOTIFICATION
========================
1. User action logged for record-keeping
2. Add to user activity monitoring
3. Review during regular audits
4. Monitor for pattern changes
5. Update user behavior analytics if needed
@endif


@if(isset($context['reversibility']))
REVERSIBILITY: {{ $context['reversibility'] ? 'Action can be reversed' : 'Action cannot be reversed' }}
@endif

@if(isset($context['rollback_instructions']))
ROLLBACK INSTRUCTIONS:
{{ $context['rollback_instructions'] }}
@endif


@if(isset($context['compliance_notes']))
COMPLIANCE NOTES
----------------
{{ $context['compliance_notes'] }}
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