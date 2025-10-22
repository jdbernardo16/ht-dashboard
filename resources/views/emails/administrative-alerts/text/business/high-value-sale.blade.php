@extends('emails.administrative-alerts.text.business')

@section('content')
🎉 HIGH-VALUE SALE RECORDED 🎉
===============================
Significant business transaction requiring attention and verification

{{ parent }}

HIGH-VALUE SALE DETAILS
-----------------------
@if(isset($context['sale_amount']))
Sale Amount: ${{ number_format($context['sale_amount'], 2) }}
@endif

@if(isset($context['sale_id']))
Sale ID: {{ $context['sale_id'] }}
@endif

@if(isset($context['transaction_date']))
Transaction Date: {{ $context['transaction_date'] }}
@endif

@if(isset($context['payment_method']))
Payment Method: {{ $context['payment_method'] }}
@endif

@if(isset($context['payment_status']))
Payment Status: {{ $context['payment_status'] }}
@endif


@if(isset($context['client_information']))
CLIENT DETAILS
--------------
@foreach($context['client_information'] as $key => $value)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach
@endif


@if(isset($context['items_sold']))
ITEMS SOLD
----------
@foreach($context['items_sold'] as $index => $item)
{{ $index + 1 }}. {{ $item['name'] ?? 'Unknown Item' }}
   Value: ${{ number_format($item['value'] ?? 0, 2) }}
   @if(isset($item['description']))
   Description: {{ $item['description'] }}
   @endif
   @if(isset($item['condition']))
   Condition: {{ $item['condition'] }}
   @endif

@endforeach
@endif


@if(isset($context['commission_info']))
COMMISSION INFORMATION
----------------------
@foreach($context['commission_info'] as $key => $value)
{{ ucfirst(str_replace('_', ' ', $key)) }}: {{ $value }}
@endforeach
@endif


@if(isset($context['profit_analysis']))
PROFIT ANALYSIS
---------------
@foreach($context['profit_analysis'] as $metric => $value)
{{ ucfirst(str_replace('_', ' ', $metric)) }}: {{ $value }}
@endforeach
@endif


📊 BUSINESS IMPACT SUMMARY
===========================
@if(isset($context['monthly_impact']))
Monthly Impact: {{ $context['monthly_impact'] }}
@endif

@if(isset($context['quarterly_impact']))
Quarterly Impact: {{ $context['quarterly_impact'] }}
@endif

@if(isset($context['goal_progress']))
Goal Progress: {{ $context['goal_progress'] }}
@endif

@if(isset($context['performance_metrics']))
Performance Metrics:
@foreach($context['performance_metrics'] as $metric => $value)
• {{ ucfirst(str_replace('_', ' ', $metric)) }}: {{ $value }}
@endforeach
@endif


✅ IMMEDIATE ACTIONS REQUIRED
=============================
1. Verify transaction details and accuracy
2. Confirm payment processing and clearance
3. Update sales records and inventory systems
4. Notify relevant team members of the achievement
5. Schedule client follow-up for satisfaction review
6. Document any special circumstances or notes


📈 NEXT STEPS
--------------
1. Update sales dashboard and reports
2. Review commission calculations and distributions
3. Consider celebration or recognition for team
4. Analyze factors contributing to this success
5. Update client records and relationship notes
6. Plan for inventory replenishment if needed


@if(isset($context['team_notifications']))
TEAM NOTIFICATIONS
------------------
@foreach($context['team_notifications'] as $notification)
• {{ $notification }}
@endforeach
@endif


@if(isset($context['client_followup']))
CLIENT FOLLOW-UP ACTIONS
-----------------------
@foreach($context['client_followup'] as $action))
• {{ $action }}
@endforeach
@endif


@if(isset($context['business_intelligence']))
BUSINESS INTELLIGENCE INSIGHTS
--------------------------------
{{ $context['business_intelligence'] }}
@endif


MARKETING & PROMOTION OPPORTUNITIES
------------------------------------
1. Consider testimonial request from satisfied client
2. Update case studies or success stories
3. Review referral potential from this transaction
4. Update social media with appropriate business milestones
5. Consider cross-selling opportunities with this client


FINANCIAL VERIFICATION CHECKLIST
---------------------------------
□ Verify payment receipt and bank deposit
□ Confirm transaction fees and processing costs
□ Update accounting records and ledgers
□ Calculate and record commissions
□ Review tax implications and documentation
□ Update financial forecasts and projections


CONGRATULATIONS! This high-value sale represents a significant achievement 
for the business and demonstrates the effectiveness of our sales strategies 
and team efforts.

🚀 Continue the excellent work! 🚀
@endsection