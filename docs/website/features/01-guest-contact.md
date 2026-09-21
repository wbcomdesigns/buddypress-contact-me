# Guest Contact with Math Captcha and Spam Heuristic

Logged-out visitors can reach members through the same contact form, with two extra fields and a built-in spam defence.

![Access tab role lists, including the Visitors group](../images/admin-access.webp)

## Enabling guest contact

Guest contact is on by default: the **Visitors (not logged in)** group is pre-selected in the send list on a fresh install. To confirm or change it:

1. Go to **WB Plugins > Contact Me > Access**.
2. In the **Who can send messages** group, tick **Visitors (not logged in)**.
3. Save.

When the send list is non-empty and **Visitors** is unticked, any unauthenticated submission is rejected with "You are not allowed to send messages." (If you clear the whole send list, everyone is allowed, including visitors.)

## What the visitor sees

Three extra elements appear when no user is logged in:

- **Your name** - required, 2 to 100 characters.
- **Email** - required, must pass `is_email()`; the recipient uses this to reply.
- **Security check** - a math captcha rendered as `X + Y = ?` for the visitor to solve.

The subject and message fields are the same as the logged-in version (3 to 200 and 10 to 5000 characters).

## Math captcha, how it works

When the form renders, the server emits a random sum such as `7 + 4` plus a hash of the answer (`wp_hash($answer)`) in a hidden field. On submit, the answer the visitor typed is hashed the same way and compared with `hash_equals()`, the same constant-time check WordPress uses for nonces.

This means:

- The captcha cannot be guessed without solving the sum.
- It works without any third-party service or API key.
- Members never see it. The captcha check short-circuits for logged-in users.

## Spam heuristic

After the captcha passes, the message body runs through a built-in regex screen (`looks_like_spam()` in `class-bcm-frontend-submit.php`) that flags submissions matching obvious patterns:

- Pharma terms (viagra, cialis, levitra, pharmacy, pills, medication).
- Gambling terms (casino, poker, blackjack, slots, gambling).
- Loan and finance offers (loan, mortgage, credit, debt, or finance followed by offer, approval, or rate).
- Aggressive calls to action (click here, buy now, order now, limited time).
- Lottery and prize spam (million dollar, you won, congratulations winner).
- Three or more URLs in the same message body.

A flagged submission is rejected with "Your message was flagged as spam. Please rephrase and try again." The verdict is filterable via `bcm_spam_check` (see [Hooks and filters](../developer-guide/01-hooks-and-filters.md)) so you can soften, harden, or replace the heuristic without forking the plugin.

## What the recipient gets

Guest submissions land in the same inbox as member submissions, marked with a "Guest" badge in the single-message view. The recipient can reply by email (the `mailto:` link uses the address the visitor entered) but cannot start a BuddyPress private message, because the sender has no BuddyPress account.
