# Inbox on the Member's Own Profile

Each member sees a dedicated inbox of every contact message they have received, on their own BuddyPress profile.

## How members reach it

- Click **Contact** on their own profile, then **Inbox** in the sub-nav.
- Click a notification-bell entry. The link goes straight to the single-message view.
- Click **Contact** in the WordPress admin bar (under "My Account"). Same destination.

The inbox URL is `/{user-slug}/contact/inbox/`. On the member's own profile the default sub-nav for the Contact tab is Inbox, so a bare `/contact/` URL lands here too.

## Layout

Each row in the listing shows:

- The sender's avatar (or a generic guest avatar for non-member submissions).
- The sender's display name, with a **Guest** badge for non-member submissions and a **New** badge for unread messages.
- The subject, in bold.
- The first line of the message, trimmed.
- The submission date.

## Unread filter

The inbox has two filters at the top:

- **All** - every message the member has ever received.
- **Unread** - only messages with an active BuddyPress notification (that is, ones the recipient has not yet opened).

The Unread filter relies on the BuddyPress Notifications component. If notifications are disabled site-wide the filter still renders but always returns an empty list. The inbox itself is unaffected.

## Unread badge in the nav

The "Contact" parent nav item shows a count badge when the inbox has unread messages. The number tracks the same data the Unread filter uses: BuddyPress notifications belonging to the `bcm_user_notifications` component that are still flagged `is_new = 1`.

## Pagination

Messages render 10 per page in newest-first order. Older pages link via standard BuddyPress pagination at the bottom of the listing. On large accounts the query is served by the `(reciever, datetime, id)` index added on install, so the listing stays fast.

## Privacy

Only the recipient can read their inbox. Opening a single message enforces ownership: the REST delete endpoint's `require_message_owner` callback and the recipient-scoped `delete_for_recipient()` method mean a direct URL to a stranger's message ID returns "Message not found" rather than any content.
