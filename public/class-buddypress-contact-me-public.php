<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://www.wbcomdesigns.com
 * @since 1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/public
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Contact_Me_Public {


	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Contact_Me_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Contact_Me_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-contact-me-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Buddypress_Contact_Me_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Buddypress_Contact_Me_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-contact-me-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Register a new tab in member's profile - Contact Me
	 *
	 * @since 1.0.1
	 */
	public function bp_contact_me_tab() {
		$bp_display_user_id          = bp_displayed_user_id();
		$contact_me_btn_value        = get_user_meta( $bp_display_user_id, 'contact_me_button' );
		$contact_me_btn_value_option = isset( $contact_me_btn_value[0] ) ? $contact_me_btn_value[0] : '';
		if ( bp_displayed_user_id() != bp_loggedin_user_id() ) {
			if ( 'on' === $contact_me_btn_value_option ) {
				bp_core_new_nav_item(
					array(
						'name'                    => esc_html__( 'Contact Me', 'buddypress-contact-me' ),
						'slug'                    => 'contact-me',
						'screen_function'         => array( $this, 'bp_contact_me_show_screen' ),
						'position'                => 80,
						'default_subnav_slug'     => 'contact-me',
						'show_for_displayed_user' => true,
					)
				);
			}
		}
	}

	/**
	 * Bp_contact_me_show_screen
	 *
	 * @return void
	 */
	public function bp_contact_me_show_screen() {
		add_action( 'bp_template_content', array( $this, 'bp_contact_me_tab_function_to_show_content' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Bp_contact_me_tab_function_to_show_content
	 *
	 * @return void
	 */
	public function bp_contact_me_tab_function_to_show_content() {
		include 'partials/bp-contact-me-tab-content.php';
	}

	/**
	 * Add contact tab on loggedin user profile.
	 *
	 * @return void
	 */
	public function bp_contact_me_show_data() {
		if ( is_user_logged_in() && bp_displayed_user_id() === bp_loggedin_user_id() ) {
			bp_core_new_nav_item(
				array(
					'name'                    => esc_html__( 'Contact', 'buddypress-contact-me' ),
					'slug'                    => 'contact',
					'screen_function'         => array( $this, 'bp_contact_me_show_data_screen' ),
					'position'                => 75,
					'default_subnav_slug'     => 'contact',
					'show_for_displayed_user' => true,
				)
			);
		}
	}

	/**
	 * Adds the user's navigation in WP Admin Bar
	 *
	 * @since 1.0.0
	 */
	public function bp_contact_me_setup_admin_bar( $wp_admin_nav = array() ) {
		global $wp_admin_bar, $current_user;
		$contact_tab_slug = bp_loggedin_user_domain() . 'contact';
		// Menus for logged in user.
		if ( is_user_logged_in() ) {
			$wp_admin_bar->add_menu(
				array(
					'parent' => 'my-account-buddypress',
					'id'     => 'my-account-contact',
					'title'  => esc_html__( 'Contact', 'buddypress-contact-me' ),
					'href'   => trailingslashit( $contact_tab_slug ),
				)
			);
		}
	}

	/**
	 * Add function for show contact data.
	 *
	 * @return void
	 */
	public function bp_contact_me_show_data_screen() {
		add_action( 'bp_template_content', array( $this, 'bp_contact_me_function_to_show_data' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Add file to show sender data to the logged in user.
	 *
	 * @return void
	 */
	public function bp_contact_me_function_to_show_data() {
		 include 'partials/bp-contact-tab-show-sender-user-data.php';
	}

	/**
	 * Bp_contact_me_btn create on member profile setting
	 *
	 * @return void
	 */
	public function bp_contact_me_button() {
		$contact_me_button        = get_user_meta( bp_displayed_user_id(), 'contact_me_button' );
		$contact_me_button_option = isset( $contact_me_button[0] ) ? $contact_me_button[0] : '';
		if ( is_user_logged_in() && bp_displayed_user_id() === bp_loggedin_user_id() ) {
			?>
		<label><?php esc_html_e( 'Enable/Disable Contact me tab', 'bp-contact-me' ); ?></label>
		<input type="checkbox" name="general[contact_me_button]" <?php echo ( 'on' === $contact_me_button_option ) ? 'checked' : 'unchecked'; ?>/>
			<?php
		}
	}
	/**
	 * Bp_contact_me_btn save option value
	 *
	 * @return void
	 */
	public function bp_contact_enbale_disable_option_save() {
		$contact_me_data = isset( $_POST['general']['contact_me_button'] ) ? sanitize_text_field( wp_unslash( $_POST['general']['contact_me_button'] ) ) : '';
		update_user_meta( bp_loggedin_user_id(), 'contact_me_button', $contact_me_data );
	}

	/**
	 * Function will trigger to register notification component
	 */
	public function bp_contact_me_notifications_get_registered_components( $component_names = array() ) {
		// Force $component_names to be an array.
		if ( ! is_array( $component_names ) ) {
			$component_names = array();
		}
		// Add 'buddypress_member_review' component to registered components array.
		array_push( $component_names, 'bcm_user_notifications' );
		// Return component's with 'buddypress_member_review' appended.
		return $component_names;
	}

	/**
	 * Function will trigger format notifications
	 */
	public function bp_contact_me_notification_format( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' ) {
		global $wpdb;
		$bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
		$get_contact_q_noti       = "SELECT * FROM $bp_contact_me_table_name  WHERE `id` = $item_id";
		$get_contact_r_noti       = $wpdb->get_row( $get_contact_q_noti, ARRAY_A );
		$sender_id                = isset( $get_contact_r_noti['sender'] ) ? $get_contact_r_noti['sender'] : '';
		$sender_data              = get_userdata( $sender_id );
		$author_name              = isset( $sender_data->data->user_login ) && is_user_logged_in() ? $sender_data->data->user_login : 'Someones';
		$loggedin_user_id         = get_current_user_id();
		$username                 = bp_core_get_username( $loggedin_user_id );
		$user_link                = get_site_url() . '/members/' . $username . '/contact/';
		if ( 'bcm_user_notifications_action' === $action ) {
			$notification_string = sprintf( __( ' %1$s wants to contact you.', 'bp-contact-me' ), $author_name );
			if ( 'string' === $format ) {
				$return = "<a href='" . esc_url( $user_link ) . "'>" . $notification_string . '</a>';
			} else {
				$return = array(
					'text' => $notification_string,
					'link' => $user_link,
				);
			}
		}
		return $return;
	}

	/**
	 * Function will trigger notifications to member users
	 */
	public function bp_contact_me_notification( $get_contact_id, $bp_display_user_id ) {
		$args                = array(
			'user_id'           => $bp_display_user_id,
			'item_id'           => $get_contact_id,
			'secondary_item_id' => $bp_display_user_id,
			'component_name'    => 'bcm_user_notifications',
			'component_action'  => 'bcm_user_notifications_action',
			'date_notified'     => bp_core_current_time(),
			'is_new'            => 1,
			'allow_duplicate'   => true,
		);
		$bcm_general_setting = get_option( 'bcm_admin_general_setting' );
		if ( isset( $bcm_general_setting['bcm_allow_notification'] ) && 'yes' === $bcm_general_setting['bcm_allow_notification'] ) {
			bp_notifications_add_notification( $args );
		}
	}

	/**
	 * Function will trigger to send email notifiction
	 */
	public function bp_contact_me_email( $get_contact_id, $bp_display_user_id ) {
		$bcm_general_setting  = get_option( 'bcm_admin_general_setting' );
		$current_user_id      = get_current_user_id();
		$username             = bp_core_get_username( $current_user_id );
		$login_contact_tab    = bp_core_get_username( $bp_display_user_id );
		$user_contact_link    = get_site_url() . '/members/' . $login_contact_tab . '/contact/';
		$user_contact_me_link = get_site_url() . '/members/' . $username . '/contact-me/';
		$author_name          = get_the_author_meta( 'display_name', $current_user_id );
		$bcm_contact_link     = '<a href="' . esc_url( $user_contact_link ) . '">' . esc_html( 'here' ) . '</a>';
		$bcm_contact_me_link  = '<a href="' . esc_url( $user_contact_me_link ) . '">' . esc_html( 'contact form' ) . '</a>';
		$to                   = get_the_author_meta( 'user_email', $bp_display_user_id );
		$replyto_mail_id      = get_the_author_meta( 'user_email', $current_user_id );
		$subject              = isset( $bcm_general_setting['bcm_email_subject'] ) && '' != $bcm_general_setting['bcm_email_subject'] ? $bcm_general_setting['bcm_email_subject'] : 'Contact';
		$user_content         = isset( $bcm_general_setting['bcm_email_content'] ) && '' != $bcm_general_setting['bcm_email_content'] ? $bcm_general_setting['bcm_email_content'] : '';
		$content              = sprintf( __( '%1$s wants to contact you. Check the all messages %2$s. Go to the %3$s.', 'bp-contact-me' ), $author_name, $bcm_contact_link, $bcm_contact_me_link );
		$content 			.= $user_content;
		$headers              = array( 'Content-Type: text/html; charset=UTF-8' );
		$reply_to             = 'Reply-To: ' . $replyto_mail_id . "\r\n" . 'X-Mailer: ';
		$bcm_general_setting  = get_option( 'bcm_admin_general_setting' );
		if ( isset( $bcm_general_setting['bcm_allow_email'] ) && 'yes' === $bcm_general_setting['bcm_allow_email'] ) {
			wp_mail( $to, $subject, $content, $headers, $reply_to );
		}
	}

}
