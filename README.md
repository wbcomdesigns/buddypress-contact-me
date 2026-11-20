# BuddyPress Contact Me

A contact form on every BuddyPress member profile, so people can reach a member without BuddyPress private messages.

## What it does

- Adds a Contact tab to member profiles. Members who are allowed to send see the form; the member's own Contact tab holds their Inbox and Preferences.
- Lets logged-out visitors send a message too, with name and email fields, a security check (a math question, or your CAPTCHA when Wbcom CAPTCHA Manager is active), a hidden trap field and a send limit of 5 messages in 10 minutes per sender.
- Notifies the recipient with a BuddyPress notification and a BuddyPress email you can edit under Dashboard > Emails. Optional sender and site admin copies go out as separate emails.
- Gives each member an inbox with All and Unread filters, a single message view with Send private message, Reply by email, Visit profile and Delete, and a personal opt-out under Contact > Preferences.
- Lets the site owner choose which roles can send and which can be contacted, and see message totals on an Overview tab.
- Embeds the form anywhere with the `[buddypress-contact-me]` shortcode (`id` or `user` picks the recipient).
- Follows the dark mode of BuddyX, BuddyX Pro and Reign, supports right-to-left languages and works on phones.

## Who it is for

Community owners who want visitors and members to contact a member (a mentor, a seller, a team member) from the profile page, with the message landing in an inbox and an email.

## Get it

- Store page: https://wbcomdesigns.com/downloads/buddypress-contact-me/
- A license key is needed for updates. Enter it under WB Plugins > Contact Me > License.

## Documentation

- User and developer guides: https://docs.wbcomdesigns.com/bp-family/buddypress-contact-me/
- Release notes: https://wbcomdesigns.com/release-notes/buddypress-contact-me/
- The same guides live in this repository under `docs/website/`.

## Requirements

- WordPress 6.5 or newer (tested up to 7.1)
- PHP 8.1 or newer (tested up to 8.5)
- BuddyPress 12.0 or newer. The BuddyPress Notifications component is needed for notification bell entries; email works without it.

## Install

1. Download the plugin zip from your Wbcom account.
2. In wp-admin go to Plugins > Add New > Upload Plugin, choose the zip and click Install Now.
3. Activate the plugin, then open WB Plugins > Contact Me.
4. On the Access tab choose who can send and who can be contacted, and tick Visitors if logged-out people may write.

## For developers

- Main file `buddypress-contact-me.php`, text domain `buddypress-contact-me`, prefixes `bcm_` and `BCM_`.
- REST namespace `bcm/v1`: `POST /messages`, `DELETE /messages/{id}` and `POST /preferences/intro-dismiss`.
- Hooks: action `bp_contact_me_form_save`; filters `bcm_spam_check`, `bcm_rate_limit`, `bcm_display_timezone` and `bcm_admin_tabs`. Details in `docs/website/developer-guide/hooks-and-filters.md`.
- Messages are stored in the `{prefix}contact_me` table. The email is a BuddyPress email post of type `bcm-contact-message`.
- No WP-CLI commands. Template overrides are described in `docs/website/developer-guide/overrides.md`.
- Uninstalling removes the table, settings and plugin user meta.

## Support and licence

- Support: https://wbcomdesigns.com/support/
- Licence: GPL-2.0 or later. See `LICENSE.txt`.
