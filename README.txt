=== Wbcom Designs - BuddyPress Contact Me ===
Contributors: Wbcom Designs
Donate link: https://www.wbcomdesigns.com
Tags: comments, spam
Requires at least: 3.0.1
Tested up to: 6.8.0
Stable tag: 1.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

BuddyPress Contact Me displays a contact form on a member's profile, allowing logged-in and non-logged-in visitors can be in touch with our community members.
== Description ==

BuddyPress Contact Me displays a contact form on a member's profile, allowing logged-in and non-logged-in visitors can be in touch with our community members.

== Installation ==

BuddyPress Contact Me displays a contact form on a member's profile, allowing logged-in and non-logged-in visitors can be in touch with our community members.

e.g.

1. Upload `buddypress-contact-me.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php do_action('plugin_name_hook'); ?>` in your templates

== Changelog ==
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
