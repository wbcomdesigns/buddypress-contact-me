# Per-Member Opt-Out from BuddyPress Settings

Even when an admin's role list says a member can be contacted, every member keeps a personal opt-out switch. Privacy stays in the member's hands.

## Where members find the toggle

1. The member opens their own profile.
2. Clicks **Settings** in the profile sub-nav, then **General** (the standard BuddyPress settings screen).
3. Finds the line: **Let other members contact me through a form on my profile.**
4. Unticks the box and clicks **Save Changes**.

The toggle is added to the BuddyPress Settings > General screen via the `bp_core_general_settings_before_submit` hook, so it sits alongside the member's other account-level preferences.

## What opt-out actually does

When a member opts out, their `contact_me_button` user-meta is set to `off`, and:

- The "Contact" tab disappears from their profile.
- A `[buddypress-contact-me]` shortcode targeting them renders nothing.

Every gate runs through `BCM_Frontend_Nav::user_accepts_contact()`, which returns `false` as soon as the meta is `off`.

## Default behaviour

New and existing members default to opted in: activation sets the meta to `on` for existing users, and the toggle treats any value that is not `off` as opted in. So the plugin is useful out of the box without per-member configuration.

## Admin override

Administrators are always contactable regardless of the role list, but the per-member opt-out still applies to them. If an admin sets their own meta to `off`, their Contact tab disappears too.

## Reactivating

A member can re-tick the box at any time. The change takes effect on the next request. There is no caching or scheduled job involved.

## Programmatic check

To check the opt-out state from code:

```php
$accepts_contact = BCM_Frontend_Nav::user_accepts_contact( $user_id );
```

The static method returns `true` only when the member has not opted out, the global Contact-tab toggle is on, and the member's role is allowed (or they are an administrator). It is the same gate the nav and the shortcode use, so it stays in lockstep with the rest of the plugin.
