# Hidden Treasures Dashboard - Architecture

## System Architecture

The Hidden Treasures Dashboard follows a modern monolithic architecture with clear separation of concerns between the backend API and frontend SPA. The system is built on Laravel 12.x with Vue.js 3, using Inertia.js for seamless server-side rendering.

### High-Level Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Frontend (Vue.js 3)                      │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │   Dashboard     │  │   Modules       │  │   Shared UI  │ │
│  │   Components    │  │   (Tasks,       │  │   Components │ │
│  │                 │  │   Sales, etc.)  │  │              │ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                         Inertia.js
                              │
┌─────────────────────────────────────────────────────────────┐
│                   Backend (Laravel 12.x)                    │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │   Controllers   │  │   Services      │  │   Middleware │ │
│  │                 │  │   (File Upload, │  │   (Auth,     │ │
│  │   (Resource     │  │   Validation,   │  │    Roles)    │ │
│  │   Controllers)  │  │   Notifications)│  │              │ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
│                              │                                │
│  ┌─────────────────┐  ┌─────────────────┐  ┌──────────────┐ │
│  │     Models      │  │   Policies      │  │   Requests   │ │
│  │   (Eloquent)    │  │   (Authorization)│  │  (Validation)│ │
│  └─────────────────┘  └─────────────────┘  └──────────────┘ │
└─────────────────────────────────────────────────────────────┘
                              │
                    ┌─────────────────┐
                    │   Database      │
                    │   (MySQL)       │
                    └─────────────────┘
```

## Backend Architecture

### MVC Pattern with Service Layer

The application follows the Model-View-Controller pattern with an additional service layer for business logic:

#### Controllers
- **Resource Controllers**: Handle standard CRUD operations
- **API Controllers**: Provide RESTful API endpoints
- **DashboardController**: Aggregates data from multiple modules

#### Models
- **Eloquent Models**: Represent database entities with relationships
- **Factories**: Generate test data for development and testing
- **Observers**: Handle model events (if needed in future)

#### Services
- **FileUploadService**: Handles file uploads and validation
- **NotificationService**: Manages system notifications
- **ImageService**: Processes images with Intervention Image

#### Middleware
- **AdminMiddleware**: Restricts access to admin users
- **ManagerMiddleware**: Restricts access to manager and admin users
- **VAMiddleware**: Restricts access to authenticated users
- **HandleInertiaRequests**: Shares data with frontend

### Authentication & Authorization

- **Laravel Sanctum**: API token authentication
- **Role-Based Access Control**: Three roles (Admin, Manager, VA)
- **Policies**: Granular authorization for resources

### Database Design

The database uses a relational design with proper foreign key relationships:

#### Core Tables
- `users`: User management with role-based access
- `tasks`: Task management with assignments and media
- `sales`: Sales records with client relationships
- `clients`: Client management
- `expenses`: Expense tracking with categories
- `goals`: Goal setting and progress tracking
- `content_posts`: Content management with media
- `notifications`: Notification system
- `categories`: Category management for content and expenses

#### Relationships
- One-to-Many: User → Tasks, Sales, Expenses, Goals
- Many-to-Many: Content Posts ↔ Categories
- Polymorphic: Task Media, Content Post Media

## Frontend Architecture

### Component-Based Architecture

The frontend follows a component-based architecture with Vue.js 3:

#### Layout Components
- **AuthenticatedLayout**: Main layout for authenticated users
- **GuestLayout**: Layout for unauthenticated users

#### Page Components
- **Dashboard**: Main dashboard with module grid
- **Module Pages**: Individual pages for tasks, sales, content, etc.
- **Role-Specific Dashboards**: Admin, Manager, VA dashboards

#### Reusable Components
- **UI Components**: Buttons, inputs, modals, etc.
- **Chart Components**: Data visualization components
- **Form Components**: Reusable form elements

#### Dashboard Components
- **DashboardGrid**: 2x3 grid layout for modules
- **Module Components**: Individual dashboard modules
- **TimePeriodDropdown**: Period selection component

### State Management

- **Inertia.js**: Server-side state management
- **Props**: Data passed from Laravel controllers
- **Events**: Client-side event handling
- **Local State**: Component-level state with Vue 3 Composition API

### Styling

- **Tailwind CSS**: Utility-first CSS framework
- **Custom Design System**: Consistent color palette and spacing
- **Responsive Design**: Mobile-first approach

## API Design

### RESTful API

The application provides RESTful API endpoints for all modules:

#### Standard Endpoints
- `GET /resource` - List all resources
- `GET /resource/{id}` - Show specific resource
- `POST /resource` - Create new resource
- `PUT /resource/{id}` - Update resource
- `DELETE /resource/{id}` - Delete resource

#### Dashboard API
- `GET /api/dashboard` - Get dashboard data
- `GET /api/dashboard/summary` - Get summary data
- `GET /api/dashboard/sales` - Get sales metrics
- `GET /api/dashboard/expenses` - Get expense data

#### File Upload API
- `POST /api/upload` - Upload single file
- `POST /api/upload/multiple` - Upload multiple files
- `DELETE /api/upload/{file}` - Delete file

## Security

### Authentication
- **Laravel Sanctum**: API token authentication
- **Session-based**: Web authentication
- **Password Hashing**: Bcrypt hashing

### Authorization
- **Role-Based Middleware**: Route-level access control
- **Policies**: Resource-level authorization
- **Input Validation**: Request validation

### Data Protection
- **CSRF Protection**: Cross-site request forgery protection
- **XSS Prevention**: Output escaping
- **SQL Injection Prevention**: Eloquent ORM

## Performance Optimization

### Backend
- **Database Indexing**: Optimized queries with proper indexes
- **Eager Loading**: Prevent N+1 query problems
- **Caching**: Query caching for frequently accessed data

### Frontend
- **Code Splitting**: Lazy loading of components
- **Asset Optimization**: Vite build optimization
- **Image Optimization**: Responsive images

## Deployment Architecture

### Production Environment
- **Hostinger**: Shared hosting environment
- **MySQL**: Production database
- **SSL**: HTTPS encryption
- **Cron Jobs**: Scheduled tasks

### Development Environment
- **Local Development**: Laravel Sail or Valet
- **SQLite**: Development database
- **Hot Reloading**: Vite development server

## Design Patterns

### Repository Pattern (Potential Future)
- Consider implementing repository pattern for data access
- Abstract database operations from controllers

### Factory Pattern
- Model factories for test data generation
- Service factories for complex object creation

### Observer Pattern
- Model events for logging and notifications
- System event listeners

### Strategy Pattern
- Payment processing strategies
- File upload strategies

## Future Architecture Considerations

### Microservices
- Potential split into microservices for scalability
- Separate services for AI features

### Event-Driven Architecture
- Event sourcing for audit trails
- Message queues for background processing

### Caching Strategy
- Redis for session storage
- Application-level caching

### CDN Integration
- Static asset delivery
- Image optimization and delivery