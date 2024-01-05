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
 * @package Buddypress_Contact_Me
 *
 * @wordpress-plugin
 * Plugin Name:       Wbcom Designs - Buddypress Contact Me
 * Plugin URI:        https://wbcomdesigns.com/downloads/buddypress-contact-me/
 * Description:       BuddyPress Contact Me displays a contact form on a member's profile which allows logged-in and non-logged-in visitor can be in touch with our community members.
 * Version:           1.1.1
 * Author:            Wbcom Designs
 * Author URI:        https://www.wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       buddypress-contact-me
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'BUDDYPRESS_CONTACT_ME_VERSION', '1.1.1' );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BUDDYPRESS_CONTACT_ME_FILE', __FILE__ );
/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-buddypress-contact-me-activator.php
 */
function activate_buddypress_contact_me() {
	 include_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me-activator.php';
	Buddypress_Contact_Me_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-buddypress-contact-me-deactivator.php
 */
function deactivate_buddypress_contact_me() {
	include_once plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me-deactivator.php';
	Buddypress_Contact_Me_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_buddypress_contact_me' );
register_deactivation_hook( __FILE__, 'deactivate_buddypress_contact_me' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-buddypress-contact-me.php';

/**
 *  Check if buddypress activate.
 */
function bp_contact_me_requires_buddypress() {
	if ( ! class_exists( 'Buddypress' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		add_action( 'admin_notices', 'bp_contact_me_required_plugin_admin_notice' );
		unset( $_GET['activate'] );
	} else {
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'bp_contact_me_plugin_links' );
	}
}
add_action( 'admin_init', 'bp_contact_me_requires_buddypress' );

/**
 * Throw an Alert to tell the Admin why it didn't activate.
 *
 * @author wbcomdesigns
 * @since  1.2.0
 */
function bp_contact_me_required_plugin_admin_notice() {
	 $bpcontact_plugin = esc_html__( 'BuddyPress Contact Me', 'buddypress-contact-me' );
	$bp_plugin         = esc_html__( 'BuddyPress', 'buddypress-contact-me' );
	echo '<div class="error"><p>';
	echo sprintf( esc_html__( '%1$s is ineffective now as it requires %2$s to be installed and active.', 'buddypress-contact-me' ), '<strong>' . esc_html( $bpcontact_plugin ) . '</strong>', '<strong>' . esc_html( $bp_plugin ) . '</strong>' );
	echo '</p></div>';
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}

}

add_action( 'activated_plugin', 'bp_contact_me_activation_redirect_settings' );

/**
 * Redirect to plugin settings page after activated
 *
 * @param plugin $plugin plugin.
 */
function bp_contact_me_activation_redirect_settings( $plugin ) {
	if ( ! isset( $_GET['plugin'] ) ) {
		return;
	}
	if ( plugin_basename( __FILE__ ) === $plugin && class_exists( 'Buddypress' ) ) {
		wp_redirect( admin_url( 'admin.php?page=buddypress-contact-me' ) );
		exit;
	}
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_buddypress_contact_me() {
	$plugin = new Buddypress_Contact_Me();
	$plugin->run();
}
add_action( 'bp_include', 'run_buddypress_contact_me' );

/**
 * This function is used for get user private message link.
 *
 * @param  mixed $user_id to send private message.
 * @return void
 */
function bp_contact_me_get_send_private_message_link( $user_id ) {
	$compose_url = bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?';
	if ( $user_id ) {
		// Check BuddyPress version and use the appropriate function
		if ( function_exists('bp_get_version') && version_compare(bp_get_version(), '12.0.0', '>=') ) {
			$compose_url .= ( 'r=' . bp_members_get_user_slug( $user_id ) );
		} else {
			$compose_url .= ( 'r=' . bp_core_get_username( $user_id ) );
		}
	}
	return wp_nonce_url( $compose_url );
}

/**
 * Function to set plugin actions links.
 *
 * @param array $links Plugin settings link array.
 * @since 1.0.0
 */
function bp_contact_me_plugin_links( $links ) {
	$bcm_links = array(
		'<a href="' . admin_url( 'admin.php?page=buddypress-contact-me' ) . '">' . __( 'Settings', 'buddypress-contact-me' ) . '</a>',
		'<a href="https://wbcomdesigns.com/contact/" target="_blank">' . __( 'Support', 'buddypress-contact-me' ) . '</a>',
	);
	return array_merge( $links, $bcm_links );
}
