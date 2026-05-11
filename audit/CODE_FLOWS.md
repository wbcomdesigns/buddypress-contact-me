# BuddyPress Contact Me — Code Flow Maps

**Generated**: 2026-05-11
**Source**: [`manifest.json`](manifest.json)

---

## Flow 1 — Send a contact message (REST + form POST)

**Entry**: visitor opens `/members/{user}/contact/send/`, fills form, submits.

```
form POST or REST POST /bcm/v1/messages
  -> BCM_Rest_Messages::submit() (includes/rest/class-bcm-rest-messages.php)
     -> nonce check (wp_rest + bcm_form_nonce)
     -> captcha check if !is_user_logged_in()
     -> BCM_Frontend_Nav::viewer_can_send( $sender, $recipient )    [role allow-list — bcm_who_contact]
     -> BCM_Frontend_Nav::user_accepts_contact( $recipient )         [reads contact_me_button usermeta]
     -> BCM_Frontend_Submit::insert()                                [INSERT INTO {prefix}contact_me]
     -> do_action( 'bp_contact_me_form_save', $msg_id, $recipient, $sender )
          -> BCM_Frontend_Notifications::queue_notification()        [bp_notifications_add_notification — BP bell]
          -> BCM_Frontend_Notifications::send_email()                [bp_send_email — recipient + optional admin/sender copy]
     -> redirect or JSON response
```

**Key invariants:**
- Anonymous submissions require captcha (no nonce alone is enough).
- Role allow-list (`bcm_who_contact`) is the de-facto capability gate; if empty, all logged-in users can send.
- Recipient opt-out (`contact_me_button=0`) blocks send regardless of sender role.

---

## Flow 2 — View inbox

**Entry**: own profile → `/members/{me}/contact/inbox/`.

```
BP screen function: BCM_Frontend_Nav::screen_inbox()
  -> bp_is_my_profile() guard
  -> SELECT * FROM {prefix}contact_me WHERE reciever = current_user_id  [sic — misspelled column]
  -> render public/partials/inbox.php
  -> public.js wires:
       - inbox row click  -> expand/collapse message
       - delete button    -> REST DELETE /bcm/v1/messages/{id}
       - intro X button   -> REST POST /bcm/v1/preferences/intro-dismiss
```

---

## Flow 3 — Toggle accept-contact preference

**Entry**: own profile → `/members/{me}/contact/preferences/`, toggles checkbox.

```
form POST to /members/{me}/contact/preferences/
  -> public/partials/tab-preferences.php:23 (the wppqa-flagged file)
     -> wp_verify_nonce( $_POST['_wpnonce'], 'bcm_preferences' )
     -> update_user_meta( get_current_user_id(), 'contact_me_button', $value )
     -> redirect
```

**Audit point:** verify line 23 reads `get_current_user_id()` for the update target. If it reads `$_POST['user_id']` instead, a logged-in user could flip another user's preference. wppqa flagged the missing cap check; manual review of those ~20 lines confirms whether it's safe.

---

## Flow 4 — License activation (EDD)

**Entry**: admin enters license key on the License tab, clicks Activate.

```
form POST to admin.php?page=buddypress-contact-me&tab=license
  -> admin_init action
  -> edd_BCM_handle_activate() (edd-license/edd-plugin-license.php:166-220)
     -> check_admin_referer( EDD_BCM_LICENSE_NONCE_ACTION, ... )
     -> [MISSING: current_user_can('manage_options') — wppqa-flagged]
     -> edd_BCM_call_api( 'activate_license', $license )
        -> wp_remote_post to wbcomdesigns.com EDD API
     -> update_option / delete_option for license_key, license_status, expires
     -> redirect with status message
```

**Fix:** mirror the auto-friends 1.8.3 fix (commit `9c5f15f`) — add `current_user_can('manage_options')` ahead of the nonce check. Same applies to `edd_BCM_handle_deactivate()` at line ~267.

---

## Flow 5 — Delete a message

**Entry**: own profile inbox → click delete on a message row.

```
JS click -> apiFetch DELETE /bcm/v1/messages/{id}
  -> BCM_Rest_Messages::delete_one()
     -> permission_callback: require_message_owner( $message_id, $current_user_id )
        -> SELECT reciever FROM {prefix}contact_me WHERE id = ?
        -> compare against current_user_id
     -> if owner: DELETE FROM {prefix}contact_me WHERE id = ?
     -> return JSON { success: true }
  -> JS removes the row from the DOM
```

---

## Key files (architecture overview)

| File | Role |
|---|---|
| `buddypress-contact-me.php` | Bootstrap, activator/deactivator hooks, plugin header |
| `includes/class-buddypress-contact-me.php` | Core orchestrator — wires hooks via Loader |
| `includes/class-buddypress-contact-me-loader.php` | Hook registry (also exposes the `add_shortcode` wrapper) |
| `includes/class-buddypress-contact-me-activator.php` | Creates `{prefix}contact_me` table on activation |
| `includes/admin/class-bcm-admin.php` | Admin menu + settings registration |
| `includes/frontend/class-bcm-frontend-submit.php` | Form submission handler (wppqa flagged 2 nonce-no-cap here) |
| `includes/frontend/class-bcm-frontend-nav.php` | BP nav registration, role gates, screen functions |
| `includes/frontend/class-bcm-frontend-shortcode.php` | `[buddypress-contact-me]` shortcode |
| `includes/frontend/class-bcm-frontend-notifications.php` | BP notification + email sender |
| `includes/rest/class-bcm-rest-messages.php` | REST routes |
| `includes/email/class-bcm-email-installer.php` | BP email post installer |
| `edd-license/edd-plugin-license.php` | License activate/deactivate forms (wppqa flagged 2 nonce-no-cap) |
| `public/partials/tab-preferences.php` | Preferences tab template (wppqa flagged 1 nonce-no-cap) |
| `public/partials/inbox.php` | Inbox template |
| `public/partials/send.php` | Send-message form template |
