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
 * Version:           1.5.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
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
define( 'BUDDYPRESS_CONTACT_ME_VERSION', '1.5.0' );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_FILE', __FILE__ );

/**
 * Run one-time upgrade steps when the stored version is older than the
 * current BUDDYPRESS_CONTACT_ME_VERSION. Safe to re-enter: every branch
 * is idempotent and version-gated.
 *
 * @since 1.5.0
 */
function bcm_maybe_upgrade() {
	$stored = get_option( 'buddypress_contact_me_db_version', '' );
	if ( version_compare( (string) $stored, BUDDYPRESS_CONTACT_ME_VERSION, '>=' ) ) {
		return;
	}

	// 1.5.0 — frontend rewrite + default-on semantics. Legacy versions
	// stored empty-string meta to mean "opt out" and only 'on' to mean
	// "opt in". The new nav layer treats anything except 'off' as
	// enabled, so migrate any empty strings to 'on' to make the meta
	// set self-consistent going forward.
	global $wpdb;
	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->usermeta} SET meta_value = %s WHERE meta_key = %s AND meta_value = ''",
			'on',
			'contact_me_button'
		)
	);

	// 1.5.0 — install the BP email post so notifications render through
	// BuddyPress's email template. No-op once the admin owns the post.
	if ( class_exists( 'BCM_Email_Installer' ) ) {
		BCM_Email_Installer::install();
	}

	update_option( 'buddypress_contact_me_db_version', BUDDYPRESS_CONTACT_ME_VERSION );
}
add_action( 'bp_init', 'bcm_maybe_upgrade', 20 );

/**
 * New users default to accepting contact messages — the plugin is
 * valuable because it "just works" out of the box.
 */
add_action(
	'user_register',
	function ( $user_id ) {
		update_user_meta( $user_id, 'contact_me_button', 'on' );
	}
);


/**
 * Plugin activation callback function.
 */
function activate_buddypress_contact_me() {
	if ( class_exists( 'BuddyPress' ) ) {
		include_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me-activator.php';
		BuddyPress_Contact_Me_Activator::activate();
	} else {
		add_action( 'admin_notices', 'bp_contact_me_required_plugin_admin_notice' );
	}
}

/**
 * Plugin deactivation callback function.
 */
function deactivate_buddypress_contact_me() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me-deactivator.php';
	BuddyPress_Contact_Me_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_buddypress_contact_me' );
register_deactivation_hook( __FILE__, 'deactivate_buddypress_contact_me' );

/**
 * Core plugin class that defines internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me.php';

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
 * Begin execution of the plugin.
 */
function run_buddypress_contact_me() {
	$plugin = new BuddyPress_Contact_Me();
	$plugin->run();
}
add_action( 'bp_include', 'run_buddypress_contact_me' );

/**
 * Get user private message link.
 *
 * @param  int $user_id User ID to send private message.
 * @return string       Private message URL.
 */
function bp_contact_me_get_send_private_message_link( $user_id ) {
	$compose_url = bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?';
	if ( $user_id ) {
		if ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
			$compose_url .= 'r=' . bp_core_get_username( $user_id );
		} else {
			$compose_url .= 'r=' . bp_members_get_user_slug( $user_id );
		}
	}
	return wp_nonce_url( $compose_url );
}

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
