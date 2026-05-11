# BuddyPress Contact Me — Feature Audit Report

**Generated**: 2026-05-11
**Version**: 1.5.0
**Source**: [`manifest.json`](manifest.json)
**Counts**: 3 REST endpoints · 0 AJAX · 2 admin pages · 1 shortcode · 1 custom table · 6 hooks fired · 0 cron · 0 CPTs · 0 WP-CLI

---

## 1. Frontend features

### 1.1 BP Profile tab — `contact`

| Property | Value |
|---|---|
| Main nav slug | `contact` (legacy alias: `contact-me`) |
| Position | 80 |
| Sub-nav (own profile) | `inbox`, `preferences` |
| Sub-nav (other profile) | `send` |
| Source | `includes/frontend/class-bcm-frontend-nav.php` |

**Visibility rules:**
- `inbox` + `preferences` — only on **own** profile
- `send` — only on **other** profiles (and only when the viewed user accepts contact AND the viewer is in `bcm_who_contact` role allow-list)

### 1.2 Shortcode — `[buddypress-contact-me]`

| Property | Value |
|---|---|
| Tag | `buddypress-contact-me` |
| Attrs | `id`, `user` |
| Handler | `BCM_Frontend_Shortcode::render` (`includes/frontend/class-bcm-frontend-shortcode.php:22`) |
| Purpose | Embed the contact form for a specific member outside the BP profile (e.g. on a regular page) |

---

## 2. AJAX handlers

**None.** This plugin uses REST + classic form POST instead of WP AJAX.

---

## 3. REST endpoints

Namespace: `bcm/v1`. Defined in `includes/rest/class-bcm-rest-messages.php`.

| Method | Route | Handler | Permission | Purpose |
|---|---|---|---|---|
| POST | `/messages` | `BCM_Rest_Messages::submit` | `__return_true` (gated by nonce + captcha + role allow-list inside handler) | Submit a contact message (anonymous or logged-in) |
| DELETE | `/messages/{id}` | `BCM_Rest_Messages::delete_one` | `require_message_owner` | Delete a message owned by the requesting user |
| POST | `/preferences/intro-dismiss` | `BCM_Rest_Messages::dismiss_intro` | `require_logged_in` | Mark inbox intro panel as dismissed |

---

## 4. Admin pages

| Slug | Parent | Capability | Source | Purpose |
|---|---|---|---|---|
| `wbcomplugins` | — | `manage_options` | `includes/admin/class-bcm-admin.php:92` | Shared WB Plugins top-level hub |
| `buddypress-contact-me` | `wbcomplugins` | `manage_options` | `includes/admin/class-bcm-admin.php:103` | Plugin settings page (tabbed) |

---

## 5. Settings inventory

### `bcm_admin_general_setting` (wp_options, array)

| Field | Type | Controls |
|---|---|---|
| `bcm_allow_notification` | bool | Master toggle for BP notifications on new messages |
| `bcm_allow_email` | bool | Send email to recipient when message arrives |
| `bcm_allow_admin_copy_email` | bool | Send admin a copy of each message |
| `bcm_allow_sender_copy_email` | bool | Send sender a copy of their own submission |
| `bcm_allow_contact_tab` | bool | Show/hide the Contact tab on BP profiles |
| `bcm_who_contact` | string[] | Role allow-list — who can SEND messages |
| `bcm_who_contacted` | string[] | Role allow-list — who can RECEIVE messages |

### EDD license keys (wp_options)

| Key | Purpose |
|---|---|
| `edd_wbcom_bp_contact_me_license_key` | License key value |
| `edd_wbcom_bp_contact_me_license_status` | active / invalid / expired |
| `edd_wbcom_bp_contact_me_license_expires` | Expiration date |

### User-level preferences (wp_usermeta)

| Key | Type | Controls |
|---|---|---|
| `contact_me_button` | bool | Per-user toggle: accept contact messages |
| `bcm_intro_dismissed` | bool | Inbox intro panel dismissal state |

### Internal

| Key | Purpose |
|---|---|
| `buddypress_contact_me_db_version` | Upgrade tracker |

---

## 6. Database table

**`{prefix}contact_me`** — created in `includes/class-buddypress-contact-me-activator.php:31`.

| Column | Type | Note |
|---|---|---|
| `id` | mediumint, auto-increment | PK |
| `sender` | int | User ID of sender (0 for anonymous) |
| `reciever` | int | User ID of recipient. **Note: misspelled in schema** — keep this spelling on read/write |
| `subject` | varchar(255) | Message subject |
| `message` | TEXT | Body |
| `name` | varchar(255) | Sender display name (anonymous flow) |
| `email` | varchar(255) | Sender email (anonymous flow) |
| `datetime` | varchar(255) | ISO timestamp |

---

## 7. Content types (CPTs / taxonomies)

**None.**

---

## 8. JavaScript modules

| File | Context | Purpose |
|---|---|---|
| `public/js/buddypress-contact-me-public.js` | Profile pages | Inbox + send form interactivity |
| `public/js/toast.js` | Profile pages | Toast notification util |
| `public/js/confirm.js` | Profile pages | Confirm modal util |
| `assets/js/admin.js` | Admin settings | Settings UI |

Minified variants under `public/js/min/` and `assets/js/min/` (where present). RTL stylesheet at `public/css/rtl/`.

---

## 9. Email templates (BuddyPress email system)

- `BCM_Email_Installer` creates the BP email post on activation
- Notification template merged via `bp_send_email( 'bcm_user_notifications', ... )` after `bp_contact_me_form_save` fires
- Admin copy + sender copy controlled by `bcm_allow_admin_copy_email` / `bcm_allow_sender_copy_email` settings

---

## 10. Cron jobs

**None.** Email sends happen synchronously after `bp_contact_me_form_save` action.

---

## 11. Hooks fired

### Action

| Hook | Args | Where |
|---|---|---|
| `bp_contact_me_form_save` | 3 (`$message_id`, `$recipient_id`, `$sender_id`) | `includes/frontend/class-bcm-frontend-submit.php:150` |

### Filters

| Hook | Args | Where | Purpose |
|---|---|---|---|
| `bcm_admin_tabs` | 1 | `includes/admin/class-bcm-admin.php:62` | Add/remove admin tabs |
| `bcm_spam_check` | 3 | `includes/frontend/class-bcm-frontend-submit.php:221,227,230` | Custom spam validation extension point |
| `bcm_license_api_sslverify` | 1 | `edd-license/edd-plugin-license.php:124` | Control SSL verify on EDD API calls |
| `bp_core_template_plugin` | 1 | `includes/frontend/class-bcm-frontend-nav.php:141,149,157` | BP core template override (3 callsites: inbox, preferences, send) |
| `edd_bmpro_sl_api_request_verify_ssl` | 2 | `edd-license/class-edd-bp-contact-me-plugin-updater.php:485` | EDD updater SSL verify |

---

## 12. Custom capabilities

**None registered.** All admin gates use `manage_options`; member-facing flows use:
- BP profile ownership (`bp_is_my_profile()`) for own-profile sub-nav
- `BCM_Frontend_Nav::viewer_can_send()` role allow-list (reads `bcm_who_contact`) for send eligibility
- `BCM_Frontend_Nav::user_accepts_contact()` per-user opt-in (reads `contact_me_button` usermeta) for recipient consent

---

## 13. Known issues surfaced by audit

| Source | Finding | Severity | Status |
|---|---|---|---|
| wppqa Phase 0 | `edd-license/edd-plugin-license.php:170` activate handler — nonce only | High | **Fix needed** (same pattern as auto-friends commit `9c5f15f`) |
| wppqa Phase 0 | `edd-license/edd-plugin-license.php:271` deactivate handler — nonce only | High | **Fix needed** (same pattern) |
| wppqa Phase 0 | `includes/frontend/class-bcm-frontend-submit.php:39` frontend submit | High (intentional public path) | **Audit needed** — verify role gate (`viewer_can_send`) runs before state writes |
| wppqa Phase 0 | `includes/frontend/class-bcm-frontend-submit.php:275` frontend submit | High (intentional public path) | **Audit needed** — same |
| wppqa Phase 0 | `public/partials/tab-preferences.php:23` preferences toggle | High (own-profile path) | **Audit needed** — verify `get_current_user_id()` is used, not `$_POST[user_id]` |
| wppqa Phase 0 | `assets/css/admin.css:556` button height <40px tap target | Low | Defer (a11y nit, same as auto-friends) |
| schema | `reciever` column misspelled | Low | Long-standing; do NOT rename (breaks every existing query) |
