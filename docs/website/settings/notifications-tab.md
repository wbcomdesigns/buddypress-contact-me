# Notifications Tab — Recipient & Copy Settings

The Notifications tab decides what happens **after** a message is submitted: who gets notified, by which channel, and whether copies fan out to admins or the sender.

![Notifications tab](../images/admin-notifications.png)

## Notify the recipient

Two channels for the person receiving the message — both can be on at the same time:

- **In-site notification** — sends a BuddyPress notification bell entry. Appears the next time the recipient visits the site, links straight to the single-message view at `/contact/inbox/{id}/`. Requires the BuddyPress Notifications component to be active.
- **Email notification** — sends an email through the BuddyPress email template (subject, body, header, footer, unsubscribe). Edit the template content at **Dashboard → Emails** under "A member received a contact message".

If both are off, the message is still saved to the database — it just sits in the recipient's inbox until they happen to visit the Contact tab. Most communities should leave at least one channel on.

## Send a copy to…

Two extra recipient toggles:

- **Site admin** — send every user with the `administrator` role their own copy of each message ("Sender sent Member a contact message"). Useful for community moderation: admins keep their inbox visible without logging in as each member.
- **Sender** — send the sender a copy of what they submitted. Acts as a "we got your message" receipt, reduces "did my message go through?" support tickets, and gives the sender a paper trail.

The sender-copy applies to both members (delivered to their account email) and guests (delivered to the email address they typed into the form). It reads "Copy of your message to Member".

Every copy goes out as its own email, so nobody sees another person's email address. The two copies have their own templates in **Dashboard → Emails**: "A copy of a contact message, sent to the member or visitor who wrote it" and "A copy of a contact message, sent to site administrators".

## Edit the email template

Above the **Email notification** toggle there is an inline link: **Edit the contact-message email →**. Clicking it opens **Dashboard → Emails** filtered to the email post type term `bcm-contact-message`, so you can tweak subject and body without hunting through every BP email.

The tokens you can use in these templates are:

- `{{sender.name}}` — display name of the sender (or guest name).
- `{{contact.recipient}}` — display name of the member the message was sent to (use this in the copy emails).
- `{{recipient.name}}` — display name of whoever is reading this email.
- `{{contact.subject}}` — the message subject.
- `{{{contact.message}}}` — the message body (auto-paragraphed; triple braces because it contains HTML).
- `{{{inbox.url}}}` — deep link to the message in the recipient's inbox.

Plus every BP-native token like `{{{site.name}}}` and the unsubscribe link.

## Independent saves

The Notifications tab and the Access tab are independent. Saving the Notifications tab only touches the notification-related options — your role allow-lists are untouched. This is enforced via per-tab "rendered keys" sentinels so an Access save can never accidentally reset notification preferences (or vice versa).

## What's next

Notifications cover *how* members find out. The [Access](access-tab.md) tab controls *who* can send and receive in the first place.
