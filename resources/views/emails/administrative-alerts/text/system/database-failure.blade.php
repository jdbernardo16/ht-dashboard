@extends('emails.administrative-alerts.text.system')

@section('content')
💥 DATABASE FAILURE DETECTED 💥
===============================
Critical database system failure requires immediate attention

{{ parent }}

DATABASE FAILURE DETAILS
------------------------
@if(isset($context['database_name']))
Database: {{ $context['database_name'] }}
@endif

@if(isset($context['error_type']))
Error Type: {{ $context['error_type'] }}
@endif

@if(isset($context['connection_status']))
Connection Status: {{ $context['connection_status'] }}
@endif

@if(isset($context['last_successful_connection']))
Last Successful Connection: {{ $context['last_successful_connection'] }}
@endif

@if(isset($context['affected_tables']))
Affected Tables:
@foreach($context['affected_tables'] as $table)
• {{ $table }}
@endforeach
@endif


@if(isset($context['error_details']))
TECHNICAL ERROR INFORMATION
---------------------------
{{ $context['error_details'] }}
@endif


@if(isset($context['performance_metrics']))
PERFORMANCE METRICS
-------------------
@foreach($context['performance_metrics'] as $metric => $value)
{{ ucfirst(str_replace('_', ' ', $metric)) }}: {{ $value }}
@endforeach
@endif


🚨 IMMEDIATE RESPONSE ACTIONS
==============================
1. CRITICAL: Database system failure detected
2. Assess database connectivity and status immediately
3. Check database server health and resources
4. Review recent database changes or migrations
5. Verify backup integrity and availability
6. Initiate database recovery procedures if needed
7. Notify all stakeholders about potential service disruption


TROUBLESHOOTING CHECKLIST
--------------------------
□ Check database server status and logs
□ Verify network connectivity to database server
□ Review database server resource utilization (CPU, Memory, Disk)
□ Check for database locks or deadlocks
□ Verify database configuration settings
□ Review recent query performance
□ Check database replication status (if applicable)
□ Verify backup systems are functioning
□ Test database connection from application server
□ Review database error logs for specific error patterns


@if(isset($context['recovery_options']))
RECOVERY OPTIONS
----------------
@foreach($context['recovery_options'] as $index => $option)
{{ $index + 1 }}. {{ $option }}
@endforeach
@endif


@if(isset($context['estimated_recovery_time']))
Estimated Recovery Time: {{ $context['estimated_recovery_time'] }}
@endif


@if(isset($context['impact_assessment']))
SERVICE IMPACT ASSESSMENT
--------------------------
{{ $context['impact_assessment'] }}
@endif


@if(isset($context['fallback_systems']))
FALLBACK SYSTEMS STATUS
-----------------------
@foreach($context['fallback_systems'] as $system => $status)
{{ $system }}: {{ $status }}
@endforeach
@endif


COMMUNICATION PROTOCOL
----------------------
1. Notify development team immediately
2. Alert system administrators and DBAs
3. Inform business stakeholders of potential impact
4. Update status page or user notifications
5. Document all actions taken for post-mortem
6. Establish regular update schedule for stakeholders


PREVENTIVE MEASURES
-------------------
1. Review database monitoring and alerting thresholds
2. Implement automated health checks
3. Consider database clustering or replication
4. Review backup and recovery procedures
5. Update incident response documentation
6. Schedule regular database maintenance
7. Implement performance monitoring and optimization


@if(isset($context['technical_logs']))
TECHNICAL LOGS
--------------
{{ $context['technical_logs'] }}
@endif
@endsection