# BuddyPress Contact Me

> **READ FIRST:** [`audit/manifest.json`](audit/manifest.json) is the canonical inventory - 3 REST endpoints, 0 AJAX, 2 admin pages, 1 shortcode, 1 custom table (`{prefix}contact_me`), 0 cron, 0 CPTs. Read it before grepping. See also [`audit/FEATURE_AUDIT.md`](audit/FEATURE_AUDIT.md), [`audit/CODE_FLOWS.md`](audit/CODE_FLOWS.md), [`audit/ROLE_MATRIX.md`](audit/ROLE_MATRIX.md), [`audit/wppqa-baseline-2026-05-11/SUMMARY.md`](audit/wppqa-baseline-2026-05-11/SUMMARY.md). Refresh via `/wp-plugin-onboard --refresh` after non-trivial structural changes.

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
| License | EDD SL SDK in `libs/` (signed updates, see Licensing below); licence screen in `edd-license/` |
| Requires | BuddyPress (or BuddyBoss Platform) |

## Architecture

No loader or orchestrator: the bootstrap file wires everything. Each class has a `register()` that adds its own hooks.

```
buddypress-contact-me.php          # bootstrap: activation hook, bp_include wiring, textdomain on init, deleted_user cleanup
includes/
  functions.php                             # bcm_display_timezone(), bcm_date_label(), bcm_notice()
  class-buddypress-contact-me-activator.php # schema, version router (maybe_upgrade on bp_init:20), email install
  data/class-bcm-messages-repo.php          # ALL SQL for {prefix}contact_me
  admin/class-bcm-admin.php + views/        # admin menu, tabs, settings (views/hub.php is the shared Wbcom hub: do not edit)
  frontend/class-bcm-frontend-submit.php    # classic POST (bp_actions:5) + process_submission() shared with REST
  frontend/class-bcm-frontend-nav.php       # BP nav, screens, preferences save, role gates, reign_bp_nav_icons
  frontend/class-bcm-frontend-shortcode.php # [buddypress-contact-me]
  frontend/class-bcm-frontend-notifications.php  # BP notification + emails
  frontend/class-bcm-frontend-assets.php    # enqueue on Contact screens / shortcode pages
  rest/class-bcm-rest-messages.php          # REST API (bcm/v1)
  email/class-bcm-email-installer.php       # installs the 3 BP email posts
edd-license/edd-plugin-license.php          # License tab handlers (activate/deactivate/status)
libs/edd-sl-sdk, libs/package-signatures   # vendored, byte-identical copies; never patch (fix upstream, re-copy)
public/partials/                            # tab-form, tab-inbox, tab-message, tab-preferences
public/css, public/js                       # readable source, shipped as-is (no build step)
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
- **Message SQL lives in `includes/data/class-bcm-messages-repo.php`** (including the Overview `stats()`). Schema SQL lives in the activator. No raw `$wpdb` anywhere else.

## Frontend and data conventions

- **Times are UTC.** `BCM_Messages_Repo::insert()` stores `current_time( 'mysql', true )`; every displayed date goes through `bcm_date_label()` (timezone from `bcm_display_timezone()`, filterable). Never call `wp_date()`, `date_i18n()` or `mysql2date()` directly. Rows from before 1.5.1 are converted once in `install_schema()` (DB_VERSION 1.5.1).
- **Schema changes** bump `BuddyPress_Contact_Me_Activator::DB_VERSION`; `maybe_upgrade()` (bp_init:20, skipped for AJAX, cron and WP-CLI) re-runs `install_schema()` behind a cache lock and only bumps `bcm_db_version` after the `REQUIRED_INDEXES` check passes. `install_schema()` also installs missing emails. One-time data fixes go just before that bump.
- **Upgrade notes:** the 1.5.1 UTC conversion of old rows runs once, guarded only by `bcm_db_version`. Restoring an options backup from before 1.5.1 (without the matching table) would shift message times a second time. The old `buddypress_contact_me_db_version` option is no longer read.
- **Contact preference:** one switch, Contact > Preferences (`contact_me_button`). No per-user meta is seeded; anything except `off` means the member accepts messages. `user_accepts_contact()` checks the user exists first.
- **Abuse limits:** per-sender throttle (`bcm_throttle_*` transients, `bcm_rate_limit` filter), guest honeypot field `bcm_website`, one-time guest captcha (`bcm_captcha_*` transients) unless CAPTCHA Manager (`wbc_captcha_service_manager`, form `contact_me`) is active. Sender copy email goes to logged-in senders only; a member's account email is never shown.
- **Notices:** `bcm_notice()` emits BuddyPress `.bp-feedback` markup; JS toasts use the BP family toast (`.bcm-toast` > `.bp-feedback`), bottom centre, no auto-hide. No flash transients.
- **Dark mode** keys only on `html[data-bx-mode="dark"]` and `body.buddyx-dark-theme`, token chains through `--bx-color-*`; never `prefers-color-scheme` on the frontend. Primary buttons use the paired `--bx-color-button-bg` / `--bx-color-button-fg`. No raw hex outside the token block.
- **Assets** load on the member Contact screens and on any singular post that contains the `[buddypress-contact-me]` shortcode; the shortcode also enqueues on render. One stylesheet serves LTR and RTL through logical properties (no RTL copy).
- **Admin shell view** is `includes/admin/views/layout.php` (hardened hosts reject zips containing `shell.php`).
- **Build:** `bin/build-release.sh` runs `bin/local-ci.sh`, then zips to `dist/` (deflated, fails on plain-text PHP or dev files). No Grunt, no minified copies. POT: `wp i18n make-pot . languages/buddypress-contact-me.pot --exclude=node_modules,vendor,libs,docs,audit,bin,dist`.
- **Data entry points (owner exception):** messages have a member frontend and a REST submit/delete, but no admin list and no REST list by design; recorded in the manifest. Messages a deleted user received are removed on `deleted_user` / `wpmu_delete_user`. No privacy exporter/eraser yet (manifest note).

## Licensing and updates

- The main file registers the product with the bundled EDD SL SDK (`edd_sl_sdk_registry`): host `BUDDYPRESS_CONTACT_ME_LICENCE_HOST` (`https://updates.wbcomdesigns.com`), `fallback_url` `https://wbcomdesigns.com`, item 1528584, key option `edd_wbcom_bp_contact_me_license_key` (unchanged so activated sites stay activated).
- Every licence call goes through `buddypress_contact_me_licence_request()`; `false` means no host answered and the License tab shows "Could not reach the license server" while keeping the last-known status. Never call the store with `wp_remote_*` directly.
- Package signatures: product `buddypress-contact-me` (store slug), key `wbcom-2026-1`, warn mode. Process: `~/.claude/skills/wbcom-services/references/signed-updates.md`.
- The admin screens drop every `admin_notices` callback, so License tab results are printed by the tab view (`edd_BCM_render_admin_notices()`).
