# BuddyPress Contact Me — Role / Permission Matrix

**Generated**: 2026-05-11
**Source**: [`manifest.json`](manifest.json)

Plugin does NOT register custom capabilities. All gates use standard WordPress caps + BP profile ownership + plugin role allow-lists.

Legend: **C** = Create, **R** = Read, **U** = Update, **D** = Delete, **—** = No access. **R*** = read-only with allow-list gate.

---

## Admin surfaces

| Feature | Administrator | Editor | Author | Subscriber |
|---|---|---|---|---|
| Access `admin.php?page=buddypress-contact-me` | R/U | — | — | — |
| Save `bcm_admin_general_setting` (notifications, role allow-list, etc) | C/R/U | — | — | — |
| Configure who can send (`bcm_who_contact`) | C/R/U | — | — | — |
| Configure who can receive (`bcm_who_contacted`) | C/R/U | — | — | — |
| License — activate / deactivate / check | C/R/U/D | — | — | — |

All gated by `current_user_can('manage_options')`. Admin form handlers in `edd-license/` need the cap check added (currently nonce-only — wppqa-flagged).

---

## Member-facing surfaces (frontend)

| Feature | Logged-in (any role) | Logged-out |
|---|---|---|
| See own Contact tab on own profile | R | — |
| View own Inbox | R | — |
| Delete own message | D | — |
| Dismiss inbox intro panel | U | — |
| Toggle own accept-contact preference (`contact_me_button`) | U | — |
| See "Send Message" tab on another user's profile | R* (if in `bcm_who_contact` allow-list AND target accepts contact) | — |
| Submit a contact message | C (if in allow-list AND target accepts) | C (with valid captcha) |
| See contents of another user's inbox | — | — |
| Delete another user's message | — | — |

---

## Plugin-level gates (not WordPress caps)

| Gate | Source | Purpose |
|---|---|---|
| `BCM_Frontend_Nav::viewer_can_send( $sender_id, $recipient_id )` | `includes/frontend/class-bcm-frontend-nav.php` | Reads `bcm_admin_general_setting[bcm_who_contact]` role allow-list. If set, only listed roles can SEND. |
| `BCM_Frontend_Nav::user_accepts_contact( $user_id )` | `includes/frontend/class-bcm-frontend-nav.php` | Reads `contact_me_button` usermeta. Recipient opt-in toggle. |
| Captcha check | `BCM_Rest_Messages::submit` | Required for `!is_user_logged_in()` submissions. |
| `BCM_Rest_Messages::require_message_owner` | `includes/rest/class-bcm-rest-messages.php` | REST permission_callback for DELETE — checks the message's `reciever` column matches `current_user_id`. |

---

## REST API permissions

| Endpoint | Permission |
|---|---|
| POST `/bcm/v1/messages` | `__return_true` (gated by nonce + captcha + role allow-list inside the handler) |
| DELETE `/bcm/v1/messages/{id}` | `require_message_owner` — current user must be the message recipient |
| POST `/bcm/v1/preferences/intro-dismiss` | `require_logged_in` |

---

## Capability gaps (intentional design choices)

- **No custom caps registered.** Multi-staff workflows are not in the current roadmap. If multi-admin moderation becomes a requirement, `bcm_moderate_messages` (read any inbox, delete any message) and `bcm_manage_settings` (separate from `manage_options`) would be natural additions.
- **Frontend submit handler is nonce-only.** This is intentional — the public path needs to work for anonymous submissions. The capability layer is the role allow-list, not a WP cap. wppqa-flagged but design-by-design.
