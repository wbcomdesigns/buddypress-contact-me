# Customising Behaviour Without Forking

This page covers the supported ways to change Contact Me behaviour from a sister plugin or a child theme's `functions.php`. Stay on these patterns and you keep the upgrade path clean.

## Customise the email content

Do not filter the email, edit the post.

1. Go to **Dashboard > Emails**.
2. Find the entry whose situation is "A member (or visitor) sent a contact message from your profile."
3. Click **Edit** and tweak the subject, body, and token usage.

Once you edit the post, the plugin will not overwrite your changes on upgrade. `BCM_Email_Installer::install()` exits early when the email-type term already exists.

The plugin-provided tokens are documented in [Email pipeline](02-email-pipeline.md): `{{sender.name}}`, `{{recipient.name}}`, `{{contact.subject}}`, `{{{contact.message}}}`, `{{{inbox.url}}}`.

## React to a saved message

The single post-save integration point is `bp_contact_me_form_save`:

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    // Mirror the message into a CRM, append to a Slack channel, sync to a webhook.
}, 30, 3 );
```

The plugin's notification and email handlers run on this same action at priorities 10 and 20, so a handler at priority 30 or later runs after them and can rely on the message being fully saved.

## Soften or harden the spam screen

Use the `bcm_spam_check` filter:

```php
// Allow editors to post link-heavy messages.
add_filter( 'bcm_spam_check', function ( $is_spam, $message, $reason ) {
    if ( 'too_many_urls' === $reason && current_user_can( 'edit_posts' ) ) {
        return false;
    }
    return $is_spam;
}, 10, 3 );
```

The filter fires once per submission with the final verdict: on the first matched spam pattern, on the URL-count gate, or on a clean pass. See [Hooks and filters](01-hooks-and-filters.md) for the exact semantics.

## Add a custom admin tab

Register a tab slug with `bcm_admin_tabs`:

```php
add_filter( 'bcm_admin_tabs', function ( $tabs ) {
    $tabs['logs'] = array(
        'label' => __( 'Logs', 'my-plugin' ),
        'icon'  => 'dashicons-list-view',
        'group' => 'settings',
    );
    return $tabs;
} );
```

The renderer maps a fixed set of slugs to view files, so a brand-new slug falls back to the Overview view. To render your own tab content, hook your plugin's own render (for example on `admin_init`, checking `$_GET['page']` and `$_GET['tab']`) rather than relying on the plugin to load a view for an unknown slug.

## Restyle or replace a template partial

The plugin's front-end partials live in `public/partials/`:

- `tab-form.php` - the contact form.
- `tab-inbox.php` - the inbox listing.
- `tab-message.php` - the single-message view.
- `tab-preferences.php` - the per-member opt-out toggle on the member's own profile.

These partials are included directly and are not routed through `locate_template()`, so theme-level template overrides are not supported. To change the markup, either:

1. Unhook the screen callbacks (`BCM_Frontend_Nav::screen_inbox`, `screen_send`, and so on) and re-register your own, or
2. Use CSS to restyle the existing markup. Every visible element has a stable `bcm-*` class.

## Disable a sub-feature

Each subsystem is its own class registered by the loader. There is no global `bcm()` accessor, so to remove the email handler you unhook the `send_email` callback on `bp_contact_me_form_save` at the priority it was added (20), using a reference to the `BCM_Frontend_Notifications` instance you obtain from the action binding. If that is impractical, unhook the whole action and re-implement only the behaviour you want.

## Filter the hub's wrapper helper slugs

If a sister plugin registers boilerplate helper pages that should not show up as separate cards on the WB Plugins hub, add their slugs to `wbcom_hub_wrapper_helper_slugs`:

```php
add_filter( 'wbcom_hub_wrapper_helper_slugs', function ( $slugs ) {
    $slugs[] = 'my-plugin-helper-page';
    return $slugs;
} );
```

Every migrated Wbcom plugin participates in this same filter.

## What is intentionally not extensible

- Validation rules (length limits on subject, message, name) are not filterable. They are content-quality controls, not preferences.
- Recipient-list assembly is internal. Admin copy and sender copy are the only two configurable add-on recipients.
- The math captcha is not pluggable. Replacing it with reCAPTCHA or hCaptcha means unhooking the submit handler and reimplementing the submit pipeline in your own plugin.

## Versioning promise

The hooks documented here and in [Hooks and filters](01-hooks-and-filters.md), plus the REST routes in [REST API](00-rest-api.md), are the only surface guaranteed stable across minor versions. Internal class methods, private hook arguments, and partial markup may change between minor releases without a deprecation notice. Bind to the public surface.
