@extends('emails.admin.base')

@section('content')
    <!-- Event Details -->
    <div class="section">
        <div class="section-title">
            <span>💼</span> Business Event Details
        </div>
        <div class="event-details">
            <div class="detail-row">
                <div class="detail-label">Event Type</div>
                <div class="detail-value">{{ class_basename($event) }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Severity</div>
                <div class="detail-value">{{ $severity_display }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">Occurred At</div>
                <div class="detail-value">{{ $occurred_at->format('M j, Y g:i A') }}</div>
            </div>
            @if(isset($context['business_type']))
            <div class="detail-row">
                <div class="detail-label">Business Type</div>
                <div class="detail-value">{{ $context['business_type'] }}</div>
            </div>
            @endif
            @if(isset($context['transaction_id']))
            <div class="detail-row">
                <div class="detail-label">Transaction ID</div>
                <div class="detail-value">{{ $context['transaction_id'] }}</div>
            </div>
            @endif
            @if(isset($context['amount']))
            <div class="detail-row">
                <div class="detail-label">Amount</div>
                <div class="detail-value">${{ number_format($context['amount'], 2) }}</div>
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
            <div class="user-avatar">
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

    <!-- Client Information -->
    @if(isset($context['client']))
    <div class="section">
        <div class="section-title">
            <span>🏢</span> Client Information
        </div>
        <div class="section-content">
            <div style="background-color: #f0f9ff; border-radius: 6px; padding: 12px; border: 1px solid #0ea5e9;">
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Client Name:</span>
                    <span style="color: #075985;">{{ $context['client']['name'] ?? 'Unknown' }}</span>
                </div>
                @if(isset($context['client']['email']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Email:</span>
                    <span style="color: #075985;">{{ $context['client']['email'] }}</span>
                </div>
                @endif
                @if(isset($context['client']['phone']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Phone:</span>
                    <span style="color: #075985;">{{ $context['client']['phone'] }}</span>
                </div>
                @endif
                @if(isset($context['client']['total_sales']))
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">Total Sales:</span>
                    <span style="color: #075985;">${{ number_format($context['client']['total_sales'], 2) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Business Details -->
    <div class="section">
        <div class="section-title">
            <span>📊</span> Business Details
        </div>
        <div class="section-content">
            <p>{{ $description }}</p>
            
            @if(isset($context['business_details']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Transaction Details:</h4>
                <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                    @foreach($context['business_details'] as $key => $value)
                    <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                        <span style="font-weight: 500; color: #374151;">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                        <span style="color: #6b7280;">
                            @if(is_numeric($value) && str_contains($key, 'amount') || str_contains($key, 'price') || str_contains($key, 'value'))
                                ${{ number_format($value, 2) }}
                            @else
                                {{ $value }}
                            @endif
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if(isset($context['items']) && is_array($context['items']))
            <div style="margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px;">Items:</h4>
                <div style="background-color: #ecfdf5; border-radius: 6px; padding: 12px; border: 1px solid #10b981;">
                    @foreach($context['items'] as $item)
                    <div style="padding: 8px 0; border-bottom: 1px solid #d1fae5;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                            <span style="font-weight: 500; color: #065f46;">{{ $item['name'] ?? 'Unknown Item' }}</span>
                            <span style="color: #047857; font-weight: 600;">
                                @if(isset($item['price']))
                                    ${{ number_format($item['price'], 2) }}
                                @endif
                            </span>
                        </div>
                        @if(isset($item['description']))
                        <div style="color: #047857; font-size: 12px;">{{ $item['description'] }}</div>
                        @endif
                        @if(isset($item['quantity']))
                        <div style="color: #047857; font-size: 12px;">Quantity: {{ $item['quantity'] }}</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Financial Impact -->
    @if(isset($context['financial_impact']))
    <div class="section">
        <div class="section-title">
            <span>💰</span> Financial Impact
        </div>
        <div class="section-content">
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                @foreach($context['financial_impact'] as $metric => $value)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #92400e;">{{ ucfirst(str_replace('_', ' ', $metric)) }}:</span>
                    <span style="color: #78350f; font-weight: 600;">
                        @if(is_numeric($value))
                            ${{ number_format($value, 2) }}
                        @else
                            {{ $value }}
                        @endif
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Business Metrics -->
    @if(isset($context['business_metrics']))
    <div class="section">
        <div class="section-title">
            <span>📈</span> Business Metrics
        </div>
        <div class="section-content">
            <div style="background-color: #ede9fe; border-radius: 6px; padding: 12px; border: 1px solid #8b5cf6;">
                @foreach($context['business_metrics'] as $metric => $value)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #5b21b6;">{{ ucfirst(str_replace('_', ' ', $metric)) }}:</span>
                    <span style="color: #6d28d9; font-weight: 600;">{{ $value }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Recommended Actions -->
    <div class="section">
        <div class="section-title">
            <span>⚡</span> Recommended Actions
        </div>
        <div class="section-content">
            @if($severity === 'CRITICAL')
            <p><strong>Immediate action required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review the business transaction immediately</li>
                <li>Verify all financial details and calculations</li>
                <li>Contact client if clarification needed</li>
                <li>Check for any compliance issues</li>
                <li>Document for audit purposes</li>
            </ul>
            @elseif($severity === 'HIGH')
            <p><strong>Priority attention required:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review the business event within the next hour</li>
                <li>Verify transaction accuracy</li>
                <li>Update appropriate business records</li>
                <li>Monitor for related business impacts</li>
            </ul>
            @else
            <p><strong>Monitor and review:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 16px;">
                <li>Review during regular business reviews</li>
                <li>Monitor business metrics and trends</li>
                <li>Update business strategies if needed</li>
            </ul>
            @endif

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" class="action-button">View Business Details</a>
            </div>
            @endif
        </div>
    </div>

    <!-- Technical Details -->
    @if(isset($context['technical_details']) || isset($context['payment_gateway_response']))
    <div class="section">
        <div class="section-title">
            <span>⚙️</span> Technical Details
        </div>
        <div class="technical-details">
            @if(isset($context['technical_details']))
            <pre>{{ $context['technical_details'] }}</pre>
            @endif
            
            @if(isset($context['payment_gateway_response']))
            <hr style="border: none; border-top: 1px solid #4b5563; margin: 16px 0;">
            <strong>Payment Gateway Response:</strong>
            <pre>{{ json_encode($context['payment_gateway_response'], JSON_PRETTY_PRINT) }}</pre>
            @endif
        </div>
    </div>
    @endif
@endsection