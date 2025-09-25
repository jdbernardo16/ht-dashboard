# Technology Stack

## Backend Technologies

### Laravel Framework

-   **Version**: 12.x
-   **PHP Requirement**: 8.2+
-   **Purpose**: Primary backend framework providing MVC architecture, routing, ORM, and authentication
-   **Key Packages**:
    -   Laravel Sanctum: API authentication
    -   Laravel Breeze: Authentication scaffolding
    -   Intervention Image: Image processing
    -   Inertia.js: Server-side rendering for Vue.js frontend

### Database

-   **Development**: SQLite for quick setup and development
-   **Production**: MySQL recommended for production deployment
-   **ORM**: Eloquent ORM with relationships, migrations, and seeding
-   **Key Features**:
    -   Database migrations for version control
    -   Model factories for testing
    -   Seeders for initial data setup

### Authentication & Authorization

-   **Laravel Sanctum**: API token authentication
-   **Role-Based Access Control**: Custom middleware for Admin, Manager, and VA roles
-   **Policies**: Gate policies for resource authorization
-   **Session Management**: Laravel's built-in session handling

## Frontend Technologies

### Vue.js

-   **Version**: 3.4+
-   **Purpose**: Reactive frontend framework for building user interfaces
-   **Key Features**:
    -   Composition API for component logic
    -   Reactive data binding
    -   Component-based architecture

### Inertia.js

-   **Version**: 2.x
-   **Purpose**: Bridge between Laravel backend and Vue.js frontend
-   **Benefits**:
    -   Single-page application feel without building an API
    -   Server-side rendering for SEO
    -   Automatic code splitting
    -   Seamless data passing between backend and frontend

### Chart.js & Vue Chart.js

-   **Version**: Chart.js 4.5+, Vue Chart.js 5.3+
-   **Purpose**: Data visualization for dashboard metrics
-   **Features**:
    -   Responsive charts
    -   Multiple chart types (pie, bar, line, etc.)
    -   Real-time data updates

### Tailwind CSS

-   **Version**: 3.x
-   **Purpose**: Utility-first CSS framework for styling
-   **Benefits**:
    -   Rapid UI development
    -   Consistent design system
    -   Responsive design out of the box
    -   Customizable via configuration

### Vite

-   **Version**: 7.x
-   **Purpose**: Build tool and development server
-   **Features**:
    -   Fast hot module replacement (HMR)
    -   Optimized production builds
    -   ES module support
    -   Plugin system

## Development Tools

### Composer

-   **Purpose**: PHP dependency management
-   **Key Scripts**:
    -   `composer install`: Install dependencies
    -   `composer update`: Update dependencies
    -   `composer dump-autoload`: Regenerate autoload files

### NPM

-   **Purpose**: Node.js package management
-   **Key Scripts**:
    -   `npm run dev`: Development server with HMR
    -   `npm run build`: Production build
    -   `npm run prod`: Production build with optimizations

### Laravel Artisan

-   **Purpose**: Command-line interface for Laravel
-   **Common Commands**:
    -   `php artisan serve`: Start development server
    -   `php artisan migrate`: Run database migrations
    -   `php artisan db:seed`: Seed database with test data
    -   `php artisan test`: Run tests
    -   `php artisan make:controller`: Generate new controller
    -   `php artisan make:model`: Generate new model

### Git

-   **Purpose**: Version control system
-   **Branching Strategy**: Feature branching with main/master as stable branch
-   **Key Files**:
    -   `.gitignore`: Excludes node_modules, .env, and other sensitive files
    -   `.gitattributes`: Configures Git attributes

## Development Environment

### Local Development

-   **Requirements**:
    -   PHP 8.2+
    -   Composer
    -   Node.js 18.x+
    -   NPM or Yarn
    -   SQLite (for development)

### Environment Configuration

-   **Environment File**: `.env` (copied from `.env.example`)
-   **Key Variables**:
    -   `APP_NAME`: Application name
    -   `APP_ENV`: Environment (local/production)
    -   `APP_KEY`: Application encryption key
    -   `DB_CONNECTION`: Database connection type
    -   `DB_DATABASE`: Database name
    -   `APP_URL`: Application URL

### Development Workflow

1. **Setup**:

    ```bash
    composer install
    npm install
    cp .env.example .env
    php artisan key:generate
    php artisan migrate
    php artisan db:seed
    ```

2. **Development**:

    ```bash
    # Start development server
    php artisan serve

    # Start Vite development server (in separate terminal)
    npm run dev
    ```

3. **Testing**:

    ```bash
    # Run all tests
    php artisan test

    # Run specific test
    php artisan test tests/Feature/AuthTest.php
    ```

4. **Building for Production**:
    ```bash
    npm run build
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

## Technical Constraints

### Performance Considerations

-   **Database Indexing**: Proper indexing on frequently queried columns
-   **Query Optimization**: Use of Eloquent relationships and eager loading
-   **Asset Optimization**: Vite handles code splitting and minification
-   **Caching**: Laravel's caching system for frequently accessed data

### Security Considerations

-   **Authentication**: Laravel Sanctum for API authentication
-   **Authorization**: Role-based access control with custom middleware
-   **Input Validation**: Laravel's validation system for form inputs
-   **CSRF Protection**: Built-in CSRF token verification
-   **SQL Injection Prevention**: Eloquent ORM uses parameterized queries

### Scalability Considerations

-   **Database**: Designed to work with both SQLite (development) and MySQL (production)
-   **File Storage**: Laravel's filesystem abstraction for different storage backends
-   **Queue System**: Laravel queues for handling background jobs
-   **Caching**: Multiple cache drivers supported (file, database, Redis)

## Deployment Considerations

### Production Environment

-   **Web Server**: Apache or Nginx
-   **PHP Version**: 8.2+
-   **Database**: MySQL 5.7+ or MariaDB 10.2+
-   **SSL**: HTTPS required for production
-   **File Permissions**: Proper permissions for storage and bootstrap/cache directories

### Environment Variables for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Deployment Process

1. **Code Deployment**: Pull latest code from repository
2. **Dependencies**: Install PHP and Node.js dependencies
3. **Environment**: Configure production environment variables
4. **Database**: Run migrations and seeders if needed
5. **Assets**: Build frontend assets
6. **Cache**: Clear and cache configuration, routes, and views
7. **Permissions**: Set proper file permissions
8. **Services**: Restart web server and queue workers
