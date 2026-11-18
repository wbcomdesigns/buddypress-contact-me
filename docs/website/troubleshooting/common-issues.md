# Common Issues & Frequently Asked Questions

A walkthrough of issues that actually come up against v1.5.0, with the real fix for each.

## "BuddyPress is not active" notice on activation

The plugin requires BuddyPress (or BuddyBoss Platform). Install and activate BuddyPress, then activate Contact Me. The notice clears automatically.

## Contact tab does not appear on a member's profile

Three independent gates have to all open before the tab shows. Check them in this order:

1. **Show a "Contact" tab on every member profile** — go to **Access** tab and confirm the master toggle is on.
2. **Recipient role allowed** — confirm the member's role is ticked in **Who can be contacted**. Administrators are always allowed regardless.
3. **Member opt-out** — visit the member's BuddyPress **Settings → General** as them and confirm **Let other members contact me** is ticked. New users default to opted-in; this only matters if they explicitly turned it off.

If all three are open and the tab still does not render, clear any object cache and reload — BP nav items can be cached by site-level caching plugins.

## Logged-out visitors see no form

Open **Access → Who can send messages** and tick **Visitors (not logged in)**. Without this, `BCM_Frontend_Nav::viewer_can_send()` returns false for unauthenticated users and the form is hidden.

## Messages send but no email arrives

Check, in this order:

1. **WB Plugins → Contact Me → Notifications** — is **Email notification** on?
2. **WordPress core email delivery** — does any other email work? (e.g., new user notifications, password resets.) If not, install a transactional email plugin like FluentSMTP, WP Mail SMTP, or Post SMTP. The plugin uses `bp_send_email()`, which uses `wp_mail()`, which depends on the host's `mail()` function being able to actually send.
3. **BuddyPress email post exists** — at **Dashboard → Emails**, look for "A member received a contact message". If missing, deactivate and reactivate Contact Me to re-run the installer.

## Captcha is wrong but the answer looks right

The captcha uses `wp_hash()` on the answer to derive a hash that is rendered into a hidden field. If you have any plugin or browser extension that strips hidden fields, modifies form data, or auto-fills aggressively, the hash and answer will not match. Test in a clean browser profile / incognito window — if it works there, the culprit is in your normal browser.

## Spam screen rejects a legitimate message

The built-in heuristic flags pharma, gambling, loan-spam, lottery wins, generic CTA phrasing, and three-or-more URLs in one message. If a real message hits one of these, soften the gate via the `bcm_spam_check` filter — see [Hooks & filters](../developer-guide/hooks-and-filters.md). For a quick pass-through:

```php
add_filter( 'bcm_spam_check', '__return_false', 99, 3 );
```

That disables the heuristic entirely. Use sparingly.

## Inbox shows zero unread but notifications bell shows a count

The unread count in the **Contact** nav item and the **Unread** filter both query the BuddyPress notifications table for `is_new = 1` rows. If the bell shows a higher count, those entries are unrelated to Contact Me — they are other BuddyPress notifications (mentions, friend requests, group invites) and the discrepancy is expected.

## Old `/contact-me/` URLs in stored emails or bookmarks

These work — `BCM_Frontend_Nav::redirect_legacy_slug()` 301-redirects them to the current `/contact/` slug. No action required. If you want to update old emails for clarity, do so when you have the chance.

## License activation fails with "not active for this URL"

Your key is bound to another site that has used up your activation slots. Either:

1. Visit the other site, go to **License**, and click **Deactivate License** — that frees the slot. Then activate on the new site.
2. Upgrade your license at wbcomdesigns.com to a higher site-count tier.

## Multisite — does it work?

Yes. Each subsite installs its own database table on activation and has its own settings. License activation is per-site too, so plan your license tier accordingly. There is no network-wide settings sync.

## Can I import old messages from another contact-form plugin?

Not via a UI. The data lives in `{prefix}contact_me` with columns `id`, `sender`, `reciever` (note the historical typo, preserved for backwards compatibility), `subject`, `message`, `name`, `email`, `datetime`. Inserting rows that match this schema is enough — the plugin reads them on the next page load. Use `wp db query` or a migration script.

## Can I export messages?

There is no built-in export. Run a SQL query against the table and dump to CSV:

```bash
wp db query "SELECT id, sender, reciever, subject, datetime FROM wp_contact_me ORDER BY datetime DESC" --skip-column-names > messages.tsv
```

For full body export include `message`. Keep the file private — messages are sensitive.

## How do I uninstall and remove all data?

Deactivate **and delete** the plugin from **Plugins → Installed Plugins**. The `uninstall.php` routine removes the database table, the plugin options, and the plugin-specific user-meta keys. Plain deactivation does not wipe data, so you can deactivate and reactivate without losing messages.

## Where do I report a bug or request a feature?

Open a ticket from your wbcomdesigns.com account, or email support directly. Include the WordPress version, BuddyPress version, PHP version, and a copy of the plugin's debug log (`wp-content/debug.log` with `WP_DEBUG` on). For visual issues, attach a screenshot.
