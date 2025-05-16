<?php


/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    BuddyPress_Contact_Me
 * @subpackage BuddyPress_Contact_Me/includes
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 * @link       https://www.wbcomdesigns.com
 */
class BuddyPress_Contact_Me_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'buddypress-contact-me',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
