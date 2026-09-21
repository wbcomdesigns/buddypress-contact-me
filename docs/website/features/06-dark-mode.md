# Dark Mode Support for BuddyX, Reign, and BuddyBoss

The contact form, inbox, and admin panels render correctly in dark mode out of the box on every Wbcom theme and the BuddyBoss Platform.

## Themes supported

- **BuddyX** and **BuddyX Pro** - the free and premium Wbcom theme pair.
- **Reign** - the Wbcom community theme.
- **BuddyBoss Theme** - bundled with BuddyBoss Platform.
- Any child theme of the above.

## Surfaces that adapt

- The contact form on member profiles (subject, message, captcha).
- The inbox listing rows (avatar, sender, subject, snippet, badges).
- The single-message view (header, body, action buttons, alerts).
- The Preferences sub-tab (per-member opt-out).
- The admin Overview, Notifications, Access, and License tabs, including hub cards and toast notifications.

## How it works

The plugin defines its own `--bcm-*` design tokens for backgrounds, borders, text, and accent colours. On a supported theme those tokens chain to the theme's own variables, with a safe fallback:

- On BuddyX and BuddyX Pro they read the theme's `--bx-*` variables.
- On Reign they read the theme's `--reign-*` variables.
- Anywhere else they fall back to sensible built-in defaults.

Dark mode is applied two ways, both without any JavaScript theme detection:

1. **Theme-native dark.** When BuddyX or Reign flips its own variables for dark mode, the `--bcm-*` tokens follow automatically through the chain above.
2. **Explicit dark triggers.** The plugin also ships an explicit dark palette that activates on the common dark-mode body and html classes, including `body.dark-mode`, `html.dark`, `[data-theme="dark"]`, `html[data-bx-mode="dark"]`, `.buddyx-dark-mode`, `.bb-dark-mode`, and `body.bcm-dark`.

The plugin deliberately does not hook `prefers-color-scheme`. The site owner's theme toggle decides light or dark, not the visitor's operating system.

## Custom themes

If you run a non-Wbcom theme with its own dark mode, the simplest path is to add one of the recognized dark-trigger classes (for example `body.bcm-dark`) when your theme is in dark mode. Contact Me then flips to its dark palette. Alternatively, define compatible `--bx-*` or `--reign-*` variables and the tokens resolve through the chain.

To restyle Contact Me specifically without touching the theme, target the plugin's body class `wbcom-bp-contact` (added when the Contact tab is active) and override individual rules:

```css
body.wbcom-bp-contact .bcm-message-view__subject {
    color: var(--your-accent-colour);
}
```

## Known limitations

- Email notifications use the BuddyPress email template, which has its own colour scheme controlled at **Dashboard > Emails > Customize**. That scheme is independent of the front-end dark-mode toggle.
- The math captcha is plain text and inherits the form's text colour, so it works in both modes without extra styling.
