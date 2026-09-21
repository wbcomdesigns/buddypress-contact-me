# REST API Endpoints (bcm/v1)

BuddyPress Contact Me ships a small REST surface under the `bcm/v1` namespace. There are exactly three routes: submit, delete, and intro-dismiss. There are no `wp_ajax_` admin-ajax handlers; everything runs through these REST routes.

## Authentication

Every route requires the standard WordPress REST cookie and nonce check (`wp_rest`). The submit route additionally requires the form nonce `bcm_form_nonce` (constant `BCM_Frontend_Submit::NONCE_ACTION`) for CSRF defence in depth. Pass it as `bcm_nonce` in the request body.

## POST `/wp-json/bcm/v1/messages`

Submit a new contact message. This is the same code path as the classic-POST handler; both call `BCM_Frontend_Submit::process_submission()`, so validation rules, captcha, and the spam screen are identical regardless of how the form is submitted.

**Permission:** `__return_true`. The route is open, and spam, captcha, role gating, and recipient opt-out are all enforced inside `process_submission()`.

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
| `bcm_captcha_answer`       | int    | Required for guest submissions. The math captcha answer. |
| `bcm_captcha_hash`         | string | Required for guest submissions. The hash emitted by the form. |
| `bcm_shortcode_user_id`    | int    | Override recipient. Used by `[buddypress-contact-me id=...]`. |
| `bcm_shortcode_username`   | string | Override recipient by login. Used by `[buddypress-contact-me user=...]`. |

**Recipient resolution (priority):** `bcm_shortcode_user_id`, then `bcm_shortcode_username`, then `bp_displayed_user_id()`.

**Success response (201):**

```json
{ "ok": true, "id": 123, "message": "Message sent." }
```

**Validation error response (422):**

```json
{
  "ok": false,
  "errors": [
    { "field": "bcm_subject", "message": "Subject must be 3 to 200 characters." }
  ]
}
```

The `field` value is the internal error code (`bcm_name`, `bcm_email`, `bcm_subject`, `bcm_message`, `bcm_spam`, and so on). A bad or missing nonce returns 403 with a `bcm_bad_nonce` error instead.

## DELETE `/wp-json/bcm/v1/messages/{id}`

Delete a single message. The recipient is the only user authorised to delete; even another logged-in member cannot delete a message addressed to someone else.

**Permission callback:** `require_message_owner`. Returns 401 for unauthenticated requests, 404 if the message does not exist, and 403 if the current user is not the recipient.

**Success response (200):**

```json
{ "ok": true, "message": "Message deleted." }
```

**Failure response (400):**

```json
{ "ok": false, "message": "Could not delete the message." }
```

## POST `/wp-json/bcm/v1/preferences/intro-dismiss`

Persist the "I have seen the inbox intro panel" flag for the current user. Used by the inbox UI to remember dismissal across page loads.

**Permission callback:** `require_logged_in`. Returns 401 for unauthenticated requests.

**Side effect:** sets user-meta `bcm_intro_dismissed` to `1` for the current user.

**Success response (200):**

```json
{ "ok": true }
```

## What does not exist

For the avoidance of doubt, these are not real endpoints:

- No `GET /bcm/v1/messages` listing endpoint.
- No `PUT /bcm/v1/messages/{id}/read` mark-as-read endpoint. Marking as read happens automatically when a recipient opens the single-message view.
- No bulk-delete endpoint.
- No admin-ajax actions.

To read messages from PHP, use `BCM_Messages_Repo::list_for_recipient()`. If you need a custom endpoint, register it in your own plugin rather than patching this one.
