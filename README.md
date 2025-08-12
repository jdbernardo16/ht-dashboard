# Hidden Treasures Dashboard

A comprehensive business management dashboard for Hidden Treasures, built with Laravel and Vue.js. This system provides role-based access control for Admins, Managers, and Virtual Assistants to manage tasks, sales, content, expenses, and goals.

## Features

-   **Role-Based Access Control**: Three distinct user roles (Admin, Manager, VA) with tailored permissions
-   **Task Management**: Create, assign, and track tasks with priorities and deadlines
-   **Sales Tracking**: Monitor sales performance and generate reports
-   **Content Management**: Plan and track content creation and publishing
-   **Expense Management**: Track business expenses and generate financial reports
-   **Goal Setting**: Set and monitor business goals with progress tracking
-   **Daily Summaries**: Generate daily business summaries and reports
-   **Responsive Design**: Works seamlessly on desktop and mobile devices

## Technology Stack

-   **Backend**: Laravel 11.x with PHP 8.4
-   **Frontend**: Vue.js 3 with Inertia.js
-   **Database**: SQLite (development) / MySQL (production)
-   **Authentication**: Laravel Sanctum
-   **Styling**: Tailwind CSS
-   **Charts**: Chart.js for data visualization

## Prerequisites

-   PHP 8.4 or higher
-   Composer
-   Node.js 18.x or higher
-   NPM or Yarn

## Installation

### 1. Clone the Repository

```bash
git clone [repository-url]
cd ht-dashboard
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate:fresh

# Seed the database with test data
php artisan db:seed
```

### 5. Build Assets

```bash
# Build for development
npm run dev

# Build for production
npm run build
```

### 6. Start Development Server

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`

## Default Login Credentials

| Role    | Email                       | Password |
| ------- | --------------------------- | -------- |
| Admin   | admin@hiddentreasures.com   | password |
| Manager | manager@hiddentreasures.com | password |
| VA      | va@hiddentreasures.com      | password |

## User Roles & Permissions

### Admin

-   Full system access
-   Manage all users, tasks, sales, content, expenses, and goals
-   Access to admin dashboard with system-wide analytics
-   Can create and manage other users

### Manager

-   Manage tasks, sales, content, expenses, and goals
-   Access to manager dashboard with team analytics
-   Cannot manage users or system settings

### Virtual Assistant (VA)

-   Manage assigned tasks
-   View sales and content data
-   Access to VA dashboard with personal task overview
-   Limited to assigned responsibilities

## API Endpoints

### Authentication

-   `POST /login` - User login
-   `POST /logout` - User logout
-   `POST /register` - User registration
-   `GET /password/reset` - Password reset request
-   `POST /password/reset` - Password reset

### Tasks

-   `GET /tasks` - List all tasks
-   `GET /tasks/create` - Create task form
-   `POST /tasks` - Store new task
-   `GET /tasks/{id}` - View task details
-   `GET /tasks/{id}/edit` - Edit task form
-   `PUT /tasks/{id}` - Update task
-   `DELETE /tasks/{id}` - Delete task

### Sales

-   `GET /sales` - List all sales
-   `GET /sales/create` - Create sale form
-   `POST /sales` - Store new sale
-   `GET /sales/{id}` - View sale details
-   `GET /sales/{id}/edit` - Edit sale form
-   `PUT /sales/{id}` - Update sale
-   `DELETE /sales/{id}` - Delete sale

### Content

-   `GET /content` - List all content posts
-   `GET /content/create` - Create content form
-   `POST /content` - Store new content
-   `GET /content/{id}` - View content details
-   `GET /content/{id}/edit` - Edit content form
-   `PUT /content/{id}` - Update content
-   `DELETE /content/{id}` - Delete content

### Expenses

-   `GET /expenses` - List all expenses
-   `GET /expenses/create` - Create expense form
-   `POST /expenses` - Store new expense
-   `GET /expenses/{id}` - View expense details
-   `GET /expenses/{id}/edit` - Edit expense form
-   `PUT /expenses/{id}` - Update expense
-   `DELETE /expenses/{id}` - Delete expense

### Goals

-   `GET /goals` - List all goals
-   `GET /goals/create` - Create goal form
-   `POST /goals` - Store new goal
-   `GET /goals/{id}` - View goal details
-   `GET /goals/{id}/edit` - Edit goal form
-   `PUT /goals/{id}` - Update goal
-   `DELETE /goals/{id}` - Delete goal

## Testing

### Run Tests

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test tests/Feature/Auth
```

### Manual Testing Checklist

#### Authentication

-   [ ] Login with valid credentials
-   [ ] Login with invalid credentials
-   [ ] Password reset functionality
-   [ ] Logout functionality

#### Role-Based Access

-   [ ] Admin can access admin dashboard
-   [ ] Manager can access manager dashboard
-   [ ] VA can access VA dashboard
-   [ ] Users cannot access unauthorized dashboards

#### CRUD Operations

-   [ ] Create, read, update, delete tasks
-   [ ] Create, read, update, delete sales
-   [ ] Create, read, update, delete content
-   [ ] Create, read, update, delete expenses
-   [ ] Create, read, update, delete goals

#### Responsive Design

-   [ ] Test on mobile devices
-   [ ] Test on tablet devices
-   [ ] Test on desktop devices

## Deployment

### Production Setup

1. Set up production database (MySQL/PostgreSQL)
2. Update `.env` file with production values
3. Run migrations: `php artisan migrate`
4. Build assets: `npm run build`
5. Set up web server (Apache/Nginx)
6. Configure SSL certificate
7. Set up scheduled tasks (cron jobs)

### Environment Variables for Production

```env
APP_NAME="Hidden Treasures Dashboard"
APP_ENV=production
APP_KEY=your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your-database
DB_USERNAME=your-username
DB_PASSWORD=your-password
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Error

```bash
# Check if database file exists
ls -la database/database.sqlite

# Create database file if missing
touch database/database.sqlite
```

#### 2. Permission Issues

```bash
# Fix storage permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

#### 3. Node Modules Issues

```bash
# Clear and reinstall
rm -rf node_modules
npm install
npm run build
```

#### 4. Cache Issues

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Support

For technical support or questions:

-   Create an issue in the repository
-   Contact the development team
-   Check the troubleshooting guide

## License

This project is proprietary software for Hidden Treasures. All rights reserved.
