# Troubleshooting Common Issues

The problems site owners hit most often, with the real fix for each.

## "BuddyPress is not active" notice on activation

The plugin requires BuddyPress (or BuddyBoss Platform). Install and activate BuddyPress, then activate Contact Me. The notice clears automatically.

## Contact tab does not appear on a member's profile

Three independent gates have to all open before the tab shows. Check them in this order:

1. **Show a "Contact" tab on every member profile** - go to **Access** tab and confirm the master toggle is on.
2. **Recipient role allowed** - confirm the member's role is ticked in **Who can be contacted**. Administrators are always allowed regardless.
3. **Member opt-out** - the member opens **Contact > Preferences** on their own profile and confirms **Let other members contact me** is ticked. Members are opted in until they turn it off.

If all three are open and the tab still does not render, clear any object cache and reload - BP nav items can be cached by site-level caching plugins.

## Logged-out visitors see no form

Open **Access → Who can send messages** and tick **Visitors (not logged in)**. Without this, `BCM_Frontend_Nav::viewer_can_send()` returns false for unauthenticated users and the form is hidden.

## Messages send but no email arrives

Check, in this order:

1. **WB Plugins → Contact Me → Notifications** - is **Email notification** on?
2. **WordPress core email delivery** - does any other email work? (e.g., new user notifications, password resets.) If not, install a transactional email plugin like FluentSMTP, WP Mail SMTP, or Post SMTP. The plugin uses `bp_send_email()`, which uses `wp_mail()`, which depends on the host's `mail()` function being able to actually send.
3. **BuddyPress email post exists** - at **Dashboard → Emails**, look for the email whose subject reads "[Site name] Sender name sent you a contact message". If missing, deactivate and reactivate Contact Me to re-run the installer.

## "The security question has expired"

Each math question works once and for 30 minutes. A page left open longer, or a page served from a full-page cache, carries an old question. Reload the page for a fresh one, and exclude pages with the contact form from full-page caching. If the Wbcom CAPTCHA Manager plugin is active with a service turned on, guests get that service instead of the math question.

## "You have sent several messages in a short time"

Each sender can send 5 messages in 10 minutes (members by account, visitors by IP address). The limit resets on its own. Change it with the `bcm_rate_limit` filter, see [Hooks & filters](../developer-guide/hooks-and-filters.md).

## Spam screen rejects a legitimate message

The built-in heuristic flags pharma, gambling, loan-spam, lottery wins, generic CTA phrasing, and three-or-more URLs in one message. If a real message hits one of these, soften the gate via the `bcm_spam_check` filter - see [Hooks & filters](../developer-guide/hooks-and-filters.md). For a quick pass-through:

```php
add_filter( 'bcm_spam_check', '__return_false', 99, 3 );
```

That disables the heuristic entirely. Use sparingly.

## Inbox shows zero unread but notifications bell shows a count

The unread count in the **Contact** nav item and the **Unread** filter both count messages that still have an unread (`is_new = 1`) Contact Me notification. If the bell shows a higher count, those entries are unrelated to Contact Me - they are other BuddyPress notifications (mentions, friend requests, group invites) and the discrepancy is expected.

## Old `/contact-me/` URLs in stored emails or bookmarks

These work - `BCM_Frontend_Nav::redirect_legacy_slug()` 301-redirects them to the current `/contact/` slug. No action required. If you want to update old emails for clarity, do so when you have the chance.

## License activation fails with "not active for this URL"

Your key is bound to another site that has used up your activation slots. Either:

1. Visit the other site, go to **License**, and click **Deactivate License** - that frees the slot. Then activate on the new site.
2. Upgrade your license at wbcomdesigns.com to a higher site-count tier.

Still stuck? See the [FAQ](../faq/faq.md) for how to report a problem.
