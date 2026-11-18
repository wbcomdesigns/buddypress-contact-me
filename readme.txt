=== Wbcom Designs - BuddyPress Contact Me ===
Contributors: wbcomdesigns
Donate link: https://wbcomdesigns.com/
Tags: buddypress, contact, profile, messaging, community
Requires at least: 6.5
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Put a private contact form on every member profile, with BuddyPress notifications, email delivery, role-based access, and a clean admin.

== Description ==

BuddyPress Contact Me drops a private contact form into every member's BuddyPress profile. Logged-in members can message each other directly; non-logged-in visitors can reach members too, so your community stays reachable without anyone sharing personal email addresses.

Highlights:

* A "Contact" tab on every member profile with a private contact form, plus a shortcode for any page.
* BuddyPress notifications + email notifications, with optional admin and sender copies.
* Three editable BuddyPress emails (to the member, a sender copy, an admin copy) with placeholders for sender, recipient, subject, message, and inbox link.
* Role-based access control: choose who can send messages and whose profiles show the form.
* Per-member opt-out under Contact > Preferences - members can hide their own form even when their role allows it.
* Spam protection for visitors: a one-time math question or your CAPTCHA Manager service, a hidden trap field, and a per-sender send limit.
* Clean, modern admin UI shared across every Wbcom plugin via the "WB Plugins" hub.

== Installation ==

Requires BuddyPress 12.0 or higher (or BuddyBoss Platform).

1. Upload the `buddypress-contact-me` folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WB Plugins → Contact Me and configure notifications and access rules.

== Changelog ==

= 1.5.1 - November 2026 =

* New      - Each sender can send 5 messages in 10 minutes (members by account, visitors by IP address); change it with the bcm_rate_limit filter.
* New      - A hidden trap field on the visitor form stops bots that fill every field.
* New      - Messages a member received are deleted when their account is deleted.
* New      - Reign shows matching icons on the Contact, Inbox, Send message and Preferences tabs.
* New      - When Wbcom CAPTCHA Manager is active and its Contact Me Form switch is on, logged-out visitors solve your site's CAPTCHA instead of the math question.
* Improve  - Activating the plugin no longer updates every member account, so it stays fast on large sites.
* Improve  - Uninstall removes all of the plugin's stored settings.
* Fix      - Messages from a member whose account was deleted show "Deleted member" instead of a guest with a profile to visit.
* Fix      - The delete confirmation opens on Cancel, so pressing Enter twice no longer deletes a message.
* Fix      - Contact form styles and scripts load on any page that uses the contact shortcode, including for logged-out visitors.
* Fix      - Message times show correctly on sites outside UTC; existing messages are converted once on update.
* Fix      - Buttons follow the theme's dark mode toggle and keep readable label colours on BuddyX, BuddyX Pro and Reign.
* Fix      - Delete button stacks full width with the other message actions on phones.
* Fix      - Several unread contact messages now show as one "You have N new contact messages" notification that opens your inbox, instead of naming only the first sender.
* Fix      - Right-to-left sites lay out the contact screens correctly from one stylesheet.
* Fix      - Settings screen fits phones without sideways scrolling or clipped text.
* Fix      - Copy-link messages are now translatable.
* Fix      - Plugin zip installs on hosts that block files named shell.php.
* Fix      - Visitors can no longer send messages when no sender group is picked; they must be ticked under Who can send.
* Fix      - Members can no longer send a contact message to themselves through the shortcode form.
* Fix      - Deleting a message also removes its notification, and older leftover notifications show readable text.
* Fix      - Turning off the profile Contact tab no longer stops the contact shortcode from showing or sending.
* Fix      - Members who stop accepting messages keep their own Contact inbox and preferences, and older message links open again.
* Fix      - Signed-in members whose role may not send now see why, instead of a sign-in prompt.
* Fix      - The plain-text version of contact emails no longer shows HTML tags.
* Fix      - The Back to inbox arrow points the right way on right-to-left sites.
* Fix      - The site admin copy setting now says each admin gets their own email.
* Fix      - The contact preference shows only under Contact > Preferences; the copy on Settings > General that did not save was removed.
* Fix      - Saving Contact > Preferences shows a "Preferences saved." notice.
* Fix      - Backslashes in a subject or message are kept as typed.
* Fix      - Without JavaScript, a form error keeps what you typed and shows the error above the form, and a sent message returns to the form with a confirmation.
* Fix      - Unread counts on the Contact tab, the Unread filter and the inbox always agree, also after a delete.
* Fix      - Opening a page number past the last inbox page shows the last page.
* Fix      - Notices and messages use the same look as other BuddyPress notices, in light and dark mode.
* Fix      - After a message is sent, keyboard focus returns to the form.
* Fix      - The Delete button in the inbox has a screen reader label on touch devices and a visible focus ring.
* Fix      - The Guest badge is readable in dark mode.
* Fix      - Reactivating the plugin brings back a contact email that was deleted under Dashboard > Emails.
* Fix      - Messages to a deleted or unknown member are refused.
* Security - The visitor math question now works once per message, so a solved answer cannot be replayed, even by requests sent at the same moment.
* Security - A member's account email is never shown to the recipient; only a visitor's typed reply address is shown.
* Security - Visitors no longer get a sender copy, so the form cannot send the site's email to an address someone typed in.
* Security - The sender copy and admin copies go out as separate emails, so the recipient's email address is no longer shared, and each copy is worded for its reader.
* Dev      - Removed the Grunt build and the minified and RTL copies; the plugin ships readable CSS and JavaScript, and bin/build-release.sh builds the zip.
* Dev      - New bcm_display_timezone filter; all dates go through one formatter.
* Compat   - Requires WordPress 6.5 and PHP 8.1, tested up to WordPress 7.1 and PHP 8.5.

= 1.5.0 - June 2026 =

* New      - Rebuilt admin UI as a clean card-panel layout with a sidebar, page header, and plain-English labels, replacing the legacy wrapper chrome.
* New      - WB Plugins hub gives every Wbcom plugin a single top-level menu with a card-grid dashboard so the admin sidebar stays tidy.
* New      - Overview dashboard shows live counts of total messages, unique senders, unique recipients, and members with the Contact Me button on.
* New      - Dedicated Notifications, Access, and License tabs, each focused on a single decision.
* New      - License tab lets you enter, activate, and check your license key without leaving the plugin's admin page.
* Improve  - Inbox list view is now ready for accounts with thousands of received messages via new composite and single-column indexes added on upgrade.
* Improve  - Unread-messages count uses a dedicated SELECT COUNT(*) instead of loading every unread ID into PHP, cutting memory use on large accounts.
* Improve  - Unread filter is capped at 1,000 rows to prevent max_allowed_packet errors on extreme accounts.
* Improve  - Inbox rows batch-prefetch all sender users before rendering, eliminating the previous N+1 queries on a cold cache.
* Improve  - Access tab uses a role-chip grid with select-all and clear-all actions instead of a dropdown, friendlier on mobile.
* Improve  - Admin assets load only on the plugin's own screens and the shared hub, reducing conflicts with third-party plugins.
* Improve  - Toast notifications and an accessible confirmation modal replace browser alerts and confirms across the admin.
* Improve  - Each reader gets their own email, so no one sees another reader's address.
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
