# Frequently Asked Questions

Common questions about how BuddyPress Contact Me behaves. For error symptoms and fixes, see [Troubleshooting](../troubleshooting/00-troubleshooting.md).

## Does it require BuddyPress?

Yes. The plugin needs BuddyPress or BuddyBoss Platform to be active. It reads BuddyPress user data and hooks BuddyPress nav, notifications, and email.

## Can senders and recipients reply back and forth inside the plugin?

No. This plugin is single-shot contact, not a threaded messenger. From the single-message view a recipient can reply by email or, when the sender is a member and the BuddyPress Messages component is active, start a BuddyPress private message. There is no in-plugin thread.

## Who can see a member's inbox?

Only that member. The single-message view enforces ownership, and a direct URL to another member's message ID returns "Message not found".

## Can guests contact members?

Yes, when the **Visitors (not logged in)** group is in the **Who can send messages** list. It is included by default. Guests solve a math captcha and provide a name and email. See [Guest contact](../features/01-guest-contact.md).

## Does it work on multisite?

Yes. Each subsite installs its own database table on activation and keeps its own settings. License activation is per site, so plan your license tier accordingly. There is no network-wide settings sync.

## Can I embed the form outside a profile?

Yes, with the `[buddypress-contact-me]` shortcode, targeting a member by `id` or `user` (login). See [Shortcode](../shortcodes/00-shortcode.md).

## Can I import old messages from another contact plugin?

Not through a UI. The data lives in `{prefix}contact_me` with columns `id`, `sender`, `reciever` (the column name is a historical typo, preserved for backwards compatibility), `subject`, `message`, `name`, `email`, `datetime`. Insert rows that match this schema and the plugin reads them on the next page load. Use `wp db query` or a migration script.

## Can I export messages?

There is no built-in export. Run a SQL query and dump to a file:

```bash
wp db query "SELECT id, sender, reciever, subject, datetime FROM wp_contact_me ORDER BY datetime DESC" --skip-column-names > messages.tsv
```

Include `message` for the full body. Keep the file private; messages are sensitive.

## Do I have to activate a license to use it?

No. Every feature works without a license. Only automatic updates and priority support are gated. See the [License tab](../settings/03-license-tab.md).

## How do I uninstall and remove all data?

Deactivate and then delete the plugin from **Plugins > Installed Plugins**. The uninstall routine drops the messages table, removes the plugin options, clears the plugin-specific user-meta, and deletes the BuddyPress email post and the plugin's notifications. Plain deactivation does not wipe data, so you can deactivate and reactivate without losing messages.

## Where do I report a bug or request a feature?

Open a ticket from your wbcomdesigns.com account, or email support. Include the WordPress, BuddyPress, and PHP versions, and, for a bug, a copy of `wp-content/debug.log` with `WP_DEBUG` on. For visual issues, attach a screenshot.
