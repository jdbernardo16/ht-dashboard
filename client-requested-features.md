# Client Requested Features

## Executive Summary

The client is requesting an internal dashboard for their sports card consignment business, leveraging AI to optimize operations from item intake through pricing, listing, sales, and payout reconciliation. The dashboard will provide executive-level KPIs, operational workflow boards, AI-assisted pricing and description generation, risk detection, and automated summaries. Key focus areas include streamlining consignment workflows, improving sell-through rates, and providing role-based access for admin, operations, finance, and consignors. The system will integrate with external platforms like eBay and Shopify, incorporate automated anomaly detection, and include comprehensive testing and documentation features.

## Categorized Feature List

### AI Features

-   **Price Suggester**: AI-driven pricing recommendations based on item details and comparable sales data from eBay and CardLadder APIs.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (items, comps), API Integrations (eBay Finding API, CardLadder)

-   **One-Click Description Writer**: Automated generation of product descriptions with templated content, condition notes, and SEO tags.

    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: Data Models (items)

-   **State of the Shop Summary**: Daily automated summary of KPI changes, risks, and opportunities posted to Slack.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Metrics & Analytics, Automation & Jobs (cron)

-   **Risk/Return Radar**: Anomaly detection for high-risk listings and orders with automated task creation.

    -   Priority: Medium
    -   Complexity: High
    -   Dependencies: Data Models (listings, orders), Dashboard Modules

-   **Auto-Tagging & Categorization**: Automatic labeling of items (player, team, era, vintage/modern) using OCR and AI models.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (items)

-   **AI Summarization Panel**: "What changed & why" explanations for KPI data.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Metrics & Analytics

-   **Forecasting**: AI-generated projections for sales, workload, and inventory trends.

    -   Priority: Low
    -   Complexity: High
    -   Dependencies: Data Models, Metrics & Analytics

-   **Image Quality Scorer**: Automated assessment of photo quality with blur/reflection detection.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Data Models (items)

-   **Forbidden Words/P&I Policy Detector**: Content validation for listings.
    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: Data Models (listings)

### Dashboard Modules

-   **Executive at a Glance Row**: KPI dashboard with new consignments, listing throughput, sell-through rate, ASP, net margin, aging inventory, and pending payouts.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models, Metrics & Analytics

-   **Intake & Pricing Operations Board**: Kanban-style board for item intake, condition assessment, comps, pricing, and SLA tracking.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (items, tasks), AI Features (Price Suggester, Auto-Tagging)

-   **Listing Quality & Risk Checks**: Pre-publish validation with photo coverage, category matching, and policy compliance.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Data Models (listings), AI Features (Image Quality Scorer)

-   **Sales & Payout Reconciliation**: Order tracking with fees, shipping, tax calculations, and payout status.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Data Models (orders, payouts), API Integrations

-   **Inventory Aging + Liquidation**: Buckets for inventory age with markdown suggestions and cross-listing recommendations.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Data Models (items), AI Features (Forecasting)

-   **KPI Card Component**: Reusable component for displaying metrics with sparklines and period comparisons.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Metrics & Analytics

-   **Ops Kanban Board**: Workflow visualization for intake to payout stages.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (tasks)

-   **Aging Table**: Item inventory with days on hand, views, and markdown actions.

    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: Data Models (items)

-   **AI Summary Panel**: Integrated AI insights within dashboard views.
    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: AI Features (AI Summarization)

### Data Models

-   **Consignors Table**: Stores consignor information including payout methods and default splits.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: None

-   **Items Table**: Core item data with photos, categories, and status tracking.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: None

-   **Comps Table**: Comparable sales data from external sources.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (items)

-   **Listings Table**: Platform-specific listing information.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (items)

-   **Orders Table**: Sales transaction data with fees and shipping.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (items, listings)

-   **Payouts Table**: Consignor payout tracking and status.

    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: Data Models (consignors, orders)

-   **Tasks Table**: Workflow task management for operations.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (items)

-   **Audit Logs Table**: Change tracking for compliance and debugging.
    -   Priority: Low
    -   Complexity: Low
    -   Dependencies: All Data Models

### API Integrations

-   **eBay Finding API**: For comparable sales data and order webhooks.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (comps, orders)

-   **CardLadder API**: Sports card pricing and comparison data.

    -   Priority: High
    -   Complexity: Medium
    -   Dependencies: Data Models (comps)

-   **Shopify API**: Order and inventory synchronization.

    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: Data Models (orders, items)

-   **Rate-Limit Aware Fetcher**: Caching and backoff for external API calls.
    -   Priority: Medium
    -   Complexity: High
    -   Dependencies: All API Integrations

### Metrics & Analytics

-   **Sell-Through Rate**: Orders vs listings created in period.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (listings, orders)

-   **Net Margin Percentage**: Profit calculations after fees and costs.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (orders)

-   **Listing Throughput**: Items listed per day.

    -   Priority: High
    -   Complexity: Low
    -   Dependencies: Data Models (listings)

-   **Inventory Aging**: Days since intake with potential net calculations.
    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: Data Models (items)

### UI/UX Components

-   **Role-Based Access**: Different views for Admin, Ops, Finance, and Consignor roles.

    -   Priority: Low
    -   Complexity: Medium
    -   Dependencies: Dashboard Modules

-   **Wireframing & Layout Generation**: AI-assisted UI design and component generation.

    -   Priority: Low
    -   Complexity: Low
    -   Dependencies: None

-   **Reusable UI Components**: Tables, dropdowns, charts using TailwindCSS.
    -   Priority: Medium
    -   Complexity: Low
    -   Dependencies: None

### Automation & Jobs

-   **Nightly Jobs**: Comp refresh, aging calculations, anomaly scans, S3 backups.

    -   Priority: Medium
    -   Complexity: High
    -   Dependencies: Data Models, API Integrations

-   **Cron Triggers**: Daily summary generation and Slack posting.
    -   Priority: Medium
    -   Complexity: Medium
    -   Dependencies: AI Features (State of the Shop Summary)

### Documentation & Testing

-   **Auto-Generated README**: Project documentation and instructions.

    -   Priority: Low
    -   Complexity: Low
    -   Dependencies: None

-   **Testing Scripts**: Unit and integration tests for modules.

    -   Priority: Low
    -   Complexity: Medium
    -   Dependencies: All Features

-   **Snapshot Tests**: Fixed fixture testing for KPI queries.

    -   Priority: Low
    -   Complexity: Low
    -   Dependencies: Metrics & Analytics

-   **Error Tracking**: Sentry integration and slow query logging.

    -   Priority: Low
    -   Complexity: Medium
    -   Dependencies: All Features

-   **Synthetic Data Seeder**: Test data generation for development.
    -   Priority: Low
    -   Complexity: Low
    -   Dependencies: Data Models
