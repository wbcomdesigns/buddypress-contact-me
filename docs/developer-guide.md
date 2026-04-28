# BuddyPress Contact Me — Developer Guide

## Overview

BuddyPress Contact Me is a WordPress plugin that adds contact functionality to BuddyPress member profiles. This guide covers the plugin's architecture, APIs, hooks, and extension points for developers.

## Plugin Architecture

### Core Components

```
buddypress-contact-me/
├── buddypress-contact-me.php          # Main plugin file
├── includes/
│   ├── class-buddypress-contact-me.php    # Core plugin class
│   ├── class-buddypress-contact-me-activator.php  # Activation handler
│   ├── class-buddypress-contact-me-deactivator.php # Deactivation handler
│   ├── class-buddypress-contact-me-loader.php      # Hook loader
│   ├── class-buddypress-contact-me-i18n.php        # Internationalization
│   ├── admin/
│   │   ├── class-bcm-admin.php        # Admin interface
│   │   └── views/                     # Admin templates
│   ├── frontend/
│   │   ├── class-bcm-frontend.php     # Frontend functionality
│   │   ├── class-bcm-frontend-nav.php # Profile navigation
│   │   └── class-bcm-frontend-form.php # Contact form
│   ├── email/
│   │   └── class-bcm-email.php        # Email handling
│   ├── rest/
│   │   └── class-bcm-rest-api.php     # REST API endpoints
│   └── data/
│       └── class-bcm-database.php     # Database operations
├── public/                            # Frontend assets
├── admin/                             # Admin assets
└── languages/                         # Translation files
```

### Database Schema

#### Contact Messages Table
```sql
CREATE TABLE wp_contact_me (
    id int(11) NOT NULL AUTO_INCREMENT,
    sender_id int(11) NOT NULL DEFAULT '0',
    recipient_id int(11) NOT NULL,
    sender_name varchar(255) DEFAULT NULL,
    sender_email varchar(255) DEFAULT NULL,
    subject varchar(255) NOT NULL,
    message longtext NOT NULL,
    status enum('read','unread') NOT NULL DEFAULT 'unread',
    created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_recipient (recipient_id),
    KEY idx_sender (sender_id),
    KEY idx_status (status),
    KEY idx_created (created_at)
);
```

#### User Meta Keys
- `contact_me_button`: User opt-out setting ('on'/'off')
- `bcm_intro_dismissed`: Admin notice dismissal flag

#### Options
- `bcm_admin_general_setting`: Main settings array
- `buddypress_contact_me_db_version`: Database version tracking

## API Reference

### Core Functions

#### Message Management
```php
// Send a contact message
bcm_send_message(array(
    'sender_id' => 123,           // 0 for visitors
    'recipient_id' => 456,
    'sender_name' => 'John Doe',  // Required for visitors
    'sender_email' => 'john@example.com', // Required for visitors
    'subject' => 'Message Subject',
    'message' => 'Message content'
));

// Get messages for a user
$messages = bcm_get_messages($user_id, array(
    'status' => 'unread',        // 'read', 'unread', or 'all'
    'limit' => 20,
    'offset' => 0,
    'order' => 'DESC'            // 'ASC' or 'DESC'
));

// Mark message as read
bcm_mark_message_read($message_id);

// Delete message
bcm_delete_message($message_id);

// Get message count
$count = bcm_get_message_count($user_id, $status);
```

#### User Permissions
```php
// Check if user can send messages
$can_send = bcm_user_can_send($user_id, $recipient_id);

// Check if user can be contacted
$can_be_contacted = bcm_user_can_be_contacted($user_id);

// Check if contact form is enabled for user
$form_enabled = bcm_contact_form_enabled($user_id);
```

#### Settings Management
```php
// Get plugin settings
$settings = bcm_get_settings();

// Get specific setting
$value = bcm_get_setting('bcm_allow_contact_tab');

// Update setting
bcm_update_setting('bcm_allow_contact_tab', 'yes');
```

### Hooks and Filters

#### Action Hooks
```php
// Before message is sent
do_action('bcm_before_send_message', $message_data, $recipient_id);

// After message is sent successfully
do_action('bcm_after_send_message', $message_id, $message_data);

// Before message is deleted
do_action('bcm_before_delete_message', $message_id);

// After message is marked as read
do_action('bcm_message_marked_read', $message_id);

// Contact form display
do_action('bcm_contact_form_before', $recipient_id);
do_action('bcm_contact_form_after', $recipient_id);
```

#### Filter Hooks
```php
// Filter message data before saving
$message_data = apply_filters('bcm_message_data', $message_data, $recipient_id);

// Filter message content before sending
$message_content = apply_filters('bcm_message_content', $message, $message_data);

// Filter email subject
$subject = apply_filters('bcm_email_subject', $subject, $message_data);

// Filter notification recipients
$recipients = apply_filters('bcm_notification_recipients', $recipients, $message_data);

// Filter contact form visibility
$visible = apply_filters('bcm_contact_form_visible', true, $user_id);
```

### REST API Endpoints

#### Send Message
```http
POST /wp-json/bcm/v1/messages
Content-Type: application/json

{
    "recipient_id": 123,
    "sender_name": "John Doe",
    "sender_email": "john@example.com",
    "subject": "Test Subject",
    "message": "Test message content"
}
```

#### Get Messages
```http
GET /wp-json/bcm/v1/messages?status=unread&limit=10&offset=0
Authorization: Bearer {token}
```

#### Mark Message Read
```http
PUT /wp-json/bcm/v1/messages/{id}/read
Authorization: Bearer {token}
```

#### Delete Message
```http
DELETE /wp-json/bcm/v1/messages/{id}
Authorization: Bearer {token}
```

## Frontend Integration

### Template Overrides

#### Contact Form Template
Create `buddypress-contact-me/contact-form.php` in your theme:
```php
<?php
/**
 * Custom contact form template
 */

if (!defined('ABSPATH')) exit;
?>

<div class="custom-contact-form">
    <h3><?php esc_html_e('Contact Me', 'your-text-domain'); ?></h3>
    
    <form method="post" class="bcm-contact-form">
        <?php wp_nonce_field('bcm_send_message', 'bcm_nonce'); ?>
        
        <?php if (!is_user_logged_in()): ?>
            <div class="form-row">
                <label for="bcm-sender-name"><?php esc_html_e('Your Name', 'your-text-domain'); ?></label>
                <input type="text" id="bcm-sender-name" name="sender_name" required>
            </div>
            
            <div class="form-row">
                <label for="bcm-sender-email"><?php esc_html_e('Your Email', 'your-text-domain'); ?></label>
                <input type="email" id="bcm-sender-email" name="sender_email" required>
            </div>
        <?php endif; ?>
        
        <div class="form-row">
            <label for="bcm-subject"><?php esc_html_e('Subject', 'your-text-domain'); ?></label>
            <input type="text" id="bcm-subject" name="subject" required>
        </div>
        
        <div class="form-row">
            <label for="bcm-message"><?php esc_html_e('Message', 'your-text-domain'); ?></label>
            <textarea id="bcm-message" name="message" required></textarea>
        </div>
        
        <div class="form-row">
            <button type="submit" class="button"><?php esc_html_e('Send Message', 'your-text-domain'); ?></button>
        </div>
    </form>
</div>
```

#### Message List Template
Create `buddypress-contact-me/message-list.php` in your theme:
```php
<?php
/**
 * Custom message list template
 */

if (!defined('ABSPATH')) exit;

$messages = bcm_get_messages(get_current_user_id(), array('status' => 'unread'));
?>

<div class="custom-message-list">
    <h3><?php esc_html_e('Unread Messages', 'your-text-domain'); ?></h3>
    
    <?php if (empty($messages)): ?>
        <p><?php esc_html_e('No unread messages.', 'your-text-domain'); ?></p>
    <?php else: ?>
        <?php foreach ($messages as $message): ?>
            <div class="message-item" data-message-id="<?php echo $message->id; ?>">
                <div class="message-header">
                    <strong><?php echo esc_html($message->sender_name); ?></strong>
                    <span class="message-date"><?php echo date_i18n(get_option('date_format'), strtotime($message->created_at)); ?></span>
                </div>
                
                <div class="message-subject">
                    <?php echo esc_html($message->subject); ?>
                </div>
                
                <div class="message-preview">
                    <?php echo wp_trim_words($message->message, 20); ?>
                </div>
                
                <div class="message-actions">
                    <button class="button mark-read" data-id="<?php echo $message->id; ?>">
                        <?php esc_html_e('Mark as Read', 'your-text-domain'); ?>
                    </button>
                    <button class="button delete" data-id="<?php echo $message->id; ?>">
                        <?php esc_html_e('Delete', 'your-text-domain'); ?>
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
```

### JavaScript Integration

#### Custom Form Handling
```javascript
// Custom contact form JavaScript
jQuery(document).ready(function($) {
    $('.bcm-contact-form').on('submit', function(e) {
        e.preventDefault();
        
        var $form = $(this);
        var $submit = $form.find('button[type="submit"]');
        
        // Disable submit button
        $submit.prop('disabled', true);
        
        $.ajax({
            url: bcm_vars.ajax_url,
            type: 'POST',
            data: $form.serialize() + '&action=bcm_send_message',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('.bcm-form-message')
                        .removeClass('error')
                        .addClass('success')
                        .text(response.data.message)
                        .show();
                    
                    // Reset form
                    $form[0].reset();
                } else {
                    // Show error message
                    $('.bcm-form-message')
                        .removeClass('success')
                        .addClass('error')
                        .text(response.data.message)
                        .show();
                }
            },
            error: function() {
                $('.bcm-form-message')
                    .removeClass('success')
                    .addClass('error')
                    .text('An error occurred. Please try again.')
                    .show();
            },
            complete: function() {
                // Re-enable submit button
                $submit.prop('disabled', false);
            }
        });
    });
    
    // Mark message as read
    $('.mark-read').on('click', function() {
        var messageId = $(this).data('id');
        var $button = $(this);
        
        $.post(bcm_vars.ajax_url, {
            action: 'bcm_mark_read',
            message_id: messageId,
            nonce: bcm_vars.nonce
        }, function(response) {
            if (response.success) {
                $button.closest('.message-item').fadeOut();
            }
        });
    });
    
    // Delete message
    $('.delete').on('click', function() {
        if (!confirm('Are you sure you want to delete this message?')) {
            return;
        }
        
        var messageId = $(this).data('id');
        var $button = $(this);
        
        $.post(bcm_vars.ajax_url, {
            action: 'bcm_delete_message',
            message_id: messageId,
            nonce: bcm_vars.nonce
        }, function(response) {
            if (response.success) {
                $button.closest('.message-item').fadeOut();
            }
        });
    });
});
```

## Custom Extensions

### Adding Custom Fields

#### Add Field to Contact Form
```php
// Add custom field to form
add_action('bcm_contact_form_fields', function($recipient_id) {
    ?>
    <div class="form-row">
        <label for="bcm-phone"><?php esc_html_e('Phone Number', 'your-text-domain'); ?></label>
        <input type="tel" id="bcm-phone" name="phone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}">
    </div>
    <?php
});

// Validate custom field
add_filter('bcm_validate_message', function($message_data, $recipient_id) {
    if (!empty($_POST['phone'])) {
        $phone = sanitize_text_field($_POST['phone']);
        if (!preg_match('/^[0-9]{3}-[0-9]{3}-[0-9]{4}$/', $phone)) {
            return new WP_Error('invalid_phone', 'Please enter a valid phone number');
        }
        $message_data['phone'] = $phone;
    }
    return $message_data;
}, 10, 2);

// Save custom field
add_action('bcm_message_saved', function($message_id, $message_data) {
    if (!empty($message_data['phone'])) {
        add_post_meta($message_id, '_phone', $message_data['phone']);
    }
}, 10, 2);
```

### Custom Notifications

#### Add SMS Notification
```php
// Add SMS notification after message sent
add_action('bcm_after_send_message', function($message_id, $message_data) {
    $recipient = get_userdata($message_data['recipient_id']);
    $phone = get_user_meta($recipient->ID, 'phone_number', true);
    
    if ($phone) {
        $message = sprintf(
            'New message from %s: %s',
            $message_data['sender_name'],
            $message_data['subject']
        );
        
        // Use SMS service API
        send_sms_notification($phone, $message);
    }
}, 10, 2);

function send_sms_notification($phone, $message) {
    // Implementation depends on SMS service
    // Example using Twilio
    $account_sid = 'your_account_sid';
    $auth_token = 'your_auth_token';
    $twilio_number = 'your_twilio_number';
    
    $client = new Twilio\Rest\Client($account_sid, $auth_token);
    
    try {
        $client->messages->create(
            $phone,
            [
                'from' => $twilio_number,
                'body' => $message
            ]
        );
    } catch (Exception $e) {
        error_log('SMS notification failed: ' . $e->getMessage());
    }
}
```

### Integration with Other Plugins

#### BuddyBoss Integration
```php
// Add compatibility with BuddyBoss
add_filter('bcm_buddyboss_compatible', function($compatible) {
    if (function_exists('bp_is_active') && bp_is_active('messages')) {
        // Integrate with BuddyBoss messaging
        add_action('bcm_after_send_message', function($message_id, $message_data) {
            // Create BuddyBoss message
            messages_new_message([
                'sender_id' => $message_data['sender_id'],
                'recipients' => [$message_data['recipient_id']],
                'subject' => $message_data['subject'],
                'content' => $message_data['message']
            ]);
        }, 10, 2);
    }
    return true;
});
```

#### CRM Integration
```php
// Add CRM integration
add_action('bcm_after_send_message', function($message_id, $message_data) {
    // Send to CRM
    $crm_data = [
        'contact_name' => $message_data['sender_name'],
        'contact_email' => $message_data['sender_email'],
        'message' => $message_data['message'],
        'source' => 'BuddyPress Contact Form'
    ];
    
    // Example using HubSpot API
    hubspot_create_contact($crm_data);
}, 10, 2);

function hubspot_create_contact($data) {
    $api_key = 'your_hubspot_api_key';
    $url = 'https://api.hubapi.com/crm/v3/objects/contacts';
    
    $payload = [
        'properties' => [
            ['property' => 'firstname', 'value' => explode(' ', $data['contact_name'])[0]],
            ['property' => 'email', 'value' => $data['contact_email']],
            ['property' => 'lifecyclestage', 'value' => 'lead']
        ]
    ];
    
    wp_remote_post($url, [
        'headers' => [
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json'
        ],
        'body' => json_encode($payload)
    ]);
}
```

## Security Considerations

### Data Validation
```php
// Always validate and sanitize input
function bcm_validate_message_data($data) {
    $validated = [];
    
    // Validate required fields
    if (empty($data['recipient_id']) || !is_numeric($data['recipient_id'])) {
        return new WP_Error('invalid_recipient', 'Invalid recipient');
    }
    
    if (empty($data['message'])) {
        return new WP_Error('empty_message', 'Message cannot be empty');
    }
    
    // Sanitize input
    $validated['recipient_id'] = absint($data['recipient_id']);
    $validated['sender_id'] = isset($data['sender_id']) ? absint($data['sender_id']) : 0;
    $validated['sender_name'] = isset($data['sender_name']) ? sanitize_text_field($data['sender_name']) : '';
    $validated['sender_email'] = isset($data['sender_email']) ? sanitize_email($data['sender_email']) : '';
    $validated['subject'] = sanitize_text_field($data['subject']);
    $validated['message'] = wp_kses_post($data['message']); // Allow basic HTML
    
    return $validated;
}
```

### Permission Checks
```php
// Always verify user permissions
function bcm_can_send_message($sender_id, $recipient_id) {
    // Check if sender is blocked
    if (is_user_blocked($sender_id, $recipient_id)) {
        return false;
    }
    
    // Check rate limiting
    if (bcm_is_rate_limited($sender_id)) {
        return false;
    }
    
    // Check user roles
    return bcm_user_can_send($sender_id, $recipient_id);
}
```

### Nonce Verification
```php
// Always verify nonces
add_action('wp_ajax_bcm_send_message', function() {
    if (!wp_verify_nonce($_POST['nonce'], 'bcm_send_message')) {
        wp_die('Security check failed');
    }
    
    // Process message...
});
```

## Performance Optimization

### Database Queries
```php
// Use efficient queries
function bcm_get_messages_optimized($user_id, $args = []) {
    global $wpdb;
    
    $defaults = [
        'status' => 'unread',
        'limit' => 20,
        'offset' => 0,
        'order' => 'DESC'
    ];
    
    $args = wp_parse_args($args, $defaults);
    
    $query = $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}contact_me 
         WHERE recipient_id = %d 
         AND status = %s 
         ORDER BY created_at {$args['order']} 
         LIMIT %d OFFSET %d",
        $user_id,
        $args['status'],
        $args['limit'],
        $args['offset']
    );
    
    return $wpdb->get_results($query);
}
```

### Caching
```php
// Cache message counts
function bcm_get_message_count_cached($user_id, $status = 'unread') {
    $cache_key = "bcm_count_{$user_id}_{$status}";
    $count = wp_cache_get($cache_key);
    
    if (false === $count) {
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}contact_me 
             WHERE recipient_id = %d AND status = %s",
            $user_id,
            $status
        ));
        
        wp_cache_set($cache_key, $count, '', 300); // 5 minutes
    }
    
    return $count;
}

// Clear cache when message status changes
add_action('bcm_message_marked_read', function($message_id) {
    $message = bcm_get_message($message_id);
    wp_cache_delete("bcm_count_{$message->recipient_id}_unread", '');
    wp_cache_delete("bcm_count_{$message->recipient_id}_read", '');
});
```

## Testing

### Unit Tests
```php
// Example unit test
class BCM_Tests extends WP_UnitTestCase {
    
    public function test_send_message() {
        $sender = $this->factory->user->create();
        $recipient = $this->factory->user->create();
        
        $message_id = bcm_send_message([
            'sender_id' => $sender,
            'recipient_id' => $recipient,
            'subject' => 'Test Subject',
            'message' => 'Test Message'
        ]);
        
        $this->assertIsInt($message_id);
        $this->assertGreaterThan(0, $message_id);
    }
    
    public function test_user_permissions() {
        $user = $this->factory->user->create(['role' => 'subscriber']);
        
        $this->assertTrue(bcm_user_can_send($user, $this->factory->user->create()));
        $this->assertFalse(bcm_user_can_send(0, $this->factory->user->create())); // Non-logged-in
    }
}
```

### Integration Tests
```php
// Test REST API endpoints
class BCM_REST_Tests extends WP_Test_REST_TestCase {
    
    public function test_send_message_endpoint() {
        $sender = $this->factory->user->create();
        $recipient = $this->factory->user->create();
        
        wp_set_current_user($sender);
        
        $request = new WP_REST_Request('POST', '/bcm/v1/messages');
        $request->set_param('recipient_id', $recipient);
        $request->set_param('subject', 'Test Subject');
        $request->set_param('message', 'Test Message');
        
        $response = $this->server->dispatch($request);
        
        $this->assertEquals(200, $response->get_status());
        $data = $response->get_data();
        $this->assertTrue($data['success']);
    }
}
```

## Contributing

### Code Standards
- Follow WordPress Coding Standards
- Use PHP 7.4+ features when appropriate
- Document all functions with proper PHPDoc
- Include unit tests for new features

### Pull Request Process
1. Fork the repository
2. Create feature branch
3. Write tests for new functionality
4. Ensure all tests pass
5. Submit pull request with description

### Development Setup
```bash
# Clone repository
git clone https://github.com/wbcomdesigns/buddypress-contact-me.git

# Install dependencies
npm install
composer install

# Run tests
npm run test
composer test

# Build assets
npm run build
```

This developer guide provides comprehensive information for extending and customizing BuddyPress Contact Me. For additional support, refer to the plugin documentation or contact the development team.
