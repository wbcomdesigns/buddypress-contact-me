# BuddyPress Contact Me — User Guide

## Overview

BuddyPress Contact Me allows community members and visitors to send private messages directly through member profiles. This guide covers how users can interact with the contact form and manage their contact preferences.

## For Site Members

### Finding the Contact Form

#### On Member Profiles
1. **Navigate to Profile**
   - Visit any member's profile page
   - Look for the **"Contact"** tab in the profile navigation

2. **Access Contact Form**
   - Click the **"Contact"** tab
   - The contact form will appear below the profile information

#### When Contact Form is Available
- The member has enabled contact on their profile
- Your user role is allowed to send messages (configured by admin)
- The plugin is active and properly configured

### Sending a Message

#### For Logged-in Members
1. **Fill the Form**
   - **Subject**: Enter a clear, descriptive subject line
   - **Message**: Write your message in the text area
   - Your name and email are automatically included

2. **Send Message**
   - Click **"Send Message"** button
   - Wait for confirmation message
   - Message is delivered to the recipient

#### For Visitors (Non-logged-in)
1. **Fill Additional Fields**
   - **Your Name**: Enter your full name
   - **Your Email**: Enter your email address
   - **Subject**: Enter message subject
   - **Message**: Write your message content

2. **Send Message**
   - Click **"Send Message"** button
   - Wait for confirmation message
   - Copy will be sent to your email if configured

### Managing Your Messages

#### Viewing Received Messages
1. **Access Your Messages**
   - Navigate to your profile
   - Look for message notifications
   - Check your email for message copies

2. **Message Notifications**
   - **In-site**: BuddyPress notification bell (if enabled)
   - **Email**: Email notification (if configured)
   - **Admin Copy**: Site admin may receive copies

#### Message Privacy
- Only you can view messages sent to you
- Site administrators can view all messages for moderation
- Messages are stored privately in the database

### Managing Your Contact Preferences

#### Opt-out of Contact Form
1. **Profile Settings**
   - Navigate to **Profile → Edit Profile**
   - Look for "Contact Me" settings
   - Uncheck "Allow members to contact me"

2. **Effect of Opt-out**
   - Contact tab disappears from your profile
   - No one can send you messages through the form
   - You can re-enable at any time

#### Contact Form Visibility
- **Enabled**: Contact tab visible on your profile
- **Disabled**: Contact tab hidden, no messages can be received
- **Admin Override**: Site admin can always contact members

## For Site Administrators

### Plugin Configuration

#### Access Control Settings
1. **Navigate to Admin**
   - Go to **WB Plugins → Contact Me**
   - Click **"Access"** tab

2. **Configure Who Can Send**
   - Select user roles that can send messages
   - Include "Visitors (not logged in)" if desired
   - Leave all unchecked to allow all logged-in users

3. **Configure Who Can Be Contacted**
   - Select roles that can receive messages
   - Useful for limiting to specific member types
   - Affects which profiles show contact forms

#### Notification Settings
1. **Navigate to Notifications**
   - Go to **WB Plugins → Contact Me**
   - Click **"Notifications"** tab

2. **Recipient Notifications**
   - **In-site notifications**: BuddyPress notifications
   - **Email notifications**: Direct email to recipients
   - Both can be enabled simultaneously

3. **Additional Copies**
   - **Site admin copy**: BCC admin on all messages
   - **Sender copy**: Send copy to message sender
   - Useful for moderation and records

### Managing Messages

#### Viewing All Messages
1. **Access Message Management**
   - Messages are stored in the database
   - Use WordPress admin to manage if needed
   - Consider using a database management interface

2. **Message Data**
   - Sender information (name, email, user ID)
   - Recipient information
   - Message content and timestamp
   - Read/unread status

#### Message Moderation
- Review messages for inappropriate content
- Respond to user complaints about messages
- Manage spam or abuse reports
- Export message data if needed

### Email Template Customization

#### Customize BuddyPress Email
1. **Access Email Templates**
   - Navigate to **Dashboard → Emails**
   - Find "A new contact message" template

2. **Edit Template**
   - Customize subject line
   - Modify email body content
   - Add site-specific branding
   - Use template variables for dynamic content

#### Template Variables
- `{{sender_name}}`: Sender's name
- `{{sender_email}}`: Sender's email address
- `{{recipient_name}}`: Recipient's name
- `{{message}}`: Message content
- `{{site_name}}`: Your site name
- `{{site_url}}`: Your site URL

## Best Practices

### For Message Senders
- **Clear Subject Lines**: Help recipients understand message purpose
- **Appropriate Content**: Keep messages professional and relevant
- **Respect Privacy**: Don't share sensitive information unnecessarily
- **Follow Up**: Allow reasonable time for responses

### For Message Recipients
- **Check Regularly**: Monitor your messages and notifications
- **Respond Promptly**: Reply to messages in a timely manner
- **Report Issues**: Contact admin about inappropriate messages
- **Manage Preferences**: Adjust your contact settings as needed

### For Site Administrators
- **Clear Policies**: Establish community guidelines for messaging
- **Regular Monitoring**: Check message volume and content quality
- **User Education**: Help members understand how to use the feature
- **Privacy Compliance**: Ensure compliance with privacy regulations

## Troubleshooting Common Issues

### Contact Form Not Appearing
**Possible Causes**:
- User has opted out of contact
- Your role isn't allowed to send messages
- Plugin settings are misconfigured

**Solutions**:
- Check your profile contact settings
- Contact site administrator
- Verify plugin is properly configured

### Messages Not Being Delivered
**Possible Causes**:
- Email delivery issues
- BuddyPress notifications not active
- Recipient email preferences

**Solutions**:
- Check email configuration
- Verify BuddyPress settings
- Contact site administrator

### Not Receiving Notifications
**Possible Causes**:
- Notification settings disabled
- Email delivery problems
- BuddyPress component issues

**Solutions**:
- Check notification preferences
- Verify email delivery
- Ensure BuddyPress Notifications is active

## Privacy and Security

### Message Privacy
- Messages are private between sender and recipient
- Site administrators can access messages for moderation
- No public visibility of message content

### Data Protection
- Messages stored securely in WordPress database
- User data handled according to WordPress standards
- Consider privacy policy implications

### Spam Prevention
- Form includes WordPress nonce protection
- Compatible with anti-spam plugins
- Monitor for suspicious message patterns

## Integration with Other Plugins

### BuddyPress Components
- **Notifications**: Required for in-site notifications
- **Extended Profiles**: Works with custom profile fields
- **Groups**: Can be used in group contexts

### Email Plugins
- **SMTP Plugins**: Recommended for reliable delivery
- **Email Logging**: Helps troubleshoot delivery issues
- **Email Templates**: Works with BuddyPress email system

### Caching Plugins
- **Page Caching**: Contact forms use AJAX, bypassing cache
- **Object Caching**: Compatible with object caching
- **CDN**: Works with content delivery networks

## Mobile Usage

### Responsive Design
- Contact form works on all device sizes
- Touch-friendly interface elements
- Optimized for mobile browsers

### Mobile Considerations
- Smaller screens may require scrolling
- Virtual keyboard behavior
- Network connectivity for form submission

## Accessibility

### Screen Reader Support
- Proper form labels and descriptions
- Semantic HTML structure
- ARIA attributes where appropriate

### Keyboard Navigation
- Full keyboard access to all form elements
- Logical tab order
- Focus indicators visible

### Visual Accessibility
- Sufficient color contrast
- Clear text sizing
- High contrast mode support

## Frequently Asked Questions

### Q: Can I block specific users from contacting me?
A: Currently, you can only opt-out entirely. Contact your admin about user-specific blocking.

### Q: Are messages encrypted?
A: Messages are stored in the WordPress database with standard WordPress security measures.

### Q: Can I export my messages?
A: Contact your site administrator about message export options.

### Q: How long are messages stored?
A: Messages persist until manually deleted by administrators.

### Q: Can visitors see my email address?
A: No, visitors only see your profile name. Your email remains private.

## Support Resources

### Documentation
- Installation guide
- Developer documentation
- Troubleshooting guide

### Community Support
- BuddyPress community forums
- Wbcom Designs support (license holders)
- WordPress.org support forums

### Getting Help
- Contact your site administrator first
- Check plugin documentation
- Reach out to plugin support team
