# Hidden Treasures Dashboard - Frontend Architecture Guide

## Table of Contents

1. [Project Overview](#project-overview)
2. [Component Architecture Strategy](#component-architecture-strategy)
3. [State Management Approach](#state-management-approach)
4. [Styling Architecture](#styling-architecture)
5. [Dashboard Layout Implementation](#dashboard-layout-implementation)
6. [Shadcn/Vue Integration](#shadcnvue-integration)
7. [Performance Optimization](#performance-optimization)
8. [Development Workflow](#development-workflow)

## Project Overview

### Current Tech Stack

-   **Backend**: Laravel 11
-   **Frontend**: Vue 3 with Composition API
-   **Routing**: Inertia.js
-   **Styling**: TailwindCSS
-   **Charts**: Chart.js
-   **Build Tool**: Vite

### Figma Design Requirements

-   **Dashboard Size**: 1405px × 1290px
-   **Layout**: 2×3 grid with 6 main modules
-   **Design Tokens**:
    -   Primary Blue: `#2563EB`
    -   Success Green: `#22C55E`
    -   Typography: Inter font family
    -   Spacing: 24px padding, 8px border radius
-   **Modules**: Daily Summary, Activity Distribution, Sales, Expenses, Content, Quarterly Goals

## 1. Component Architecture Strategy

### 1.1 Component Hierarchy

```
resources/js/
├── Components/
│   ├── UI/                    # Shadcn/Vue base components
│   │   ├── Card.vue
│   │   ├── Button.vue
│   │   ├── Avatar.vue
│   │   └── Badge.vue
│   ├── Dashboard/             # Dashboard-specific components
│   │   ├── DashboardGrid.vue
│   │   ├── DashboardModule.vue
│   │   ├── Modules/
│   │   │   ├── DailySummaryModule.vue
│   │   │   ├── ActivityDistributionModule.vue
│   │   │   ├── SalesModule.vue
│   │   │   ├── ExpensesModule.vue
│   │   │   ├── ContentModule.vue
│   │   │   └── QuarterlyGoalsModule.vue
│   │   └── Widgets/
│   │       ├── MetricCard.vue
│   │       ├── TeamMemberWidget.vue
│   │       ├── ProgressWidget.vue
│   │       └── ChartWidget.vue
│   ├── Charts/                # Chart components
│   │   ├── PieChart.vue
│   │   ├── ProgressBar.vue
│   │   ├── LineChart.vue
│   │   └── BarChart.vue
│   └── Common/                # Shared components
│       ├── DataTable.vue
│       ├── SearchFilter.vue
│       └── FormModal.vue
```

### 1.2 Component Design Principles

#### Atomic Design Pattern

-   **Atoms**: Basic UI elements (Button, Input, Avatar)
-   **Molecules**: Simple component combinations (MetricCard, ProgressWidget)
-   **Organisms**: Complex components (DashboardModule, DataTable)
-   **Templates**: Page layouts (DashboardGrid)
-   **Pages**: Complete views (Dashboard.vue)

#### Component Composition Strategy

```vue
<!-- DashboardModule.vue - Base module wrapper -->
<template>
    <Card :class="moduleClasses">
        <CardHeader>
            <CardTitle>{{ title }}</CardTitle>
            <CardDescription v-if="description">{{
                description
            }}</CardDescription>
        </CardHeader>
        <CardContent>
            <slot />
        </CardContent>
        <CardFooter v-if="$slots.footer">
            <slot name="footer" />
        </CardFooter>
    </Card>
</template>

<script setup>
import { computed } from "vue";
import { cn } from "@/Utils/utils";

const props = defineProps({
    title: String,
    description: String,
    size: {
        type: String,
        default: "default",
        validator: (value) => ["small", "default", "large"].includes(value),
    },
});

const moduleClasses = computed(() =>
    cn("dashboard-module", {
        "col-span-1": props.size === "small",
        "col-span-1": props.size === "default",
        "col-span-2": props.size === "large",
    })
);
</script>
```

### 1.3 Reusable Component Patterns

#### MetricCard Component

```vue
<!-- MetricCard.vue -->
<template>
    <div class="metric-card">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600">{{ label }}</p>
                <p class="text-2xl font-bold" :class="valueColor">
                    {{ formattedValue }}
                </p>
            </div>
            <div v-if="icon" class="p-2 rounded-lg" :class="iconBg">
                <component :is="icon" class="w-6 h-6" :class="iconColor" />
            </div>
        </div>
        <div v-if="trend" class="mt-2 flex items-center text-sm">
            <TrendIcon :trend="trend.direction" class="w-4 h-4 mr-1" />
            <span :class="trendColor"
                >{{ trend.value }}% {{ trend.period }}</span
            >
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
    label: String,
    value: [String, Number],
    icon: Object,
    trend: Object,
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            ["default", "success", "warning", "danger"].includes(value),
    },
});

const formattedValue = computed(() => {
    if (typeof props.value === "number") {
        return new Intl.NumberFormat().format(props.value);
    }
    return props.value;
});

const valueColor = computed(() => ({
    "text-gray-900": props.variant === "default",
    "text-green-600": props.variant === "success",
    "text-yellow-600": props.variant === "warning",
    "text-red-600": props.variant === "danger",
}));
</script>
```

## 2. State Management Approach

### 2.1 Inertia.js Data Flow

Since we're using manual refresh only, our state management will be straightforward:

```vue
<!-- Dashboard.vue -->
<script setup>
import { computed } from "vue";

// Props from Laravel controller
const props = defineProps({
    dashboardData: Object,
    user: Object,
});

// Computed properties for dashboard modules
const dailySummary = computed(() => props.dashboardData.dailySummary);
const activityDistribution = computed(
    () => props.dashboardData.activityDistribution
);
const salesMetrics = computed(() => props.dashboardData.salesMetrics);
const expenses = computed(() => props.dashboardData.expenses);
const contentStats = computed(() => props.dashboardData.contentStats);
const quarterlyGoals = computed(() => props.dashboardData.quarterlyGoals);
</script>
```

### 2.2 Laravel Controller Structure

```php
// app/Http/Controllers/DashboardController.php
<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'dashboardData' => [
                'dailySummary' => $this->getDailySummary($user),
                'activityDistribution' => $this->getActivityDistribution($user),
                'salesMetrics' => $this->getSalesMetrics($user),
                'expenses' => $this->getExpenses($user),
                'contentStats' => $this->getContentStats($user),
                'quarterlyGoals' => $this->getQuarterlyGoals($user),
            ],
            'user' => $user->only(['id', 'name', 'email', 'avatar'])
        ]);
    }

    private function getDailySummary($user)
    {
        return [
            'totalTasks' => $user->tasks()->count(),
            'completedTasks' => $user->tasks()->where('status', 'completed')->count(),
            'totalRevenue' => $user->sales()->sum('amount'),
            'activeProjects' => $user->projects()->where('status', 'active')->count(),
        ];
    }

    // Additional methods for other modules...
}
```

### 2.3 Component Communication Patterns

#### Parent-Child Communication

```vue
<!-- Parent: Dashboard.vue -->
<template>
    <DashboardGrid>
        <DailySummaryModule
            :data="dailySummary"
            @task-completed="handleTaskCompleted"
        />
    </DashboardGrid>
</template>

<!-- Child: DailySummaryModule.vue -->
<script setup>
const emit = defineEmits(["task-completed"]);

const handleTaskAction = (taskId) => {
    // Handle task completion
    emit("task-completed", taskId);
};
</script>
```

#### Provide/Inject for Deep Nesting

```vue
<!-- Dashboard.vue -->
<script setup>
import { provide } from "vue";

provide("dashboardContext", {
    user: props.user,
    refreshData: () => router.reload({ only: ["dashboardData"] }),
});
</script>

<!-- Deep child component -->
<script setup>
import { inject } from "vue";

const { user, refreshData } = inject("dashboardContext");
</script>
```

## 3. Styling Architecture

### 3.1 TailwindCSS Configuration for Figma Design Tokens

```javascript
// tailwind.config.js
export default {
    content: ["./resources/js/**/*.vue", "./resources/views/**/*.blade.php"],
    theme: {
        extend: {
            fontFamily: {
                sans: ["Inter", ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Figma Design Tokens
                primary: {
                    50: "#eff6ff",
                    100: "#dbeafe",
                    500: "#3b82f6",
                    600: "#2563eb", // Main Figma blue
                    700: "#1d4ed8",
                },
                success: {
                    50: "#f0fdf4",
                    500: "#22c55e", // Figma green
                    600: "#16a34a",
                },
                // Dashboard specific colors
                dashboard: {
                    bg: "#f9fafb",
                    card: "#ffffff",
                    border: "#e5e7eb",
                    text: {
                        primary: "#111827",
                        secondary: "#6b7280",
                        muted: "#9ca3af",
                    },
                },
            },
            spacing: {
                // Figma spacing tokens
                18: "4.5rem", // 72px
                22: "5.5rem", // 88px
                88: "22rem", // 352px
            },
            borderRadius: {
                figma: "8px", // Figma border radius
                "4xl": "2rem",
            },
            boxShadow: {
                "figma-soft":
                    "0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)",
                "figma-card":
                    "0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06)",
            },
            // Dashboard grid system
            gridTemplateColumns: {
                dashboard: "repeat(2, minmax(0, 1fr))",
                "dashboard-lg": "repeat(3, minmax(0, 1fr))",
            },
        },
    },
    plugins: [require("@tailwindcss/forms")],
};
```

### 3.2 Component Styling Patterns

#### CSS Custom Properties for Dynamic Theming

```css
/* resources/css/app.css */
@import "tailwindcss/base";
@import "tailwindcss/components";
@import "tailwindcss/utilities";

@layer base {
    :root {
        /* Figma Design Tokens */
        --color-primary: 37 99 235; /* #2563EB */
        --color-success: 34 197 94; /* #22C55E */
        --color-background: 249 250 251; /* #F9FAFB */

        /* Dashboard specific */
        --dashboard-padding: 24px;
        --dashboard-border-radius: 8px;
        --dashboard-card-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    }
}

@layer components {
    .dashboard-module {
        @apply bg-white rounded-figma shadow-figma-card border border-dashboard-border;
        padding: var(--dashboard-padding);
    }

    .metric-value {
        @apply text-2xl font-bold text-dashboard-text-primary;
    }

    .metric-label {
        @apply text-sm font-medium text-dashboard-text-secondary;
    }
}
```

### 3.3 Responsive Design Strategy

```vue
<!-- DashboardGrid.vue -->
<template>
    <div class="dashboard-grid">
        <slot />
    </div>
</template>

<style scoped>
.dashboard-grid {
    @apply grid gap-6;
    @apply grid-cols-1; /* Mobile: 1 column */
    @apply md:grid-cols-2; /* Tablet: 2 columns */
    @apply lg:grid-cols-2; /* Desktop: 2 columns (Figma design) */
    @apply xl:grid-cols-3; /* Large: 3 columns for extra space */

    /* Figma design constraints */
    max-width: 1405px;
    margin: 0 auto;
}

/* Module size variants */
.dashboard-module.size-small {
    @apply col-span-1;
}

.dashboard-module.size-default {
    @apply col-span-1 md:col-span-1;
}

.dashboard-module.size-large {
    @apply col-span-1 md:col-span-2;
}

/* Responsive breakpoints matching Figma */
@media (min-width: 1405px) {
    .dashboard-grid {
        width: 1405px;
    }
}
</style>
```

## 4. Dashboard Layout Implementation

### 4.1 Grid System for 6 Dashboard Modules

```vue
<!-- Dashboard.vue -->
<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="dashboard-header">
                <h1 class="text-3xl font-bold text-dashboard-text-primary">
                    Dashboard
                </h1>
                <p class="text-dashboard-text-secondary mt-1">
                    Welcome back! Here's your performance overview.
                </p>
            </div>
        </template>

        <div class="dashboard-container">
            <DashboardGrid>
                <!-- Row 1 -->
                <DailySummaryModule
                    :data="dailySummary"
                    class="dashboard-module"
                />
                <ActivityDistributionModule
                    :data="activityDistribution"
                    class="dashboard-module"
                />

                <!-- Row 2 -->
                <SalesModule :data="salesMetrics" class="dashboard-module" />
                <ExpensesModule :data="expenses" class="dashboard-module" />

                <!-- Row 3 -->
                <ContentModule :data="contentStats" class="dashboard-module" />
                <QuarterlyGoalsModule
                    :data="quarterlyGoals"
                    class="dashboard-module"
                />
            </DashboardGrid>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.dashboard-container {
    @apply py-8 px-4 sm:px-6 lg:px-8;
    @apply mx-auto max-w-7xl;
}

.dashboard-header {
    @apply mb-8;
}
</style>
```

### 4.2 Individual Module Components

```vue
<!-- DailySummaryModule.vue -->
<template>
    <DashboardModule
        title="Daily Summary"
        description="Your daily performance overview"
    >
        <div class="grid grid-cols-2 gap-4">
            <MetricCard
                label="Total Tasks"
                :value="data.totalTasks"
                :icon="TaskIcon"
                variant="default"
            />
            <MetricCard
                label="Completed"
                :value="data.completedTasks"
                :icon="CheckIcon"
                variant="success"
                :trend="{ direction: 'up', value: 12, period: 'vs yesterday' }"
            />
            <MetricCard
                label="Revenue"
                :value="formatCurrency(data.totalRevenue)"
                :icon="DollarIcon"
                variant="success"
            />
            <MetricCard
                label="Active Projects"
                :value="data.activeProjects"
                :icon="ProjectIcon"
                variant="default"
            />
        </div>
    </DashboardModule>
</template>

<script setup>
import DashboardModule from "./DashboardModule.vue";
import MetricCard from "../Widgets/MetricCard.vue";
import {
    TaskIcon,
    CheckIcon,
    DollarIcon,
    ProjectIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
    }).format(value);
};
</script>
```

## 5. Shadcn/Vue Integration

### 5.1 Installation and Setup

```bash
# Install Shadcn/Vue
npm install @shadcn/vue
npx shadcn-vue@latest init

# Install required components
npx shadcn-vue@latest add card
npx shadcn-vue@latest add button
npx shadcn-vue@latest add avatar
npx shadcn-vue@latest add badge
npx shadcn-vue@latest add progress
npx shadcn-vue@latest add separator
```

### 5.2 Shadcn/Vue Configuration

```javascript
// components.json
{
  "$schema": "https://ui.shadcn.com/schema.json",
  "style": "default",
  "rsc": false,
  "tsx": false,
  "tailwind": {
    "config": "tailwind.config.js",
    "css": "resources/css/app.css",
    "baseColor": "slate",
    "cssVariables": true
  },
  "aliases": {
    "components": "resources/js/Components/UI",
    "utils": "resources/js/Utils"
  }
}
```

### 5.3 Customizing Shadcn Components for Figma Design

```vue
<!-- resources/js/Components/UI/Card.vue - Enhanced -->
<template>
    <div :class="cardClasses">
        <slot />
    </div>
</template>

<script setup>
import { computed } from "vue";
import { cn } from "@/Utils/utils";

const props = defineProps({
    variant: {
        type: String,
        default: "default",
        validator: (value) =>
            ["default", "dashboard", "elevated"].includes(value),
    },
});

const cardClasses = computed(() =>
    cn("rounded-lg border bg-card text-card-foreground shadow-sm", {
        // Default Shadcn styling
        "border-border": props.variant === "default",
        // Dashboard specific styling (Figma)
        "border-dashboard-border bg-white shadow-figma-card rounded-figma":
            props.variant === "dashboard",
        // Elevated variant
        "shadow-figma-soft border-transparent": props.variant === "elevated",
    })
);
</script>
```

### 5.4 Component Library Organization

```
resources/js/Components/
├── UI/                        # Shadcn/Vue components (customized)
│   ├── Card.vue              # Enhanced with Figma tokens
│   ├── Button.vue            # Customized variants
│   ├── Avatar.vue            # Team member avatars
│   ├── Badge.vue             # Status indicators
│   ├── Progress.vue          # Progress bars
│   └── Separator.vue         # Visual separators
├── Dashboard/                 # Dashboard-specific components
│   └── [modules and widgets]
└── Common/                    # Project-specific shared components
    └── [existing components]
```

## 6. Performance Optimization

### 6.1 Component Lazy Loading

```vue
<!-- Dashboard.vue -->
<script setup>
import { defineAsyncComponent } from "vue";

// Lazy load dashboard modules
const DailySummaryModule = defineAsyncComponent(() =>
    import("./Components/Dashboard/Modules/DailySummaryModule.vue")
);
const ActivityDistributionModule = defineAsyncComponent(() =>
    import("./Components/Dashboard/Modules/ActivityDistributionModule.vue")
);
const SalesModule = defineAsyncComponent(() =>
    import("./Components/Dashboard/Modules/SalesModule.vue")
);

// Loading component for better UX
const LoadingComponent = {
    template: `
    <div class="dashboard-module animate-pulse">
      <div class="h-4 bg-gray-200 rounded w-1/4 mb-4"></div>
      <div class="space-y-2">
        <div class="h-3 bg-gray-200 rounded"></div>
        <div class="h-3 bg-gray-200 rounded w-5/6"></div>
      </div>
    </div>
  `,
};
</script>
```

### 6.2 Data Fetching Strategies

```php
// app/Http/Controllers/DashboardController.php
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Eager load relationships to prevent N+1 queries
        $user = $request->user()->load([
            'tasks:id,user_id,status,created_at',
            'sales:id,user_id,amount,created_at',
            'expenses:id,user_id,amount,created_at',
            'goals:id,user_id,target_amount,current_amount'
        ]);

        // Cache expensive calculations
        $dashboardData = Cache::remember(
            "dashboard_data_{$user->id}",
            now()->addMinutes(15),
            fn() => $this->calculateDashboardData($user)
        );

        return Inertia::render('Dashboard', [
            'dashboardData' => $dashboardData,
            'user' => $user->only(['id', 'name', 'email', 'avatar'])
        ]);
    }
}
```

### 6.3 Chart Rendering Optimization

```vue
<!-- PieChart.vue - Optimized -->
<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    data: Object,
    options: Object,
});

const chartCanvas = ref(null);
let chart = null;
let resizeObserver = null;

const createChart = async () => {
    if (!chartCanvas.value) return;

    await nextTick();

    const ctx = chartCanvas.value.getContext("2d");
    chart = new Chart(ctx, {
        type: "pie",
        data: props.data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
                duration: 750,
                easing: "easeInOutQuart",
            },
            ...props.options,
        },
    });
};

// Optimize resize handling
onMounted(() => {
    createChart();

    // Use ResizeObserver for better performance
    resizeObserver = new ResizeObserver(() => {
        if (chart) {
            chart.resize();
        }
    });

    if (chartCanvas.value) {
        resizeObserver.observe(chartCanvas.value.parentElement);
    }
});

onUnmounted(() => {
    if (chart) {
        chart.destroy();
    }
    if (resizeObserver) {
        resizeObserver.disconnect();
    }
});
</script>
```

## 7. Development Workflow

### 7.1 Component Development Order

1. **Phase 1: Foundation**

    - Set up Shadcn/Vue components
    - Configure TailwindCSS with Figma tokens
    - Create base `DashboardModule` wrapper

2. **Phase 2: Core Widgets**

    - `MetricCard` component
    - `ProgressWidget` component
    - `TeamMemberWidget` component
    - `ChartWidget` component

3. **Phase 3: Dashboard Modules**

    - `DailySummaryModule`
    - `ActivityDistributionModule`
    - `SalesModule`
    - `ExpensesModule`
    - `ContentModule`
    - `QuarterlyGoalsModule`

4. **Phase 4: Integration**

    - `DashboardGrid` layout
    - Laravel controller integration
    - Data flow implementation

5. **Phase 5: Polish**
    - Responsive design refinements
    - Performance optimizations
    - Testing and bug fixes

### 7.2 Testing Strategy

```javascript
// tests/Components/Dashboard/MetricCard.test.js
import { mount } from "@vue/test-utils";
import MetricCard from "@/Components/Dashboard/Widgets/MetricCard.vue";

describe("MetricCard", () => {
    it("displays metric value and label correctly", () => {
        const wrapper = mount(MetricCard, {
            props: {
                label: "Total Sales",
                value: 1250,
                variant: "success",
            },
        });

        expect(wrapper.find(".metric-label").text()).toBe("Total Sales");
        expect(wrapper.find(".metric-value").text()).toBe("1,250");
        expect(wrapper.classes()).toContain("text-green-600");
    });

    it("formats currency values correctly", () => {
        const wrapper = mount(MetricCard, {
            props: {
                label: "Revenue",
                value: "$12,450",
            },
        });

        expect(wrapper.find(".metric-value").text()).toBe("$12,450");
    });
});
```

### 7.3 Code Organization Best Practices

#### File Naming Conventions

```
PascalCase for components:     MetricCard.vue
camelCase for composables:     useDashboardData.js
kebab-case for utilities:      format-currency.js
SCREAMING_SNAKE for constants: DASHBOARD_MODULES.js
```

#### Import Organization

```vue
<script setup>
// Vue imports first
import { ref, computed, onMounted } from "vue";

// Third-party imports
import { Chart } from "chart.js";

// Internal components (alphabetical)
import Card from "@/Components/UI/Card.vue";
import MetricCard from "@/Components/Dashboard/Widgets/MetricCard.vue";

// Utilities and composables
import { cn } from "@/Utils/utils";
import { useDashboardData } from "@/Composables/useDashboardData";

// Constants and types
import { DASHBOARD_MODULES } from "@/Constants/dashboard";
</script>
```

#### Component Structure Template

```vue
<template>
    <!-- Template content -->
</template>

<script setup>
// Imports

// Props definition
const props = defineProps({
    // prop definitions
});

// Emits definition
const emit = defineEmits(["event-name"]);

// Reactive state
const state = ref();

// Computed properties
const computed = computed(() => {});

// Methods
const method = () => {};

// Lifecycle hooks
onMounted(() => {});
</script>

<style scoped>
/* Component-specific styles */
</style>
```

## Implementation Checklist

### Setup Phase

-   [ ] Install and configure Shadcn/Vue
-   [ ] Update TailwindCSS config with Figma design tokens
-   [ ] Set up component directory structure
-   [ ] Create base utility functions

### Component Development

-   [ ] Create `DashboardModule` base component
-   [ ] Build `MetricCard` widget
-   [ ] Build `ProgressWidget` component
-   [ ] Build `TeamMemberWidget` component
-   [ ] Build `ChartWidget` wrapper

### Dashboard Modules

-   [ ] Implement `DailySummaryModule`
-   [ ] Implement `ActivityDistributionModule`
-   [ ] Implement `SalesModule`
-   [ ] Implement `ExpensesModule`
-   [ ] Implement `ContentModule`
-   [ ] Implement `QuarterlyGoalsModule`

### Integration

-   [ ] Create `DashboardGrid` layout component
-   [ ] Update Laravel `DashboardController`
-   [ ] Implement data flow from backend to components
-   [ ] Add error handling and loading states

### Optimization & Testing

-   [ ] Implement lazy loading for modules
-   [ ] Add performance monitoring
-   [ ] Write component tests
-   [ ] Test responsive design
-   [ ] Optimize chart rendering

### Final Polish

-   [ ] Cross-browser testing
-   [ ] Accessibility improvements
-   [ ] Performance audit
-   [ ] Documentation updates

---

This architecture guide provides a comprehensive roadmap for implementing the Hidden Treasures dashboard with a focus on maintainability, performance, and adherence to the Figma design specifications. The modular approach ensures scalability and makes it easy for developers to work on individual components without affecting the entire system.
