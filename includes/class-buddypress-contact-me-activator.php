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
	 * Required indexes the schema MUST end up with after install_schema()
	 * runs. Used by the post-migration verify step to confirm dbDelta
	 * actually applied the change before we bump the stored db_version.
	 *
	 * @since 1.5.0
	 */
	const REQUIRED_INDEXES = array( 'bcm_recipient_recent', 'bcm_recipient' );

	/**
	 * Cache key + TTL for the migration concurrency lock. wp_cache_add()
	 * is atomic, so only one of N concurrent requests succeeds in setting
	 * the key — the rest skip the migration on this request. 60s is long
	 * enough for an ALTER on a multi-million-row contact_me table to
	 * finish on a typical InnoDB host; if the lock holder dies before
	 * release, the next request after 60s retries.
	 *
	 * @since 1.5.0
	 */
	const UPGRADE_LOCK_KEY = 'bcm_db_upgrade_lock';
	const UPGRADE_LOCK_TTL = 60;

	/**
	 * Idempotent schema install — safe to call on activation OR upgrade.
	 *
	 * dbDelta is the right tool: it CREATEs missing tables AND ADDs missing
	 * keys to existing tables without dropping data. We rely on that here
	 * so existing installs get the (reciever, datetime DESC, id DESC)
	 * composite index without a manual ALTER TABLE script.
	 *
	 * Returns true on success, false if dbDelta ran but the required indexes
	 * are still missing (e.g. DB user lacks ALTER privilege on a managed host).
	 *
	 * @since 1.5.0
	 *
	 * @return bool
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

		// Verify that every required index actually exists. dbDelta() never
		// throws — if the DB user lacks ALTER privilege, it silently does
		// nothing. Without this check we'd bump db_version anyway and the
		// perf fix would never land while looking like it had. SHOW INDEX
		// is the canonical post-condition check; runs in microseconds.
		foreach ( self::REQUIRED_INDEXES as $key_name ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
			$found = $wpdb->get_var(
				$wpdb->prepare(
					"SHOW INDEX FROM {$table} WHERE Key_name = %s",
					$key_name
				)
			);
			if ( ! $found ) {
				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
					error_log( sprintf(
						'[buddypress-contact-me] Schema migration to %s failed: index "%s" missing on %s after dbDelta. The DB user may lack ALTER TABLE privilege on this host. db_version NOT bumped — next page load will retry.',
						self::DB_VERSION,
						$key_name,
						$table
					) );
				}
				return false;
			}
		}

		// All required indexes present — safe to bump the stored version.
		update_option( 'bcm_db_version', self::DB_VERSION, false );
		return true;
	}

	/**
	 * Run install_schema() on plugins_loaded if the stored db version is
	 * older than the current one. Lets existing installs pick up new
	 * indexes without re-activating the plugin.
	 *
	 * Hardened for 500-site rollout (1.5.0):
	 *   - Skipped on AJAX / REST / cron / WP-CLI requests so a frontend
	 *     page load owns the migration window. Keeps debug visibility on
	 *     a route where errors land where humans see them.
	 *   - Concurrency lock via wp_cache_add() so two simultaneous
	 *     requests don't both fire ALTER TABLE on the same install.
	 *   - install_schema() does its own post-condition check; only bumps
	 *     db_version if the new indexes actually exist.
	 *   - On any failure, db_version stays at the old value so the next
	 *     unlocked request retries.
	 *
	 * @since 1.5.0
	 */
	public static function maybe_upgrade() {
		$stored = get_option( 'bcm_db_version', '0' );
		if ( version_compare( $stored, self::DB_VERSION, '>=' ) ) {
			return;
		}

		// Don't run mid-AJAX / mid-REST / mid-cron / under WP-CLI — those
		// requests are time-sensitive and have nowhere useful to surface
		// debug output. A frontend or admin page load will pick it up.
		if (
			( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
			( defined( 'DOING_CRON' ) && DOING_CRON ) ||
			( defined( 'WP_CLI' ) && WP_CLI ) ||
			( defined( 'REST_REQUEST' ) && REST_REQUEST )
		) {
			return;
		}

		// Concurrency lock — wp_cache_add() returns true only for the
		// first caller. Other requests that race to this line skip the
		// migration this time around and try again on the next request
		// (after the lock-holder has either bumped the version or
		// released the lock by TTL).
		if ( ! wp_cache_add( self::UPGRADE_LOCK_KEY, 1, '', self::UPGRADE_LOCK_TTL ) ) {
			return;
		}

		try {
			self::install_schema();
		} finally {
			wp_cache_delete( self::UPGRADE_LOCK_KEY );
		}
	}
}
