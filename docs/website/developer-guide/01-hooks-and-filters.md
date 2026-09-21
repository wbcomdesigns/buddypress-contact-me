# Action Hooks and Filters Reference

This is the complete extension surface. Every hook below exists in source. There are no legacy `bcm_before_*` or `bcm_after_*` hooks even if older docs or third-party tutorials reference them; those were never shipped.

## Action: `bp_contact_me_form_save`

Fires after a contact message has been validated, sanitised, and saved to the `{prefix}contact_me` table. The plugin's own notification and email handlers subscribe to this action too, so your handler runs alongside them.

```php
do_action( 'bp_contact_me_form_save', $message_id, $recipient_id, $sender_id );
```

**Parameters:**

- `int $message_id` - primary key in the contact_me table.
- `int $recipient_id` - the user who will receive the message.
- `int $sender_id` - the user who sent it; `0` for guest submissions.

**Source:** `includes/frontend/class-bcm-frontend-submit.php`.

**Subscribers shipped with the plugin:**

- `BCM_Frontend_Notifications::queue_notification()` at priority 10 - fires the BuddyPress notification.
- `BCM_Frontend_Notifications::send_email()` at priority 20 - fires the BuddyPress-templated email.

**Example, log every submission:**

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    error_log( sprintf( '[bcm] message %d saved (sender %d to recipient %d)', $message_id, $sender_id, $recipient_id ) );
}, 10, 3 );
```

**Example, push to Slack after the built-in handlers:**

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    $msg = BCM_Messages_Repo::find( $message_id );
    wp_remote_post( SLACK_WEBHOOK, array(
        'body' => wp_json_encode( array( 'text' => sprintf( 'New contact message: %s', $msg->subject ) ) ),
    ) );
}, 30, 3 );
```

## Filter: `bcm_spam_check`

Wraps the built-in spam heuristic so you can soften, harden, or replace it without forking. The filter fires once per submission, with the final verdict:

- If a spam pattern matches, it fires immediately with `$is_spam = true` and `$reason` set to the matched regex pattern, and screening stops.
- If no pattern matches but the message has three or more URLs, it fires with `$is_spam = true` and `$reason = 'too_many_urls'`.
- If nothing matches, it fires once with `$is_spam = false` and an empty `$reason`.

```php
apply_filters( 'bcm_spam_check', $is_spam, $message, $reason );
```

**Parameters:**

- `bool $is_spam` - the heuristic's current verdict.
- `string $message` - the submitted message body.
- `string $reason` - the matched regex pattern, the literal `'too_many_urls'`, or an empty string on the clean pass.

**Source:** `includes/frontend/class-bcm-frontend-submit.php`.

**Example, allow a high URL count for editors:**

```php
add_filter( 'bcm_spam_check', function ( $is_spam, $message, $reason ) {
    if ( 'too_many_urls' === $reason && current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $is_spam;
}, 10, 3 );
```

**Example, add an extra rejection rule:**

```php
add_filter( 'bcm_spam_check', function ( $is_spam, $message, $reason ) {
    if ( $is_spam ) {
        return $is_spam; // Do not override a positive.
    }
    if ( preg_match( '/\\b(?:bitcoin|crypto|nft)\\b/i', $message ) ) {
        return true;
    }
    return $is_spam;
}, 10, 3 );
```

## Filter: `bcm_admin_tabs`

Filters the array of admin tabs rendered in the Contact Me admin sidebar. Use it to add a custom tab from a sister plugin, or to rename or reorder the existing ones.

```php
apply_filters( 'bcm_admin_tabs', $tabs );
```

**Parameters:**

- `array $tabs` - keyed by tab slug. Each value has `label`, `icon` (a Dashicons class), and `group` (`'main'`, `'settings'`, or `'account'`).

**Source:** `includes/admin/class-bcm-admin.php`.

Adding a tab via this filter alone is not enough. The renderer maps a fixed set of tab slugs to view files, so an unknown slug falls back to the Overview view. To render custom tab content, render it from your own plugin. See [Customising behaviour without forking](03-overrides.md).

## Filter: `wbcom_hub_wrapper_helper_slugs`

Filters the list of helper-page slugs that legacy Wbcom-wrapper plugins register under the shared **WB Plugins** hub. Slugs in this list are excluded from the hub's card grid so they do not appear as duplicate plugin tiles.

```php
apply_filters( 'wbcom_hub_wrapper_helper_slugs', $slugs );
```

**Default value:** `[ 'wbcom-plugins-page', 'wbcom-themes-page', 'wbcom-support-page', 'wbcom-license-page' ]`.

**Source:** `includes/admin/views/hub.php`.

This is a cross-plugin filter shared by every Wbcom plugin on the new hub. If a sister plugin ships helper pages that should not show up as cards, add their slugs here.

## What does not exist

The following hooks are sometimes referenced in tutorials but do not exist:

- `bcm_before_send_message` and `bcm_after_send_message` - replaced by `bp_contact_me_form_save`.
- `bcm_message_data` and `bcm_message_content` - message data is built inline in `process_submission()` and is not filterable. Modify it post-save via `bp_contact_me_form_save`.
- `bcm_email_subject` and `bcm_email_body` - the email is rendered through the BuddyPress email template. Edit the post at **Dashboard > Emails**. See [Email pipeline](02-email-pipeline.md).
- `bcm_notification_recipients` - recipient assembly is internal and not filterable.
- `bcm_contact_form_visible`, `bcm_validate_message`, `bcm_message_saved` - none of these exist.

## Helper methods that do exist

Public static methods you can call from PHP:

- `BCM_Frontend_Submit::process_submission( array $input )` - validates and inserts; returns `array{id,recipient}` or `WP_Error`.
- `BCM_Frontend_Nav::user_accepts_contact( int $user_id )` - true if a user accepts contact and is in an allowed role group.
- `BCM_Frontend_Nav::viewer_can_send()` - true if the current viewer (member or guest) is allowed to send.
- `BCM_Messages_Repo::find( int $id )` - load a single message row.
- `BCM_Messages_Repo::list_for_recipient( int $user_id, int $per_page = 10, int $page = 1 )` - paged inbox list.
- `BCM_Messages_Repo::count_for_recipient( int $user_id )` - total messages for a recipient (COUNT(*)).
- `BCM_Messages_Repo::unread_message_ids( int $recipient_id )` - IDs with active BP notifications (capped at 1,000, newest first).
- `BCM_Messages_Repo::count_unread_for_recipient( int $recipient_id )` - true unread COUNT(*).
- `BCM_Messages_Repo::delete_for_recipient( int $id, int $recipient_id )` - recipient-scoped delete.
- `BCM_Messages_Repo::insert( array $data )` - insert a row (keys: sender, recipient, subject, message, name, email).

There is no `bcm_send_message()`, `bcm_get_messages()`, `bcm_user_can_send()`, or `bcm_get_message_count()` global function. The real API is the static methods above.
