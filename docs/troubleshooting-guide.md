# Troubleshooting Guide - Hidden Treasures Dashboard

## Quick Diagnostic Steps

Before diving into specific issues, try these general troubleshooting steps:

1. **Refresh the page** (F5 or Ctrl+R)
2. **Clear browser cache** (Ctrl+Shift+Delete)
3. **Check internet connection**
4. **Try a different browser**
5. **Check if the server is running**

## Common Issues and Solutions

### 1. Login Problems

#### Issue: "Invalid credentials" error

**Symptoms:**

-   Cannot log in with correct email/password
-   Password reset not working

**Solutions:**

1. **Check email spelling** - Ensure email is typed correctly
2. **Check password** - Remember passwords are case-sensitive
3. **Reset password** - Use "Forgot Password" feature
4. **Check user exists** - Verify account hasn't been deleted
5. **Clear browser data** - Clear cache and cookies

#### Issue: "Page not found" after login

**Symptoms:**

-   Login successful but redirected to 404 page
-   Dashboard not loading

**Solutions:**

1. **Check URL** - Ensure you're accessing correct domain
2. **Verify role** - Check if user has appropriate role assigned
3. **Check routes** - Ensure all routes are properly configured
4. **Server restart** - Restart the development server

### 2. Database Issues

#### Issue: "Database connection error"

**Symptoms:**

-   Application shows database error
-   Cannot retrieve or save data

**Solutions:**

```bash
# Check if database file exists
ls -la database/database.sqlite

# Create database if missing
touch database/database.sqlite

# Run migrations
php artisan migrate:fresh

# Check database permissions
chmod 664 database/database.sqlite
```

#### Issue: "Table not found" error

**Symptoms:**

-   Specific table errors
-   Missing data after updates

**Solutions:**

```bash
# Re-run migrations
php artisan migrate:fresh

# Re-seed database
php artisan db:seed

# Check migration status
php artisan migrate:status
```

### 3. Server Issues

#### Issue: "Connection refused" or "Site can't be reached"

**Symptoms:**

-   Cannot access localhost:8000
-   Server not responding

**Solutions:**

```bash
# Start development server
php artisan serve

# Start on different port
php artisan serve --port=8001

# Check if port is in use
lsof -i :8000

# Kill process using port
kill -9 [PID]
```

#### Issue: "500 Internal Server Error"

**Symptoms:**

-   Generic server error
-   No specific error message

**Solutions:**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Check file permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

### 4. Frontend Issues

#### Issue: "Build failed" or "Vite error"

**Symptoms:**

-   JavaScript not loading
-   CSS styles missing
-   Console errors about modules

**Solutions:**

```bash
# Rebuild assets
npm run build

# Development build
npm run dev

# Clear node modules and reinstall
rm -rf node_modules
npm install
npm run build

# Check for build errors
npm run build --verbose
```

#### Issue: "CSRF token mismatch"

**Symptoms:**

-   Form submissions failing
-   Security errors in console

**Solutions:**

1. **Refresh CSRF token** - Visit `/sanctum/csrf-cookie`
2. **Clear browser cache** - Hard refresh (Ctrl+F5)
3. **Check session** - Ensure session is properly started
4. **Verify headers** - Ensure X-CSRF-TOKEN is sent

### 5. Permission Issues

#### Issue: "403 Forbidden" or "Unauthorized"

**Symptoms:**

-   Cannot access certain pages
-   Role-based access errors

**Solutions:**

1. **Check user role** - Verify correct role assignment
2. **Check middleware** - Ensure middleware is properly configured
3. **Clear session** - Log out and log back in
4. **Check route permissions** - Verify route middleware

#### Issue: File upload errors

**Symptoms:**

-   Cannot upload images or files
-   "Permission denied" errors

**Solutions:**

```bash
# Fix storage permissions
chmod -R 755 storage/
chmod -R 755 public/storage/

# Create storage link
php artisan storage:link

# Check disk permissions
ls -la storage/app/public/
```

### 6. Performance Issues

#### Issue: Slow page loading

**Symptoms:**

-   Pages take long time to load
-   Database queries are slow

**Solutions:**

```bash
# Optimize application
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Check database performance
php artisan db:monitor

# Enable query logging
DB::enableQueryLog();
```

#### Issue: Memory limit exceeded

**Symptoms:**

-   "Allowed memory size exhausted"
-   Server crashes during operations

**Solutions:**

1. **Increase PHP memory limit** - Edit php.ini
2. **Optimize queries** - Check for N+1 problems
3. **Use pagination** - Limit result sets
4. **Monitor usage** - Check memory consumption

### 7. Database Migration Issues

#### Issue: Migration fails

**Symptoms:**

-   Cannot run migrations
-   Schema errors

**Solutions:**

```bash
# Reset and re-run migrations
php artisan migrate:fresh

# Rollback specific migration
php artisan migrate:rollback --step=1

# Check migration status
php artisan migrate:status

# Force run in production
php artisan migrate --force
```

#### Issue: "Column not found" error

**Symptoms:**

-   Missing database columns
-   Schema out of sync

**Solutions:**

```bash
# Re-run all migrations
php artisan migrate:fresh

# Check for pending migrations
php artisan migrate

# Verify schema
php artisan schema:dump
```

### 8. Environment Configuration Issues

#### Issue: Environment variables not loading

**Symptoms:**

-   Wrong database connection
-   Debug mode not working

**Solutions:**

1. **Check .env file** - Ensure file exists and is readable
2. **Clear config cache** - `php artisan config:clear`
3. **Reload environment** - Restart server
4. **Check file permissions** - Ensure .env is readable

#### Issue: Wrong APP_URL causing issues

**Symptoms:**

-   Assets not loading
-   Wrong redirects

**Solutions:**

1. **Update .env** - Set correct APP_URL
2. **Clear config** - `php artisan config:clear`
3. **Rebuild assets** - `npm run build`
4. **Check browser console** - Look for asset errors

## Browser-Specific Issues

### Chrome

-   **Issue**: "Aw, Snap!" error
-   **Solution**: Clear cache, disable extensions, check memory

### Firefox

-   **Issue**: "Secure Connection Failed"
-   **Solution**: Check SSL certificates, clear cache

### Safari

-   **Issue**: "Can't open page"
-   **Solution**: Clear website data, check settings

### Edge

-   **Issue**: "Hmm, we can't reach this page"
-   **Solution**: Check network settings, clear cache

## Mobile/Responsive Issues

### Issue: Layout broken on mobile

**Solutions:**

1. **Check viewport meta tag** - Ensure responsive meta is present
2. **Test on actual devices** - Use browser dev tools
3. **Check CSS media queries** - Ensure breakpoints are correct
4. **Verify touch targets** - Ensure buttons are large enough

### Issue: Touch events not working

**Solutions:**

1. **Check JavaScript** - Ensure touch events are handled
2. **Test on real devices** - Emulators may not be accurate
3. **Check CSS** - Ensure no pointer-events: none
4. **Verify z-index** - Ensure elements are clickable

## Development Environment Issues

### Issue: npm run dev not working

**Solutions:**

```bash
# Check Node.js version
node --version

# Clear npm cache
npm cache clean --force

# Delete and reinstall
rm -rf node_modules package-lock.json
npm install

# Check for port conflicts
lsof -i :5173
```

### Issue: Hot reload not working

**Solutions:**

1. **Check Vite config** - Ensure HMR is enabled
2. **Clear browser cache** - Hard refresh
3. **Check file permissions** - Ensure files are readable
4. **Restart dev server** - Stop and restart npm run dev

## Production Deployment Issues

### Issue: Application works locally but not on server

**Solutions:**

1. **Check PHP version** - Ensure server meets requirements
2. **Verify extensions** - Check required PHP extensions
3. **Check file permissions** - Ensure proper permissions
4. **Review error logs** - Check server and Laravel logs

### Issue: Assets not loading in production

**Solutions:**

```bash
# Build for production
npm run build

# Check public/build directory
ls -la public/build/

# Verify asset paths
grep -r "build/assets" public/

# Check .env APP_URL
cat .env | grep APP_URL
```

## Debugging Tools and Commands

### Laravel Debug Commands

```bash
# Check application status
php artisan about

# Test database connection
php artisan tinker
>>> DB::connection()->getPdo()

# Check routes
php artisan route:list

# Check configuration
php artisan config:show

# Test email
php artisan tinker
>>> Mail::raw('Test email', function($msg) { $msg->to('test@example.com'); });
```

### System Health Check

```bash
#!/bin/bash
echo "=== System Health Check ==="
echo "PHP Version: $(php --version | head -1)"
echo "Composer Version: $(composer --version)"
echo "Node Version: $(node --version)"
echo "NPM Version: $(npm --version)"
echo "Laravel Version: $(php artisan --version)"
echo "Database Connection: $(php artisan tinker --execute='try { DB::connection()->getPdo(); echo "OK"; } catch(Exception $e) { echo "FAILED"; }')"
echo "Storage Permissions: $(test -w storage && echo "OK" || echo "FAILED")"
echo "Cache Permissions: $(test -w bootstrap/cache && echo "OK" || echo "FAILED")"
```

## Getting Help

### Before Asking for Help

1. **Check this guide** - Your issue might be listed here
2. **Check logs** - Look at `storage/logs/laravel.log`
3. **Search online** - Common issues usually have solutions
4. **Reproduce consistently** - Ensure it's not a one-time error

### Information to Provide

When asking for help, include:

1. **Error message** - Exact text of any error messages
2. **Steps to reproduce** - What you did to cause the issue
3. **Environment details** - OS, PHP version, Laravel version
4. **Recent changes** - What was changed before the issue
5. **Log excerpts** - Relevant parts of error logs

### Support Channels

-   **Internal**: Contact system administrator
-   **Documentation**: Check Laravel documentation
-   **Community**: Laravel Discord, Stack Overflow
-   **Professional**: Laravel support services

## Emergency Procedures

### Complete Reset (Last Resort)

```bash
# WARNING: This will delete all data
php artisan migrate:fresh
php artisan db:seed
npm run build
```

### Quick Recovery

1. **Backup database** - Always backup before major changes
2. **Document current state** - Note what's working
3. **Incremental fixes** - Fix one issue at a time
4. **Test thoroughly** - Verify each fix works
