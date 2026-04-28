# What BuddyPress Contact Me Does

BuddyPress Contact Me adds a private "Contact" tab to every member profile. Visitors and other members can send a message without ever seeing the recipient's email address. The recipient gets a BuddyPress notification, an email through the standard BuddyPress email template, or both — your choice.

![Admin overview dashboard](../images/admin-overview.png)

## Who it is for

- Community sites that want members to be reachable without exposing email addresses.
- Membership and directory sites where guests need to contact listed members.
- Mentor / coach / consultant communities where inbound contact has to flow through a structured form, not a public email link.

## What you get out of the box

- A "Contact" tab on every member profile.
- A built-in inbox at `/contact/inbox/` on the member's own profile, with an "Unread" filter and unread badge in the nav.
- Email notification to the recipient using the official BuddyPress email template — no custom templating to maintain.
- BuddyPress notification bell entries for in-site notifications.
- Role-based access control: pick which roles can send messages and which roles can receive them.
- Per-member opt-out via BuddyPress Settings → General — every member keeps the final say.
- Math captcha and a built-in spam heuristic for logged-out submissions.
- A `[buddypress-contact-me]` shortcode for embedding the form on any page.
- Admin bar shortcut: "Contact" appears under My Account so members can reach their inbox in one click.

## What it does not do

- Two-way messaging — a recipient replies through email or BuddyPress private messages, not through this plugin's inbox.
- Threading or attachments — messages are single-shot and text-only.
- BBPress / external user-store integrations — the plugin assumes BuddyPress 12+ user data only.

## Compatibility

- WordPress 6.0 and above (tested up to 6.9).
- PHP 7.4 and above (tested on 8.4).
- BuddyPress 12.x and 14.x, plus BuddyBoss Platform.
- BuddyX, Reign, and BuddyBoss themes — including their dark-mode variants.

## What's next

The next page covers installing and activating the plugin. After that the Settings docs walk through the four admin tabs (Overview, Notifications, Access, License), and the Features docs explain the user-facing behaviour in detail.
