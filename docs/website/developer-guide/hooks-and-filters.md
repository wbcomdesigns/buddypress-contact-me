# Action Hooks & Filters Reference

This is the complete extension surface in 1.5.1. Every hook below exists in source - there are no legacy `bcm_before_*` / `bcm_after_*` hooks even if older docs or third-party tutorials reference them; those were never shipped.

## Action: `bp_contact_me_form_save`

Fires after a contact message has been validated, sanitised, and saved to the `{prefix}contact_me` table. The plugin's own notification and email handlers also subscribe to this action - your custom handler runs alongside them.

```php
do_action( 'bp_contact_me_form_save', $message_id, $recipient_id, $sender_id );
```

**Parameters:**

- `int $message_id` - primary key in the contact_me table.
- `int $recipient_id` - the user who will receive the message.
- `int $sender_id` - the user who sent it; `0` for guest submissions.

**Source:** `includes/frontend/class-bcm-frontend-submit.php` line 254.

**Subscribers shipped with the plugin:**

- `BCM_Frontend_Notifications::queue_notification()` at priority 10 - fires the BuddyPress notification.
- `BCM_Frontend_Notifications::send_email()` at priority 20 - fires the BuddyPress-templated email.

**Example - log every submission:**

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    error_log( sprintf( '[bcm] message %d saved (sender %d → recipient %d)', $message_id, $sender_id, $recipient_id ) );
}, 10, 3 );
```

**Example - push to Slack:**

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    $msg = BCM_Messages_Repo::find( $message_id );
    wp_remote_post( SLACK_WEBHOOK, array(
        'body' => wp_json_encode( array( 'text' => sprintf( 'New contact message: %s', $msg->subject ) ) ),
    ) );
}, 30, 3 );
```

## Filter: `bcm_spam_check`

Wraps the built-in spam heuristic so you can soften, harden, or replace it without forking. The filter runs three times during a single message validation pass - once for each pattern category and once for the URL count check. Your callback returns the final boolean.

```php
apply_filters( 'bcm_spam_check', $is_spam, $message, $reason );
```

**Parameters:**

- `bool $is_spam` - current verdict from the heuristic.
- `string $message` - the submitted message body.
- `string $reason` - either the matched regex pattern or the literal string `'too_many_urls'` for the URL count gate, or empty string for the final negative pass.

**Source:** `includes/frontend/class-bcm-frontend-submit.php` lines 384, 390, 393.

**Example - whitelist a legitimate URL count for "share my links" pages:**

```php
add_filter( 'bcm_spam_check', function ( $is_spam, $message, $reason ) {
    if ( 'too_many_urls' === $reason && current_user_can( 'edit_posts' ) ) {
        return false; // Editors can post link-heavy messages.
    }
    return $is_spam;
}, 10, 3 );
```

**Example - add an extra rejection rule:**

```php
add_filter( 'bcm_spam_check', function ( $is_spam, $message, $reason ) {
    if ( $is_spam ) {
        return $is_spam; // Don't override a positive.
    }
    if ( preg_match( '/\\b(?:bitcoin|crypto|nft)\\b/i', $message ) ) {
        return true;
    }
    return $is_spam;
}, 10, 3 );
```

## Filter: `bcm_rate_limit`

Sets how many contact messages one sender may send in a time window. Members are counted by user ID, visitors by IP address. Once the limit is reached the next message is refused with "You have sent several messages in a short time. Please wait a few minutes and try again." (REST status 422, error code `bcm_throttled`).

```php
apply_filters( 'bcm_rate_limit', array( 'limit' => 5, 'window' => 600 ), $sender_id );
```

**Parameters:**

- `array $rule` - `limit` (messages allowed, `0` turns the limit off) and `window` (seconds).
- `int $sender_id` - the sender, `0` for a visitor.

**Source:** `includes/frontend/class-bcm-frontend-submit.php` line 284.

**Example - allow editors 20 messages an hour:**

```php
add_filter( 'bcm_rate_limit', function ( $rule, $sender_id ) {
    if ( $sender_id && user_can( $sender_id, 'edit_others_posts' ) ) {
        return array( 'limit' => 20, 'window' => HOUR_IN_SECONDS );
    }
    return $rule;
}, 10, 2 );
```

## Filter: `bcm_display_timezone`

Sets the timezone every Contact Me date is shown in (inbox, single message, license screen). Messages are stored in UTC; the default display is the site timezone.

```php
apply_filters( 'bcm_display_timezone', wp_timezone(), $user_id );
```

**Source:** `includes/functions.php` line 27.

## Filter: `bcm_license_api_sslverify`

Whether license calls to wbcomdesigns.com verify the SSL certificate. Default `true`. Only turn it off on a local or staging host with a broken certificate store.

**Source:** `edd-license/edd-plugin-license.php` line 124.

## Theme integration: `reign_bp_nav_icons`

Contact Me adds its tab slugs to the Reign theme's `reign_bp_nav_icons` filter so the Contact, Inbox, Send message and Preferences tabs get matching icons. Other themes ignore it. To pick different icons, filter it after Contact Me (priority above 10) and change the `contact`, `inbox`, `send` or `preferences` keys.

## Filter: `bcm_admin_tabs`

Filters the array of admin tabs rendered in the Contact Me admin sidebar. Use this to add a custom tab from a sister plugin, or to rename / reorder the existing ones.

```php
apply_filters( 'bcm_admin_tabs', $tabs );
```

**Parameters:**

- `array $tabs` - keyed by tab slug. Each value is an array with `label`, `icon` (Dashicons class), and `group` (`'main'`, `'settings'`, or `'account'`).

**Source:** `includes/admin/class-bcm-admin.php` line 62.

Adding a tab via this filter alone is not enough - you also need to render the tab's contents. Look at how `BCM_Admin::render_page()` maps tab slugs to view files in `includes/admin/views/` and follow the same pattern from your own plugin.

## Filter: `wbcom_hub_wrapper_helper_slugs`

Filters the list of "helper page" slugs that legacy Wbcom-wrapper plugins register under the shared **WB Plugins** hub. Slugs in this list are filtered out of the hub landing card grid so they do not appear as duplicate plugin tiles.

```php
apply_filters( 'wbcom_hub_wrapper_helper_slugs', $slugs );
```

**Default value:** `[ 'wbcom-plugins-page', 'wbcom-themes-page', 'wbcom-support-page', 'wbcom-license-page' ]`.

**Source:** `includes/admin/views/hub.php` line 32 (shared Wbcom hub file).

This is a *cross-plugin* filter - it is shared with every Wbcom plugin that has migrated to the new hub pattern. If you ship a sister plugin with custom helper pages that should not show up as cards, add their slugs here.

## What does *not* exist

The following hooks are sometimes referenced in tutorials but **do not exist** in v1.5.0:

- `bcm_before_send_message` / `bcm_after_send_message` - replaced entirely by `bp_contact_me_form_save` above.
- `bcm_message_data` / `bcm_message_content` - message data is built inline in `process_submission()` and not filterable; modify it post-save via `bp_contact_me_form_save` instead.
- `bcm_email_subject` / `bcm_email_body` - the email is rendered through the BuddyPress email template; edit the post at **Dashboard → Emails** instead. See [Email pipeline](email-pipeline.md).
- `bcm_notification_recipients` - recipient assembly is internal to `BCM_Frontend_Notifications::collect_recipients()` and not filterable.
- `bcm_contact_form_visible` / `bcm_validate_message` / `bcm_message_saved` - none of these exist; they appear in some legacy third-party tutorials but were never shipped.

If you need behaviour that requires one of these missing hooks, open a feature request at wbcomdesigns.com and we will evaluate whether to add it in a future version.

## Helper functions that *do* exist

Public helpers you can call from PHP:

- `BCM_Frontend_Submit::process_submission( array $input )` - validates and inserts; returns `array{id,recipient,captcha}` (`captcha` is the next guest question or `null`) or `WP_Error`.
- `BCM_Frontend_Nav::user_accepts_contact( int $user_id )` - true if a user accepts contact and is in an allowed role group.
- `BCM_Frontend_Nav::viewer_can_send()` - true if the current viewer (member or guest) is allowed to send.
- `BCM_Messages_Repo::find( int $id )` - load a single message row.
- `BCM_Messages_Repo::list_for_recipient( int $user_id, int $per_page = 10, int $page = 1, bool $unread_only = false )` - paged inbox list; each row carries `is_unread`.
- `BCM_Messages_Repo::count_for_recipient( int $user_id )` - total messages.
- `BCM_Messages_Repo::count_unread_for_recipient( int $recipient_id )` - messages not opened yet.
- `BCM_Messages_Repo::delete_for_recipient( int $id, int $recipient_id )` - recipient-scoped delete.
- `BCM_Messages_Repo::delete_for_user( int $user_id )` - deletes every message a user received; runs on `deleted_user` and `wpmu_delete_user`.
- `bcm_date_label( $utc, $fallback = '', $format = null, $user_id = 0 )` - formats a stored UTC time in the display timezone.
- `bcm_notice( string $text, string $type = 'info' )` - prints a notice in BuddyPress `.bp-feedback` markup.

There is no `bcm_send_message()`, `bcm_get_messages()`, `bcm_user_can_send()`, or `bcm_get_message_count()` global function - those names appear in some legacy docs but the real API is the static methods above.
