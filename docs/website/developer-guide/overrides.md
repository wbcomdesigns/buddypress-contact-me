# Customising Behaviour Without Forking

This page covers the supported ways to change Contact Me behaviour from a sister plugin or your child theme's `functions.php`. Stay on these patterns and you keep the upgrade path clean.

## Customise the email content

Do not filter the email — edit the post.

1. Go to **Dashboard → Emails**.
2. Find "A member received a contact message".
3. Click **Edit** and tweak subject, body, and any token usage.

Once you have edited the post, the plugin will not overwrite your changes on future upgrades — `BCM_Email_Installer::install()` exits early when the email type term already exists.

The five tokens you have available are documented in [Email pipeline](email-pipeline.md): `{{sender.name}}`, `{{recipient.name}}`, `{{contact.subject}}`, `{{contact.message}}`, `{{inbox.url}}`.

## React to a saved message

The single integration point post-save is `bp_contact_me_form_save`:

```php
add_action( 'bp_contact_me_form_save', function ( $message_id, $recipient_id, $sender_id ) {
    // Mirror the message into a CRM, append to a Slack channel, sync to a webhook, etc.
} , 10, 3 );
```

The plugin's own notification and email handlers run on this same action at priorities 10 and 20, so a custom handler at priority 30+ runs after them and can rely on the message being fully saved and notifications dispatched.

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

The filter runs once per pattern category plus once for the URL count gate. See [Hooks & filters](hooks-and-filters.md) for full semantics.

## Add a custom admin tab

Use `bcm_admin_tabs` to register a tab slug, then map it to a view file using the same pattern `BCM_Admin::render_page()` follows:

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

The plugin's renderer maps tab slugs through a hard-coded `$view_map` array, so a brand-new slug will fall back to rendering the Overview view. To actually render your custom tab content, render it from your own plugin's hook (e.g., on `admin_init` check `$_GET['page']` and `$_GET['tab']`) and either echo before the shell or use a sister-plugin admin shell.

## Override a template partial

The plugin's frontend partials live in `public/partials/`:

- `tab-form.php` — the contact form.
- `tab-inbox.php` — the inbox listing.
- `tab-message.php` — the single-message view.
- `tab-preferences.php` — the per-member opt-out toggle on the member's own profile.

These partials are included directly via `BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/...'` — they are not filtered through `locate_template()`, so theme-level overrides are not supported. To customise the markup, either:

1. Unhook the screen callbacks (`BCM_Frontend_Nav::screen_inbox`, `screen_send`, etc.) and re-register your own. The hooks are added in `BCM_Frontend_Nav::register()`.
2. Or use CSS to restyle the existing markup — every visible element has a stable `bcm-*` class.

## Disable a sub-feature entirely

The plugin's main loader registers each subsystem as its own class. To disable email notifications without changing settings:

```php
add_action( 'bp_init', function () {
    // BCM hooks itself on bp_init via the loader; remove the email handler.
    remove_action( 'bp_contact_me_form_save', array( bcm()->frontend_notifications ?? null, 'send_email' ), 20 );
}, 99 );
```

Note: there is no global `bcm()` accessor in v1.5.0 — the example above is illustrative. In practice you would track the `BCM_Frontend_Notifications` instance via the action's class binding or replace the action wholesale by unhooking with a higher priority.

## Filter the hub's wrapper helper slugs

If your sister plugin registers boilerplate helper pages (a "Themes" page, an "About" page, etc.) that should not show up as separate cards on the WB Plugins hub landing, add their slugs to `wbcom_hub_wrapper_helper_slugs`:

```php
add_filter( 'wbcom_hub_wrapper_helper_slugs', function ( $slugs ) {
    $slugs[] = 'my-plugin-helper-page';
    return $slugs;
} );
```

This is the same filter every migrated Wbcom plugin participates in.

## What is intentionally not extensible

- Validation rules (length limits on subject, message, name) are not filterable. They are content-quality controls, not preferences.
- Recipient list assembly is internal — admin copy and sender copy are the only two configurable add-on recipients.
- The math captcha is not pluggable — replacing it with reCAPTCHA / hCaptcha would require unhooking `BCM_Frontend_Submit::register()` and reimplementing the entire submit pipeline. If you need that level of integration, ship your own plugin and unregister this one's classic-POST handler.

## Versioning promise

The hooks documented here and in [Hooks & filters](hooks-and-filters.md) plus the REST routes in [REST API](rest-api.md) are the *only* surface guaranteed to remain stable across minor versions. Internal class methods, private hook arguments, and template partial markup may change between minor releases without a deprecation notice — bind to the public surface.
