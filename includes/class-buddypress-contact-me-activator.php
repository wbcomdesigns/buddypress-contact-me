<?php
/**
 * Plugin activation handler.
 *
 * @package BuddyPress_Contact_Me
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class BuddyPress_Contact_Me_Activator {

	/**
	 * Schema version. Bump when CREATE TABLE shape changes so maybe_upgrade()
	 * can re-run dbDelta on existing installs.
	 */
	const DB_VERSION = '1.5.0';

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		self::install_schema();

		$contact_me_option_setting = get_user_meta( get_current_user_id(), 'contact_me_button', true );
		if ( false === $contact_me_option_setting || '' === $contact_me_option_setting ) {
			$all_users = get_users();
			foreach ( $all_users as $all_userdata ) {
				$all_users_id = $all_userdata->ID;
				update_user_meta( $all_users_id, 'contact_me_button', 'on' );
			}
		}
		$bp_contact_me_admin_settings = get_option( 'bcm_admin_general_setting' );
		if ( false === $bp_contact_me_admin_settings ) {

			$user_roles = array_reverse( get_editable_roles() );
			$user_array = array( 'visitors' );
			foreach ( $user_roles as $role => $details ) {
				$user_array[] = $role;

			}
			$bp_contact_me_admin_settings = array(
				'bcm_allow_notification'      => 'yes',
				'bcm_allow_email'             => 'yes',
				'bcm_allow_contact_tab'       => 'yes',
				'bcm_allow_sender_copy_email' => 'yes',
				'bcm_allow_admin_copy_email'  => 'no',
				'bcm_who_contact'             => $user_array,
				'bcm_who_contacted'           => $user_array,
			);
			update_option( 'bcm_admin_general_setting', $bp_contact_me_admin_settings );
		}
	}

	/**
	 * Idempotent schema install — safe to call on activation OR upgrade.
	 *
	 * dbDelta is the right tool: it CREATEs missing tables AND ADDs missing
	 * keys to existing tables without dropping data. We rely on that here
	 * so existing installs get the (reciever, datetime DESC, id DESC)
	 * composite index without a manual ALTER TABLE script.
	 *
	 * @since 1.5.0
	 */
	public static function install_schema() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table           = $wpdb->prefix . 'contact_me';

		// Indexes added in 1.5.0 for big-site readiness:
		//   bcm_recipient_recent — covers the inbox list query
		//                          WHERE reciever = %d ORDER BY datetime DESC, id DESC
		//   bcm_recipient        — covers count_for_recipient COUNT(*) WHERE reciever = %d
		// dbDelta uses key NAMES to detect existing keys, so renaming an
		// existing key would orphan the old one. These names are stable;
		// never rename them in a future release.
		$sql = "CREATE TABLE {$table} (
			id mediumint(11) NOT NULL AUTO_INCREMENT,
			sender int(11) NOT NULL,
			reciever int(11) NOT NULL,
			subject varchar(255) NOT NULL,
			message TEXT NOT NULL,
			name varchar(255) NOT NULL,
			email varchar(255) NULL,
			datetime varchar(255) NULL,
			PRIMARY KEY  (id),
			KEY bcm_recipient_recent (reciever,datetime,id),
			KEY bcm_recipient (reciever)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( 'bcm_db_version', self::DB_VERSION, false );
	}

	/**
	 * Run install_schema() on plugins_loaded if the stored db version is
	 * older than the current one. Lets existing installs pick up new
	 * indexes without re-activating the plugin.
	 *
	 * @since 1.5.0
	 */
	public static function maybe_upgrade() {
		$stored = get_option( 'bcm_db_version', '0' );
		if ( version_compare( $stored, self::DB_VERSION, '<' ) ) {
			self::install_schema();
		}
	}
}
