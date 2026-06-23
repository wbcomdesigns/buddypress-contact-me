=== Wbcom Designs - BuddyPress Contact Me ===
Contributors: wbcomdesigns
Donate link: https://wbcomdesigns.com/
Tags: buddypress, contact, profile, messaging, community
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Put a private contact form on every member profile, with BuddyPress notifications, email delivery, role-based access, and a clean admin.

== Description ==

BuddyPress Contact Me drops a private contact form into every member's BuddyPress profile. Logged-in members can message each other directly; non-logged-in visitors can reach members too, so your community stays reachable without anyone sharing personal email addresses.

Highlights:

* A "Contact" tab on every member profile with a modal or inline form.
* BuddyPress notifications + email notifications, with optional admin and sender copies.
* Editable email template with placeholders for sender, subject, message, and site name.
* Role-based access control: choose who can send messages and whose profiles show the form.
* Per-member opt-out — members can hide their own form even when their role allows it.
* Clean, modern admin UI shared across every Wbcom plugin via the "WB Plugins" hub.

== Installation ==

1. Upload the `buddypress-contact-me` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WB Plugins → Contact Me and configure notifications, email template, and access rules.

== Changelog ==

= 1.5.0 - June 2026 =

* New      - Rebuilt admin UI as a clean card-panel layout with a sidebar, page header, and plain-English labels, replacing the legacy wrapper chrome.
* New      - WB Plugins hub gives every Wbcom plugin a single top-level menu with a card-grid dashboard so the admin sidebar stays tidy.
* New      - Overview dashboard shows live counts of total messages, unique senders, unique recipients, and members with the Contact Me button on.
* New      - Dedicated Notifications, Email Template, Access, and License tabs, each focused on a single decision.
* New      - License tab lets you enter, activate, and check your license key without leaving the plugin's admin page.
* Improve  - Inbox list view is now ready for accounts with thousands of received messages via new composite and single-column indexes added on upgrade.
* Improve  - Unread-messages count uses a dedicated SELECT COUNT(*) instead of loading every unread ID into PHP, cutting memory use on large accounts.
* Improve  - Unread filter is capped at 1,000 rows to prevent max_allowed_packet errors on extreme accounts.
* Improve  - Inbox rows batch-prefetch all sender users before rendering, eliminating the previous N+1 queries on a cold cache.
* Improve  - Access tab uses a role-chip grid with select-all and clear-all actions instead of a dropdown, friendlier on mobile.
* Improve  - Admin assets load only on the plugin's own screens and the shared hub, reducing conflicts with third-party plugins.
* Improve  - Toast notifications and an accessible confirmation modal replace browser alerts and confirms across the admin.
* Improve  - Per-recipient email delivery by default, with an explicit toggle for the multi-recipient show-other-recipients mode.
* Improve  - Public REST calls now route through a centralised wrapper with a 15-second timeout so the UI always recovers from a hung server.
* Fix      - Frontend Contact tab no longer paints WordPress-admin blue over the active theme, keeping each theme's accent color intact.
* Fix      - Contact tab now follows the host theme's dark mode on BuddyX, BuddyX Pro, and Reign with readable surfaces and text.
* Fix      - Guest contact submissions no longer fail with an "Invalid JSON response" error.
* Fix      - Settings tabs no longer wipe each other's fields on save, and the Access tab "Clear all" now persists empty role grids.
* Fix      - License deactivate button now actually deactivates the license.
* Fix      - Overview "Edit Email Template" now opens the real BuddyPress email post.
* Fix      - Resolved guest-email property warnings, an undefined admin-page variable warning, and a missing Visitors option on Who Can Send Messages.
* Fix      - Loads the text domain on init to clear the WordPress 6.7+ just-in-time translation notice.
* Security - License activation and deactivation now require the manage_options capability in addition to the nonce check, blocking lower-privileged users.
* Compat   - Tested against WordPress 6.9, BuddyPress 14.x, BuddyBoss Platform, and PHP 8.4.

= 1.4.0 =
* Fixed: Notification and message display issues for both logged-in and logged-out users.
* Improved: Contact tab UI, including icon, gravatar, and notice styling.
* Enhanced: CAPTCHA with new layout, client-side and server-side validation, and CSRF protection.
* Improved: Form validation UX and added security with proper input handling and output escaping.
* Fixed: Message count, pagination, and settings visibility issues in BuddyPress and BuddyBoss.
* Updated: RTL support, dark mode compatibility, and general UI cleanup.
* Cleaned: Deprecated code, replaced outdated jQuery functions, and resolved PHPCS issues.
* Updated: Translation files, documentation links, and internal code structure.

= 1.3.0 =
* Fixed: Issue to dynamically change email content as specified by the admin.
* Fixed: Fatal error when notifications component is disabled, including admin settings message.
* Fixed: Plugin activation issue during bulk activation.
* Fixed: Redirection links related to plugin documentation and feedback form.
* Fixed: Fatal error related to BP function during plugin activation.
* Fixed: Mobile view table issue with BuddyX Pro theme.
* Fixed: Enable/disable contact tab button issue with BuddyX Pro theme.
* Updated: Improved `.pot` file for translations.
* Updated: Removed hard-coded dependency for better flexibility.
* Enhanced: Logic to hide the "Contact Me" tab for restricted roles and logged-out users.

= 1.2.2 =
* Fix: UI Issue with Buddyboss theme
* Fix: Translate
* Fix: Options are not enabling and deprecated issue
* Fix: Message send issue
* Fix: Update issue
* Fix: PHP warning
* Managed: Selectize to initiate contact

= 1.2.1 =
* Fix: The license does not deactivate if the response is failed.

= 1.2.0 =
* Fix: (#38) Fixed PHPv8 warnings 
* Fix: (#39) Fixed issue in email subject name
* Fix: Updated code of bp12 function replacement
* Fix: (#14, #26) Fixed conflict notification issue with member review and blog pro

= 1.1.2 =
* Updated: Admin label and description
* Fix: (#34,35) Fatal Error on sending a contact message
* Fix: (#33) Fatal error on plugin activation
* Fix: Text domain issue
* Fix: BP v12 fixes
* Fix: License fixes
* Fix: PHPCS fixes
* Fix: (#24) Double license page issue
* Fix: (#25) Issue with PHP 8.2

= 1.1.1 =
* Fix: Fixed update now the link is not showing

= 1.1.0 =
* Fix: Set Only Inactive when the license key deactivates
* Fix: Update License activation file and set response in transient

= 1.0.0 =
* Initial Release
