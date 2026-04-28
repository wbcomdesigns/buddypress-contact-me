# BuddyPress Contact Me — Installation Guide

## Overview

BuddyPress Contact Me adds a contact form to member profiles, allowing both logged-in and non-logged-in visitors to send messages to community members.

## Requirements

### System Requirements
- **WordPress**: 6.0 or higher
- **PHP**: 7.4 or higher
- **BuddyPress**: 10.0 or higher with Notifications component enabled
- **Memory**: 64MB minimum (128MB recommended)

### Optional Requirements
- **SMTP Plugin**: For reliable email delivery (recommended)
- **Email Logging Plugin**: For troubleshooting email issues
- **Caching Plugin**: Compatible with most caching solutions

## Installation Methods

### Method 1: WordPress Admin (Recommended)

1. **Download Plugin**
   - Purchase from [Wbcom Designs](https://wbcomdesigns.com/downloads/buddypress-contact-me/)
   - Download the ZIP file to your computer

2. **Install via WordPress**
   - Log in to your WordPress admin dashboard
   - Navigate to **Plugins → Add New**
   - Click **Upload Plugin**
   - Choose the downloaded ZIP file
   - Click **Install Now**
   - Click **Activate Plugin**

### Method 2: FTP/SFTP

1. **Extract Plugin**
   - Extract the downloaded ZIP file on your computer
   - You'll get a folder named `buddypress-contact-me`

2. **Upload to Server**
   - Connect to your server via FTP/SFTP
   - Navigate to `/wp-content/plugins/`
   - Upload the `buddypress-contact-me` folder

3. **Activate Plugin**
   - Log in to WordPress admin
   - Navigate to **Plugins → Installed Plugins**
   - Find "BuddyPress Contact Me" and click **Activate**

### Method 3: WP-CLI

```bash
# Download and extract
wget https://path-to-plugin/buddypress-contact-me.zip
unzip buddypress-contact-me.zip

# Move to plugins directory
mv buddypress-contact-me /path/to/wordpress/wp-content/plugins/

# Activate plugin
wp plugin activate buddypress-contact-me
```

## Post-Installation Setup

### 1. Initial Configuration

1. **Access Settings**
   - Navigate to **WB Plugins → Contact Me**
   - Review the default settings

2. **Configure Access Control**
   - Go to **Access** tab
   - Choose who can send messages
   - Choose who can be contacted
   - Enable/disable profile contact tab

3. **Setup Notifications**
   - Go to **Notifications** tab
   - Enable in-site notifications (if BuddyPress Notifications is active)
   - Configure email notifications
   - Set admin/sender copy preferences

### 2. License Activation (Optional)

1. **Enter License Key**
   - Go to **License** tab
   - Enter your license key from Wbcom Designs
   - Click **Activate License**

2. **Verify Activation**
   - Status should show "Active: receiving updates"
   - Automatic updates will be available

### 3. BuddyPress Email Template

1. **Customize Email Template**
   - Navigate to **Dashboard → Emails**
   - Find "A new contact message" template
   - Customize subject and body as needed

## Database Setup

The plugin automatically creates the following on activation:

### Database Table
- **`wp_contact_me`**: Stores contact messages with fields:
  - `id` (Primary key)
  - `sender_id` (Sender user ID, 0 for visitors)
  - `recipient_id` (Recipient user ID)
  - `sender_name` (Sender name for visitors)
  - `sender_email` (Sender email for visitors)
  - `subject` (Message subject)
  - `message` (Message content)
  - `status` (Message status: read/unread)
  - `created_at` (Timestamp)

### User Meta
- **`contact_me_button`**: Per-user opt-out setting ('on'/'off')
- **`bcm_intro_dismissed`**: Admin notice dismissal flag

### Options
- **`bcm_admin_general_setting`**: Plugin settings array
- **`buddypress_contact_me_db_version`**: Database version tracking
- **`edd_wbcom_bp_contact_me_license_*`**: License-related options

## Common Installation Issues

### Issue: "BuddyPress Notifications component not active"
**Solution**: 
- Navigate to **BuddyPress → Settings → Components**
- Enable **Notifications** component
- Save changes

### Issue: "Contact form not appearing on profiles"
**Causes**:
- BuddyPress not properly configured
- User has opted out in their profile settings
- Access control settings are too restrictive

**Solutions**:
- Verify BuddyPress profile pages are working
- Check user profile settings for contact form preference
- Review Access control settings in plugin admin

### Issue: "Emails not being sent"
**Causes**:
- WordPress mail function not configured
- SMTP not properly set up
- Email template not created

**Solutions**:
- Install SMTP plugin (WP Mail SMTP, Post SMTP, etc.)
- Check email logs
- Verify BuddyPress email template exists

## Multisite Installation

### Network Activation
1. **Network Install**
   - Upload plugin to network admin
   - Network activate the plugin

2. **Per-Site Configuration**
   - Each site needs individual configuration
   - Settings are stored per-site

3. **Database Tables**
   - Tables created per-site (e.g., `wp_2_contact_me`)
   - Each site maintains separate message storage

## Performance Considerations

### Caching Compatibility
- Compatible with most caching plugins
- Contact forms use AJAX, bypassing page cache
- Database queries are optimized

### Database Optimization
- Consider regular cleanup of old messages
- Use WordPress built-in cron for maintenance
- Monitor table size on high-traffic sites

## Security Considerations

### User Privacy
- Messages are stored privately
- Only recipients can view their messages
- Admin can view all messages for moderation

### Spam Protection
- Uses WordPress nonce for form submissions
- Compatible with most anti-spam plugins
- Consider CAPTCHA for high-spam environments

### Data Retention
- Messages persist until manually deleted
- Consider privacy policy compliance
- Use uninstall constant to preserve data

## Next Steps

After installation:

1. **Test the Contact Form**
   - Send test messages between users
   - Test visitor contact functionality
   - Verify email delivery

2. **Configure User Settings**
   - Educate users on profile contact settings
   - Set up user documentation

3. **Monitor Usage**
   - Check message volume
   - Monitor for spam or abuse
   - Adjust settings as needed

## Support Resources

- **Documentation**: Full user guide and developer documentation
- **Support**: Wbcom Designs support portal (license holders)
- **Community**: BuddyPress community forums
- **Troubleshooting**: See troubleshooting guide for common issues

## Upgrade Instructions

### From Previous Versions
1. Backup your database
2. Deactivate old version
3. Delete old plugin files
4. Install new version
5. Reactivate plugin
6. Run database upgrade if prompted

### Automatic Updates
- License holders receive automatic updates
- Manual update available if needed
- Always backup before updating
