@extends('emails.admin.system')

@section('content')
    <!-- Database Failure Specific Banner -->
    <div style="background-color: #dc2626; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            🗄️ DATABASE FAILURE 🗄️
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Critical database connectivity or performance issue detected
        </div>
    </div>

    <!-- Original System Content -->
    @parent

    <!-- Database Failure Details -->
    <div class="section">
        <div class="section-title">
            <span>💥</span> Database Failure Details
        </div>
        <div class="section-content">
            <div style="background-color: #fef2f2; border: 2px solid #dc2626; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                @if(isset($context['database_type']))
                <p><strong>Database Type:</strong> {{ $context['database_type'] }}</p>
                @endif
                
                @if(isset($context['failure_type']))
                <p><strong>Failure Type:</strong> {{ $context['failure_type'] }}</p>
                @endif
                
                @if(isset($context['error_code']))
                <p><strong>Error Code:</strong> {{ $context['error_code'] }}</p>
                @endif
                
                @if(isset($context['connection_string']))
                <p><strong>Connection:</strong> {{ $context['connection_string'] }}</p>
                @endif
                
                @if(isset($context['affected_tables']))
                <p><strong>Affected Tables:</strong> {{ $context['affected_tables'] }}</p>
                @endif
            </div>

            @if(isset($context['failure_details']))
            <div style="background-color: #fef2f2; border-radius: 6px; padding: 12px; border: 1px solid #fca5a5; margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #dc2626;">💥 Failure Details:</h4>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @foreach($context['failure_details'] as $detail)
                    <li style="margin-bottom: 4px; color: #991b1b;">{{ $detail }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Database Status -->
    <div class="section">
        <div class="section-title">
            <span>📊</span> Database Status
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                @if(isset($context['connection_status']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Connection Status:</span>
                    <span style="color: {{ $context['connection_status'] === 'connected' ? '#16a34a' : '#dc2626' }}; font-weight: 600;">
                        {{ ucfirst($context['connection_status']) }}
                    </span>
                </div>
                @endif
                
                @if(isset($context['response_time']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Response Time:</span>
                    <span style="color: #6b7280;">{{ $context['response_time'] }}ms</span>
                </div>
                @endif
                
                @if(isset($context['active_connections']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Active Connections:</span>
                    <span style="color: #6b7280;">{{ $context['active_connections'] }}</span>
                </div>
                @endif
                
                @if(isset($context['max_connections']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Max Connections:</span>
                    <span style="color: #6b7280;">{{ $context['max_connections'] }}</span>
                </div>
                @endif
                
                @if(isset($context['database_size']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">Database Size:</span>
                    <span style="color: #6b7280;">{{ $context['database_size'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    @if(isset($context['performance_metrics']))
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Performance Metrics
        </div>
        <div class="section-content">
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                @foreach($context['performance_metrics'] as $metric => $value)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #92400e;">{{ ucfirst(str_replace('_', ' ', $metric)) }}:</span>
                    <span style="color: #78350f; font-weight: 600;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Database Errors -->
    @if(isset($context['recent_errors']) && !empty($context['recent_errors']))
    <div class="section">
        <div class="section-title">
            <span>📋</span> Recent Database Errors
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                @foreach($context['recent_errors'] as $error)
                <div style="padding: 8px 0; border-bottom: 1px solid #e5e7eb;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                        <span style="font-weight: 500; color: #374151; font-size: 12px;">
                            {{ $error['type'] ?? 'Unknown Error' }}
                        </span>
                        <span style="color: #6b7280; font-size: 11px;">
                            {{ $error['timestamp'] ?? 'Unknown Time' }}
                        </span>
                    </div>
                    @if(isset($error['message']))
                    <div style="color: #dc2626; font-size: 11px; margin-bottom: 2px;">{{ $error['message'] }}</div>
                    @endif
                    @if(isset($error['query']))
                    <div style="color: #6b7280; font-size: 10px; font-family: monospace;">{{ $error['query'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Database Recovery Actions -->
    <div class="section">
        <div class="section-title">
            <span>🔧</span> Database Recovery Actions
        </div>
        <div class="section-content">
            <div style="background-color: #dc2626; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    🚨 IMMEDIATE DATABASE ACTIONS 🚨
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @if(isset($context['failure_type']) && $context['failure_type'] === 'connection')
                    <li style="margin-bottom: 8px;">Check database server status and connectivity</li>
                    <li style="margin-bottom: 8px;">Verify network connectivity to database server</li>
                    <li style="margin-bottom: 8px;">Check database service is running</li>
                    <li style="margin-bottom: 8px;">Validate database credentials and permissions</li>
                    @elseif(isset($context['failure_type']) && $context['failure_type'] === 'performance')
                    <li style="margin-bottom: 8px;">Analyze slow query logs</li>
                    <li style="margin-bottom: 8px;">Check database server resources (CPU, memory, disk)</li>
                    <li style="margin-bottom: 8px;">Review database indexes and optimization</li>
                    <li style="margin-bottom: 8px;">Consider database maintenance operations</li>
                    @else
                    <li style="margin-bottom: 8px;">Review database error logs immediately</li>
                    <li style="margin-bottom: 8px;">Check database integrity and consistency</li>
                    <li style="margin-bottom: 8px;">Verify recent database changes or migrations</li>
                    <li style="margin-bottom: 8px;">Check for database lock contention</li>
                    @endif
                    <li style="margin-bottom: 8px;">Monitor database performance metrics</li>
                    <li style="margin-bottom: 8px;">Prepare for database restart if necessary</li>
                    <li style="margin-bottom: 8px;">Verify backup status and availability</li>
                </ul>
            </div>

            @if(isset($context['backup_status']))
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24; margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #92400e;">💾 Backup Status:</h4>
                <div style="color: #78350f; font-size: 12px;">
                    Last Backup: {{ $context['backup_status']['last_backup'] ?? 'Unknown' }}<br>
                    Backup Size: {{ $context['backup_status']['size'] ?? 'Unknown' }}<br>
                    Status: <span style="font-weight: 600; color: {{ ($context['backup_status']['status'] ?? 'unknown') === 'success' ? '#16a34a' : '#dc2626' }}">
                        {{ ucfirst($context['backup_status']['status'] ?? 'Unknown') }}
                    </span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Database Configuration -->
    @if(isset($context['database_config']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Database Configuration
        </div>
        <div class="section-content">
            <div style="background-color: #f0f9ff; border-radius: 6px; padding: 12px; border: 1px solid #0ea5e9;">
                @foreach($context['database_config'] as $setting => $value)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e; font-size: 12px;">
                        {{ ucfirst(str_replace('_', ' ', $setting)) }}:
                    </span>
                    <span style="color: #075985; font-size: 12px;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Impact Assessment -->
    @if(isset($context['impact_assessment']))
    <div class="section">
        <div class="section-title">
            <span>📊</span> Impact Assessment
        </div>
        <div class="section-content">
            <div style="background-color: #fef2f2; border-radius: 6px; padding: 12px; border: 1px solid #fca5a5;">
                @foreach($context['impact_assessment'] as $area => $impact)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #991b1b;">{{ ucfirst($area) }}:</span>
                    <span style="color: #7f1d1d; font-weight: 600;">{{ $impact }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Technical Details -->
    @if(isset($context['error_logs']) || isset($context['query_log']))
    <div class="section">
        <div class="section-title">
            <span>🔍</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['error_logs']))
            <strong>Database Error Logs:</strong>
            <pre>{{ $context['error_logs'] }}</pre>
            @endif
            
            @if(isset($context['query_log']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Query Log:</strong>
            <pre>{{ $context['query_log'] }}</pre>
            @endif
        </div>
    </div>
    @endif
@endsection