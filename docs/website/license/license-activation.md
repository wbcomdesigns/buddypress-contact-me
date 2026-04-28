# Activate Your License Key for Updates

Activation links your license key to your site so the plugin can fetch updates directly from the WordPress Plugins screen.

![License tab](../images/admin-license.png)

## Activate the key

1. Go to **WB Plugins → Contact Me → License**.
2. Paste the license key from your Wbcom Designs purchase confirmation email or **My Account → Purchase History** into the **License key** field.
3. Click **Activate License**.

Once activation succeeds the status flips to "Active: receiving updates" and the key field becomes read-only.

## Deactivate to move the key

To use the same key on another site:

1. Go to **License** on the source site.
2. Click **Deactivate License**.

The field unlocks and the slot frees up on your account so you can paste it into a new site. Deactivation submits inline — there is no confirmation popup (1.5.0 changed this to match the standard EDD pattern).

## What activation unlocks

- Update notifications appear in **Plugins → Installed Plugins** when a new release is available.
- One-click updates pull straight from `https://wbcomdesigns.com`.
- Priority support from the Wbcom Designs team.

## What if I never activate?

The plugin keeps working at full capability — there is no gated feature. Only updates and support are gated. If you prefer to update manually, you can drop in a new ZIP whenever a new release ships and skip the License tab entirely.

## Inline status messages

Activation problems render inline on the License tab — you do not get bounced to a different screen. Common messages and what they mean:

- **"Invalid license."** The key is mistyped, expired, or refunded. Double-check the key in your Wbcom Designs account.
- **"Your license is not active for this URL."** The key is in use on another site that has hit the activation limit. Deactivate it there first, or upgrade the licence to a higher site count.
- **"Key saved but not activated."** The key is stored locally but the site has not contacted the activation server. Click **Activate License** again — usually a transient connectivity issue.

## What's next

With the licence linked you are ready to configure access. The Settings docs walk through Notifications, Access, and the Overview dashboard.
