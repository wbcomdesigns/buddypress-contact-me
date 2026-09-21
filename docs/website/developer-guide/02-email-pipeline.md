# BuddyPress Email Pipeline and Tokens

Contact-message emails are not custom-templated by the plugin. They go through `bp_send_email()` against a BuddyPress email post installed on activation, so every email inherits the standard BuddyPress header, body card, footer, and unsubscribe handling.

## Email type

| Field | Value |
|-------|-------|
| Email type slug | `bcm-contact-message` |
| Constant        | `BCM_Email_Installer::TYPE` |
| Post type       | `bp-email` (BuddyPress core) |
| Taxonomy        | `bp-email-type` (BuddyPress core) |
| Default subject | `[{{{site.name}}}] {{sender.name}} sent you a contact message` |
| Situation shown in BP Emails | "A member (or visitor) sent a contact message from your profile." |

The post is created idempotently on activation and on every version bump via `BCM_Email_Installer::install()`. Once the email-type term exists, the installer is a no-op, so admin edits at **Dashboard > Emails** are never overwritten on upgrade.

## Tokens

These tokens are registered at send time in `BCM_Frontend_Notifications::build_tokens()` and resolved by `bp_send_email()`:

| Token | Resolves to |
|-------|-------------|
| `{{sender.name}}`     | Display name of the sender, or the guest-supplied name when the sender is `0`. |
| `{{recipient.name}}`  | Recipient's display name (`display_name` from `get_userdata()`). |
| `{{contact.subject}}` | Message subject, stripped of HTML and slashes. |
| `{{contact.message}}` | Message body, stripped of HTML and slashes and run through `wpautop()`. Use triple braces in the template (`{{{contact.message}}}`) because it embeds HTML. |
| `{{inbox.url}}`       | Deep link to `/contact/inbox/{message-id}/` on the recipient's profile. Use triple braces (`{{{inbox.url}}}`) because it embeds a URL. |

BP-native tokens such as `{{{site.name}}}`, `{{recipient.email}}`, and `{{unsubscribe}}` are provided by the framework, not this plugin.

## Default email content

```text
Subject: [{{{site.name}}}] {{sender.name}} sent you a contact message

<a href="{{{inbox.url}}}">{{sender.name}}</a> sent you a contact message:

<strong>{{contact.subject}}</strong>

<blockquote>{{{contact.message}}}</blockquote>

<a href="{{{inbox.url}}}">Open the message in your inbox</a> to reply or delete it.
```

This is the value emitted by `BCM_Email_Installer::schema()` on activation. After the post exists, all edits happen at **Dashboard > Emails**. There is no plugin setting that mirrors the body content.

## Recipient assembly

`BCM_Frontend_Notifications::collect_recipients()` builds the recipient list per send:

1. Always includes the message recipient, by user ID.
2. If **Sender copy** is on:
   - Sender is a member, adds them by user ID.
   - Sender is a guest, adds them as a `BP_Email_Recipient( $email, $name )` so BuddyPress does not look up an email-only string against the users table (which raises undefined-property warnings on PHP 8.2 and above).
3. If **Site admin copy** is on, adds every user with the `administrator` role.

Recipients are de-duplicated by user ID and by lowercase email, so a member who is also an admin never receives a duplicate.

## Per-recipient delivery

`bp_send_email()` is called once with the full recipient list. BuddyPress sends one email per recipient internally, so recipients never see each other's address in any header. There is no BCC fan-out from this plugin's code.

## Output buffering

`send_email()` runs inside the `bp_contact_me_form_save` action, which fires from the submit request. To keep any third-party hook from emitting whitespace or notices into the JSON response, the plugin output-buffers the `bp_send_email()` call:

```php
$ob_started = ob_start();
try {
    bp_send_email( BCM_Email_Installer::TYPE, $recipients, array( 'tokens' => $tokens ) );
} finally {
    if ( $ob_started ) {
        $leaked = ob_get_clean();
        if ( $leaked && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
            error_log( '[bcm] bp_send_email leaked output: ' . wp_strip_all_tags( $leaked ) );
        }
    }
}
```

When `WP_DEBUG` is on, leaked output is logged so root causes are still visible, without breaking the response.

## What you cannot do

- There is no `bcm_email_subject` or `bcm_email_body` filter. Edit the post at **Dashboard > Emails**.
- There is no per-role email template. All roles use the same email post.
- There is no scheduled or digest mode. Emails are sent in real time during the submit request.

For conditional templating, unhook `BCM_Frontend_Notifications::send_email()` and reimplement it in your own plugin against the same `bp_contact_me_form_save` action.
