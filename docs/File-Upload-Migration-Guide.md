# File Upload System Migration Guide

## Overview

This guide provides step-by-step instructions for migrating from the old complex file upload system to the new simplified architecture. The new system eliminates the over-engineered FileData wrappers and FileDataHelperService in favor of direct Laravel Storage integration with standard File objects.

## Migration Strategy

### Phase 1: Preparation (Completed)

1. **Create new services and exception classes**

    - `FileStorageService.php` - Simplified file storage service
    - `FileUploadException.php` - Base exception class
    - `FileValidationException.php` - Validation-specific exceptions
    - `FileStorageException.php` - Storage-specific exceptions

2. **Update configuration**

    - Enhanced `config/filesystems.php` with better organization
    - Updated `config/validation.php` with file validation rules

3. **Create form request classes**
    - `StoreContentPostRequest.php` - Validation for creating posts
    - `UpdateContentPostRequest.php` - Validation for updating posts

### Phase 2: Backend Updates (Completed)

1. **Update controllers**

    - Simplified `ContentPostController.php` to use direct file handling
    - Removed complex FileData processing chains

2. **Update exception handler**

    - Added custom exception handling in `Handler.php`
    - User-friendly error messages for file upload issues

3. **Create custom validation rules**
    - `ValidFileType.php` - File type validation
    - `ValidImageDimensions.php` - Image dimension validation

### Phase 3: Frontend Updates (Completed)

1. **Create new Vue components**

    - `SimpleFileUploader.vue` - Basic file uploader
    - `ImageUploader.vue` - Specialized image uploader with preview

2. **Update existing pages**
    - `Content/Create.vue` - Use new components
    - `Content/Edit.vue` - Use new components

### Phase 4: Testing (Completed)

1. **Unit tests**

    - `FileStorageServiceTest.php` - Test service layer
    - `ValidFileTypeTest.php` - Test file type validation
    - `ValidImageDimensionsTest.php` - Test image dimension validation

2. **Feature tests**
    - `ContentPostFileUploadTest.php` - Test file upload functionality
    - `FileUploadErrorHandlingTest.php` - Test error scenarios
    - `FileUploadIntegrationTest.php` - Test complete workflows

### Phase 5: Migration and Cleanup (Current)

## Step-by-Step Migration

### 1. Backup Current System

Before making any changes, create a backup of the current system:

```bash
# Backup database
php artisan db:backup

# Backup current file upload code
cp -r app/Services/FileDataHelperService.php app/Services/FileDataHelperService.php.backup
cp -r resources/js/Components/BaseFileUploader.vue resources/js/Components/BaseFileUploader.vue.backup
```

### 2. Update Database Schema (if needed)

The current database schema should work with the new system, but verify:

```sql
-- Check content_posts table
DESCRIBE content_posts;

-- Check content_post_media table
DESCRIBE content_post_media;
```

The new system uses the same schema, so no migrations are needed.

### 3. Update Existing Content Posts

For existing content posts with file paths, run a data migration to ensure paths are compatible:

```php
// Create a migration if needed
php artisan make:migration update_existing_file_paths

// In the migration file, update any old file paths to match new structure
// This is likely not needed as the new system maintains compatibility
```

### 4. Remove Old Components

Once the new system is verified to work:

```bash
# Remove old BaseFileUploader component
rm resources/js/Components/BaseFileUploader.vue

# Remove old FileDataHelperService
rm app/Services/FileDataHelperService.php

# Remove old FileUploadController (if not used elsewhere)
rm app/Http/Controllers/FileUploadController.php
```

### 5. Clean Up Tests

Remove or update old tests that are no longer relevant:

```bash
# Remove old test files
rm tests/Feature/ContentPostFileDataTest.php
rm tests/Feature/FileDataHelperServiceTest.php
rm tests/Feature/ContentPostFileUploadManualTest.php
```

### 6. Update Documentation

Update any documentation that references the old system:

1. Update API documentation
2. Update user guides
3. Update developer documentation
4. Update any README files

### 7. Clear Caches

After migration, clear all caches:

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild frontend assets
npm run build
```

## Verification Steps

### 1. Test File Upload Functionality

1. **Test single image upload**

    - Create a new content post with a featured image
    - Verify the image is stored correctly
    - Verify the image is displayed properly

2. **Test multiple file upload**

    - Create a new content post with multiple media files
    - Verify all files are stored correctly
    - Verify all files are accessible

3. **Test file updates**
    - Update an existing post with new files
    - Verify old files are deleted
    - Verify new files are stored correctly

### 2. Test Error Handling

1. **Test invalid file types**

    - Try uploading executable files
    - Verify proper error messages are shown

2. **Test oversized files**

    - Try uploading files that exceed size limits
    - Verify proper error messages are shown

3. **Test corrupted files**
    - Try uploading corrupted image files
    - Verify proper error messages are shown

### 3. Test Edge Cases

1. **Test special characters in filenames**

    - Upload files with spaces, symbols, and unicode characters
    - Verify filenames are handled correctly

2. **Test concurrent uploads**

    - Upload multiple files simultaneously
    - Verify all files are processed correctly

3. **Test AJAX requests**
    - Test file uploads via AJAX
    - Verify proper JSON responses

## Rollback Plan

If issues arise during migration:

1. **Immediate rollback**

    ```bash
    # Restore from backup
    git checkout <previous-commit>

    # Restore database if needed
    php artisan db:restore <backup-file>
    ```

2. **Partial rollback**

    - Keep new components but restore old service
    - Gradually migrate functionality back

3. **Issue-specific fixes**
    - Address specific issues without full rollback
    - Use feature flags to control which system is used

## Performance Considerations

### Before Migration

-   Monitor current file upload performance
-   Document current response times
-   Note any existing performance issues

### After Migration

-   Compare performance metrics
-   Monitor for any regressions
-   Optimize if necessary

## Security Considerations

### File Validation

-   Ensure all file types are properly validated
-   Verify file size limits are enforced
-   Check for malware scanning if needed

### Access Control

-   Verify file access permissions
-   Ensure sensitive files are protected
-   Test authorization for file operations

## Monitoring and Maintenance

### Log Monitoring

Set up monitoring for:

-   File upload errors
-   Storage space usage
-   Unusual file access patterns

### Regular Maintenance

-   Monitor storage usage and cleanup old files
-   Review error logs regularly
-   Update validation rules as needed

## Troubleshooting

### Common Issues

1. **Files not uploading**

    - Check storage permissions
    - Verify disk space
    - Check file size limits

2. **Images not displaying**

    - Verify file paths are correct
    - Check storage links
    - Verify file permissions

3. **Validation errors**
    - Check validation rules
    - Verify file types are allowed
    - Check file size limits

### Debug Mode

Enable debug mode to troubleshoot issues:

```php
// In .env file
APP_DEBUG=true
LOG_LEVEL=debug
```

## Final Checklist

Before completing the migration:

-   [ ] All tests pass
-   [ ] File upload functionality works correctly
-   [ ] Error handling works as expected
-   [ ] Performance is acceptable
-   [ ] Security measures are in place
-   [ ] Documentation is updated
-   [ ] Old code is removed
-   [ ] Caches are cleared
-   [ ] Backup is verified
-   [ ] Rollback plan is tested

## Post-Migration Support

After migration:

1. Monitor system for 1-2 weeks
2. Collect user feedback
3. Address any issues promptly
4. Document any additional requirements
5. Plan for future improvements

## Conclusion

This migration simplifies the file upload system while maintaining all existing functionality. The new architecture is more maintainable, easier to understand, and follows Laravel best practices. Proper testing and monitoring will ensure a smooth transition.
