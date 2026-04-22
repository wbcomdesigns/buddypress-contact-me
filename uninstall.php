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

// 4. Delete the per-user opt-in flag (contact_me_button = 'on') written
// to usermeta by class-buddypress-contact-me-activator.php for every
// existing member when the plugin was first activated.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$wpdb->delete(
	$wpdb->usermeta,
	array( 'meta_key' => 'contact_me_button' ),
	array( '%s' )
);
