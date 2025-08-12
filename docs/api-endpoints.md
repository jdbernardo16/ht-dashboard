# API Endpoints Documentation - Hidden Treasures Dashboard

## Authentication Endpoints

### POST /login

Authenticate user and establish session.

**Request:**

```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response:**

-   Success: Redirect to role-specific dashboard
-   Error: Validation errors with 422 status

### POST /logout

Terminate current user session.

**Response:**

-   Success: Redirect to login page
-   Always returns 302 redirect

### POST /register

Register new user account.

**Request:**

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

**Response:**

-   Success: Redirect to dashboard
-   Error: Validation errors with 422 status

## Task Endpoints

### GET /tasks

Retrieve all tasks (filtered by user role).

**Response:**

```json
{
    "tasks": [
        {
            "id": 1,
            "title": "Create social media content",
            "description": "Design 5 posts for Instagram",
            "priority": "high",
            "status": "pending",
            "due_date": "2024-01-15",
            "assigned_to": {
                "id": 1,
                "name": "Jane VA"
            },
            "created_at": "2024-01-10T10:00:00Z"
        }
    ]
}
```

### POST /tasks

Create new task.

**Request:**

```json
{
    "title": "Task title",
    "description": "Detailed task description",
    "priority": "high|medium|low",
    "status": "pending|in_progress|completed|cancelled",
    "due_date": "2024-01-15",
    "assigned_to_id": 1
}
```

**Response:**

-   Success: 201 with created task
-   Error: 422 with validation errors

### GET /tasks/{id}

Retrieve specific task details.

**Response:**

```json
{
  "task": {
    "id": 1,
    "title": "Task title",
    "description": "Description",
    "priority": "high",
    "status": "pending",
    "due_date": "2024-01-15",
    "assigned_to": {...},
    "created_by": {...},
    "created_at": "2024-01-10T10:00:00Z",
    "updated_at": "2024-01-10T10:00:00Z"
  }
}
```

### PUT /tasks/{id}

Update existing task.

**Request:** Same as POST /tasks

**Response:**

-   Success: 200 with updated task
-   Error: 422 with validation errors or 404 if not found

### DELETE /tasks/{id}

Delete task.

**Response:**

-   Success: 204 No Content
-   Error: 404 if task not found

## Sales Endpoints

### GET /sales

Retrieve all sales records.

**Response:**

```json
{
  "sales": [
    {
      "id": 1,
      "amount": 150.00,
      "description": "Handmade necklace sale",
      "date": "2024-01-10",
      "platform": "facebook",
      "notes": "Customer loved the design",
      "created_by": {...}
    }
  ]
}
```

### POST /sales

Create new sale record.

**Request:**

```json
{
    "amount": 150.0,
    "description": "Product description",
    "date": "2024-01-10",
    "platform": "facebook|instagram|website|other",
    "notes": "Additional notes"
}
```

### GET /sales/{id}

Retrieve specific sale details.

### PUT /sales/{id}

Update existing sale record.

### DELETE /sales/{id}

Delete sale record.

## Content Endpoints

### GET /content

Retrieve all content posts.

**Response:**

```json
{
    "content": [
        {
            "id": 1,
            "title": "New Product Launch",
            "type": "post",
            "platform": "instagram",
            "scheduled_date": "2024-01-15",
            "status": "planned|in_progress|published|cancelled",
            "notes": "Product launch announcement"
        }
    ]
}
```

### POST /content

Create new content post.

**Request:**

```json
{
    "title": "Content title",
    "type": "post|story|reel|live|other",
    "platform": "facebook|instagram|tiktok|website|other",
    "scheduled_date": "2024-01-15",
    "status": "planned",
    "notes": "Content description and requirements"
}
```

### GET /content/{id}

Retrieve specific content details.

### PUT /content/{id}

Update existing content post.

### DELETE /content/{id}

Delete content post.

## Expense Endpoints

### GET /expenses

Retrieve all expense records.

**Response:**

```json
{
    "expenses": [
        {
            "id": 1,
            "amount": 25.5,
            "category": "supplies",
            "description": "Craft materials",
            "date": "2024-01-10",
            "notes": "Beads and wire for new collection"
        }
    ]
}
```

### POST /expenses

Create new expense record.

**Request:**

```json
{
    "amount": 25.5,
    "category": "supplies|marketing|tools|shipping|other",
    "description": "Expense description",
    "date": "2024-01-10",
    "notes": "Additional details"
}
```

### GET /expenses/{id}

Retrieve specific expense details.

### PUT /expenses/{id}

Update existing expense record.

### DELETE /expenses/{id}

Delete expense record.

## Goal Endpoints

### GET /goals

Retrieve all goals.

**Response:**

```json
{
    "goals": [
        {
            "id": 1,
            "title": "Monthly Sales Target",
            "description": "Achieve $5000 in sales this month",
            "target_amount": 5000.0,
            "current_amount": 3250.0,
            "deadline": "2024-01-31",
            "status": "active|completed|cancelled"
        }
    ]
}
```

### POST /goals

Create new goal.

**Request:**

```json
{
    "title": "Goal title",
    "description": "Detailed goal description",
    "target_amount": 5000.0,
    "current_amount": 0.0,
    "deadline": "2024-01-31",
    "status": "active"
}
```

### GET /goals/{id}

Retrieve specific goal details.

### PUT /goals/{id}

Update existing goal.

### DELETE /goals/{id}

Delete goal.

## Dashboard Endpoints

### GET /admin/dashboard

Admin dashboard data.

**Response:**

```json
{
  "total_users": 10,
  "total_tasks": 45,
  "total_sales": 12500.00,
  "total_goals": 8,
  "recent_activity": [...],
  "analytics": {...}
}
```

### GET /manager/dashboard

Manager dashboard data.

**Response:**

```json
{
  "team_tasks": 25,
  "monthly_sales": 8500.00,
  "pending_tasks": 12,
  "content_scheduled": 15,
  "team_performance": {...}
}
```

### GET /va/dashboard

VA dashboard data.

**Response:**

```json
{
  "my_tasks": 8,
  "completed_today": 3,
  "pending_tasks": 5,
  "overdue_tasks": 1,
  "recent_tasks": [...]
}
```

## User Profile Endpoints

### GET /profile

Get current user profile.

**Response:**

```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "manager",
        "created_at": "2024-01-01T00:00:00Z"
    }
}
```

### PATCH /profile

Update user profile.

**Request:**

```json
{
    "name": "Updated Name",
    "email": "updated@example.com"
}
```

### PUT /password

Update user password.

**Request:**

```json
{
    "current_password": "current_password",
    "password": "new_password",
    "password_confirmation": "new_password"
}
```

## Error Responses

### Validation Errors (422)

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Error message 1", "Error message 2"]
    }
}
```

### Not Found (404)

```json
{
    "message": "Resource not found"
}
```

### Unauthorized (403)

```json
{
    "message": "This action is unauthorized"
}
```

### Authentication Required (401)

```json
{
    "message": "Unauthenticated"
}
```

## Rate Limiting

-   Standard endpoints: 60 requests per minute
-   Authentication endpoints: 5 requests per minute

## Pagination

List endpoints support pagination via query parameters:

-   `page`: Page number (default: 1)
-   `per_page`: Items per page (default: 15, max: 100)

Example: `GET /tasks?page=2&per_page=20`

## Filtering and Sorting

List endpoints support filtering via query parameters:

-   `status`: Filter by status
-   `priority`: Filter by priority (tasks)
-   `platform`: Filter by platform (sales/content)
-   `category`: Filter by category (expenses)

Example: `GET /tasks?status=pending&priority=high`

## CSRF Protection

All POST, PUT, PATCH, and DELETE requests require:

-   Valid CSRF token (obtained from `/sanctum/csrf-cookie`)
-   `X-Requested-With: XMLHttpRequest` header

## CORS Configuration

-   Allowed origins: Configured in .env
-   Allowed methods: GET, POST, PUT, PATCH, DELETE
-   Allowed headers: Content-Type, Authorization, X-Requested-With
