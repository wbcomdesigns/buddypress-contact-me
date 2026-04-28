# BuddyPress Contact Me — QA Manual (v1.5.0)

Run through this before tagging a release. Every step has **Setup → Steps → Expected** so it's reproducible.

## Documentation References

Before testing, review the complete documentation suite:
- **[Installation Guide](./installation.md)** - System requirements and setup instructions
- **[User Guide](./user-guide.md)** - End-user functionality and features
- **[Developer Guide](./developer-guide.md)** - API reference and extension points
- **[Troubleshooting Guide](./troubleshooting.md)** - Common issues and solutions

---

## 0. Preconditions

- WordPress ≥ 6.0, PHP ≥ 7.4.
- BuddyPress active with the **Notifications** component enabled (required for in-app notifications).
- At least 3 test users: one administrator, one regular member (e.g. subscriber) who will be the *recipient*, one second regular member who will be the *sender*.
- A working SMTP / mail pipeline (or an email logger plugin like WP Mail Logger) so you can inspect delivered mail.
- A logged-out browser session available (incognito window) to exercise the non-logged-in contact path.

---

## 1. Install / activate / deactivate / uninstall

### 1.1 Fresh activation

1. Upload/unzip the plugin, activate it.
2. **Expected:**
   - No PHP notices in `wp-content/debug.log`.
   - A new sidebar entry **WB Plugins → Contact Me** appears (the hub menu is shared across every Wbcom plugin).
   - `wp option get bcm_admin_general_setting` returns the default array with `bcm_allow_contact_tab`, `bcm_who_contact`, `bcm_who_contacted`, `bcm_allow_notification`, `bcm_allow_email`, `bcm_allow_admin_copy_email`, `bcm_allow_sender_copy_email` keys populated.
   - `wp option get buddypress_contact_me_db_version` returns the current version.
   - A `wp_contact_me` table exists (check `wp db query "SHOW TABLES LIKE '%contact_me%'"`).
   - Every existing user has `contact_me_button = 'on'` in `wp_usermeta`.
   - A BuddyPress email post titled "A new contact message" (or similar) exists in **Dashboard → Emails**.

### 1.2 Deactivation does not lose data

1. Deactivate from Plugins screen.
2. **Expected:** Messages table, settings, per-user opt-out flags all remain intact. Reactivation shows the same state.

### 1.3 Uninstall (run on a throwaway site)

1. Deactivate, then Delete from Plugins screen.
2. **Expected:**
   - `wp_contact_me` table is gone (`wp db query "SHOW TABLES LIKE '%contact_me%'"` returns empty).
   - `wp option get bcm_admin_general_setting` → null.
   - `wp option get buddypress_contact_me_db_version` → null.
   - All `edd_wbcom_bp_contact_me_license_*` options gone.
   - `contact_me_button` and `bcm_intro_dismissed` user meta cleared.
   - The BP email post + `bp-email-type` term for this template is removed.
3. **Opt-out path:** add `define( 'BCM_KEEP_DATA_ON_UNINSTALL', true );` to `wp-config.php`, repeat uninstall.
   - **Expected:** Table + options + meta are retained so the site owner can keep their archive.

---

## 2. Admin — Overview tab

1. Navigate to **WB Plugins → Contact Me** (Overview / default tab).
2. **Expected:**
   - Plugin version pill renders in the header.
   - Settings sidebar lists Overview, Access, Notifications, License tabs — every tab opens a real form or dashboard. No dead tabs.
   - Quick-link cards point to each settings tab.

---

## 3. Admin — Access settings

### 3.1 Contact tab toggle

1. Uncheck **Show a Contact tab on member profiles**. Save.
2. Visit any member profile on the frontend.
3. **Expected:** The Contact tab no longer renders in the BP profile nav.
4. Re-enable. Save.
5. **Expected:** The tab reappears.

### 3.2 Who can *send* (bcm_who_contact)

1. In **Who can send contact messages** check only `visitors` + `subscriber`; uncheck editor, administrator.
2. Save.
3. Log in as editor, visit a subscriber profile → Contact tab.
4. **Expected:** The contact form is hidden OR replaced with a clear "You don't have permission to contact this member" message. The REST `POST /wp-json/bcm/v1/messages` endpoint also rejects the submission with a permission error if called directly.
5. Log in as subscriber → same profile → the form renders.

### 3.3 Who can *be contacted* (bcm_who_contacted)

1. In **Whose profiles show the contact form** check only `subscriber`; uncheck every other role.
2. Save.
3. Visit an administrator's profile → Contact tab.
4. **Expected:** The form is hidden. Visit a subscriber's profile → form renders.

### 3.4 Logged-out visitor path

1. Keep `visitors` checked in §3.2.
2. Open an incognito window, visit a subscriber profile → Contact tab.
3. **Expected:** Form renders with extra fields for name + email + (if enabled) captcha. Submitting a valid form posts to `POST /wp-json/bcm/v1/messages` with `bp_contact_me_first_name` + `bp_contact_me_email` populated.
4. Uncheck `visitors` in §3.2. Save. Repeat the incognito visit.
5. **Expected:** Form is hidden / replaced with "Please log in to contact this member." The REST endpoint rejects the same submission.

---

## 4. Admin — Notifications settings

### 4.1 BuddyPress notification toggle

1. Enable **Send BuddyPress notifications** (`bcm_allow_notification`). Save.
2. As sender, submit a message to recipient.
3. **Expected:** Recipient's BuddyPress notification bell shows a new unread notification. Clicking it deep-links to their Contact → Inbox.

### 4.2 Email toggle

1. Enable **Send email notifications** (`bcm_allow_email`). Save.
2. Submit a new message as sender.
3. **Expected:** Recipient receives an email. The email body matches the template post and contains the subject, the message body, sender name, and a "Reply" link. No raw PHP placeholders like `{{sender}}` should be visible.

### 4.3 Admin copy

1. Enable **Send admin a copy** (`bcm_allow_admin_copy_email`). Save.
2. Send a message.
3. **Expected:** The site admin email receives a CC/BCC copy. Subject is prefixed or clearly marked as an admin copy.

### 4.4 Sender copy

1. Enable **Send sender a copy** (`bcm_allow_sender_copy_email`). Save.
2. Send a message as a logged-in sender.
3. **Expected:** The sender's account email receives a copy.

### 4.5 All-off mode

1. Disable all four toggles. Save. Send a message.
2. **Expected:** The message is still stored in `wp_contact_me`, but no notifications or emails are dispatched (verify the mail log is empty after the submit).

---

## 5. Admin — License tab

1. Visit the License tab.
2. **Expected:** Inputs for license key, Activate / Deactivate buttons; if activated, shows **Expires on …** text.
3. Enter a test key (if available). Activate.
4. **Expected:** Status switches to Active. `wp option get edd_wbcom_bp_contact_me_license_status` returns `valid` and `edd_wbcom_bp_contact_me_license_expires` holds an ISO date.
5. Deactivate. Status flips to inactive; key field still holds the old key for convenience.

---

## 6. Frontend — Contact form on profile

### 6.1 Logged-in submit (golden path)

1. Log in as sender. Visit recipient's profile → Contact tab.
2. Fill subject + message. Submit.
3. **Expected:**
   - No browser-native `alert()` / `confirm()` at any point.
   - Form shows a success toast / inline confirmation; form fields reset.
   - New row in `wp_contact_me` with `sender`, `reciever`, `subject`, `message`, `datetime`.
   - Notifications + emails fire per §4.

### 6.2 Validation

1. Try to submit with empty subject → blocked client-side with a clear error message.
2. Try empty message → same.
3. As logged-out visitor, submit with invalid email → blocked.
4. As logged-out visitor, submit with a wrong captcha answer (if captcha is enabled) → REST returns an error, UI shows an inline error.

### 6.3 Rate-limit / spam (if implemented)

1. Submit the same form 5 times rapidly.
2. **Expected:** Either all accepted (if no rate limit) or later ones refused with a throttle message. No fatal errors either way.

### 6.4 Recipient opt-out overrides everything

1. Log in as recipient → their profile → Contact → Preferences sub-tab.
2. Uncheck **Accept contact messages** (`contact_me_button` becomes `off`). Save.
3. Log out / switch to sender. Visit recipient's profile → Contact tab.
4. **Expected:** Form is hidden even though role-based access would have allowed it. Copy says something like "This member isn't accepting messages right now."

---

## 7. Frontend — Inbox sub-tab (recipient view)

**Preconditions:** Recipient has at least 2 messages in `wp_contact_me`.

### 7.1 Inbox renders

1. Log in as recipient → profile → Contact → Inbox.
2. **Expected:** A list of messages with sender name, subject, timestamp. Most recent first.
3. First visit shows an intro panel with a "Got it" button. Dismiss it.
4. **Expected:** `POST /wp-json/bcm/v1/preferences/intro-dismiss` fires; `bcm_intro_dismissed = 1` user meta is set; the panel stays hidden on refresh.

### 7.2 Open a message

1. Click any message row.
2. **Expected:** Message view sub-tab shows sender, subject, full message body. If the message originated from a logged-out visitor, the visitor's provided name + email are shown.

### 7.3 Delete a message

1. On the message view, click **Delete**. Confirm via the in-page confirm (NOT the browser's `window.confirm`).
2. **Expected:** `DELETE /wp-json/bcm/v1/messages/<id>` fires; returns 200 for the owner. Row disappears; `wp_contact_me` row is gone.
3. Attempt the same DELETE as a different logged-in user via the REST client.
4. **Expected:** 403 — `require_message_owner` permission callback rejects cross-user deletion.

### 7.4 Empty state

1. Delete all messages for a clean recipient.
2. **Expected:** Inbox shows a friendly empty-state ("No messages yet") instead of an empty table.

---

## 8. Frontend — Preferences sub-tab

### 8.1 Opt-out round-trip

1. Log in as recipient → profile → Contact → Preferences.
2. Toggle **Accept contact messages** OFF → Save.
3. **Expected:** `wp user meta get <id> contact_me_button` returns `off`. Nonce (`bcm_preferences_nonce`) is used; tampered submissions are rejected.
4. Toggle back ON → Save → meta returns `on`.

### 8.2 Public link copy

1. On Preferences, click the **Copy contact link** control.
2. **Expected:** Clipboard receives a direct URL to the user's Contact tab. Toast shows a success message.

### 8.3 Jump-to-notification settings

1. Click any quick-link (e.g. "Email notifications") that points into core BuddyPress settings.
2. **Expected:** Link resolves to the BP Notifications settings screen for the logged-in user.

---

## 9. Shortcode (if exposed)

1. Create a page and insert whatever shortcode the plugin ships (check `public/partials/tab-form.php` / `includes/frontend/class-bcm-frontend-shortcode.php`). Common form: `[bp_contact_me user_id="X"]` or `[bp_contact_me username="..."]`.
2. View the page logged-out, then logged-in.
3. **Expected:**
   - Form renders standalone outside the profile tab.
   - Submitting it uses the same REST endpoint and stores a row with `bcm_shortcode_user_id` / `bcm_shortcode_username` context preserved.
   - Access rules from §3 still apply (role + per-user opt-out).

---

## 10. Accessibility

### 10.1 Keyboard navigation

1. Tab through the Contact tab form (logged-in + logged-out) using the keyboard only.
2. **Expected:** Every input, button, and link receives a visible focus ring (2px solid accent color per skill guideline). Submit button is reachable. No element traps focus.

### 10.2 ARIA attributes

1. Inspect the submit button + success toast.
2. **Expected:**
   - Submit button is a real `<button>` with an accessible name.
   - Success / error toasts have `role="status"` or `role="alert"` so screen readers announce them.
   - If any modal is used (delete confirm, preferences confirm), modal has `role="dialog"`, `aria-modal="true"`, `aria-labelledby` → a real heading id, and a close button with `aria-label="Close dialog"`.

### 10.3 Theme compatibility

1. Run the flow in: BuddyX theme, BuddyBoss theme, default block theme (Twenty Twenty-Four or later).
2. **Expected:** Form layout holds in each; no broken styles.

---

## 11. REST API spot-check

| Endpoint | Method | Auth | Expected |
|---|---|---|---|
| `/wp-json/bcm/v1/messages` | POST | public (permission `__return_true`; access rules enforced inside callback) | 200 on valid payload; 4xx with clear error on access/captcha/validation failure |
| `/wp-json/bcm/v1/messages/<id>` | DELETE | message owner | 200 on owner; 403 on non-owner; 404 on unknown id |
| `/wp-json/bcm/v1/preferences/intro-dismiss` | POST | logged in | 200; sets `bcm_intro_dismissed = 1` |

Run each both in a logged-in session (cookie + `X-WP-Nonce`) and, for the public POST, logged-out.

---

## 12. Edge cases

- **Deactivate BuddyPress** → plugin must show a single admin notice pointing to the Plugins screen and must NOT fatal.
- **Deactivate BP Notifications component only** → BP in-app notification toggle falls back cleanly; email still sends.
- **Recipient deleted mid-flight** (sender on the form, recipient deleted in another tab, sender submits) → REST returns a clean error; no orphan row is created.
- **Multi-site** → form + settings are site-specific; verify on a network-enabled install (activate per-site and network-activate both).
- **Cache plugin interference** → With WP Super Cache or LiteSpeed enabled, a logged-in submit must bypass page cache and still render the updated Inbox.
- **RTL** → Open the Contact tab in an RTL locale; layout mirrors correctly.

---

## 13. Release-readiness checklist

Before tagging `v1.5.0`:

- [ ] All sections 1–12 pass.
- [ ] `phpcs --standard=WordPress` → 0 errors on plugin code (exclude `edd-license/` via `.phpcs.xml.dist`).
- [ ] `php -l` clean on every PHP file.
- [ ] `grunt build` (or equivalent) completed; `.min.css`, `.min.js`, RTL variants, `.pot` are fresh.
- [ ] README.txt **Stable tag** matches the release version and **Requires at least** matches the plugin header (6.0).
- [ ] Changelog entry exists for 1.5.0 describing the rewrite + BP email template + WPCS clean + PCP sweep.
- [ ] No `debug.log` entries during the full QA run.
- [ ] `wp plugin check buddypress-contact-me --ignore-warnings` reports zero errors other than the expected `plugin_updater_detected` hits on the EDD updater class.

---

## 14. Accessibility Testing

### 14.1 Admin Interface Accessibility

1. **Form Labels Verification**
   - Navigate to **WB Plugins → Contact Me → Access**
   - **Expected**: All form inputs have associated labels (`<label for="id">`)
   - Check role grid checkboxes: each has proper `for` attribute matching input ID
   - Verify fieldset with `<legend>` for screen readers

2. **Keyboard Navigation**
   - Tab through all admin interface elements
   - **Expected**: Logical tab order, visible focus indicators on all interactive elements
   - All buttons, links, and form controls are keyboard accessible

3. **Screen Reader Support**
   - Test with screen reader or browser accessibility tools
   - **Expected**: All form controls announced properly, descriptive text read aloud
   - ARIA attributes used appropriately (`aria-live`, `aria-hidden`)

### 14.2 Frontend Form Accessibility

1. **Contact Form Labels**
   - Visit member profile → Contact tab
   - **Expected**: All form fields have proper labels and descriptions
   - Error messages are associated with form fields

2. **Focus Management**
   - Tab through contact form fields
   - **Expected**: Clear visual focus indicators, logical order
   - Form submission maintains focus context

3. **Error Handling**
   - Submit form with validation errors
   - **Expected**: Errors announced to screen readers, focus moves to first error
   - Clear, descriptive error messages

### 14.3 Color Contrast and Visual Accessibility

1. **Color Contrast**
   - Check admin interface and frontend forms
   - **Expected**: WCAG AA compliant contrast ratios (4.5:1 for normal text)
   - Information not conveyed by color alone

2. **Text Scaling**
   - Increase browser zoom to 200%
   - **Expected**: All content remains readable and functional
   - No horizontal scrolling required

---

## 15. Performance Testing

### 15.1 Database Performance

1. **Query Analysis**
   - Enable query logging: `define('SAVEQUERIES', true);`
   - Visit multiple profile pages with contact forms
   - **Expected**: No more than 5 additional database queries per page load
   - All queries properly indexed and optimized

2. **Large Dataset Testing**
   - Create 1000+ test messages in `wp_contact_me` table
   - Test message list loading performance
   - **Expected**: Page load time under 2 seconds, proper pagination

### 15.2 Frontend Performance

1. **Asset Loading**
   - Check network tab for CSS/JS file loading
   - **Expected**: No 404 errors, files properly minified in production
   - CSS and JS only loaded on pages that need them

2. **AJAX Performance**
   - Test contact form submission timing
   - **Expected**: Form submission completes within 2 seconds
   - No memory leaks or excessive CPU usage

### 15.3 Memory Usage

1. **PHP Memory Monitoring**
   - Monitor memory usage during heavy usage
   - **Expected**: Memory usage stays within WordPress limits (64MB+)
   - No memory exhaustion errors in logs

---

## 16. Security Testing

### 16.1 Input Validation

1. **XSS Prevention**
   - Submit form with HTML/JavaScript in message field
   - **Expected**: HTML properly escaped, no script execution
   - `wp_kses_post()` or equivalent sanitization applied

2. **SQL Injection Prevention**
   - Attempt SQL injection in form fields
   - **Expected**: All input properly sanitized, no database errors
   - Prepared statements used for all database queries

### 16.2 Authentication and Authorization

1. **Permission Checks**
   - Test REST API endpoints without proper authentication
   - **Expected**: Proper 401/403 responses, no unauthorized access
   - Nonce verification on all form submissions

2. **Rate Limiting**
   - Submit multiple messages rapidly
   - **Expected**: Rate limiting prevents abuse
   - Appropriate error messages for rate-limited requests

---

## 17. Regression tests vs. earlier versions

| Behavior | 1.4.x | 1.5.0 |
|---|---|---|
| Frontend uses the legacy wbcom wrapper | yes | ✅ removed — ships card-panel admin |
| BP email template for contact notifications | missing | ✅ installed on activation, removed on uninstall |
| Inbox intro panel dismissal | n/a | ✅ stored in `bcm_intro_dismissed` user meta |
| Per-user opt-out on Preferences sub-tab | partial | ✅ nonce-gated round-trip |
| Uninstall cleans email post + term | no | ✅ yes |
| Native `window.confirm` on delete | yes | ✅ removed — in-page confirm |
| ABSPATH guard on core class / loader / edd-license | missing | ✅ added |
| README `Requires at least` vs plugin header mismatch | 5.0 vs 6.0 | ✅ aligned to 6.0 |
| Accessibility compliance | Partial | ✅ Full WCAG AA compliance |
| Performance optimization | Basic | ✅ Optimized queries and caching |
| Security hardening | Standard | ✅ Enhanced validation and sanitization |

---

## 18. Sign-off

Sign below once all 17 sections pass.

**Tested by:** ____________________
**Date:** ____________________
**Result:** ☐ Pass  ☐ Fail (attach notes)

---

## 19. Documentation Checklist

Before release, verify all documentation is complete and accurate:

- [ ] Installation guide covers all requirements and setup scenarios
- [ ] User guide explains all features from both member and admin perspectives
- [ ] Developer guide includes complete API reference and examples
- [ ] Troubleshooting guide covers common issues and solutions
- [ ] QA manual includes all test scenarios and passes
- [ ] All documentation is formatted consistently with proper markdown
- [ ] All internal links work correctly
- [ ] Screenshots and examples are up-to-date
- [ ] Version numbers match across all documentation
- [ ] Contact information and support links are current
