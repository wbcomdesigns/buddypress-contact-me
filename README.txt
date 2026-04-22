=== Wbcom Designs - BuddyPress Contact Me ===
Contributors: wbcomdesigns
Donate link: https://wbcomdesigns.com/
Tags: buddypress, contact, profile, messaging, community
Requires at least: 5.0
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

= 1.5.0 =
* New: Brand-new admin UI — every screen was rewritten as a clean card-panel layout with a sidebar, proper page header, and plain-English labels. No more legacy wrapper chrome.
* New: "WB Plugins" hub — all Wbcom plugins now share a single top-level menu with a card-grid dashboard, so the admin sidebar stays tidy even when a site runs the full community bundle. Legacy (un-migrated) Wbcom plugins coexist cleanly with the new hub.
* New: Overview dashboard with live counts of total messages, unique senders, unique recipients, and members with the Contact Me button on, plus a snapshot of the current configuration at a glance.
* New: Dedicated Notifications, Email Template, Access, and License tabs — each one focused on a single decision so site owners don't have to hunt across screens.
* New: License tab directly inside Contact Me — enter, activate, and check your license key without leaving the plugin's admin page.
* Improvement: Access tab uses a role-chip grid with select-all / clear-all actions instead of a selectize dropdown. Much friendlier on mobile and faster to scan.
* Improvement: Admin assets load only on our own screens and the shared hub — other admin pages are untouched, reducing conflicts with third-party plugins.
* Improvement: Toast notifications and an accessible confirmation modal replace browser alerts and confirms across the admin.
* Improvement: Plain-language labels across every settings screen — "Notify the recipient", "Send a copy to…", "Who can send messages", "Who can be contacted". No jargon or internal option names.
* Improvement: Per-recipient email delivery by default (each recipient gets their own email) with an explicit toggle for the multi-recipient "show other recipients" mode.
* Improvement: Activation routine bookmarks the current plugin version so future upgrades have a reliable comparison point.
* Compatibility: Tested against WordPress 6.9, BuddyPress 14.x, BuddyBoss Platform, and PHP 8.4.

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
