# BuddyPress Email Pipeline & Tokens

Contact-message emails are not custom-templated by the plugin. They go through `bp_send_email()` against an email post type entry installed on activation, so every email inherits the standard BuddyPress header, body card, footer, and unsubscribe handling.

## Email type

| Field | Value |
|-------|-------|
| Email type slug | `bcm-contact-message` |
| Constant        | `BCM_Email_Installer::TYPE` |
| Post type       | `bp-email` (BuddyPress core) |
| Taxonomy        | `bp-email-type` (BuddyPress core) |
| Admin label     | "A member received a contact message" |

The post is created idempotently on activation and on every version bump via `BCM_Email_Installer::install()`. Once the post exists — i.e. once the term is in the database — the installer is a no-op, so admin edits at **Dashboard → Emails** are never overwritten on future plugin upgrades.

## Tokens

These tokens are registered at send time in `BCM_Frontend_Notifications::build_tokens()` and resolved by `bp_send_email()` against your edited email post:

| Token | Resolves to |
|-------|-------------|
| `{{sender.name}}`     | Display name of the sender, or the guest-supplied name when sender is `0`. |
| `{{recipient.name}}`  | Recipient's display name (`display_name` from `get_userdata()`). |
| `{{contact.subject}}` | Message subject, stripped of HTML and slashes. |
| `{{contact.message}}` | Message body, stripped of HTML, slashed cleared, run through `wpautop()` for paragraphing. Triple-braces in the template (`{{{contact.message}}}`) because it embeds HTML. |
| `{{inbox.url}}`       | Deep link to `/contact/inbox/{message-id}/` on the recipient's profile. Triple-braces in the template (`{{{inbox.url}}}`) because it embeds a URL. |

BP-native tokens like `{{{site.name}}}`, `{{recipient.email}}`, and `{{unsubscribe}}` are also available — provided by the framework, not this plugin.

## Default email content

```text
Subject: [{{{site.name}}}] {{sender.name}} sent you a contact message

<a href="{{{inbox.url}}}">{{sender.name}}</a> sent you a contact message:

<strong>{{contact.subject}}</strong>

<blockquote>{{{contact.message}}}</blockquote>

<a href="{{{inbox.url}}}">Open the message in your inbox</a> to reply or delete it.
```

This is the value emitted by `BCM_Email_Installer::schema()` on activation. After the post exists, all edits happen at **Dashboard → Emails → "A member received a contact message"** — there is no plugin setting that mirrors the body content.

## Recipient assembly

`BCM_Frontend_Notifications::collect_recipients()` builds the recipient list per send:

1. Always includes the message recipient (by user ID).
2. If **Sender copy** is on:
   - Sender is a member → adds them by user ID.
   - Sender is a guest → adds them as a `BP_Email_Recipient(email, name)` so BuddyPress does not try to look up an email-only string against the users table (avoids PHP 8.2+ undefined-property warnings).
3. If **Site admin copy** is on, adds every user with the `administrator` role.

Recipients are de-duplicated by user ID and lowercase email address — you can never get a duplicate email even if a member is also an admin.

## Per-recipient delivery

`bp_send_email()` is called once with the full recipient list. BuddyPress sends one email per recipient internally, so recipients never see each other's email addresses in any header — there is no BCC fan-out from this plugin's code, BuddyPress handles each address as a private send.

## Output buffering

`send_email()` runs inside the `bp_contact_me_form_save` action, which fires from the REST submit callback. To prevent any third-party hook on `bp_send_email`-internal actions from emitting whitespace or notices into the JSON response stream, the plugin output-buffers the entire `bp_send_email()` call:

```php
$ob_started = ob_start();
try {
    bp_send_email( BCM_Email_Installer::TYPE, $recipients, array( 'tokens' => $tokens ) );
} finally {
    if ( $ob_started ) {
        $leaked = ob_get_clean();
        if ( $leaked && WP_DEBUG ) {
            error_log( '[bcm] bp_send_email leaked output: ' . wp_strip_all_tags( $leaked ) );
        }
    }
}
```

When `WP_DEBUG` is on, leaked output is captured and logged via `error_log()` so root causes are still surfaced — they just do not break apiFetch with "response is not a valid JSON response".

## What you cannot do

- There is no `bcm_email_subject` or `bcm_email_body` filter — edit the post at **Dashboard → Emails** instead.
- There is no per-role email template — all roles use the same email post.
- There is no scheduled / digest mode — emails are sent in real time during the submit request.

If you need conditional templating (different copy for VIP recipients, for example), unhook `BCM_Frontend_Notifications::send_email()` and reimplement it in your own plugin against the same `bp_contact_me_form_save` action.
