# REST API Endpoints (bcm/v1)

BuddyPress Contact Me ships a small REST surface under the `bcm/v1` namespace. There are exactly three routes - submit, delete, and intro-dismiss - and that is it. There are no `wp_ajax_` admin-ajax handlers; everything that used to be admin-ajax in older versions runs through these REST routes.

## Authentication

- **Submit** is open to guests. It requires the form nonce `bcm_form_nonce` (constant `BCM_Frontend_Submit::NONCE_ACTION`), passed as `bcm_nonce` in the request body. Logged-in members also send the usual `X-WP-Nonce` header (`wp_rest`) so WordPress keeps them signed in.
- **Delete** and **intro-dismiss** need a logged-in member with the `X-WP-Nonce` header (`wp_rest`). Delete also checks that the member is the message's recipient.

## POST `/wp-json/bcm/v1/messages`

Submit a new contact message. This is the same code path as the classic-POST handler - both call `BCM_Frontend_Submit::process_submission()` so validation rules, captcha, and spam screen are identical regardless of how the form is submitted.

**Permission:** `__return_true` - the route is open. Spam, captcha, role-based gating, and recipient opt-out checks all happen inside `process_submission()`.

**Required parameters** (in the request body):

| Parameter | Type | Notes |
|-----------|------|-------|
| `bp_contact_me_subject` | string | 3 to 200 characters. |
| `bp_contact_me_msg`     | string | 10 to 5000 characters. |
| `bcm_nonce`             | string | The `bcm_form_nonce` token. |

**Conditional parameters:**

| Parameter | Type | When |
|-----------|------|------|
| `bp_contact_me_first_name` | string | Required for guest submissions. 2 to 100 characters. |
| `bp_contact_me_email`      | string | Required for guest submissions. Must pass `is_email()`. |
| `bcm_captcha_answer`       | int    | Required for guest submissions with the math question. The answer. |
| `bcm_captcha_hash`         | string | Required for guest submissions with the math question. The one-time token from the form (each token works once, for 30 minutes). |
| `bcm_website`              | string | Hidden trap field. Must be empty for guests, or the message is rejected as spam. |
| `bcm_shortcode_user_id`    | int    | Override recipient - used by the `[buddypress-contact-me id=…]` shortcode. |
| `bcm_shortcode_username`   | string | Override recipient by login - used by the `[buddypress-contact-me user=…]` shortcode. |

**Recipient resolution (priority):** `bcm_shortcode_user_id` → `bcm_shortcode_username` → `bp_displayed_user_id()`.

**Success response (201):**

```json
{ "ok": true, "id": 123, "message": "Message sent.", "captcha": { "sum": "7 + 4 = ?", "label": "What is 7 plus 4?", "token": "..." } }
```

`captcha` is a fresh one-time question for a guest's next message, or `null` for members and when the site CAPTCHA service is in use.

Sends are limited per sender (see `bcm_rate_limit`). Over the limit the route returns 422 with the error code `bcm_throttled`.

**Validation error response (422):**

```json
{
  "ok": false,
  "errors": [
    { "field": "bcm_subject", "message": "Subject must be 3 to 200 characters." }
  ]
}
```

## DELETE `/wp-json/bcm/v1/messages/{id}`

Delete a single message. The recipient is the only user authorised to delete - even another logged-in member cannot delete a message addressed to someone else.

**Permission callback:** `require_message_owner` - returns 401 for unauthenticated requests, 404 if the message does not exist, and 403 if the current user is not the recipient.

**Success response (200):**

```json
{ "ok": true, "message": "Message deleted.", "counts": { "all": 48, "unread": 44 }, "titles": { "all": "48 messages", "unread": "44 unread messages" } }
```

`counts` and `titles` are the recipient's inbox totals after the delete, so the page can update its counters without reloading.

**Failure response (400):**

```json
{ "ok": false, "message": "Could not delete the message." }
```

## POST `/wp-json/bcm/v1/preferences/intro-dismiss`

Persist the "I have seen the inbox intro panel" flag for the current user. Used by the inbox UI to remember dismissal across page loads.

**Permission callback:** `require_logged_in` - returns 401 for unauthenticated requests.

**Side effect:** sets user-meta `bcm_intro_dismissed` to `1` for the current user.

**Success response (200):**

```json
{ "ok": true }
```

## What does *not* exist

For the avoidance of doubt, these are NOT real endpoints:

- No `GET /bcm/v1/messages` listing endpoint.
- No `PUT /bcm/v1/messages/{id}/read` mark-as-read endpoint. Marking-as-read happens automatically when a recipient opens the single-message view.
- No bulk delete endpoint.
- No admin-ajax actions (`wp_ajax_bcm_send_message`, `wp_ajax_bcm_delete_message`, etc.) - these were all removed during the 1.5.0 rewrite.

If you need to read messages programmatically, use `BCM_Messages_Repo::list_for_recipient()` from PHP. If you need a custom REST endpoint, register it in your own plugin - do not patch this one.
