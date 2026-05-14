# BuddyPress Contact Me

> **READ FIRST:** [`audit/manifest.json`](audit/manifest.json) is the canonical inventory — 3 REST endpoints, 0 AJAX, 2 admin pages, 1 shortcode, 1 custom table (`{prefix}contact_me`), 6 hooks fired, 7 settings keys, 0 cron, 0 CPTs. Read it before grepping. See also [`audit/FEATURE_AUDIT.md`](audit/FEATURE_AUDIT.md), [`audit/CODE_FLOWS.md`](audit/CODE_FLOWS.md), [`audit/ROLE_MATRIX.md`](audit/ROLE_MATRIX.md), [`audit/wppqa-baseline-2026-05-11/SUMMARY.md`](audit/wppqa-baseline-2026-05-11/SUMMARY.md). Refresh via `/wp-plugin-onboard --refresh` after non-trivial structural changes.

## Quick Reference

| Field | Value |
|-------|-------|
| Slug | `buddypress-contact-me` |
| Version | 1.5.0 (in-flight on `master`; last released tag is `v1.4.0`) |
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
buddypress-contact-me.php          # bootstrap, activator/deactivator
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
- **No raw `$wpdb` outside `includes/rest/` and `includes/frontend/class-bcm-frontend-submit.php`.** Other modules go through the activator/REST controller.

## Known issues (from wppqa baseline 2026-05-11)

| File | Severity | Note |
|---|---|---|
| `edd-license/edd-plugin-license.php:170` | high | nonce-no-cap on license activate — fix with `current_user_can('manage_options')` (same pattern as buddypress-auto-friends commit 9c5f15f) |
| `edd-license/edd-plugin-license.php:271` | high | nonce-no-cap on license deactivate — same fix |
| `includes/frontend/class-bcm-frontend-submit.php:39+275` | high (intentional public path) | role gate is `viewer_can_send`, not `current_user_can`. Audit but likely safe-by-design. |
| `public/partials/tab-preferences.php:23` | high (own-profile path) | Verify `get_current_user_id()` is the update target, not `$_POST[user_id]` |
| `assets/css/admin.css:556` | low | button 36px tap target (a11y nit) |

## Recent changes

| Date | Description |
|---|---|
| 2026-05-11 | Onboarded — manifest + audit reports + wppqa baseline at `audit/`, READ-FIRST pointer added |
| 2026-05-11 | 1.5.0 (in-flight) — EDD license cap checks added; public REST layer refactored into `bcmApi()` wrapper with 15s timeout + shared `confirmDeleteMessage()` helper. wppqa 5 high → 3 high (all 3 remaining audited safe-by-design with inline comments). |
| 2026-05-14 | 1.5.0 (in-flight) — Frontend palette neutralised so the plugin no longer paints WP-admin blue over the active theme (basecamp 9890995245). New `--bcm-color-on-primary` token keeps text auto-inverted between light and dark mode without per-rule overrides. |
| 2026-05-14 | 1.5.0 (in-flight) — **Migration hardening for 500-site rollout**. The `dbDelta` index migration shipped earlier today is now production-safe: skips on AJAX / REST / cron / WP-CLI; concurrency-locked via `wp_cache_add( 'bcm_db_upgrade_lock', ..., 60 )` so simultaneous requests don't double-ALTER; post-condition `SHOW INDEX` check on every required key (`REQUIRED_INDEXES` const) before bumping `bcm_db_version`; `error_log()` under `WP_DEBUG` when the verify fails (typical case: managed host with DB user lacking `ALTER` privilege). Verified by deleting `bcm_db_version`, hitting a frontend page, confirming both indexes still present + version bumped. |
| 2026-05-14 | 1.5.0 (in-flight) — **Big-site readiness for the inbox**. Added `(reciever, datetime, id)` composite + `(reciever)` single-column indexes via a `dbDelta` migration that runs once per install on `plugins_loaded` (gated by `bcm_db_version` option). `BCM_Messages_Repo::unread_message_ids()` is now hard-capped at 1,000 rows ordered newest-first so the `IN (...)` clause downstream can't blow `max_allowed_packet`. New `BCM_Messages_Repo::count_unread_for_recipient()` returns the true unread COUNT(*) so the "Unread (N)" badge stays accurate above the cap. Inbox template batch-prefetches every sender via `cache_users()` before the render loop, killing N+1 user lookups on cold cache. EXPLAIN confirms: was `type: ALL` + `Using filesort`, now `type: ref` + `bcm_recipient_recent` + no filesort. |
