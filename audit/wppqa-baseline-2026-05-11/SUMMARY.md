# wppqa baseline — 2026-05-11 (HEAD = 887783c, master)

Phase 0 of `/wp-plugin-onboard` — surfaces real bugs before generating the manifest.

| Check | Passed | Failed | Skipped | Duration |
|---|---|---|---|---|
| plugin-dev-rules | 4 | 5 | 0 | 17ms |
| wiring-completeness | 0 | 0 | 1 | 0ms |
| rest-js-contract | 3 | 0 | 0 | 5ms |

## plugin-dev-rules — 5 high-severity findings

All 5 errors are `nonce-no-cap` (nonce verified, no `current_user_can` paired). Triage per finding:

### 1. `edd-license/edd-plugin-license.php:170` — EDD license activate

**Same shared EDD util as buddypress-auto-friends.** The auto-friends fix added `current_user_can('manage_options')` ahead of the nonce check; same fix applies here. Severity: high — non-admins with the activation nonce can write license options.

### 2. `edd-license/edd-plugin-license.php:271` — EDD license deactivate

**Same as #1.** Apply identical cap-check fix.

### 3. `includes/frontend/class-bcm-frontend-submit.php:39` — frontend submit (PUBLIC)

**Triage: intentional, BUT review the role gate.** This is a public-facing contact-form handler — anonymous users with captcha can submit. The "capability" gate is `BCM_Frontend_Nav::viewer_can_send()` which checks the `bcm_who_contact` role allow-list, NOT `current_user_can()`. wppqa's regex can't see this role gate. Verify the role gate runs BEFORE any state-changing operation.

### 4. `includes/frontend/class-bcm-frontend-submit.php:275` — frontend submit (PUBLIC)

**Same path as #3.** Same triage applies.

### 5. `public/partials/tab-preferences.php:23` — preferences tab POST handler

**Triage: own-profile gating.** This updates the `contact_me_button` usermeta toggle (accept/reject contact messages). Updates run against the current user only — the cap is implicit ("you can only toggle your own preference"). Verify the handler reads `get_current_user_id()` for the user_meta_update and doesn't accept an arbitrary user_id from $_POST.

## plugin-dev-rules — 1 low warning

`assets/css/admin.css:556` — button height 36px <40px tap target. Accessibility nit, not blocking.

## wiring-completeness — skipped

No `templates/` directory. Plugin uses `public/partials/` instead; wppqa's heuristic doesn't match this convention. Not a defect.

## rest-js-contract — clean (3 passed)

REST handlers in `includes/rest/class-bcm-rest-messages.php` return predictable shapes and the JS consumer (`public/js/buddypress-contact-me-public.js`) accesses fields that exist in the PHP envelope.

## Recommended fix order

1. **Apply auto-friends EDD cap-check pattern to #1 + #2** — straightforward, mirrors commit `9c5f15f` in buddypress-auto-friends.
2. **Audit #3, #4, #5 manually** — these are likely intentional public/own-profile paths, but the role/user gate must run before any state-changing query. Quick read-through of the 3 files (~150 lines combined) confirms whether they're safe or need defense-in-depth caps.
3. **Defer the tap-target warning** — schedule for a future a11y polish pass alongside the same finding in buddypress-auto-friends.
