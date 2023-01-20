<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/includes
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Contact_Me_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		$contact_me_option_setting = get_user_meta( get_current_user_id(), 'contact_me_button' );
		if ( false == $contact_me_option_setting || '' == $contact_me_option_setting ) {
			$all_users = get_users();
			foreach( $all_users as $all_userdata ){
				$all_users_id = $all_userdata->ID;
				update_user_meta( $all_users_id, 'contact_me_button', 'on' );
			}
		}
	}

}
