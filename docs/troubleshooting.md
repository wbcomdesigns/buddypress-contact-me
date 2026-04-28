# BuddyPress Contact Me — Troubleshooting Guide

## Overview

This guide helps diagnose and resolve common issues with BuddyPress Contact Me. Issues are categorized by severity and complexity, with step-by-step solutions.

## Quick Diagnosis Checklist

Before diving into specific issues, check these basics:

### ✅ Basic Plugin Status
- Plugin is activated in WordPress admin
- No PHP errors in debug log
- Plugin tables exist in database
- BuddyPress is active and configured

### ✅ System Requirements
- WordPress 6.0+ installed
- PHP 7.4+ running
- BuddyPress 10.0+ with Notifications component
- Sufficient server memory (64MB+)

### ✅ Configuration Status
- License activated (if using premium features)
- Access control settings configured
- Notification settings enabled
- Email template exists

## Common Issues and Solutions

### Issue 1: Contact Form Not Appearing

#### Symptoms
- No "Contact" tab on member profiles
- Contact tab visible but form doesn't load
- Form appears but submit button doesn't work

#### Possible Causes and Solutions

**Cause A: User Has Opted Out**
```php
// Check user meta
SELECT meta_value FROM wp_usermeta WHERE meta_key = 'contact_me_button' AND user_id = [USER_ID];
```
**Solution**: 
- Ask user to check profile settings
- User can re-enable in profile edit page
- Admin can reset via database if needed

**Cause B: Access Control Too Restrictive**
**Check**: Admin → Contact Me → Access tab
**Solution**:
- Review "Who Can Send Messages" settings
- Review "Who Can Be Contacted" settings
- Ensure user roles are properly selected

**Cause C: BuddyPress Profile Issues**
**Check**: 
- BuddyPress profile pages are working
- Profile navigation is functional
- Other profile tabs work correctly

**Solution**:
- Verify BuddyPress installation
- Check BuddyPress theme compatibility
- Test with default WordPress theme

**Cause D: Theme or Plugin Conflict**
**Test**:
```php
// Add to wp-config.php for testing
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```
**Solution**:
- Switch to default theme (Twenty Twenty-Three)
- Deactivate other plugins one by one
- Check for JavaScript errors in browser console

#### Debugging Steps
1. **Check Browser Console**
   ```javascript
   // Look for JavaScript errors
   // Check for 404 errors on CSS/JS files
   // Verify AJAX requests are working
   ```

2. **Verify Database Tables**
   ```sql
   SHOW TABLES LIKE '%contact_me%';
   DESCRIBE wp_contact_me;
   ```

3. **Check User Capabilities**
   ```php
   // In theme functions.php temporarily
   add_action('wp_head', function() {
       if (is_user_logged_in()) {
           $user = wp_get_current_user();
           error_log('Current user roles: ' . print_r($user->roles, true));
       }
   });
   ```

### Issue 2: Messages Not Being Sent

#### Symptoms
- Form submits but no message received
- No email notifications sent
- Error messages on form submission

#### Possible Causes and Solutions

**Cause A: Email Delivery Issues**
**Check**:
```php
// Test WordPress mail function
wp_mail('test@example.com', 'Test Subject', 'Test Message');
```

**Solutions**:
- Install SMTP plugin (WP Mail SMTP, Post SMTP)
- Configure SMTP settings with your email provider
- Check email logs for delivery failures
- Verify from email address is valid

**Cause B: BuddyPress Notifications Not Active**
**Check**: BuddyPress → Settings → Components
**Solution**:
- Enable Notifications component
- Save settings
- Test notification delivery

**Cause C: Email Template Missing**
**Check**: Dashboard → Emails
**Solution**:
- Look for "A new contact message" template
- Create template if missing
- Customize template content

**Cause D: Plugin Configuration Issues**
**Check**: Contact Me → Notifications tab
**Solution**:
- Enable email notifications
- Check recipient notification settings
- Verify admin copy settings if needed

#### Debugging Email Issues

**Step 1: Test WordPress Mail**
```php
// Add to functions.php temporarily
add_action('wp_loaded', function() {
    if (isset($_GET['test_mail'])) {
        $sent = wp_mail(get_option('admin_email'), 'Test Email', 'This is a test');
        echo $sent ? 'Mail sent successfully' : 'Mail failed';
        exit;
    }
});
// Visit /?test_mail=1 to test
```

**Step 2: Check Email Logs**
- Install WP Mail Logger plugin
- Send test message
- Review logged emails for errors

**Step 3: Verify SMTP Settings**
```php
// Check current mail configuration
add_action('phpmailer_init', function($phpmailer) {
    error_log('PHPMailer configuration: ' . print_r($phpmailer, true));
});
```

### Issue 3: Notification Problems

#### Symptoms
- Users don't receive in-site notifications
- Email notifications not working
- Admin copies not received

#### Solutions

**In-Site Notifications**:
- Verify BuddyPress Notifications component is active
- Check user notification preferences
- Clear BuddyPress cache: `wp cache flush`

**Email Notifications**:
- Test email delivery (see Issue 2)
- Check email template configuration
- Verify recipient email addresses

**Admin Copies**:
- Enable admin copy in notification settings
- Check admin email address in WordPress settings
- Verify email delivery to admin

### Issue 4: Performance Problems

#### Symptoms
- Slow page loads on profile pages
- High database query count
- Memory exhaustion errors

#### Solutions

**Database Optimization**:
```sql
-- Check table size
SELECT table_name, ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables 
WHERE table_schema = 'your_database_name' 
AND table_name LIKE '%contact_me%';

-- Clean old messages (older than 1 year)
DELETE FROM wp_contact_me WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR);
```

**Caching Configuration**:
- Enable object caching if available
- Configure page caching to exclude AJAX requests
- Use CDN for static assets

**Query Optimization**:
```php
// Add to functions.php to monitor queries
add_action('wp_loaded', function() {
    if (current_user_can('administrator')) {
        add_filter('query', function($query) {
            if (strpos($query, 'contact_me') !== false) {
                error_log('Contact Me Query: ' . $query);
            }
            return $query;
        });
    }
});
```

### Issue 5: Security and Spam Issues

#### Symptoms
- Receiving spam messages
- Security warnings in logs
- Form submission abuse

#### Solutions

**Spam Prevention**:
- Install anti-spam plugin (Akismet, Antispam Bee)
- Add CAPTCHA to contact form
- Implement rate limiting

**Security Hardening**:
```php
// Add to wp-config.php
define('DISALLOW_FILE_EDIT', true);
define('DISALLOW_FILE_MODS', true);

// Rate limiting for contact form
add_action('template_redirect', function() {
    if (bp_is_user_profile() && isset($_POST['bcm_contact_submit'])) {
        $user_id = get_current_user_id();
        $cache_key = 'bcm_submit_' . $user_id;
        if (wp_cache_get($cache_key)) {
            wp_die('Please wait before submitting another message.');
        }
        wp_cache_set($cache_key, 1, '', 60); // 60 second cooldown
    }
});
```

**Content Filtering**:
```php
// Add content validation
add_filter('bcm_validate_message', function($message, $data) {
    // Check for spam keywords
    $spam_keywords = ['viagra', 'casino', 'lottery'];
    foreach ($spam_keywords as $keyword) {
        if (stripos($message, $keyword) !== false) {
            return new WP_Error('spam_detected', 'Message contains spam content');
        }
    }
    return $message;
}, 10, 2);
```

## Advanced Debugging

### Enable Debug Mode

**WordPress Debug**:
```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

**Plugin-Specific Debug**:
```php
// Add to functions.php
add_action('init', function() {
    if (current_user_can('administrator')) {
        define('BCM_DEBUG', true);
    }
});
```

### Database Diagnostics

**Check Plugin Tables**:
```sql
-- Verify table structure
DESCRIBE wp_contact_me;

-- Check for orphaned records
SELECT COUNT(*) as orphaned FROM wp_contact_me 
WHERE recipient_id NOT IN (SELECT ID FROM wp_users);

-- Check message volume by date
SELECT DATE(created_at) as date, COUNT(*) as messages 
FROM wp_contact_me 
GROUP BY DATE(created_at) 
ORDER BY date DESC 
LIMIT 30;
```

**User Meta Issues**:
```sql
-- Check for missing user meta
SELECT u.ID, u.user_login, um.meta_value 
FROM wp_users u 
LEFT JOIN wp_usermeta um ON u.ID = um.user_id AND um.meta_key = 'contact_me_button' 
WHERE um.meta_value IS NULL;

-- Fix missing user meta
INSERT INTO wp_usermeta (user_id, meta_key, meta_value)
SELECT ID, 'contact_me_button', 'on'
FROM wp_users 
WHERE ID NOT IN (
    SELECT user_id FROM wp_usermeta WHERE meta_key = 'contact_me_button'
);
```

### JavaScript Debugging

**Browser Console Check**:
```javascript
// Check for jQuery
console.log('jQuery version:', $ && $.fn.jquery);

// Check for BuddyPress
console.log('BuddyPress loaded:', typeof BP !== 'undefined');

// Check AJAX functionality
jQuery.post(ajaxurl, {action: 'test'}, function(response) {
    console.log('AJAX test:', response);
});
```

**Network Tab Analysis**:
- Monitor AJAX requests during form submission
- Check for 404 errors on script files
- Verify response codes and payloads

## Compatibility Issues

### Theme Conflicts

**Common Theme Issues**:
- Custom profile templates overriding plugin
- CSS conflicts with form styling
- JavaScript conflicts with theme scripts

**Resolution Steps**:
1. Test with default theme
2. Check for template overrides
3. Review custom CSS conflicts
4. Verify JavaScript compatibility

### Plugin Conflicts

**Common Conflicting Plugins**:
- Other contact form plugins
- BuddyPress add-ons with similar features
- Security plugins with strict rules
- Caching plugins with aggressive settings

**Resolution Process**:
1. Create plugin list
2. Deactivate all except Contact Me
3. Test functionality
4. Reactivate plugins one by one
5. Identify conflicting plugin

### BuddyPress Version Issues

**Version Compatibility**:
- BuddyPress 10.0+ required
- Some features need newer versions
- Check for deprecated function usage

**Upgrade Process**:
1. Backup database
2. Update BuddyPress
3. Test Contact Me functionality
4. Update plugin if needed

## Performance Optimization

### Database Optimization

**Regular Maintenance**:
```sql
-- Optimize table
OPTIMIZE TABLE wp_contact_me;

-- Add indexes for better performance
ALTER TABLE wp_contact_me ADD INDEX idx_recipient_created (recipient_id, created_at);
ALTER TABLE wp_contact_me ADD INDEX idx_status_created (status, created_at);
```

**Cleanup Old Messages**:
```php
// Add to functions.php
add_action('wp_cron', 'cleanup_old_contact_messages');
function cleanup_old_contact_messages() {
    global $wpdb;
    $old_messages = $wpdb->query(
        "DELETE FROM {$wpdb->prefix}contact_me 
         WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
    );
    error_log("Cleaned up $old_messages old contact messages");
}

// Schedule cleanup
if (!wp_next_scheduled('cleanup_old_contact_messages')) {
    wp_schedule_event(time(), 'daily', 'cleanup_old_contact_messages');
}
```

### Caching Strategies

**Object Caching**:
```php
// Cache message count
function get_contact_message_count($user_id) {
    $cache_key = 'contact_count_' . $user_id;
    $count = wp_cache_get($cache_key);
    
    if (false === $count) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}contact_me 
             WHERE recipient_id = %d",
            $user_id
        ));
        wp_cache_set($cache_key, $count, '', 300); // 5 minutes
    }
    
    return $count;
}
```

## Getting Help

### Support Channels

**License Holders**:
- Wbcom Designs support portal
- Priority email support
- Access to premium documentation

**Community Support**:
- BuddyPress community forums
- WordPress.org support forums
- Plugin documentation and FAQs

### Reporting Issues

**Bug Reports**:
Include:
- WordPress and BuddyPress versions
- PHP version and server environment
- Theme and other active plugins
- Steps to reproduce the issue
- Error messages and logs
- Browser and device information

**Feature Requests**:
- Describe the desired functionality
- Explain use case and benefits
- Suggest implementation approach
- Provide examples if possible

### Professional Support

**Development Services**:
- Custom integration development
- Performance optimization
- Security audits
- Migration assistance

**Maintenance Services**:
- Regular updates and monitoring
- Performance optimization
- Security maintenance
- Backup and recovery

## Prevention and Maintenance

### Regular Maintenance Tasks

**Monthly**:
- Check message volume and patterns
- Review error logs
- Update plugin and dependencies
- Test email delivery

**Quarterly**:
- Clean up old messages
- Review access control settings
- Audit user permissions
- Performance monitoring

**Annually**:
- Full security audit
- Database optimization
- Feature usage analysis
- Documentation updates

### Monitoring and Alerts

**Error Monitoring**:
```php
// Log critical errors
add_action('bcm_message_send_error', function($error, $data) {
    error_log('Contact Me Error: ' . $error->get_error_message());
    // Send admin notification for critical errors
    if ($error->get_error_code() === 'critical') {
        wp_mail(get_option('admin_email'), 'Contact Me Critical Error', $error->get_error_message());
    }
}, 10, 2);
```

**Performance Monitoring**:
- Monitor database query times
- Track message submission rates
- Watch for unusual activity patterns
- Set up alerts for high error rates

This troubleshooting guide should help resolve most common issues with BuddyPress Contact Me. For complex problems or professional support, contact the plugin development team.
