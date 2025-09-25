# System Architecture

## Overall Architecture

The Hidden Treasures Dashboard follows a traditional Model-View-Controller (MVC) architecture with a modern frontend approach using Laravel's Inertia.js. The system is designed as a single-page application (SPA) with server-side rendering capabilities, providing both performance and SEO benefits.

### High-Level Components

1. **Backend (Laravel)**: Handles business logic, data persistence, authentication, and API endpoints
2. **Frontend (Vue.js + Inertia.js)**: Provides responsive user interface with reactive components
3. **Database (SQLite/MySQL)**: Stores all application data with proper relationships and constraints
4. **File Storage**: Manages file uploads and media assets

## Source Code Paths

### Backend Structure

```
app/
├── Http/
│   ├── Controllers/         # Request handlers and business logic
│   │   ├── DashboardController.php    # Main dashboard data aggregation
│   │   ├── TaskController.php         # Task management
│   │   ├── SalesController.php        # Sales tracking
│   │   ├── ContentPostController.php  # Content management
│   │   ├── ExpenseController.php      # Expense tracking
│   │   ├── GoalController.php         # Goal management
│   │   └── ProfileController.php      # User profile management
│   ├── Middleware/          # Request interception and processing
│   │   ├── AdminMiddleware.php       # Admin access control
│   │   ├── ManagerMiddleware.php     # Manager access control
│   │   ├── VAMiddleware.php          # VA access control
│   │   └── HandleInertiaRequests.php # Inertia.js request handling
│   └── Requests/            # Form validation and authorization
├── Models/                 # Eloquent ORM models representing data entities
│   ├── User.php                   # User accounts and roles
│   ├── Task.php                   # Task management
│   ├── Sale.php                   # Sales records
│   ├── Expense.php                # Expense tracking
│   ├── Goal.php                   # Goal setting and tracking
│   └── ContentPost.php            # Content management
├── Policies/              # Authorization policies for resource access
└── Services/              # Business logic services
    ├── FileValidationService.php   # File upload validation
    ├── ImageService.php            # Image processing
    └── NotificationService.php     # Notification management
```

### Frontend Structure

```
resources/js/
├── Components/            # Reusable Vue components
│   ├── Dashboard/         # Dashboard-specific components
│   │   ├── DashboardGrid.vue          # Grid layout for dashboard modules
│   │   ├── DailySummaryModule.vue     # Daily summary display
│   │   ├── ActivityDistributionModule.vue # Activity charts
│   │   ├── SalesModule.vue            # Sales metrics
│   │   ├── ExpensesModule.vue         # Expense tracking
│   │   ├── ContentModule.vue          # Content statistics
│   │   ├── QuarterlyGoalsModule.vue  # Goal progress
│   │   └── TimePeriodDropdown.vue     # Time period selector
│   ├── Charts/            # Chart components using Chart.js
│   │   ├── PieChart.vue              # Pie chart visualization
│   │   └── ProgressBar.vue           # Progress bar component
│   ├── Shared/            # Shared UI components
│   │   ├── Fields/
│   │   │   └── BaseFileUploader.vue   # File upload component
│   │   └── FormModal.vue              # Modal form wrapper
│   └── UI/                # Basic UI components
│       ├── DataTable.vue             # Reusable data table
│       ├── SearchFilter.vue          # Search and filter interface
│       └── Sidebar.vue               # Navigation sidebar
├── Layouts/               # Page layout components
│   ├── AuthenticatedLayout.vue       # Layout for authenticated users
│   └── GuestLayout.vue               # Layout for guests
└── Pages/                 # Page-level components
    ├── Dashboard.vue               # Main dashboard page
    ├── Admin/Dashboard.vue          # Admin-specific dashboard
    ├── Manager/Dashboard.vue        # Manager-specific dashboard
    ├── VA/Dashboard.vue             # VA-specific dashboard
    ├── Tasks/                       # Task management pages
    ├── Sales/                       # Sales management pages
    ├── Content/                     # Content management pages
    ├── Expenses/                    # Expense management pages
    └── Goals/                       # Goal management pages
```

### Database Structure

```
database/
├── migrations/            # Database schema definitions
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 2025_08_07_082233_add_profile_fields_to_users_table.php
│   ├── 2025_08_07_082436_create_tasks_table.php
│   ├── 2025_08_07_082605_create_daily_summaries_table.php
│   ├── 2025_08_07_082729_create_sales_table.php
│   ├── 2025_08_07_082830_create_content_posts_table.php
│   ├── 2025_08_07_082939_create_expenses_table.php
│   ├── 2025_08_07_083052_create_goals_table.php
│   ├── 2025_08_12_101645_add_client_id_to_sales_table.php
│   ├── 2025_08_19_022812_create_categories_table.php
│   ├── 2025_08_26_135115_create_task_media_table.php
│   ├── 2025_08_26_135437_create_notifications_table.php
│   ├── 2025_08_26_141847_create_content_post_media_table.php
│   └── 2025_09_18_092237_create_clients_table.php
└── seeders/              # Database seeding for development
```

## Key Technical Decisions

### 1. Role-Based Access Control Implementation

The system implements a hierarchical role-based access control (RBAC) system using custom middleware:

-   **Admin**: Full system access with user management capabilities
-   **Manager**: Business operations access without user management
-   **Virtual Assistant**: Limited access to assigned tasks and view-only access to certain data

### 2. Time-Period Filtering Architecture

Dashboard modules implement a consistent time-period filtering pattern:

-   Daily, weekly, monthly, quarterly, yearly, and custom date ranges
-   Centralized date range calculation in DashboardController
-   Consistent API endpoints for all dashboard modules
-   Reactive frontend updates when time periods change

### 3. Data Aggregation Pattern

The DashboardController uses a consistent pattern for data aggregation:

-   Private methods for each data type (gatherSummaryData, gatherSalesMetricsData, etc.)
-   Time-period parameter handling with fallbacks
-   Consistent response format across all data types
-   Database query optimization with proper indexing

### 4. Frontend Component Architecture

Vue.js components follow a modular architecture:

-   Reusable chart components for data visualization
-   Dashboard modules as self-contained components
-   Shared UI components for consistent look and feel
-   Props-based data passing with event emission for user interactions

## Component Relationships

### Data Flow

1. **User Request** → **Route** → **Middleware** → **Controller**
2. **Controller** → **Model** → **Database Query** → **Data Processing**
3. **Controller** → **Inertia Response** → **Vue Component**
4. **Vue Component** → **User Interaction** → **Event Emission** → **API Call**
5. **API Call** → **Controller** → **Model** → **Database Update** → **Response**

### Key Relationships

-   **User** hasMany **Tasks**, **Sales**, **Expenses**, **Goals**, **ContentPosts**
-   **Task** belongsTo **User** (assigned_to) and hasMany **TaskMedia**
-   **Sale** belongsTo **User** and **Client**
-   **Expense** belongsTo **User**
-   **Goal** belongsTo **User**
-   **ContentPost** belongsTo **User** and hasMany **ContentPostMedia**

### Dashboard Module Dependencies

All dashboard modules depend on:

-   DashboardController for data aggregation
-   TimePeriodDropdown component for filtering
-   DashboardGrid for layout
-   Individual chart components for visualization

## Critical Implementation Paths

### Authentication Flow

1. User submits login credentials
2. Laravel authentication validates credentials
3. Session established with user role
4. Middleware checks role permissions for each request
5. Appropriate dashboard and features loaded based on role

### Dashboard Data Loading

1. User accesses dashboard
2. DashboardController aggregates data from multiple models
3. Time-period filters applied to all queries
4. Data formatted for frontend consumption
5. Inertia.js passes data to Vue components
6. Components render charts and metrics

### Task Management Flow

1. User creates/edits task through form
2. Form validation on frontend and backend
3. Task saved to database with proper relationships
4. Notifications triggered for assigned users
5. Task status updates reflected in dashboard
6. Media files uploaded and associated with task

### Sales Tracking Flow

1. User records sale with product details
2. Sale data saved with client association
3. Dashboard metrics updated in real-time
4. Sales trends calculated for charts
5. Revenue targets compared against actuals
