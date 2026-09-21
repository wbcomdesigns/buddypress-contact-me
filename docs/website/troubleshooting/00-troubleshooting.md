# Troubleshooting

Symptoms that come up in practice, with the real fix for each. For behaviour questions, see the [FAQ](../faq/00-faq.md).

## "BuddyPress is not active" notice on activation

The plugin requires BuddyPress or BuddyBoss Platform. Install and activate one of them, then activate Contact Me. The notice clears automatically.

## The Contact tab does not appear on a member's profile

The tab is shown based on the recipient (the profile owner). Three recipient-side gates must all pass. Check them in order:

1. **Global toggle** - go to the **Access** tab and confirm **Show a "Contact" tab on every member profile** is on.
2. **Recipient role allowed** - confirm the member's role is ticked in **Who can be contacted**. Administrators are always allowed regardless.
3. **Member opt-out** - as that member, open BuddyPress **Settings > General** and confirm **Let other members contact me** is ticked. New members default to opted in; this only matters if they turned it off.

If all three pass and the tab still does not render, clear any object cache and reload. BuddyPress nav items can be cached by site-level caching plugins.

## A visitor sees the form but the submission is rejected

Form visibility is decided by the recipient's settings, while permission to send is enforced when the form is submitted. If a visitor or member sees the form but gets "You are not allowed to send messages." on submit, add their group to **Access > Who can send messages** (tick **Visitors (not logged in)** for guests). If the send grid is completely empty, everyone is allowed.

## Messages send but no email arrives

Check, in order:

1. **WB Plugins > Contact Me > Notifications** - is **Email notification** on?
2. **WordPress email delivery** - does any other email work (new-user notices, password resets)? If not, install a transactional email plugin such as FluentSMTP, WP Mail SMTP, or Post SMTP. The plugin uses `bp_send_email()`, which uses `wp_mail()`, which depends on the host being able to send mail.
3. **The email post exists** - at **Dashboard > Emails**, look for the entry whose situation is "A member (or visitor) sent a contact message from your profile." If it is missing, deactivate and reactivate Contact Me to re-run the installer.

## The captcha is wrong even though the answer looks right

The captcha renders a hash of the answer into a hidden field and compares it on submit. If a plugin or browser extension strips hidden fields, rewrites form data, or auto-fills aggressively, the hash and answer will not match. Test in a clean browser profile or incognito window; if it works there, the culprit is in your normal browser.

## The spam screen rejects a legitimate message

The heuristic flags pharma, gambling, loan and finance offers, lottery wins, generic call-to-action phrasing, and three or more URLs in one message. If a real message hits one of these, soften the gate with the `bcm_spam_check` filter (see [Hooks and filters](../developer-guide/01-hooks-and-filters.md)). To disable the heuristic entirely:

```php
add_filter( 'bcm_spam_check', '__return_false', 99, 3 );
```

Use that sparingly.

## The inbox shows zero unread but the bell shows a count

The unread count in the **Contact** nav and the **Unread** filter query the BuddyPress notifications table for `is_new = 1` rows belonging to the `bcm_user_notifications` component. A higher number on the bell is other BuddyPress notifications (mentions, friend requests, group invites) and the difference is expected.

## Old `/contact-me/` URLs in stored emails or bookmarks

They still work. The plugin 301-redirects `/contact-me/` to the current `/contact/` slug. No action needed. Update old emails for clarity whenever convenient.

## License activation fails with "not active for this URL"

Your key is bound to another site that has used up its activation slots. Either:

1. Visit the other site, go to **License**, and click **Deactivate License** to free the slot, then activate on the new site, or
2. Upgrade your license at wbcomdesigns.com to a higher site-count tier.

## Something else

Turn on `WP_DEBUG` and check `wp-content/debug.log`. The plugin logs email-send leakage and failed schema migrations there. Include that log when you open a support ticket.
