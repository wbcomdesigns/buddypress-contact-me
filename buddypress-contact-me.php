<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link    https://www.wbcomdesigns.com
 * @since   1.0.0
 * @package BuddyPress_Contact_Me
 *
 * @wordpress-plugin
 * Plugin Name:       Wbcom Designs - BuddyPress Contact Me
 * Plugin URI:        https://wbcomdesigns.com/downloads/buddypress-contact-me/
 * Description:       BuddyPress Contact Me displays a contact form on members' profiles, allowing both logged-in and non-logged-in visitors to connect with community members.
 * Version:           1.5.1
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Wbcom Designs
 * Author URI:        https://www.wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       buddypress-contact-me
 * Domain Path:       /languages
 */

// Abort if this file is called directly.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define constants for the plugin.
define( 'BUDDYPRESS_CONTACT_ME_VERSION', '1.5.1' );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_FILE', __FILE__ );

/**
 * Plugin activation callback function.
 */
function activate_buddypress_contact_me() {
	if ( class_exists( 'BuddyPress' ) ) {
		BuddyPress_Contact_Me_Activator::activate();
	}
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me-activator.php';
register_activation_hook( __FILE__, 'activate_buddypress_contact_me' );

// The one upgrade router (schema, data fixes, emails). See the activator.
add_action( 'bp_init', array( 'BuddyPress_Contact_Me_Activator', 'maybe_upgrade' ), 20 );

/**
 * Check if BuddyPress is active.
 */
function bp_contact_me_requires_buddypress() {
	if ( ! class_exists( 'BuddyPress' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'bp_contact_me_required_plugin_admin_notice' );
	} else {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bp_contact_me_plugin_links' );
	}
}
add_action( 'admin_init', 'bp_contact_me_requires_buddypress' );

/**
 * Display an admin notice if BuddyPress is not active.
 */
function bp_contact_me_required_plugin_admin_notice() {
	echo '<div class="error"><p>';
	printf(
		// Translators: %s.
		esc_html__( '%1$s is inactive as it requires %2$s to be installed and active.', 'buddypress-contact-me' ),
		'<strong>' . esc_html__( 'BuddyPress Contact Me', 'buddypress-contact-me' ) . '</strong>',
		'<strong>' . esc_html__( 'BuddyPress', 'buddypress-contact-me' ) . '</strong>'
	);
	echo '</p></div>';
}

/**
 * Redirect to plugin settings page after activation.
 *
 * @param string $plugin The plugin slug.
 */
function bp_contact_me_activation_redirect_settings( $plugin ) {

	if ( plugin_basename( __FILE__ ) === $plugin && class_exists( 'BuddyPress' ) ) {
		if ( isset( $_REQUEST['action'] ) && 'activate' === $_REQUEST['action'] && isset( $_REQUEST['plugin'] ) && $plugin === $_REQUEST['plugin'] ) { //phpcs:ignore
			wp_safe_redirect( admin_url( 'admin.php?page=buddypress-contact-me&redirects=1' ) );
			exit;
		}
	}
}
add_action( 'activated_plugin', 'bp_contact_me_activation_redirect_settings' );

/**
 * Load the plugin once BuddyPress (or BuddyBoss Platform) is loaded.
 */
function run_buddypress_contact_me() {
	$base = plugin_dir_path( __FILE__ );
	foreach ( array(
		'includes/functions.php',
		'includes/admin/class-bcm-admin.php',
		'includes/data/class-bcm-messages-repo.php',
		'includes/frontend/class-bcm-frontend-nav.php',
		'includes/frontend/class-bcm-frontend-assets.php',
		'includes/frontend/class-bcm-frontend-submit.php',
		'includes/frontend/class-bcm-frontend-notifications.php',
		'includes/frontend/class-bcm-frontend-shortcode.php',
		'includes/rest/class-bcm-rest-messages.php',
		'includes/email/class-bcm-email-installer.php',
		'edd-license/edd-plugin-license.php',
	) as $file ) {
		require_once $base . $file;
	}

	( new BCM_Admin() )->register();
	( new BCM_Frontend_Nav() )->register();
	$assets = new BCM_Frontend_Assets( 'buddypress-contact-me', BUDDYPRESS_CONTACT_ME_VERSION );
	$assets->register();
	( new BCM_Frontend_Submit() )->register();
	( new BCM_Frontend_Notifications() )->register();
	( new BCM_Frontend_Shortcode( $assets ) )->register();
	( new BCM_Rest_Messages() )->register();

	// A deleted member's received messages go with them (single site and network delete).
	add_action( 'deleted_user', array( 'BCM_Messages_Repo', 'delete_for_user' ) );
	add_action( 'wpmu_delete_user', array( 'BCM_Messages_Repo', 'delete_for_user' ) );
}
add_action( 'bp_include', 'run_buddypress_contact_me' );

add_action(
	'init',
	static function () {
		load_plugin_textdomain( 'buddypress-contact-me', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
);

/**
 * Set plugin action links.
 *
 * @param array $links Plugin settings link array.
 * @return array       Updated settings link array.
 */
function bp_contact_me_plugin_links( $links ) {
	$bcm_links = array(
		'<a href="' . admin_url( 'admin.php?page=buddypress-contact-me' ) . '">' . __( 'Settings', 'buddypress-contact-me' ) . '</a>',
		'<a href="https://wbcomdesigns.com/contact/" target="_blank">' . __( 'Support', 'buddypress-contact-me' ) . '</a>',
	);
	return array_merge( $links, $bcm_links );
}
