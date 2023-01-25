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
 * Plugin Name:       Buddypress Contact Me
 * Plugin URI:        https://www.wbcomdesigns.com
 * Description:        Using this plugin members can contact to each other without friendship.
 * Version:           1.0.0
 * Author:            WBCOM Designs
 * Author URI:        https://www.wbcomdesigns.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       buddypress-contact-me
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (! defined('WPINC') ) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define('BUDDYPRESS_CONTACT_ME_VERSION', '1.0.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-buddypress-contact-me-activator.php
 */
function activate_buddypress_contact_me()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-buddypress-contact-me-activator.php';
    Buddypress_Contact_Me_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-buddypress-contact-me-deactivator.php
 */
function deactivate_buddypress_contact_me()
{
    include_once plugin_dir_path(__FILE__) . 'includes/class-buddypress-contact-me-deactivator.php';
    Buddypress_Contact_Me_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_buddypress_contact_me');
register_deactivation_hook(__FILE__, 'deactivate_buddypress_contact_me');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-buddypress-contact-me.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since 1.0.0
 */
function run_buddypress_contact_me()
{

    $plugin = new Buddypress_Contact_Me();
    $plugin->run();
}
add_action('bp_include', 'run_buddypress_contact_me');

/**
 * This function is used for get user private message link.
 *
 * @param  mixed $user_id to send private message.
 * @return void
 */
function bp_contact_me_get_send_private_message_link( $user_id )
{   
    $compose_url=bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?';
    if ( $user_id ) {
        $compose_url.=('r=' . bp_core_get_username($user_id));
    }
    return wp_nonce_url($compose_url);
}
