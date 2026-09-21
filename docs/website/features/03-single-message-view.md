# Single Message View: Reply, Visit, Delete

Clicking a message in the inbox opens a full-page view of that one message with the actions a recipient typically wants.

## URL pattern

The single-message view lives at `/{user-slug}/contact/inbox/{message-id}/`. Notification-bell links and the email "Open in your inbox" link target this URL directly.

If the message ID does not exist or belongs to someone else, the page renders a "Message not found" state with a **Back to inbox** button, never a stranger's content.

## Header

The header shows:

- The sender's avatar (BuddyPress avatar for members, Gravatar fallback for guests).
- The subject, large.
- The sender's name, linked to their profile if they are a member, otherwise marked with a **Guest** badge.
- The sender's email, clickable as a `mailto:` for a quick reply.
- The submission timestamp in the site's date and time format.

## Action buttons

A row of action buttons appears below the message body. Which buttons render depends on whether the sender is a member and whether they left contact details:

- **Send private message** - the primary action when the sender is a BuddyPress member and the Messages component is active. Opens the standard BP private-message compose form addressed to them.
- **Reply by email** - opens a `mailto:` with the subject pre-filled as `Re: {original subject}`. Works for both member and guest senders as long as an email is available.
- **Visit profile** - opens the sender's profile in a new tab. Renders only for member senders.
- **Delete** - a confirm-then-delete button that calls the REST delete endpoint with a fresh nonce, then routes back to the inbox.

If the sender left no contact details at all (a rare edge case, a guest with an empty email), the page surfaces a note that there is no way to reply but still allows delete.

## Marking as read

Opening the single-message view clears the BuddyPress notification tied to that message. The unread badge in the parent **Contact** nav and the **Unread** filter both update on the next page load. The mark-as-read step is scoped to that one message, so other unread messages stay flagged.

## Delete action

Clicking **Delete** calls `DELETE /wp-json/bcm/v1/messages/{id}` with the `wp_rest` nonce and the form nonce for defence in depth. The endpoint enforces ownership at the permission layer, returning 403 if the current user is not the recipient, so deletion is impossible from another account even with a guessed ID. After a successful delete the page returns to the inbox with a success message.
