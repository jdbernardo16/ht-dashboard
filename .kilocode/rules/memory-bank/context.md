# Hidden Treasures Dashboard - Context

## Current State

The Hidden Treasures Dashboard is a fully functional Laravel 12.x + Vue.js 3 application designed for sports card consignment business management. The application is currently in production with a comprehensive set of features for task management, sales tracking, content management, expense tracking, and goal setting.

## Recent Development

The application has been recently updated with:
- Enhanced dashboard with time period filtering (daily, weekly, monthly, custom)
- Comprehensive API endpoints for all modules
- File upload system with media management
- Role-based access control with Admin, Manager, and Virtual Assistant roles
- Notification system with real-time updates
- Responsive design optimized for desktop, tablet, and mobile

## Current Architecture

### Backend (Laravel 12.x)
- **Authentication**: Laravel Sanctum with role-based middleware
- **Database**: MySQL with comprehensive schema for all business entities
- **Controllers**: Well-structured with separation of concerns
- **Services**: File upload, validation, and notification services
- **Middleware**: Role-based access control (Admin, Manager, VA)

### Frontend (Vue.js 3 + Inertia.js)
- **Components**: Modular component architecture with reusable UI elements
- **Dashboard**: 2x3 grid layout with real-time data updates
- **Charts**: Chart.js integration for data visualization
- **Styling**: Tailwind CSS with custom design system
- **State Management**: Inertia.js for seamless server-side state

### Key Modules
1. **Task Management**: Full CRUD with assignment, priorities, and media attachments
2. **Sales Tracking**: Client management, sales records, and performance metrics
3. **Content Management**: Social media content planning and scheduling
4. **Expense Management**: Category-based expense tracking with reporting
5. **Goal Setting**: Quarterly goals with progress tracking
6. **Notifications**: Real-time notification system

## Database Schema

The application uses a comprehensive MySQL schema with the following core tables:
- `users` - User management with role-based access
- `tasks` - Task management with assignments and media
- `sales` - Sales records with client relationships
- `clients` - Client management
- `expenses` - Expense tracking with categories
- `goals` - Goal setting and progress tracking
- `content_posts` - Content management with media
- `notifications` - Notification system
- `categories` - Category management for content and expenses

## Current Development Environment

- **PHP**: 8.2+
- **Laravel**: 12.x
- **Node.js**: 18.x+
- **Database**: MySQL (production) / SQLite (development)
- **Build Tools**: Vite for frontend asset compilation
- **Testing**: PHPUnit with feature and unit tests

## Deployment

The application is configured for deployment on Hostinger with:
- Optimized asset compilation
- Environment-specific configurations
- Database migrations
- Cron job setup for scheduled tasks

## Current Focus Areas

1. **AI Integration**: Preparing for AI-powered features including:
   - Price suggestions using eBay Finding API
   - Description generation with SEO optimization
   - Risk detection for negative margins
   - Auto-tagging system using OCR

2. **Performance Optimization**: 
   - Database query optimization
   - Frontend asset optimization
   - Caching strategies

3. **Enhanced Analytics**:
   - Advanced KPI tracking
   - Business intelligence features
   - Automated reporting

## Technical Debt

- Some controllers could benefit from additional service layer abstraction
- Frontend components could use additional documentation
- Test coverage could be expanded for edge cases
- API documentation could be enhanced

## Next Steps

1. Implement AI-powered features
2. Enhance mobile responsiveness
3. Add advanced reporting capabilities
4. Improve test coverage
5. Optimize database queries for large datasets