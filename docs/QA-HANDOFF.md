# BuddyPress Contact Me — QA Handoff (v1.5.0 release candidate)

**Build under test:** branch `feature/1.5.0-release`, HEAD after this commit.
**Compared against:** the last shipped release, **v1.4.0**.
**Status:** 1.5.0 has **not yet been released**. This is a first-time ship of the rebuilt plugin. QA sign-off is the gate to merge `feature/1.5.0-release` → `master` and tag `v1.5.0`.
**Tester follows:** [`docs/QA-MANUAL.md`](./QA-MANUAL.md) (15 sections, v1.5.0). Because this is a ground-up rewrite, the full manual should be run — this handoff just adds context.

---

## What's new in 1.5.0

1.5.0 is the card-panel admin rebuild — the shape every other migrated Wbcom plugin has shipped in (BPSP 2.3.7, BAUTOF 1.8.2). Summary of customer-facing changes:

- **New admin UI**: Overview, Notifications, Access, License tabs inside a shared "WB Plugins" top-level menu. Replaces the legacy tab-based wrapper.
- **New BP email template** installed on activation, cleaned up on uninstall.
- **Per-recipient email delivery by default** with an explicit toggle for the multi-recipient / show-others mode.
- **Role-chip grid** for access control, replaces the selectize dropdown (friendlier on mobile).
- **Access tab** with distinct "Who can send" + "Who can be contacted" role sets.
- **License tab inside the plugin** (no more separate wbcom-license-page).
- **Per-member opt-out** via the Preferences sub-tab on the frontend profile.
- **REST API** at `/wp-json/bcm/v1/messages` (submit + delete) and `/wp-json/bcm/v1/preferences/intro-dismiss`.

Full entry lives in `readme.txt` under `= 1.5.0 =`.

---

## What changed on the release branch *during QA prep*

Everything below lives on `feature/1.5.0-release` and has not yet been part of any shipped release. All three commits are polish on the 1.5.0 candidate:

| Commit | Change | Why QA should care |
|---|---|---|
| `b09898f` | Plugin Check sweep — ABSPATH guards on the loader / core class / edd-license file, translators comments on the two license-flow `__()` calls with placeholders, README `Requires at least` aligned with plugin header (both now `6.0`). | Light regression risk on the License tab and admin bootstrap. Mainly improves static-analysis + translator experience. |
| `2956d8f` | Added `docs/QA-MANUAL.md` (15 sections). | The document QA actually runs. |
| `ddcd271` | **Fix**: `render_page()` was writing a `$tabs` variable while `shell.php` read `$bcm_tabs` — every admin tab threw "Undefined variable $bcm_tabs" and "foreach() argument must be of type array|object, null given". After the rename, the sidebar nav renders properly. | **Critical.** Without this fix, the entire admin UI is broken with `WP_DEBUG=true` and the sidebar is blank. Verify the sidebar nav populates on every tab. |

Because 1.5.0 has never shipped, customers will only ever see the post-fix behavior — no "what's new since X.Y.Z" hand-wringing needed on these.

---

## Focused test plan (on top of `QA-MANUAL.md`)

### Must-pass — full admin regression

Run **§1 through §5** of `QA-MANUAL.md` end-to-end. Particular attention to:

- Sidebar nav populates on every tab (Overview / Notifications / Access / License). This is the direct target of the `ddcd271` fix. With `WP_DEBUG=true` + `WP_DEBUG_LOG=true`, `wp-content/debug.log` should stay empty.
- Access tab role chips — check / uncheck mix works, select-all / clear-all chips work on both "Who can send" and "Who can be contacted" grids.
- Notifications tab — toggle `bcm_allow_notification`, `bcm_allow_email`, `bcm_allow_admin_copy_email`, `bcm_allow_sender_copy_email` independently. Each save round-trips to `get_option( 'bcm_admin_general_setting' )`.

### Must-pass — frontend golden paths

Run **§6 through §8** of `QA-MANUAL.md`:

- Logged-in submit on a recipient profile (form → success toast → row in `wp_contact_me` → BP notification + email fire per settings).
- Logged-out visitor submit (with captcha if enabled).
- Recipient opt-out via Preferences sub-tab — after opt-out, sender sees the "not accepting messages" copy.
- Inbox: intro dismissal (`bcm_intro_dismissed` user meta → 1), delete (DELETE `/bcm/v1/messages/{id}` → 200 for owner, 403 for non-owner).

### Must-pass — install / uninstall data integrity

**§1.1** (fresh activation) and **§1.3** (uninstall) on a **throwaway** site:

- Activation: `wp_contact_me` table exists, every user has `contact_me_button = 'on'`, BP email template appears under Dashboard → Emails.
- Uninstall: table gone, email post gone, options gone, meta gone — *unless* `BCM_KEEP_DATA_ON_UNINSTALL` is defined, in which case data is preserved.

### Must-pass — REST permissions

`§11`:

- `POST /bcm/v1/messages` as logged-out, logged-in, and a role that access rules forbid — only the allowed cases should succeed.
- `DELETE /bcm/v1/messages/{id}` as the message owner vs a different user — 200 vs 403.

### Recommended — accessibility, edge cases, regression vs 1.4.x

`§10`, `§12`, `§14` of `QA-MANUAL.md` cover these.

---

## Sign-off

| # | Section | Result | Notes |
|---|---------|--------|-------|
| 1 | §1 Install / activate / deactivate / uninstall | ☐ Pass ☐ Fail |  |
| 2 | §2 Overview | ☐ Pass ☐ Fail |  |
| 3 | §3 Access | ☐ Pass ☐ Fail |  |
| 4 | §4 Notifications | ☐ Pass ☐ Fail |  |
| 5 | §5 License | ☐ Pass ☐ Fail |  |
| 6 | §6 Frontend contact form | ☐ Pass ☐ Fail |  |
| 7 | §7 Inbox | ☐ Pass ☐ Fail |  |
| 8 | §8 Preferences | ☐ Pass ☐ Fail |  |
| 9 | §9 Shortcode | ☐ Pass ☐ Fail |  |
| 10 | §10 Accessibility | ☐ Pass ☐ Fail |  |
| 11 | §11 REST API | ☐ Pass ☐ Fail |  |
| 12 | §12 Edge cases | ☐ Pass ☐ Fail |  |

**Tested by:** ____________________ &nbsp;&nbsp;&nbsp; **Date:** ____________________
**Overall:** ☐ Approved — proceed to merge `feature/1.5.0-release` → `master` and tag `v1.5.0`.  ☐ Blocked (attach issues).
