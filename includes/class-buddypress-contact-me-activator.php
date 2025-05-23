<?php


/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    BuddyPress_Contact_Me
 * @subpackage BuddyPress_Contact_Me/includes
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 * @link       https://www.wbcomdesigns.com
 */
class BuddyPress_Contact_Me_Activator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function activate() {
		global $wpdb;
		$charset_collate          = $wpdb->get_charset_collate();
		$bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
		// phpcs:disable
		if ( $wpdb->get_var( "show tables like '$bp_contact_me_table_name'" ) != $bp_contact_me_table_name ) {	// phpcs:ignore
			$bp_contact_sql = "CREATE TABLE $bp_contact_me_table_name (
						id mediumint(11) NOT NULL AUTO_INCREMENT,
						sender int(11) NOT NULL,
						reciever  int(11) NOT NULL, 
						subject  varchar(255) NOT NULL,
						message TEXT  NOT NULL,
						name varchar(255)   NOT NULL,
						email varchar(255) NULL,
						datetime varchar(255) NULL,
						UNIQUE KEY id (id)
			) $charset_collate;";
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $bp_contact_sql );
		}
		// phpcs:enable
		$contact_me_option_setting = get_user_meta( get_current_user_id(), 'contact_me_button' );
		if ( false == $contact_me_option_setting || '' == $contact_me_option_setting ) {
			$all_users = get_users();
			foreach ( $all_users as $all_userdata ) {
				$all_users_id = $all_userdata->ID;
				update_user_meta( $all_users_id, 'contact_me_button', 'on' );
			}
		}
		$bp_contact_me_admin_settings = get_option( 'bcm_admin_general_setting' );
		if ( false === $bp_contact_me_admin_settings ) {
			
			$bcm_contact_link = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) ? bp_core_get_user_domain( bp_loggedin_user_id() ) . 'contact' : bp_members_get_user_url( bp_loggedin_user_id() ) . 'contact';
			
			$bcm_click  = '<a href=" ' . $bcm_contact_link . '">Click here</a>';
			$user_roles = array_reverse( get_editable_roles() );
			$user_array = array( 'visitors' );
			foreach ( $user_roles as $role => $details ) {
				$user_array[] = $role;

			}
			$bp_contact_me_admin_settings = array(
				'bcm_allow_notification'      => 'yes',
				'bcm_allow_email'             => 'yes',
				'bcm_allow_contact_tab'       => 'yes',
				'bcm_email_subject'           => '{user_name} has contacted you.',
				'bcm_allow_sender_copy_email' => 'yes',
				'bcm_allow_admin_copy_email'  => 'no',
				'bcm_who_contact'             => $user_array,
				'bcm_who_contacted'           => $user_array,
			);
			update_option( 'bcm_admin_general_setting', $bp_contact_me_admin_settings );
		}
	}

}
