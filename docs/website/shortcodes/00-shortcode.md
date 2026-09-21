# Shortcode for Embedding the Form Anywhere

The `[buddypress-contact-me]` shortcode renders the contact form on any post, page, or widget area. It is useful for landing pages, "Contact the team" pages, or page-builder blocks where the form needs to live outside a member's profile.

## Basic syntax

```text
[buddypress-contact-me]
```

With no attributes, the shortcode targets the displayed BuddyPress user, which is useful inside member-profile templates. On a non-profile page with no displayed user, it renders nothing.

## Target a specific user by ID

```text
[buddypress-contact-me id="42"]
```

Renders the form addressed to user ID 42. Use this on a fixed "Contact our founder" page.

## Target a specific user by login

```text
[buddypress-contact-me user="varundubey"]
```

Looks the user up by `user_login` and renders the form addressed to them. Useful when you want a stable identifier that does not change if the database is moved to another environment with different IDs.

## Attribute precedence

When both attributes are provided, `id` wins. The resolution order is:

1. `id` if present and non-zero.
2. `user` (looked up via `get_user_by( 'login', ... )`).
3. The displayed BuddyPress user (`bp_displayed_user_id()`).

If none of these resolve to a real user, the shortcode renders nothing.

## Opt-out and recipient rules are respected

The shortcode runs the same `BCM_Frontend_Nav::user_accepts_contact()` check the profile tab uses. If the targeted user has opted out, or their role is not in the **Who can be contacted** list, or the global Contact-tab toggle is off, the shortcode returns an empty string. There is no error, so the page stays clean.

## Sender permission is enforced on submit

The shortcode renders the form whenever the recipient accepts contact. It does not hide the form based on the sender's own permission. If the person filling it in is not allowed by the **Who can send messages** list (for example a logged-out visitor where Visitors are not allowed), the submission is rejected with "You are not allowed to send messages." rather than the form being hidden.

## Putting it on a page

1. Create or edit any page or post.
2. Add a Shortcode block, or paste the shortcode into the Classic editor.
3. Pick the recipient with `id` or `user`.
4. Save and visit the page. The form is fully wired, including the math captcha for guests.

## Caching plugins

The form uses a fresh nonce on every render. If you cache the page output aggressively, an expired nonce causes a submission to fail with "Session expired. Please refresh the page and try again." Either exclude pages that carry the shortcode from caching, or use a fragment cache that re-runs PHP for the form region.
