# Single Message View: Reply, Visit, Delete

Clicking a message in the inbox opens a full-page view of that one message with all the actions a recipient typically wants.

![Single message view](../images/frontend-message-view.webp)

## URL pattern

The single-message view lives at `/{user-slug}/contact/inbox/{message-id}/`. Notification bell links and email "Open in your inbox" links target this URL directly.

If the message ID does not exist or belongs to someone else, the page renders a "Message not found" empty state with a **Back to inbox** button - never a stranger's content.

## Header

The header shows:

- The sender's avatar (BuddyPress avatar for members, Gravatar fallback for guests).
- The subject, large.
- The sender's name, linked to their profile if they are a member, otherwise marked with a **Guest** badge.
- For a guest, the reply address they typed, as a `mailto:` link. A member's account email is never shown.
- The time it was sent, in the site's timezone and date and time format.

## Action buttons

A row of action buttons appears below the message body. Which buttons render depends on whether the sender is a member and whether they left contact details:

- **Send private message** - primary action when the sender is a BuddyPress member and the Messages component is active. Opens the standard BP private-message compose form addressed to them.
- **Reply by email** - guest senders only. Opens a `mailto:` to the address the guest typed, with the subject pre-filled as `Re: {original subject}`.
- **Visit profile** - opens the sender's profile in a new tab. Only renders for member senders.
- **Delete** - asks for confirmation first (the dialog opens on Cancel, so pressing Enter twice does not delete), then calls the REST delete endpoint with a fresh nonce and routes back to the inbox. A delete also removes the message's notification.

If there is no way to reply (a guest with no address, or a member when the Messages component is off), the page shows a notice saying so and still allows delete.

## Marking as read

Opening the single-message view automatically clears the BuddyPress notification tied to that message - the unread badge in the parent **Contact** nav and the **Unread** filter both update on next page load. The mark-as-read step is scoped: only the notification for that specific message is touched, so other unread messages stay flagged.

## Delete action

Clicking **Delete** calls `DELETE /wp-json/bcm/v1/messages/{id}` with the `wp_rest` nonce. The endpoint enforces ownership at the permission layer - it returns 403 if the current user is not the recipient - so deletion is impossible from another account even with a guessed ID.

After a successful delete a "Message deleted." notice shows and the page returns to the inbox.
