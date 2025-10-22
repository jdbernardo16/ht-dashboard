@extends('emails.admin.business')

@section('content')
    <!-- High Value Sale Specific Banner -->
    <div style="background-color: #16a34a; color: white; padding: 16px; text-align: center; margin-bottom: 24px;">
        <div style="font-size: 18px; font-weight: 700; margin-bottom: 4px;">
            💰 HIGH VALUE SALE 💰
        </div>
        <div style="font-size: 14px; opacity: 0.9;">
            Significant business transaction completed - Review and confirmation required
        </div>
    </div>

    <!-- Original Business Content -->
    @parent

    <!-- High Value Sale Details -->
    <div class="section">
        <div class="section-title">
            <span>🎯</span> High Value Sale Details
        </div>
        <div class="section-content">
            <div style="background-color: #f0fdf4; border: 2px solid #16a34a; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                @if(isset($context['sale_amount']))
                <div style="font-size: 20px; font-weight: 700; color: #16a34a; margin-bottom: 12px; text-align: center;">
                    Sale Amount: ${{ number_format($context['sale_amount'], 2) }}
                </div>
                @endif
                
                @if(isset($context['sale_id']))
                <p><strong>Sale ID:</strong> {{ $context['sale_id'] }}</p>
                @endif
                
                @if(isset($context['sale_date']))
                <p><strong>Sale Date:</strong> {{ $context['sale_date'] }}</p>
                @endif
                
                @if(isset($context['payment_method']))
                <p><strong>Payment Method:</strong> {{ $context['payment_method'] }}</p>
                @endif
                
                @if(isset($context['payment_status']))
                <p><strong>Payment Status:</strong> 
                    <span style="color: {{ $context['payment_status'] === 'completed' ? '#16a34a' : '#ca8a04' }}; font-weight: 600;">
                        {{ ucfirst($context['payment_status']) }}
                    </span>
                </p>
                @endif
                
                @if(isset($context['profit_margin']))
                <p><strong>Profit Margin:</strong> 
                    <span style="color: {{ $context['profit_margin'] > 20 ? '#16a34a' : ($context['profit_margin'] > 10 ? '#ca8a04' : '#dc2626') }}; font-weight: 600;">
                        {{ $context['profit_margin'] }}%
                    </span>
                </p>
                @endif
            </div>

            @if(isset($context['sale_highlights']) && !empty($context['sale_highlights']))
            <div style="background-color: #f0fdf4; border-radius: 6px; padding: 12px; border: 1px solid #86efac; margin-top: 16px;">
                <h4 style="font-size: 14px; font-weight: 600; margin-bottom: 8px; color: #16a34a;">🌟 Sale Highlights:</h4>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    @foreach($context['sale_highlights'] as $highlight)
                    <li style="margin-bottom: 4px; color: #166534;">{{ $highlight }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    </div>

    <!-- Sports Card Details -->
    @if(isset($context['cards']) && !empty($context['cards']))
    <div class="section">
        <div class="section-title">
            <span>🃏</span> Sports Card Details
        </div>
        <div class="section-content">
            <div style="background-color: #ecfdf5; border-radius: 6px; padding: 12px; border: 1px solid #10b981;">
                @foreach($context['cards'] as $index => $card)
                <div style="padding: 12px 0; {{ !$loop->last ? 'border-bottom: 1px solid #d1fae5;' : '' }}">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-weight: 600; color: #065f46;">
                            {{ $card['player_name'] ?? 'Unknown Player' }}
                            @if(isset($card['year']))
                                ({{ $card['year'] }})
                            @endif
                        </span>
                        <span style="color: #047857; font-weight: 700; font-size: 16px;">
                            ${{ number_format($card['sale_price'] ?? 0, 2) }}
                        </span>
                    </div>
                    
                    @if(isset($card['card_name']))
                    <div style="color: #047857; font-size: 12px; margin-bottom: 4px;">
                        {{ $card['card_name'] }}
                    </div>
                    @endif
                    
                    @if(isset($card['grade']))
                    <div style="display: inline-block; padding: 2px 8px; background-color: #10b981; color: white; border-radius: 12px; font-size: 10px; font-weight: 600; margin-bottom: 4px;">
                        Grade: {{ $card['grade'] }}
                    </div>
                    @endif
                    
                    @if(isset($card['estimated_value']))
                    <div style="color: #047857; font-size: 11px;">
                        Estimated Value: ${{ number_format($card['estimated_value'], 2) }}
                        @if(isset($card['sale_price']) && $card['sale_price'] > $card['estimated_value'])
                            <span style="color: #16a34a; font-weight: 600;"> (+{{ number_format((($card['sale_price'] - $card['estimated_value']) / $card['estimated_value']) * 100, 1) }}%)</span>
                        @endif
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Commission Information -->
    @if(isset($context['commission_details']))
    <div class="section">
        <div class="section-title">
            <span>💵</span> Commission Information
        </div>
        <div class="section-content">
            <div style="background-color: #fef3c7; border-radius: 6px; padding: 12px; border: 1px solid #fbbf24;">
                @foreach($context['commission_details'] as $type => $amount)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #92400e;">{{ ucfirst(str_replace('_', ' ', $type)) }}:</span>
                    <span style="color: #78350f; font-weight: 600;">${{ number_format($amount, 2) }}</span>
                </div>
                @endforeach
                
                @if(isset($context['commission_details']['total_commission']) && isset($context['sale_amount']))
                <div style="border-top: 1px solid #fde68a; padding-top: 8px; margin-top: 8px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: 600; color: #92400e;">Commission Rate:</span>
                        <span style="color: #78350f; font-weight: 600;">
                            {{ number_format(($context['commission_details']['total_commission'] / $context['sale_amount']) * 100, 2) }}%
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Business Impact -->
    <div class="section">
        <div class="section-title">
            <span>📈</span> Business Impact
        </div>
        <div class="section-content">
            <div style="background-color: #ede9fe; border-radius: 6px; padding: 12px; border: 1px solid #8b5cf6;">
                @if(isset($context['business_impact']))
                @foreach($context['business_impact'] as $metric => $impact)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #5b21b6;">{{ ucfirst(str_replace('_', ' ', $metric)) }}:</span>
                    <span style="color: #6d28d9; font-weight: 600;">{{ $impact }}</span>
                </div>
                @endforeach
                @endif
                
                @if(isset($context['sale_amount']) && $context['sale_amount'] > 1000)
                <div style="background-color: #f0f9ff; border-radius: 4px; padding: 8px; margin-top: 12px; border: 1px solid #0ea5e9;">
                    <div style="color: #0c4a6e; font-weight: 600; font-size: 12px; text-align: center;">
                        🏆 TOP TIER SALE - Exceeds $1,000 threshold
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Client Relationship Impact -->
    @if(isset($context['client_impact']))
    <div class="section">
        <div class="section-title">
            <span>🤝</span> Client Relationship Impact
        </div>
        <div class="section-content">
            <div style="background-color: #f0f9ff; border-radius: 6px; padding: 12px; border: 1px solid #0ea5e9;">
                @foreach($context['client_impact'] as $aspect => $impact)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #0c4a6e;">{{ ucfirst(str_replace('_', ' ', $aspect)) }}:</span>
                    <span style="color: #075985;">{{ $impact }}</span>
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
            <div style="background-color: #16a34a; color: white; border-radius: 8px; padding: 16px; margin-bottom: 16px;">
                <div style="font-size: 16px; font-weight: 600; margin-bottom: 12px;">
                    🎯 HIGH VALUE SALE ACTIONS 🎯
                </div>
                <ul style="margin-left: 20px; margin-bottom: 0;">
                    <li style="margin-bottom: 8px;">Verify all transaction details and accuracy</li>
                    <li style="margin-bottom: 8px;">Confirm payment processing and settlement</li>
                    <li style="margin-bottom: 8px;">Update client records and relationship notes</li>
                    <li style="margin-bottom: 8px;">Document any special considerations or negotiations</li>
                    <li style="margin-bottom: 8px;">Update inventory and consignment records</li>
                    <li style="margin-bottom: 8px;">Consider client follow-up and retention strategies</li>
                    <li style="margin-bottom: 8px;">Review for tax and financial reporting</li>
                </ul>
            </div>

            @if($action_url)
            <div style="text-align: center; margin-top: 24px;">
                <a href="{{ $action_url }}" style="display: inline-block; padding: 14px 28px; background-color: #16a34a; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px; text-align: center; transition: all 0.2s;">
                    📋 VIEW SALE DETAILS
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Market Comparison -->
    @if(isset($context['market_comparison']))
    <div class="section">
        <div class="section-title">
            <span>📊</span> Market Comparison
        </div>
        <div class="section-content">
            <div style="background-color: #f9fafb; border-radius: 6px; padding: 12px; border: 1px solid #e5e7eb;">
                @foreach($context['market_comparison'] as $metric => $comparison)
                <div style="display: flex; justify-content: space-between; padding: 4px 0;">
                    <span style="font-weight: 500; color: #374151;">{{ ucfirst(str_replace('_', ' ', $metric)) }}:</span>
                    <span style="color: #6b7280;">{{ $comparison }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Next Steps -->
    <div class="section">
        <div class="section-title">
            <span>📝</span> Next Steps
        </div>
        <div class="section-content">
            <div style="background-color: #f3f4f6; border-radius: 6px; padding: 12px; border: 1px solid #d1d5db;">
                <ol style="margin-left: 20px; margin-bottom: 0; color: #374151; font-size: 14px;">
                    <li style="margin-bottom: 6px;">Confirm payment receipt and processing</li>
                    <li style="margin-bottom: 6px;">Update financial records and accounting system</li>
                    <li style="margin-bottom: 6px;">Coordinate item shipping or delivery arrangements</li>
                    <li style="margin-bottom: 6px;">Send confirmation and thank you to client</li>
                    <li style="margin-bottom: 6px;">Update consignment client on sale status</li>
                    <li style="margin-bottom: 6px;">Schedule commission payment processing</li>
                    <li style="margin-bottom: 6px;">Document sale for future reference and marketing</li>
                </ol>
            </div>
        </div>
    </div>

    <!-- Footer Celebration -->
    <div style="background-color: #f0fdf4; border: 1px solid #86efac; border-radius: 6px; padding: 12px; margin-top: 24px; text-align: center;">
        <div style="color: #16a34a; font-weight: 600; font-size: 16px; margin-bottom: 4px;">
            🎉 CONGRATULATIONS ON HIGH VALUE SALE! 🎉
        </div>
        <div style="color: #166534; font-size: 12px;">
            This represents a significant achievement for the business. Great work!
        </div>
    </div>
@endsection