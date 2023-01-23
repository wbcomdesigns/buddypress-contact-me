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
		global $wpdb;
		$bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
		if ( $wpdb->get_var( "show tables like '$bp_contact_me_table_name'" ) != $bp_contact_me_table_name ) {
			$bp_contact_sql = "CREATE TABLE $bp_contact_me_table_name (
						id mediumint(11) NOT NULL AUTO_INCREMENT,
						sender int(11) NOT NULL,
						reciever  int(11) NOT NULL, 
						subject  varchar(255) NOT NULL,
						message varchar(255)   NOT NULL,
						UNIQUE KEY id (id)
			) $charset_collate;";
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $bp_contact_sql );
		}
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
