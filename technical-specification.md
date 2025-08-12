# Hidden Treasures Dashboard - Technical Specification

## Navigation Architecture & Frontend CRUD Component Structure

### Project Overview

**Current Status**: Laravel 11 + Inertia.js + Vue 3 application with role-based access control (Admin, Manager, VA)
**Goal**: Create comprehensive navigation architecture and CRUD component structure for 5 main modules

---

## 1. Navigation Architecture

### 1.1 Role-Based Navigation Structure

#### 1.1.1 Admin Navigation

**Primary Navigation Items:**

-   Dashboard (`/admin/dashboard`)
-   Sales Management (`/admin/sales`)
    -   All Sales (`/admin/sales`)
    -   Sales Reports (`/admin/sales/reports`)
-   Content Management (`/admin/content`)
    -   Content Posts (`/admin/content/posts`)
    -   Content Analytics (`/admin/content/analytics`)
-   Expense Management (`/admin/expenses`)
    -   All Expenses (`/admin/expenses`)
    -   Expense Categories (`/admin/expenses/categories`)
-   Goals Management (`/admin/goals`)
    -   Team Goals (`/admin/goals`)
    -   Goal Analytics (`/admin/goals/analytics`)
-   Task Management (`/admin/tasks`)
    -   All Tasks (`/admin/tasks`)
    -   Task Reports (`/admin/tasks/reports`)
-   User Management (`/admin/users`)
    -   Users (`/admin/users`)
    -   Roles & Permissions (`/admin/users/roles`)

#### 1.1.2 Manager Navigation

**Primary Navigation Items:**

-   Dashboard (`/manager/dashboard`)
-   Sales Overview (`/manager/sales`)
    -   My Sales (`/manager/sales`)
    -   Team Sales (`/manager/sales/team`)
-   Content Overview (`/manager/content`)
    -   My Content (`/manager/content`)
    -   Content Performance (`/manager/content/performance`)
-   Expense Tracking (`/manager/expenses`)
    -   My Expenses (`/manager/expenses`)
    -   Expense Reports (`/manager/expenses/reports`)
-   Goals Tracking (`/manager/goals`)
    -   My Goals (`/manager/goals`)
    -   Team Goals (`/manager/goals/team`)
-   Task Management (`/manager/tasks`)
    -   My Tasks (`/manager/tasks`)
    -   Assigned Tasks (`/manager/tasks/assigned`)

#### 1.1.3 VA (Virtual Assistant) Navigation

**Primary Navigation Items:**

-   Dashboard (`/va/dashboard`)
-   Sales Data Entry (`/va/sales`)
    -   Add Sale (`/va/sales/create`)
    -   View Sales (`/va/sales`)
-   Content Data Entry (`/va/content`)
    -   Add Content Post (`/va/content/create`)
    -   View Content (`/va/content`)
-   Expense Data Entry (`/va/expenses`)
    -   Add Expense (`/va/expenses/create`)
    -   View Expenses (`/va/expenses`)
-   Goals Tracking (`/va/goals`)
    -   View Goals (`/va/goals`)
    -   Update Progress (`/va/goals/progress`)
-   Task Management (`/va/tasks`)
    -   My Tasks (`/va/tasks`)
    -   Completed Tasks (`/va/tasks/completed`)

### 1.2 Navigation Component Structure

#### 1.2.1 Main Navigation Components

```
resources/js/Components/Navigation/
├── MainNavigation.vue          # Primary navigation container
├── DesktopNavigation.vue       # Desktop navigation menu
├── MobileNavigation.vue        # Mobile navigation menu
├── NavigationMenu.vue          # Reusable navigation menu
├── NavigationItem.vue          # Individual navigation item
├── NavigationDropdown.vue      # Dropdown navigation
├── NavigationBreadcrumbs.vue   # Breadcrumb navigation
└── UserMenu.vue               # User profile dropdown
```

#### 1.2.2 Role-Based Navigation Logic

```javascript
// Navigation configuration based on user role
const navigationConfig = {
    admin: [
        { name: "Dashboard", href: "/admin/dashboard", icon: "HomeIcon" },
        { name: "Sales", href: "/admin/sales", icon: "CurrencyDollarIcon" },
        { name: "Content", href: "/admin/content", icon: "DocumentTextIcon" },
        { name: "Expenses", href: "/admin/expenses", icon: "CreditCardIcon" },
        { name: "Goals", href: "/admin/goals", icon: "TargetIcon" },
        {
            name: "Tasks",
            href: "/admin/tasks",
            icon: "ClipboardDocumentListIcon",
        },
        { name: "Users", href: "/admin/users", icon: "UsersIcon" },
    ],
    manager: [
        { name: "Dashboard", href: "/manager/dashboard", icon: "HomeIcon" },
        { name: "Sales", href: "/manager/sales", icon: "CurrencyDollarIcon" },
        { name: "Content", href: "/manager/content", icon: "DocumentTextIcon" },
        { name: "Expenses", href: "/manager/expenses", icon: "CreditCardIcon" },
        { name: "Goals", href: "/manager/goals", icon: "TargetIcon" },
        {
            name: "Tasks",
            href: "/manager/tasks",
            icon: "ClipboardDocumentListIcon",
        },
    ],
    va: [
        { name: "Dashboard", href: "/va/dashboard", icon: "HomeIcon" },
        { name: "Sales", href: "/va/sales", icon: "CurrencyDollarIcon" },
        { name: "Content", href: "/va/content", icon: "DocumentTextIcon" },
        { name: "Expenses", href: "/va/expenses", icon: "CreditCardIcon" },
        { name: "Goals", href: "/va/goals", icon: "TargetIcon" },
        { name: "Tasks", href: "/va/tasks", icon: "ClipboardDocumentListIcon" },
    ],
};
```

---

## 2. Frontend CRUD Component Structure

### 2.1 Module-Based Component Organization

#### 2.1.1 Sales Module Components

```
resources/js/Pages/Sales/
├── Admin/
│   ├── SalesList.vue
│   ├── SalesForm.vue
│   ├── SalesShow.vue
│   └── SalesReports.vue
├── Manager/
│   ├── SalesList.vue
│   ├── SalesForm.vue
│   └── SalesShow.vue
└── VA/
    ├── SalesList.vue
    ├── SalesForm.vue
    └── SalesShow.vue

resources/js/Components/Sales/
├── SalesDataTable.vue
├── SalesFormFields.vue
├── SalesFilters.vue
├── SalesActions.vue
└── SalesStats.vue
```

#### 2.1.2 Content Module Components

```
resources/js/Pages/Content/
├── Admin/
│   ├── ContentList.vue
│   ├── ContentForm.vue
│   ├── ContentShow.vue
│   └── ContentAnalytics.vue
├── Manager/
│   ├── ContentList.vue
│   ├── ContentForm.vue
│   └── ContentShow.vue
└── VA/
    ├── ContentList.vue
    ├── ContentForm.vue
    └── ContentShow.vue

resources/js/Components/Content/
├── ContentDataTable.vue
├── ContentFormFields.vue
├── ContentFilters.vue
├── ContentActions.vue
└── ContentStats.vue
```

#### 2.1.3 Expenses Module Components

```
resources/js/Pages/Expenses/
├── Admin/
│   ├── ExpenseList.vue
│   ├── ExpenseForm.vue
│   ├── ExpenseShow.vue
│   └── ExpenseCategories.vue
├── Manager/
│   ├── ExpenseList.vue
│   ├── ExpenseForm.vue
│   └── ExpenseShow.vue
└── VA/
    ├── ExpenseList.vue
    ├── ExpenseForm.vue
    └── ExpenseShow.vue

resources/js/Components/Expenses/
├── ExpenseDataTable.vue
├── ExpenseFormFields.vue
├── ExpenseFilters.vue
├── ExpenseActions.vue
└── ExpenseStats.vue
```

#### 2.1.4 Goals Module Components

```
resources/js/Pages/Goals/
├── Admin/
│   ├── GoalList.vue
│   ├── GoalForm.vue
│   ├── GoalShow.vue
│   └── GoalAnalytics.vue
├── Manager/
│   ├── GoalList.vue
│   ├── GoalForm.vue
│   └── GoalShow.vue
└── VA/
    ├── GoalList.vue
│   ├── GoalProgress.vue
│   └── GoalShow.vue

resources/js/Components/Goals/
├── GoalDataTable.vue
├── GoalFormFields.vue
├── GoalFilters.vue
├── GoalActions.vue
├── GoalProgressBar.vue
└── GoalStats.vue
```

#### 2.1.5 Tasks Module Components

```
resources/js/Pages/Tasks/
├── Admin/
│   ├── TaskList.vue
│   ├── TaskForm.vue
│   ├── TaskShow.vue
│   └── TaskReports.vue
├── Manager/
│   ├── TaskList.vue
│   ├── TaskForm.vue
│   └── TaskShow.vue
└── VA/
    ├── TaskList.vue
    ├── TaskForm.vue
    └── TaskShow.vue

resources/js/Components/Tasks/
├── TaskDataTable.vue
├── TaskFormFields.vue
├── TaskFilters.vue
├── TaskActions.vue
├── TaskStatusBadge.vue
└── TaskStats.vue
```

### 2.2 Reusable CRUD Components

#### 2.2.1 Data Display Components

```
resources/js/Components/Common/
├── DataTable/
│   ├── DataTable.vue
│   ├── DataTableHeader.vue
│   ├── DataTableBody.vue
│   ├── DataTableFooter.vue
│   ├── DataTablePagination.vue
│   └── DataTableSearch.vue
├── Forms/
│   ├── FormContainer.vue
│   ├── FormField.vue
│   ├── FormInput.vue
│   ├── FormSelect.vue
│   ├── FormTextarea.vue
│   ├── FormDatePicker.vue
│   ├── FormCheckbox.vue
│   ├── FormRadio.vue
│   └── FormError.vue
├── Actions/
│   ├── ActionButtons.vue
│   ├── CreateButton.vue
│   ├── EditButton.vue
│   ├── DeleteButton.vue
│   ├── ViewButton.vue
│   └── BulkActions.vue
├── Modals/
│   ├── ModalContainer.vue
│   ├── ConfirmModal.vue
│   ├── FormModal.vue
│   └── SuccessModal.vue
├── Filters/
│   ├── FilterContainer.vue
│   ├── DateRangeFilter.vue
│   ├── StatusFilter.vue
│   └── SearchFilter.vue
└── Layout/
    ├── PageHeader.vue
    ├── PageActions.vue
    ├── PageContent.vue
    └── EmptyState.vue
```

---

## 3. Route Structure

### 3.1 RESTful Route Pattern

```
HTTP Method | URL Pattern              | Action | Route Name
-----------|--------------------------|--------|------------------
GET        | /{role}/{resource}       | index  | {role}.{resource}.index
GET        | /{role}/{resource}/create| create | {role}.{resource}.create
POST       | /{role}/{resource}       | store  | {role}.{resource}.store
GET        | /{role}/{resource}/{id}  | show   | {role}.{resource}.show
GET        | /{role}/{resource}/{id}/edit| edit| {role}.{resource}.edit
PUT/PATCH  | /{role}/{resource}/{id}  | update | {role}.{resource}.update
DELETE     | /{role}/{resource}/{id}  | destroy| {role}.{resource}.destroy
```

### 3.2 Complete Route Structure

#### 3.2.1 Admin Routes

```php
// Admin Routes
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Sales Routes
    Route::resource('sales', AdminSaleController::class)->names('admin.sales');
    Route::get('sales/reports', [AdminSaleController::class, 'reports'])->name('admin.sales.reports');

    // Content Routes
    Route::resource('content', AdminContentController::class)->names('admin.content');
    Route::get('content/analytics', [AdminContentController::class, 'analytics'])->name('admin.content.analytics');

    // Expense Routes
    Route::resource('expenses', AdminExpenseController::class)->names('admin.expenses');
    Route::get('expenses/categories', [AdminExpenseController::class, 'categories'])->name('admin.expenses.categories');

    // Goal Routes
    Route::resource('goals', AdminGoalController::class)->names('admin.goals');
    Route::get('goals/analytics', [AdminGoalController::class, 'analytics'])->name('admin.goals.analytics');

    // Task Routes
    Route::resource('tasks', AdminTaskController::class)->names('admin.tasks');
    Route::get('tasks/reports', [AdminTaskController::class, 'reports'])->name('admin.tasks.reports');

    // User Routes
    Route::resource('users', AdminUserController::class)->names('admin.users');
    Route::get('users/roles', [AdminUserController::class, 'roles'])->name('admin.users.roles');
});
```

#### 3.2.2 Manager Routes

```php
// Manager Routes
Route::middleware(['auth', 'manager'])->prefix('manager')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('manager.dashboard');

    // Sales Routes
    Route::resource('sales', ManagerSaleController::class)->names('manager.sales');
    Route::get('sales/team', [ManagerSaleController::class, 'team'])->name('manager.sales.team');

    // Content Routes
    Route::resource('content', ManagerContentController::class)->names('manager.content');
    Route::get('content/performance', [ManagerContentController::class, 'performance'])->name('manager.content.performance');

    // Expense Routes
    Route::resource('expenses', ManagerExpenseController::class)->names('manager.expenses');
    Route::get('expenses/reports', [ManagerExpenseController::class, 'reports'])->name('manager.expenses.reports');

    // Goal Routes
    Route::resource('goals', ManagerGoalController::class)->names('manager.goals');
    Route::get('goals/team', [ManagerGoalController::class, 'team'])->name('manager.goals.team');

    // Task Routes
    Route::resource('tasks', ManagerTaskController::class)->names('manager.tasks');
    Route::get('tasks/assigned', [ManagerTaskController::class, 'assigned'])->name('manager.tasks.assigned');
});
```

#### 3.2.3 VA Routes

```php
// VA Routes
Route::middleware(['auth', 'va'])->prefix('va')->group(function () {
    Route::get('/dashboard', [VADashboardController::class, 'index'])->name('va.dashboard');

    // Sales Routes
    Route::resource('sales', VASaleController::class)->names('va.sales');

    // Content Routes
    Route::resource('content', VAContentController::class)->names('va.content');

    // Expense Routes
    Route::resource('expenses', VAExpenseController::class)->names('va.expenses');

    // Goal Routes
    Route::resource('goals', VAGoalController::class)->names('va.goals');
    Route::get('goals/progress', [VAGoalController::class, 'progress'])->name('va.goals.progress');

    // Task Routes
    Route::resource('tasks', VATaskController::class)->names('va.tasks');
    Route::get('tasks/completed', [VATaskController::class, 'completed'])->name('va.tasks.completed');
});
```

---

## 4. API Endpoint Design

### 4.1 RESTful API Structure

#### 4.1.1 Base API Routes

```
/api/v1/{resource}
```

#### 4.1.2 Resource Endpoints

**Sales API:**

```
GET    /api/v1/sales              # List all sales
POST   /api/v1/sales              # Create new sale
GET    /api/v1/sales/{id}         # Get specific sale
PUT    /api/v1/sales/{id}         # Update sale
DELETE /api/v1/sales/{id}         # Delete sale
GET    /api/v1/sales/reports      # Get sales reports
GET    /api/v1/sales/summary      # Get sales summary
```

**Content API:**

```
GET    /api/v1/content            # List all content posts
POST   /api/v1/content            # Create new content post
GET    /api/v1/content/{id}       # Get specific content post
PUT    /api/v1/content/{id}       # Update content post
DELETE /api/v1/content/{id}       # Delete content post
GET    /api/v1/content/analytics  # Get content analytics
```

**Expenses API:**

```
GET    /api/v1/expenses           # List all expenses
POST   /api/v1/expenses           # Create new expense
GET    /api/v1/expenses/{id}      # Get specific expense
PUT    /api/v1/expenses/{id}      # Update expense
DELETE /api/v1/expenses/{id}      # Delete expense
GET    /api/v1/expenses/categories # Get expense categories
```

**Goals API:**

```
GET    /api/v1/goals              # List all goals
POST   /api/v1/goals              # Create new goal
GET    /api/v1/goals/{id}         # Get specific goal
PUT    /api/v1/goals/{id}         # Update goal
DELETE /api/v1/goals/{id}         # Delete goal
PUT    /api/v1/goals/{id}/progress # Update goal progress
```

**Tasks API:**

```
GET    /api/v1/tasks              # List all tasks
POST   /api/v1/tasks              # Create new task
GET    /api/v1/tasks/{id}         # Get specific task
PUT    /api/v1/tasks/{id}         # Update task
DELETE /api/v1/tasks/{id}         # Delete task
PUT    /api/v1/tasks/{id}/status  # Update task status
```

### 4.2 API Response Structure

#### 4.2.1 Success Response

```json
{
    "success": true,
    "data": {
        // Resource data
    },
    "message": "Operation completed successfully",
    "meta": {
        "timestamp": "2025-08-07T11:00:00Z",
        "version": "1.0"
    }
}
```

#### 4.2.2 Error Response

```json
{
    "success": false,
    "message": "Validation error",
    "errors": {
        "field_name": ["Error message"]
    },
    "meta": {
        "timestamp": "2025-08-07T11:00:00Z",
        "version": "1.0"
    }
}
```

---

## 5. Mobile-Responsive Navigation

### 5.1 Responsive Design Breakpoints

-   **Mobile**: < 768px
-   **Tablet**: 768px - 1024px
-   **Desktop**: > 1024px

### 5.2 Mobile Navigation Features

-   Collapsible sidebar navigation
-   Bottom tab bar for primary actions
-   Swipe gestures for navigation
-   Touch-friendly buttons (min 44x44px)
-   Optimized for one-handed use

### 5.3 Mobile Navigation Structure

```vue
<!-- MobileNavigation.vue -->
<template>
    <div class="mobile-nav">
        <!-- Bottom Tab Bar -->
        <nav class="fixed bottom-0 left-0 right-0 bg-white border-t">
            <div class="flex justify-around py-2">
                <button
                    v-for="item in mobileNavItems"
                    :key="item.name"
                    @click="navigate(item.href)"
                    class="flex flex-col items-center p-2"
                >
                    <component :is="item.icon" class="w-6 h-6" />
                    <span class="text-xs mt-1">{{ item.name }}</span>
                </button>
            </div>
        </nav>

        <!-- Hamburger Menu -->
        <div class="hamburger-menu">
            <!-- Full screen overlay menu -->
        </div>
    </div>
</template>
```

---

## 6. Breadcrumb Navigation System

### 6.1 Breadcrumb Structure

```vue
<!-- NavigationBreadcrumbs.vue -->
<template>
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-2">
            <li>
                <Link
                    :href="route('dashboard')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    Home
                </Link>
            </li>
            <li v-for="crumb in breadcrumbs" :key="crumb.name">
                <div class="flex items-center">
                    <ChevronRightIcon class="h-5 w-5 text-gray-400" />
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                    >
                        {{ crumb.name }}
                    </Link>
                    <span v-else class="ml-2 text-sm font-medium text-gray-700">
                        {{ crumb.name }}
                    </span>
                </div>
            </li>
        </ol>
    </nav>
</template>
```

### 6.2 Breadcrumb Generation Logic

```javascript
// Generate breadcrumbs based on current route
const generateBreadcrumbs = (route) => {
    const segments = route.split("/");
    const breadcrumbs = [];

    let path = "";
    segments.forEach((segment, index) => {
        if (segment) {
            path += `/${segment}`;
            breadcrumbs.push({
                name: formatSegmentName(segment),
                href: index < segments.length - 1 ? path : null,
            });
        }
    });

    return breadcrumbs;
};
```

---

## 7. Modal/Dialog System

### 7.1 Modal Types

-   **Form Modal**: For create/edit operations
-   **Confirm Modal**: For delete confirmations
-   **Info Modal**: For displaying detailed information
-   **Success Modal**: For operation success feedback

### 7.2 Modal Component Structure

```vue
<!-- ModalContainer.vue -->
<template>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black bg-opacity-25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 shadow-xl transition-all"
                        >
                            <slot />
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
```

---

## 8. Pagination & Search Components

### 8.1 Pagination Component

```vue
<!-- DataTablePagination.vue -->
<template>
    <div
        class="flex items-center justify-between border-t border-gray-200 px-4 py-3"
    >
        <div class="flex flex-1 justify-between sm:hidden">
            <button
                @click="previousPage"
                :disabled="!hasPreviousPage"
                class="relative inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium"
            >
                Previous
            </button>
            <button
                @click="nextPage"
                :disabled="!hasNextPage"
                class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-medium"
            >
                Next
            </button>
        </div>
        <div
            class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
        >
            <div>
                <p class="text-sm text-gray-700">
                    Showing <span class="font-medium">{{ from }}</span> to
                    <span class="font-medium">{{ to }}</span> of
                    <span class="font-medium">{{ total }}</span> results
                </p>
            </div>
            <div>
                <nav
                    class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                >
                    <button
                        v-for="page in pages"
                        :key="page"
                        @click="goToPage(page)"
                        :class="pageClasses(page)"
                    >
                        {{ page }}
                    </button>
                </nav>
            </div>
        </div>
    </div>
</template>
```

### 8.2 Search Component

```vue
<!-- DataTableSearch.vue -->
<template>
    <div class="relative">
        <div
            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
        >
            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" />
        </div>
        <input
            v-model="searchQuery"
            @input="onSearch"
            type="text"
            placeholder="Search..."
            class="block w-full rounded-md border-gray-300 pl-10 text-sm"
        />
    </div>
</template>
```

---

## 9. Implementation Guidelines

### 9.1 Development Phases

#### Phase 1: Foundation (Week 1)

-   [ ] Set up navigation architecture
-   [ ] Create base layout components
-   [ ] Implement role-based routing
-   [ ] Create reusable component library

#### Phase 2: CRUD Components (Week 2)

-   [ ] Create DataTable component
-   [ ] Create Form components
-   [ ] Create Modal system
-   [ ] Implement pagination and search

#### Phase 3: Module Implementation (Week 3-4)

-   [ ] Implement Sales module
-   [ ] Implement Content module
-   [ ] Implement Expenses module
-   [ ] Implement Goals module
-   [ ] Implement Tasks module

#### Phase 4: Polish & Testing (Week 5)

-   [ ] Mobile responsiveness
-   [ ] Performance optimization
-   [ ] Accessibility improvements
-   [ ] User testing and feedback

### 9.2 Component Development Order

1. **Base Components** (reusable across all modules)
2. **Navigation Components** (layout and routing)
3. **Form Components** (create/edit operations)
4. **Data Display Components** (list/show operations)
5. **Module-Specific Components** (business logic)

### 9.3 Testing Strategy

-   Unit tests for individual components
-   Integration tests for CRUD operations
-   End-to-end tests for user workflows
-   Cross-browser compatibility testing
-   Mobile responsiveness testing

### 9.4 Performance Considerations

-   Lazy loading for route components
-   Pagination for large datasets
-   Debounced search inputs
-   Optimized re-rendering with Vue 3
-   Image optimization for avatars

### 9.5 Accessibility Requirements

-   ARIA labels for all interactive elements
-   Keyboard navigation support
-   Screen reader compatibility
-   Color contrast compliance (WCAG 2.1)
-   Focus management for modals

---

## 10. File Structure Summary

```
resources/js/
├── Components/
│   ├── Common/           # Reusable components
│   ├── Navigation/       # Navigation components
│   ├── Sales/           # Sales-specific components
│   ├── Content/         # Content-specific components
│   ├── Expenses/        # Expense-specific components
│   ├── Goals/           # Goal-specific components
│   └── Tasks/           # Task-specific components
├── Pages/
│   ├── Admin/           # Admin role pages
│   ├── Manager/         # Manager role pages
│   ├── VA/              # VA role pages
│   └── Shared/          # Shared pages
├── Composables/         # Vue 3 composables
├── Utils/              # Utility functions
└── Stores/             # Pinia stores (if needed)
```

This technical specification provides a comprehensive blueprint for implementing the navigation architecture and frontend CRUD component structure for the Hidden Treasures dashboard.
