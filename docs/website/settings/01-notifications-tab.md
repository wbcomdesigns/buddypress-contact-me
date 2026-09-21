# Notifications Tab: Recipient and Copy Settings

The Notifications tab decides what happens after a message is submitted: who gets notified, by which channel, and whether copies fan out to admins or the sender.

![Notifications tab](../images/admin-notifications.webp)

## Notify the recipient

Two channels for the person receiving the message. Both can be on at once:

- **In-site notification** - a BuddyPress notification-bell entry. It appears the next time the recipient visits the site and links straight to the single-message view at `/contact/inbox/{id}/`. Requires the BuddyPress Notifications component to be active.
- **Email notification** - an email sent through the BuddyPress email template (subject, body, header, footer, unsubscribe). Edit the content at **Dashboard > Emails**.

If both are off, the message is still saved to the database. It simply sits in the recipient's inbox until they visit the Contact tab. Most communities should leave at least one channel on. Both are on by default.

## Send a copy to

Two extra recipient toggles:

- **Site admin** - send every message to every user with the `administrator` role. Useful for community moderation. Off by default.
- **Sender** - send the sender a copy of what they submitted. Acts as a "we got your message" receipt and reduces "did my message go through?" support tickets. On by default.

The sender copy applies to both members (delivered to their account email) and guests (delivered to the email they typed into the form).

## Edit the email template

Above the email toggle is an inline link to the contact-message email. It opens the `bcm-contact-message` BuddyPress email post in the editor so you can tweak the subject and body without hunting through every BP email. In the Emails list, the post is described by its situation, "A member (or visitor) sent a contact message from your profile."

The plugin-provided tokens are:

- `{{sender.name}}` - display name of the sender (or the guest name).
- `{{recipient.name}}` - the recipient's display name.
- `{{contact.subject}}` - the message subject.
- `{{{contact.message}}}` - the message body, auto-paragraphed (triple braces because it contains HTML).
- `{{{inbox.url}}}` - a deep link to the message in the recipient's inbox.

Every BP-native token, such as `{{{site.name}}}` and the unsubscribe link, is available too.

## Independent saves

The Notifications tab and the Access tab save independently. Saving the Notifications tab touches only the notification-related options and leaves your role lists alone. This is enforced with per-tab "rendered keys" sentinels, so an Access save can never accidentally reset notification preferences, or the reverse.

## What's next

Notifications cover how members find out. The [Access](02-access-tab.md) tab controls who can send and receive in the first place.
