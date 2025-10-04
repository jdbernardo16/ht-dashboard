# Hidden Treasures Dashboard - Project Brief

## Overview

The Hidden Treasures Dashboard is a comprehensive business management system designed for a sports card consignment business. This AI-powered internal dashboard streamlines operations from item intake through pricing, listing, sales, and payout reconciliation, providing role-based access control for administrators, managers, and virtual assistants.

## Main Objectives

- Optimize consignment workflows and improve operational efficiency
- Enhance sell-through rates through AI-assisted pricing and description generation
- Provide executive-level KPIs and business analytics
- Automate risk detection and anomaly identification
- Streamline communication through automated summaries and notifications

## Key Features

### Core Modules
- **Task Management**: Create, assign, and track tasks with priorities and deadlines
- **Sales Tracking**: Monitor sales performance and generate comprehensive reports
- **Content Management**: Plan and track content creation and publishing
- **Expense Management**: Track business expenses and generate financial reports
- **Goal Setting**: Set and monitor business goals with progress tracking
- **Daily Summaries**: Generate automated business summaries and reports

### AI-Powered Features
- **AI Price Suggester**: Automated pricing recommendations using eBay Finding API and CardLadder data
- **AI Description Generator**: One-click product description creation with SEO optimization
- **Risk Detection**: Automated identification of high-risk listings and negative margin orders
- **Auto-Tagging System**: Automatic categorization of items using OCR and AI models
- **AI Summary Panel**: "What changed & why" explanations for KPIs and business metrics

## Technology Stack

### Backend
- **Framework**: Laravel 12.x with PHP 8.2+
- **Authentication**: Laravel Sanctum
- **Database**: SQLite (development) / MySQL (production)
- **Image Processing**: Intervention Image

### Frontend
- **Framework**: Vue.js 3 with Inertia.js
- **Styling**: Tailwind CSS
- **Charts**: Chart.js with vue-chartjs
- **Build Tool**: Vite

### External Integrations
- **eBay Finding API**: Comparable sales data and order webhooks
- **CardLadder API**: Sports card pricing and comparison data
- **Shopify API**: Order and inventory synchronization

## User Roles & Access Control

- **Admin**: Full system access with comprehensive analytics and user management
- **Manager**: Team management with access to sales, content, expenses, and goals
- **Virtual Assistant**: Task-focused access with limited permissions based on assignments

## Significance

This dashboard transforms a traditional sports card consignment business into an AI-driven operation, significantly improving efficiency, accuracy, and profitability. By automating repetitive tasks, providing intelligent pricing recommendations, and offering real-time business insights, the system enables data-driven decision-making and operational excellence in a competitive market.