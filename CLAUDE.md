# BuddyPress Contact Me

> **READ FIRST:** [`audit/manifest.json`](audit/manifest.json) is the canonical inventory — 3 REST endpoints, 0 AJAX, 2 admin pages, 1 shortcode, 1 custom table (`{prefix}contact_me`), 6 hooks fired, 7 settings keys, 0 cron, 0 CPTs. Read it before grepping. See also [`audit/FEATURE_AUDIT.md`](audit/FEATURE_AUDIT.md), [`audit/CODE_FLOWS.md`](audit/CODE_FLOWS.md), [`audit/ROLE_MATRIX.md`](audit/ROLE_MATRIX.md), [`audit/wppqa-baseline-2026-05-11/SUMMARY.md`](audit/wppqa-baseline-2026-05-11/SUMMARY.md). Refresh via `/wp-plugin-onboard --refresh` after non-trivial structural changes.

## Quick Reference

| Field | Value |
|-------|-------|
| Slug | `buddypress-contact-me` |
| Version | 1.5.1 (dev branch `1.5.1`; last released tag is `v1.5.0`) |
| Text Domain | `buddypress-contact-me` |
| Prefix | `bcm_` / `BCM_` |
| Option Key | `bcm_admin_general_setting` |
| DB Table | `{prefix}contact_me` (note: `reciever` column is misspelled — historic, do not rename) |
| Admin Page | `admin.php?page=buddypress-contact-me` |
| Repo | `wbcomdesigns/buddypress-contact-me` |
| Default Branch | `master` |
| License | EDD (via `edd-license/`) |
| Requires | BuddyPress (or BuddyBoss Platform) |

## Architecture

Classic WordPress Plugin Boilerplate (Loader pattern). Bootstrap → core orchestrator → Loader registers hooks. Source layout:

```
buddypress-contact-me.php          # bootstrap, activator
includes/
  class-buddypress-contact-me.php           # orchestrator
  class-buddypress-contact-me-loader.php    # hook registry
  class-buddypress-contact-me-activator.php # creates {prefix}contact_me
  admin/class-bcm-admin.php                 # admin menu + settings
  frontend/class-bcm-frontend-submit.php    # form handler (PUBLIC)
  frontend/class-bcm-frontend-nav.php       # BP profile nav + role gates
  frontend/class-bcm-frontend-shortcode.php # [buddypress-contact-me]
  frontend/class-bcm-frontend-notifications.php  # BP notification + email
  rest/class-bcm-rest-messages.php          # REST API (bcm/v1)
  email/class-bcm-email-installer.php       # installs BP email post
edd-license/                                # EDD updater + license forms
public/partials/                            # frontend templates
  inbox.php  send.php  tab-preferences.php
```

## Key entry points

- **Form submit** — `BCM_Rest_Messages::submit` (REST) or classic POST handled by `BCM_Frontend_Submit`
- **Inbox** — `BCM_Frontend_Nav::screen_inbox` (own-profile only)
- **Send** — `BCM_Frontend_Nav::screen_send` (other-profile only)
- **Preferences** — `BCM_Frontend_Nav::screen_preferences` (own-profile only)

## BuddyPress / BuddyBoss compat

- Enabled when `class_exists('BuddyPress')` (covers both BP and BBoss platform).
- BBoss detection via `isset(buddypress()->buddyboss)` — switches user-slug retrieval between `bp_members_get_user_slug` (BBoss) and `bp_core_get_username` (BP).
- BP nav slug `contact` (legacy alias `contact-me` still works for old bookmarks).

## Capability gates

- Admin: `manage_options` on every admin handler.
- Frontend send: dual-gate via `BCM_Frontend_Nav::viewer_can_send()` (role allow-list `bcm_who_contact`) and `BCM_Frontend_Nav::user_accepts_contact()` (per-user opt-in `contact_me_button` usermeta).
- Anonymous submit: captcha required.
- REST DELETE: `require_message_owner` (current user must be `reciever`).

## Coding conventions

- **Prefix:** `bcm_` for snake_case (option keys, hook names), `BCM_` for class names.
- **Column name `reciever` is intentionally misspelled in the schema.** Every query reads/writes that literal string. Renaming would break installs in the field.
- **Message SQL lives in `includes/data/class-bcm-messages-repo.php`.** Schema SQL lives in the activator; admin stat counts in `includes/admin/views/overview.php`.

## Frontend and data conventions

- **Times are UTC.** `BCM_Messages_Repo::insert()` stores `current_time( 'mysql', true )`; templates read times through `BCM_Messages_Repo::timestamp()` and format with `wp_date()`. Never `date_i18n()`, `mysql2date()` or a bare `strtotime()` on a stored value. Rows from before 1.5.1 are converted once in `install_schema()` (DB_VERSION 1.5.1).
- **Schema changes** bump `BuddyPress_Contact_Me_Activator::DB_VERSION`; `maybe_upgrade()` re-runs `install_schema()` behind a cache lock and only bumps `bcm_db_version` after the `REQUIRED_INDEXES` check passes. One-time data fixes go just before that bump.
- **Contact preference:** no per-user meta is seeded. Anything except `contact_me_button = off` means the member accepts messages; counts are "all users minus off".
- **Dark mode** keys only on `html[data-bx-mode="dark"]` and `body.buddyx-dark-theme`, token chains through `--bx-color-*`; never `prefers-color-scheme` on the frontend. Primary buttons use the paired `--bx-color-button-bg` / `--bx-color-button-fg`.
- **Assets** load on the member Contact screens and on any singular post that contains the `[buddypress-contact-me]` shortcode; the shortcode also enqueues on render. RTL sites get `public/css/rtl/`.
- **Admin shell view** is `includes/admin/views/layout.php` (hardened hosts reject zips containing `shell.php`).
- **Build:** `npx grunt build` (cssmin, uglify, rtlcss, checktextdomain). POT: `wp i18n make-pot . languages/buddypress-contact-me.pot --exclude=node_modules,vendor,docs,audit,bin,public/js/min`.
