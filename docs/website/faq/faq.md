# Frequently Asked Questions

Short answers about multisite, data, privacy and support.

## Multisite - does it work?

Yes. Each subsite installs its own database table on activation and has its own settings. License activation is per-site too, so plan your license tier accordingly. There is no network-wide settings sync.

## Can I import old messages from another contact-form plugin?

Not via a UI. The data lives in `{prefix}contact_me` with columns `id`, `sender`, `reciever` (note the historical typo, preserved for backwards compatibility), `subject`, `message`, `name`, `email`, `datetime`. Inserting rows that match this schema is enough - the plugin reads them on the next page load. Use `wp db query` or a migration script.

## Can I export messages?

There is no built-in export. Run a SQL query against the table and dump to CSV:

```bash
wp db query "SELECT id, sender, reciever, subject, datetime FROM wp_contact_me ORDER BY datetime DESC" --skip-column-names > messages.tsv
```

For full body export include `message`. Keep the file private - messages are sensitive.

## How do I uninstall and remove all data?

Deactivate **and delete** the plugin from **Plugins → Installed Plugins**. The `uninstall.php` routine removes the database table, the plugin options, the plugin-specific user-meta keys and the plugin's temporary data (captcha and send-limit counters). Plain deactivation does not wipe data, so you can deactivate and reactivate without losing messages.

## What happens to messages when a member is deleted?

Messages the deleted member received are deleted with the account, in batches of 500. Messages they sent stay in their recipients' inboxes.

## Where do I report a bug or request a feature?

Open a ticket from your wbcomdesigns.com account, or email support directly. Include the WordPress version, BuddyPress version, PHP version, and a copy of the plugin's debug log (`wp-content/debug.log` with `WP_DEBUG` on). For visual issues, attach a screenshot.
