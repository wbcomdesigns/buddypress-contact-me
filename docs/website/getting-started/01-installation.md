# Install and Activate the Plugin

This page walks through installing BuddyPress Contact Me and reaching its admin screen for the first time.

## Requirements

| Requirement | Minimum | Tested |
|-------------|---------|--------|
| WordPress   | 6.0     | 6.9    |
| PHP         | 7.4     | 8.4    |
| BuddyPress or BuddyBoss Platform | active | BuddyPress 14.x |

The BuddyPress Notifications component must be active if you want in-site notification-bell entries. Email notifications work without it.

## Install via WordPress admin

1. Go to **Plugins > Add New**.
2. Click **Upload Plugin** and choose the ZIP you downloaded from your Wbcom Designs account.
3. Click **Install Now**, then **Activate Plugin**.

## Install via FTP

1. Unzip the plugin archive on your computer. You will get a folder named `buddypress-contact-me`.
2. Upload the folder to `/wp-content/plugins/` on your server.
3. Go to **Plugins > Installed Plugins** and click **Activate** under "Wbcom Designs - BuddyPress Contact Me".

## Install via WP-CLI

```bash
wp plugin install /path/to/buddypress-contact-me.zip --activate
```

## What happens on activation

The activator runs once and does the following:

1. Creates the `{prefix}contact_me` table that stores all submitted messages, with the recipient indexes the inbox relies on.
2. Records the database schema version in the `bcm_db_version` option so future upgrades can add indexes without you re-activating.
3. Sets every existing user's `contact_me_button` user-meta to `on`, so existing members default to accepting contact.
4. Seeds the default settings: notifications, email, the profile Contact tab, and the sender copy are on, the admin copy is off, and the send and receive role lists include Visitors plus every editable role.
5. Installs a BuddyPress email post of type `bcm-contact-message` so notifications render through the BuddyPress email template. Once you edit that email from **Dashboard > Emails**, the plugin never overwrites your changes on future upgrades.

## Find the admin screen

Wbcom plugins share a single top-level WordPress menu called **WB Plugins**. After activation:

1. Open **WB Plugins** in the sidebar.
2. Click **Contact Me** in the submenu.

You land on the Overview tab. The sidebar inside the page lists the four tabs: Overview, Notifications, Access, and License.

![Contact Me admin overview](../images/admin-overview.webp)

## Recommended first-run checklist

1. **Notifications tab** - confirm at least one of "BuddyPress notification" or "Email notification" is on so recipients find out about new messages. Both are on by default.
2. **Access tab** - confirm the role lists match your community. By default every editable role plus Visitors can send, and every editable role can be contacted. Administrators are always allowed and always contactable.
3. **License tab** - paste your license key and click **Activate License** so the plugin can pull updates straight from your dashboard.

## What's next

The Features section walks through every user-facing surface in detail, and the Settings section explains each admin tab.
