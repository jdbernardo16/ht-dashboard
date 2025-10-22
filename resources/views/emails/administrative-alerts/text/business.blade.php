@extends('emails.administrative-alerts.text.base')

@section('content')
BUSINESS EVENT DETAILS
----------------------
Event Type: {{ class_basename($event) }}
Severity: {{ $severity_display }}
Occurred: {{ $occurred_at->format('M j, Y g:i A T') }}

@if(isset($context['business_category']))
Business Category: {{ $context['business_category'] }}
@endif

@if(isset($context['transaction_type']))
Transaction Type: {{ $context['transaction_type'] }}
@endif

@if(isset($context['monetary_value']))
Value: ${{ number_format($context['monetary_value'], 2) }}
@endif


@if($initiated_by)
INITIATED BY
------------
Name: {{ $initiated_by->name }}
Email: {{ $initiated_by->email }}
Role: {{ $initiated_by->role ?? 'Unknown' }}
@endif

@if(isset($context['client_information']))
CLIENT INFORMATION
------------------
@foreach($context['client_information'] as $key => $value)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach
@endif


BUSINESS IMPACT
---------------
{{ $description }}

@if(isset($context['business_impact']))
Impact Assessment: {{ $context['business_impact'] }}
@endif

@if(isset($context['financial_impact']))
Financial Impact: {{ $context['financial_impact'] }}
@endif

@if(isset($context['operational_impact']))
Operational Impact: {{ $context['operational_impact'] }}
@endif

@if(isset($context['business_metrics']))
Business Metrics:
@foreach($context['business_metrics'] as $metric => $value)
{{ ucfirst(str_replace('_', ' ', $metric)) }}: {{ $value }}
@endforeach
@endif


@if($severity === 'CRITICAL')
CRITICAL BUSINESS EVENT - IMMEDIATE ACTION REQUIRED
==================================================
1. CRITICAL: High-impact business event detected
2. Assess immediate financial and operational impact
3. Notify key stakeholders and leadership
4. Review business processes for gaps or issues
5. Implement corrective measures immediately
6. Document all actions for business continuity planning

@elseif($severity === 'HIGH')
HIGH PRIORITY BUSINESS ALERT
===========================
1. Significant business event requires attention
2. Review impact on business operations and metrics
3. Notify relevant department heads
4. Monitor for related business events
5. Update business forecasts if needed
6. Consider process improvements

@else
BUSINESS NOTIFICATION
====================
1. Business event logged for tracking and analysis
2. Add to business intelligence reporting
3. Review during regular business reviews
4. Monitor for trends and patterns
5. Update business documentation if needed
@endif


@if(isset($context['recommended_actions']))
RECOMMENDED BUSINESS ACTIONS
----------------------------
@foreach($context['recommended_actions'] as $index => $action)
{{ $index + 1 }}. {{ $action }}
@endforeach
@endif


@if(isset($context['next_steps']))
NEXT STEPS
----------
@foreach($context['next_steps'] as $step)
• {{ $step }}
@endforeach
@endif


@if(isset($context['related_transactions']))
RELATED TRANSACTIONS
--------------------
@foreach($context['related_transactions'] as $transaction)
• {{ $transaction }}
@endforeach
@endif


@if(isset($context['compliance_notes']))
COMPLIANCE & REGULATORY NOTES
------------------------------
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