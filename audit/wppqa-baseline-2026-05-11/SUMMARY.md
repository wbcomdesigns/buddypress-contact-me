# wppqa baseline — 2026-05-11 (initial baseline + audit verdicts)

Phase 0 of `/wp-plugin-onboard` — surfaces real bugs before generating the manifest.

| Check | Initial | After fixes | Δ |
|---|---|---|---|
| plugin-dev-rules passed | 4 | 6 | +2 |
| plugin-dev-rules high errors | 5 | 3 (all triaged safe-by-design) | -2 |
| wiring-completeness | skipped (no `templates/` dir) | unchanged | — |
| rest-js-contract | 3 passed, 0 failed | unchanged | — |

## plugin-dev-rules — finding-by-finding verdict

### Fixed (commits in this onboarding session)

| File:Line | Verdict | Fix |
|---|---|---|
| `edd-license/edd-plugin-license.php:170` | High — license activate handler was nonce-only | Added `current_user_can('manage_options')` ahead of nonce check (mirrors buddypress-auto-friends commit 9c5f15f) |
| `edd-license/edd-plugin-license.php:271` | High — license deactivate handler was nonce-only | Same fix pattern |

### Safe-by-design (manually audited, NOT fixed)

| File:Line | Verdict | Audit evidence |
|---|---|---|
| `includes/frontend/class-bcm-frontend-submit.php:39` | High → **safe-by-design** | The "capability" gate is the plugin's role allow-list, not a WP cap. `process_submission()` (called at line 43, immediately after the nonce check) runs `BCM_Frontend_Nav::viewer_can_send()` at line 71 BEFORE the INSERT at line 128. Public path (anonymous users can submit with captcha); a `current_user_can` check would block legitimate use. |
| `includes/frontend/class-bcm-frontend-submit.php:275` | High → **safe-by-design** | The handler `save_settings_toggle()` runs only when `bp_is_settings_component() && bp_is_current_action('general')` (line 272 — own-profile BP-settings context). Update target is `bp_loggedin_user_id()` (line 280), NOT `$_POST['user_id']`. No way to flip another user's preference. |
| `public/partials/tab-preferences.php:23` | High → **safe-by-design** | Line 15 sets `$bcm_user_id = get_current_user_id()`; line 16-18 explicitly bails when `bp_displayed_user_id() !== $bcm_user_id`. The `update_user_meta` at line 25 writes to the gated `$bcm_user_id`, not any user-controlled value. |

**Decision:** The 3 safe-by-design hits are kept as-is. Adding redundant `current_user_can` calls would not improve security and would obscure the actual gate (role allow-list / own-profile check). Future wppqa runs will continue to flag these — that's a known false-positive for these 3 specific lines.

### Outstanding low-severity

| File:Line | Detail |
|---|---|
| `assets/css/admin.css:556` | Button height 36px <40px tap target — a11y nit, defer to a future polish pass alongside the same finding in buddypress-auto-friends |

## Recommended next steps

1. Apply the EDD cap-check fix to other Wbcom plugins that ship the same shared util (every plugin under wbcomdesigns/* with an `edd-license/` directory). 1.8.3 of buddypress-auto-friends and this commit are the canonical reference.
2. Address the 11 GitHub Dependabot CVEs on this repo (2 high, 7 moderate, 2 low) — almost certainly Grunt devDeps; `npm audit` will confirm.
