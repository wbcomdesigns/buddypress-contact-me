# Profile Contact Form on Every Member Profile

The plugin adds a "Contact" tab to every member profile that accepts contact. When another member or a visitor opens that tab, they see a private form addressed to that specific member.

## How the form is reached

- A member visiting another member's profile sees the "Contact" tab in the profile sub-nav. Inside the tab is the **Send message** sub-tab, which holds the form.
- A logged-out visitor sees the same tab on any public profile whose owner accepts contact.
- The legacy `/contact-me/` slug from older versions still works. It 301-redirects to the current `/contact/` slug so old bookmarks, email links, and notification URLs keep resolving.

## When the tab appears

The Contact tab is shown when the profile owner (the recipient) accepts contact. Three recipient-side gates decide this:

1. The global **Show a "Contact" tab on every member profile** toggle is on (Access tab).
2. The owner's role is in the **Who can be contacted** list, or the owner is an administrator (admins are always contactable).
3. The owner has not opted out from their own BuddyPress Settings.

The sender's own permission (the **Who can send messages** list) is enforced when the form is submitted, not by hiding the tab. A visitor or member who is not in the send list can still open the form, but the submission is rejected with "You are not allowed to send messages." See [Guest contact](01-guest-contact.md) and the [Access tab](../settings/02-access-tab.md).

## What logged-in members fill in

- **Subject** - 3 to 200 characters.
- **Message** - 10 to 5000 characters.

The sender's name and email are pulled from their profile and shared with the recipient automatically, so there is no surprise about what the recipient sees.

## What logged-out visitors fill in

Logged-out visitors see two extra fields (name and email) plus a math captcha. See the dedicated [Guest contact](01-guest-contact.md) page for the full guest experience.

## Validation rules

The same rules apply to both the classic POST path and the REST endpoint, so the experience is identical whether or not JavaScript is enabled:

- Name - 2 to 100 characters.
- Email - must pass `is_email()` for guests; pulled from the user record for members.
- Subject - 3 to 200 characters.
- Message - 10 to 5000 characters.

If any field fails, the form re-renders with a clear inline error.

## Where messages go

Submissions are stored in the `{prefix}contact_me` table (sender, recipient, subject, message, name, email, datetime). The recipient sees them in their own profile under **Contact > Inbox**. See the [Inbox](02-inbox.md) page.

## Form placement elsewhere

The form lives at `/contact/send/` on each member's profile. If you want it somewhere else, such as a custom page or a team bio, use the `[buddypress-contact-me]` shortcode covered in [Shortcode](../shortcodes/00-shortcode.md).
