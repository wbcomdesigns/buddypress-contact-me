<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * Drops the custom contact-messages table and removes every option /
 * transient / usermeta the plugin writes, so no data is left behind
 * when a site owner deletes Contact Me from the Plugins screen.
 *
 * @package Buddypress_Contact_Me
 * @since   1.0.0
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Respect opt-out for site owners who want to keep their message
// archive after uninstall — add `define( 'BCM_KEEP_DATA_ON_UNINSTALL', true );`
// to wp-config.php.
if ( defined( 'BCM_KEEP_DATA_ON_UNINSTALL' ) && BCM_KEEP_DATA_ON_UNINSTALL ) {
	return;
}

// 1. Drop the custom contact_me table (stores every message sent
// through the plugin). Created in class-buddypress-contact-me-activator.php.
$table_name = $wpdb->prefix . 'contact_me';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );

// 2. Delete plugin-wide options.
$options = array(
	// Main settings array.
	'bcm_admin_general_setting',
	// Upgrade / bookkeeping.
	'buddypress_contact_me_db_version',
	// EDD Software Licensing.
	'edd_wbcom_bp_contact_me_license_key',
	'edd_wbcom_bp_contact_me_license_status',
	'edd_wbcom_bp_contact_me_license_expires',
);
foreach ( $options as $option_name ) {
	delete_option( $option_name );
	// Multisite fallback: also try delete_site_option so stray
	// network-wide rows are cleared on uninstall.
	if ( is_multisite() ) {
		delete_site_option( $option_name );
	}
}

// 3. Delete transients cached by the EDD license flow.
delete_transient( 'edd_wbcom_bp_contact_me_license_key_data' );

// 4. Delete per-user metadata written by the plugin.
$user_meta_keys = array(
	// Opt-in flag for receiving messages.
	'contact_me_button',
	// Dismissed-intro flag for the inbox info panel (1.5.0+).
	'bcm_intro_dismissed',
);
foreach ( $user_meta_keys as $meta_key ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.SlowDBQuery.slow_db_query_meta_key
	$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => $meta_key ), array( '%s' ) );
}

// 5. Delete the BP email post + term installed in 1.5.0 so the template
// does not linger in Dashboard → Emails after uninstall. Best-effort:
// if BuddyPress is gone, skip silently.
if ( function_exists( 'bp_get_email_tax_type' ) && function_exists( 'bp_get_email_post_type' ) ) {
	$bcm_tax_type = bp_get_email_tax_type();
	$bcm_term     = get_term_by( 'slug', 'bcm-contact-message', $bcm_tax_type );
	if ( $bcm_term ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Uninstall-only, runs once.
		$bcm_email_ids = get_posts(
			array(
				'post_type'   => bp_get_email_post_type(),
				'tax_query'   => array(
					array(
						'taxonomy' => $bcm_tax_type,
						'field'    => 'term_id',
						'terms'    => (int) $bcm_term->term_id,
					),
				),
				'numberposts' => -1,
				'fields'      => 'ids',
			)
		);
		foreach ( $bcm_email_ids as $bcm_post_id ) {
			wp_delete_post( (int) $bcm_post_id, true );
		}
		wp_delete_term( (int) $bcm_term->term_id, $bcm_tax_type );
	}
}

// 6. Clear BP notifications tied to this plugin's component.
$bcm_notifications_table = $wpdb->prefix . 'bp_notifications';
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bcm_notifications_table ) ) === $bcm_notifications_table ) {
	$wpdb->delete(
		$bcm_notifications_table,
		array(
			'component_name'   => 'bcm_user_notifications',
			'component_action' => 'bcm_user_notifications_action',
		),
		array( '%s', '%s' )
	);
}
// phpcs:enable

// 7. Flash-notice transients written by BCM_Frontend_Flash for logged-out
// senders that never got consumed.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->query(
	$wpdb->prepare(
		"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
		$wpdb->esc_like( '_transient_bcm_flash_' ) . '%',
		$wpdb->esc_like( '_transient_timeout_bcm_flash_' ) . '%'
	)
);
