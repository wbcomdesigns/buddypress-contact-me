<?php


/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    BuddyPress_Contact_Me
 * @subpackage BuddyPress_Contact_Me/public
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 * @link  https://www.wbcomdesigns.com
 * @since 1.0.0
 */
class BuddyPress_Contact_Me_Public
{


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
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in BuddyPress_Contact_Me_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The BuddyPress_Contact_Me_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if( function_exists( 'bp_is_user' ) && bp_is_user() && ( bp_is_current_component( 'contact-me' ) || bp_is_current_component( 'contact' ) || bp_is_current_component( 'settings' ) ) ) {

            if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$extension = is_rtl() ? '.rtl.css' : '.css';
				$path      = is_rtl() ? '/rtl' : '';
			} else {
				$extension = is_rtl() ? '.rtl.css' : '.min.css';
				$path      = is_rtl() ? '/rtl' : '/min';
			}

            wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css' . $path . '/buddypress-contact-me-public' . $extension, array(), $this->version, 'all');
        }
        

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        global $wpdb;
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in BuddyPress_Contact_Me_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The BuddyPress_Contact_Me_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        if(function_exists( 'bp_is_user' ) && bp_is_user() ){

            if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$extension = '.js';
				$path      = '';
			} else {
				$extension = '.min.js';
				$path      = '/min';
			}
            $bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
            // phpcs:disable
            $contact_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $bp_contact_me_table_name WHERE `reciever` = %d",
                get_current_user_id()
            ));
            // phpcs:enable
            $is_buddyboss_active = (function_exists('buddypress') && buddypress()->buddyboss) ? true : false;

            wp_enqueue_script($this->plugin_name . '-sweetalert', plugin_dir_url(__FILE__) . 'js/vendor/sweetalert.min.js', array( 'jquery' ), $this->version, false);
            wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js' . $path . '/buddypress-contact-me-public' . $extension, array( 'jquery', $this->plugin_name . '-sweetalert' ), $this->version, false);
            $user_logged = 0;
            if (is_user_logged_in() ) {
                $user_logged = 1;
            }
            wp_localize_script(
                $this->plugin_name,
                'bcm_ajax_object',
                array(
                    'ajax_url'   => admin_url('admin-ajax.php'),
                    'ajax_nonce' => wp_create_nonce('bcm-contact-nonce'),
                    'user_log'   => $user_logged,
                    'contact_count' => $contact_count,
                    'is_buddyboss_active' => $is_buddyboss_active,
                    // Add localized strings
                    'delete_confirm' => __('Are you sure you want to delete this message?', 'buddypress-contact-me'),
                    'delete_error' => __('Error deleting message. Please try again.', 'buddypress-contact-me'),
                    'email_error' => __('Please enter a valid email address', 'buddypress-contact-me'),
                    'field_required' => __('This field is required', 'buddypress-contact-me'),
                    'min_length' => __('Minimum {min} characters required', 'buddypress-contact-me'),
                    'max_length' => __('Maximum {max} characters allowed', 'buddypress-contact-me'),
                    'captcha_error' => __('Incorrect answer', 'buddypress-contact-me'),
                    'popup_error' => __('Error loading message. Please try again.', 'buddypress-contact-me'),
                    'close_text' => __('Close', 'buddypress-contact-me'),
                )
            );
        }
    }

    /**
     * Get displayed user role.
     *
     * @since  2.3.0
     * @access public
     * @author Wbcom Designs
     */
    public function bcm_get_current_user_roles( $user_id )
    {
        if (is_user_logged_in() ) {
            $user  = get_userdata($user_id);
            $roles = array();
            if (is_object($user) ) {
                $roles = $user->roles;
            }
            return $roles; // This returns an array.
        }
    }

    /**
     * Register a new tab in member's profile - Contact Me
     *
     * @since 1.0.1
     */
    public function bp_contact_me_tab()
    {

        $bp_display_user_id          = bp_displayed_user_id();
        $contact_me_btn_value        = get_user_meta($bp_display_user_id, 'contact_me_button');
        $contact_me_btn_value_option = isset($contact_me_btn_value[0]) ? $contact_me_btn_value[0] : '';
        $bcm_admin_general_setting   = get_option('bcm_admin_general_setting');
        if ('' != $bcm_admin_general_setting && array_key_exists('bcm_who_contact', $bcm_admin_general_setting) ) {
            $bcm_who_contact = $bcm_admin_general_setting['bcm_who_contact'];
        }
        if ('' != $bcm_admin_general_setting && array_key_exists('bcm_who_contacted', $bcm_admin_general_setting) ) {
            $bcm_who_contacted = $bcm_admin_general_setting['bcm_who_contacted'];
        }


        if (bp_displayed_user_id() === bp_loggedin_user_id() ) {
            if (! empty($bcm_who_contact) ) {
                $user_role = $this->bcm_get_current_user_roles(bp_loggedin_user_id());
                if (! empty($user_role) && in_array($user_role[0], $bcm_who_contact, true) && ! bp_loggedin_user_id() ) {
                    bp_core_new_nav_item(
                        array(
                        'name'                    => esc_html__('Contact Me', 'buddypress-contact-me'),
                        'slug'                    => 'contact-me',
                        'screen_function'         => array( $this, 'bp_contact_me_show_screen' ),
                        'position'                => 80,
                        'default_subnav_slug'     => 'contact-me',
                        'show_for_displayed_user' => true,
                        )
                    );
                }
            }
        } else {
            $user_role = $this->bcm_get_current_user_roles(bp_loggedin_user_id());
            if (! empty($user_role) && ! empty($bcm_who_contact) && ! in_array($user_role[0], $bcm_who_contact, true) ) {
                return;
            }
            if (! empty($bcm_who_contacted) && ! empty($user_role) ) {
                $user_role = $this->bcm_get_current_user_roles(bp_displayed_user_id());

                $user_role = ! empty($user_role[0]) ? $user_role[0] : array();

                if (in_array($user_role, $bcm_who_contacted, true) && ! empty( $bcm_who_contact ) ) {
                    $bcm_user = get_user_meta( bp_displayed_user_id(), 'contact_me_button' );
                    if( 'on' == isset($bcm_user[0]) && $bcm_user[0] ){
                    bp_core_new_nav_item(
                        array(
                        'name'                    => esc_html__('Contact Me', 'buddypress-contact-me'),
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
        }            
        // for logout user
        if ( ! empty( $bcm_who_contact ) && in_array('visitors', $bcm_who_contact, true) && ! is_user_logged_in() ) {
            $user = get_userdata( bp_displayed_user_id() );
            if ( !empty( $user ) ){
                $user_role = $user->roles;
            }
            if ( ! empty ( $bcm_who_contacted ) && ! empty( $user_role ) && in_array( $user_role[0] , $bcm_who_contacted ) ) {
                $contact_me_btn = get_user_meta( bp_displayed_user_id(), 'contact_me_button' );
                if( ! empty( $contact_me_btn[0] ) && ( 'on' === $contact_me_btn[0] ) )   {
                    bp_core_new_nav_item(
                        array(
                            'name'                    => esc_html__('Contact Me', 'buddypress-contact-me'),
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
    }


   


    /**
     * Bp_contact_me_show_screen
     *
     * @return void
     */
    public function bp_contact_me_show_screen()
    {
        add_action('bp_template_content', array( $this, 'bp_contact_me_tab_function_to_show_content' ));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Bp_contact_me_tab_function_to_show_content
     *
     * @return void
     */
    public function bp_contact_me_tab_function_to_show_content()
    {
        include 'partials/bp-contact-me-tab-content.php';
    }

    /**
     * Add contact tab on loggedin user profile.
     *
     * @return void
     */
    public function bp_contact_me_show_data()
    {
        global $wpdb;
        if (is_user_logged_in() && bp_displayed_user_id() === bp_loggedin_user_id() ) {
            $bcm_get_contact = get_option('bcm_admin_general_setting');
            if (array_key_exists('bcm_allow_contact_tab', $bcm_get_contact) ) {
                $bp_contact_me_table_name = $wpdb->prefix . 'contact_me';

                // phpcs:disable
                $contact_count = $wpdb->get_var( $wpdb->prepare(
                    "SELECT COUNT(*) FROM $bp_contact_me_table_name WHERE `reciever` = %d",
                    get_current_user_id()
                ) );
                // phpcs:enable

                bp_core_new_nav_item(
                    array(
                    'name'                    => esc_html__('Contact ', 'buddypress-contact-me').'<span class="count">'.$contact_count.'</span>',
                    'slug'                    => 'contact',
                    'screen_function'         => array( $this, 'bp_contact_me_show_data_screen' ),
                    'position'                => 75,
                    'default_subnav_slug'     => 'contact',
                    'show_for_displayed_user' => true,
                    'item_css_id' => 'bp_contact_count',
                    )
                );
            }
        }
    }

    /**
     * Adds the user's navigation in WP Admin Bar
     *
     * @since 1.0.0
     */
    public function bp_contact_me_setup_admin_bar( $wp_admin_nav = array() )
    {
        global $wp_admin_bar, $current_user;
        $bcm_get_contact = !empty( get_option('bcm_admin_general_setting') ) ? get_option('bcm_admin_general_setting') : array();

        if ( array_key_exists( 'bcm_allow_contact_tab', $bcm_get_contact ) ) {
            $contact_tab_slug = bp_loggedin_user_domain() . 'contact';
            // Menus for logged in user.
            if (is_user_logged_in() ) {
                $wp_admin_bar->add_menu(
                    array(
                    'parent' => 'my-account-buddypress',
                    'id'     => 'my-account-contact',
                    'title'  => esc_html__('Contact', 'buddypress-contact-me'),
                    'href'   => trailingslashit($contact_tab_slug),
                    )
                );
            }
        }
    }

    /**
     * Add function for show contact data.
     *
     * @return void
     */
    public function bp_contact_me_show_data_screen()
    {
        add_action('bp_template_content', array( $this, 'bp_contact_me_function_to_show_data' ));
        bp_core_load_template(apply_filters('bp_core_template_plugin', 'members/single/plugins'));
    }

    /**
     * Add file to show sender data to the logged in user.
     *
     * @return void
     */
    public function bp_contact_me_function_to_show_data()
    {
        if( function_exists( 'bp_is_active' ) && bp_is_active( 'notifications' ) && function_exists( 'bp_notifications_mark_notifications_by_type' ) ) {
            bp_notifications_mark_notifications_by_type( get_current_user_id(), 'bcm_user_notifications', 'bcm_user_notifications_action' );
        }
        include 'partials/bp-contact-tab-show-sender-user-data.php';
    }

    /**
     * Bp_contact_me_btn create on member profile setting
     *
     * @return void
     */
    public function bp_contact_me_button()
    {
        $contact_me_button        = get_user_meta(bp_displayed_user_id(), 'contact_me_button');
        $contact_me_button_option = isset($contact_me_button[0]) ? $contact_me_button[0] : '';
        if (is_user_logged_in() && bp_displayed_user_id() === bp_loggedin_user_id() ) {
            ?>
        <div class="enable-bp-contact-tab">            
            <input type="checkbox" name="contact_me_button" <?php echo ( 'on' === $contact_me_button_option ) ? 'checked' : 'unchecked'; ?>/>
            <label><?php esc_html_e('Enable/Disable Contact me tab', 'buddypress-contact-me'); ?></label>
        </div>
            <?php
        }
    }
    /**
     * Bp_contact_me_btn save option value
     *
     * @return void
     */
    public function bp_contact_enbale_disable_option_save()
    {
        if (! bp_is_post_request() ) {
            return;
        }

        // Bail if no submit action.
        if (! isset($_POST['submit']) ) {
            return;
        }

        // Bail if not in settings.
        if (! bp_is_settings_component() || ! bp_is_current_action('general') ) {
            return;
        }

        // 404 if there are any additional action variables attached
        if (bp_action_variables() ) {
            bp_do_404();
            return;
        }
        check_admin_referer('bp_settings_general');
        if (isset($_POST['contact_me_button']) && ! empty($_POST['contact_me_button']) ) {
            update_user_meta(bp_loggedin_user_id(), 'contact_me_button', sanitize_text_field( wp_unslash( $_POST['contact_me_button'] ) ) );
        } else {
            update_user_meta(bp_loggedin_user_id(), 'contact_me_button', '');
        }
    }

    /**
     * Function to add notice when the setting is saved in the user settings section.
     * @param $user_id int User ID
     * @param $redirect_to string Redirection url after settings are saved.
     * 
     * @since 1.3.1
     * @return void
     * 
     */
    public function bp_contact_me_render_user_settings_save_notice( $user_id, $redirect_to) { 

        $feedback[]    = __( 'Your settings have been saved.', 'buddypress-contact-me' );
		$feedback_type = 'success';
        
        bp_core_add_message( implode( "\n", $feedback ), $feedback_type );

        $path_chunks[] = 'settings/general';
	    $redirect_to   = bp_displayed_user_url( bp_members_get_path_chunks( $path_chunks ) );
        bp_core_redirect($redirect_to);
    }
    /**
     * Function will trigger to register notification component
     */
    public function bp_contact_me_notifications_get_registered_components( $component_names = array() )
    {
        // Force $component_names to be an array.
        if (! is_array($component_names) ) {
            $component_names = array();
        }
        // Add 'buddypress_member_review' component to registered components array.
        array_push($component_names, 'bcm_user_notifications');
        // Return component's with 'buddypress_member_review' appended.
        return $component_names;
    }

    /**
     * Function will trigger format notifications.
     *
     * @param string $action The notification action.
     * @param int $item_id The item ID.
     * @param int $secondary_item_id The secondary item ID.
     * @param int $total_items The total number of items.
     * @param string $format The format of the notification.
     * @param string $component_action_name The component action name.
     * @param string $component_name The component name.
     * @return mixed The formatted notification.
     */
    public function bp_contact_me_notification_format($action, $item_id, $secondary_item_id, $total_items, $format, $component_action_name, $component_name)
    {
        $format = 'string';
        if (
            'bcm_user_notifications_action' === $component_action_name
        ) {
            global $wpdb;
            // phpcs:disable
            $bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
            $get_contact_q_noti       = $wpdb->prepare("SELECT * FROM $bp_contact_me_table_name WHERE `id` = %d", $item_id);
            $get_contact_r_noti       = $wpdb->get_row($get_contact_q_noti, ARRAY_A);
            // phpcs:enable
            $get_contact_r_name       = isset($get_contact_r_noti['name']) && !empty($get_contact_r_noti['name']) ? $get_contact_r_noti['name'] : '';
            $sender_id                = isset($get_contact_r_noti['sender']) ? $get_contact_r_noti['sender'] : '';
            $sender_data              = get_userdata($sender_id);
            $author_name              = isset($sender_data->data->user_login) && is_user_logged_in() ? $sender_data->data->user_login : $get_contact_r_name;
            $loggedin_user_id         = get_current_user_id();

            // Get the members root slug dynamically.
            $members_slug = bp_get_members_root_slug();
            if( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
                $username = bp_core_get_username($loggedin_user_id);
            } else {
                $username = bp_members_get_user_slug($loggedin_user_id);
            }
            
            $user_link = get_site_url() . '/' . $members_slug . '/' . $username . '/contact/';

            /* translators: %s: */
            $notification_string = sprintf(__(' %1$s has contacted you.', 'buddypress-contact-me'), $author_name);

            if ('string' === $format) {
                $return = "<a href='" . esc_url($user_link) . "'>" . $notification_string . '</a>';
            } else {
                $return = array(
                    'text' => $notification_string,
                    'link' => $user_link,
                );
            }
            return $return;
        }
        return $action;
    }


    /**
     * Function will trigger notifications to member users
     */
    public function bp_contact_me_notification( $get_contact_id, $bp_display_user_id, $bp_sender_user_id)
    {
        if ( function_exists( 'bp_is_active' ) && bp_is_active('notifications') ) {
            $args = array(
            'user_id'           => $bp_display_user_id,
            'item_id'           => $get_contact_id,
            'secondary_item_id' => $bp_sender_user_id,
            'component_name'    => 'bcm_user_notifications',
            'component_action'  => 'bcm_user_notifications_action',
            'date_notified'     => bp_core_current_time(),
            'is_new'            => 1,
            'allow_duplicate'   => true,
            );

            $bcm_general_setting = get_option('bcm_admin_general_setting');
            if (isset($bcm_general_setting['bcm_allow_notification']) && 'yes' === $bcm_general_setting['bcm_allow_notification'] ) {
                if ( function_exists( 'bp_notifications_add_notification' ) ) {
                    bp_notifications_add_notification($args);
                }
                
            }
        }
        
    }

    /**
     * Format email subject.
     *
     * Dynamically replace tags with corresponding strings.
     *
     * @param array $email_subject The email subject array.
     * @return string The formatted email subject.
     */
    public function bcm_get_email_subject($email_subject)
    {
        $subject = '';

        // Check if email subject is set and not empty.
        if (
            isset($email_subject['bcm_email_subject']) && !empty($email_subject['bcm_email_subject'])
        ) {
            $subject = $email_subject['bcm_email_subject'];
            $author_name = 'An Anonymous person'; // Default value for non-logged in users.

            // Check if the user is logged in.
            if (is_user_logged_in()) {
                $current_user_id = get_current_user_id();
                $author_name = get_the_author_meta('display_name', $current_user_id);
            }

            // Replace {user_name} placeholder with the actual author name.
            if (strpos($subject, '{user_name}') !== false) {
                $subject = str_replace('{user_name}', $author_name, $subject);
            }
        }

        return apply_filters('bcm_email_subject', $subject, $email_subject);
    }


    /**
     * Sends an email notification when a contact is made.
     *
     * @param int $get_contact_id The ID of the contact message.
     * @param int $bp_display_user_id The ID of the user receiving the contact message.
     */
    public function bp_contact_me_email( $get_contact_id, $bp_display_user_id )
    {
        global $wpdb;

        // Retrieve plugin settings.
        $bcm_general_setting = get_option('bcm_admin_general_setting');
        $bcm_sender_email_id = isset($bcm_general_setting['bcm_user_email']) && !empty($bcm_general_setting['bcm_user_email']) ? $bcm_general_setting['bcm_user_email'] : get_option('admin_email');
        $current_user_id = get_current_user_id();

        // Retrieve user slugs based on BuddyPress version.

        if( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) {
            $username             = bp_core_get_username($current_user_id);
            $login_username       = bp_core_get_username($bp_display_user_id);
        }else{
            $username             = bp_members_get_user_slug($current_user_id);
            $login_username       = bp_members_get_user_slug($bp_display_user_id);
        }
        
        $user_contact_link    = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) ? bp_core_get_user_domain($bp_display_user_id) . 'contact' : bp_members_get_user_url($bp_display_user_id) . 'contact';
        $user_contact_me_link = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) ? bp_core_get_user_domain($current_user_id) . 'contact-me' : bp_members_get_user_url($current_user_id) . 'contact-me';
              
        // Create contact links.
        $bcm_contact_link = '<a href="' . esc_url($user_contact_link) . '">' . esc_html__('Click here', 'buddypress-contact-me') . '</a>';
        $bcm_contact_me_link = '<a href="' . esc_url($user_contact_me_link) . '">' . esc_html__('contact form', 'buddypress-contact-me') . '</a>';
    
        // Retrieve contact message details.
        // phpcs:disable
        $bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
        $get_contact_q_noti = $wpdb->prepare("SELECT * FROM $bp_contact_me_table_name WHERE `id` = %d", $get_contact_id);
        $get_contact_r_noti = $wpdb->get_row($get_contact_q_noti, ARRAY_A);
        // phpcs:enable

        // Get the email subject.
        $subject = $this->bcm_get_email_subject($bcm_general_setting);

        // Prepare recipients list.
        $bcm_admin_multiuser_mail = array();
        $to = get_the_author_meta('user_email', $bp_display_user_id);
        $bcm_admin_multiuser_mail[] = $to;

        // Include sender's email if the option is enabled.
        if ( array_key_exists('bcm_allow_sender_copy_email', $bcm_general_setting ) ) {
            
            if( ! empty( $current_user_id ) ) { 
                $sender_mail_id = get_the_author_meta('user_email', $current_user_id);
            } else {
                $sender_mail_id = $get_contact_r_noti['email'];
            }
           
            $bcm_admin_multiuser_mail[] = $sender_mail_id;
        }

        // Include admin emails if the option is enabled.
        if ( !empty($bcm_general_setting['bcm_allow_admin_copy_email']) && 'yes' === $bcm_general_setting['bcm_allow_admin_copy_email'] ) {
            $admin_users = get_users(
                array(
                    'role'   => 'administrator',
                    'fields' => array('ID', 'display_name'),
                )
            );
            foreach ($admin_users as $admin_user) {
                $bcm_admin_mail_id = get_the_author_meta('user_email', $admin_user->ID);
                $bcm_admin_multiuser_mail[] = $bcm_admin_mail_id;
            }
        }

        // Include multiple user emails if the option is enabled.
        if (
            array_key_exists('bcm_multiple_user_copy_email', $bcm_general_setting)
        ) {
            $bcm_multi_data_users = $bcm_general_setting['bcm_multiple_user_copy_email'];
            foreach ($bcm_multi_data_users as $bcm_multi_data_val) {
                $bcm_multi_users_mail_id = get_the_author_meta('user_email', $bcm_multi_data_val);
                $bcm_admin_multiuser_mail[] = $bcm_multi_users_mail_id;
            }
        }

        // Get the author name.
        $author_name = is_user_logged_in() ? get_the_author_meta('display_name', $current_user_id) : $get_contact_r_noti['name'];

        // Get the email content.
        $user_content = isset($bcm_general_setting['bcm_email_content']) && !empty($bcm_general_setting['bcm_email_content']) ? $bcm_general_setting['bcm_email_content'] : '';
        $user_content = str_replace( array( "{user_name}", "{sender_user_name}", "{site_name}" ) ,  array( $login_username , $author_name , get_bloginfo( 'name' )  ), $user_content);
        
        $user_content = str_replace( "Click here", $bcm_contact_link, $user_content );

        // Prepare email headers.
        $headers = "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= 'From: ' . $bcm_sender_email_id . "\r\n";
        $headers .= 'Cc:' . implode(',', array_unique($bcm_admin_multiuser_mail));

        // Send the email if the option is enabled.
        if (
            isset($bcm_general_setting['bcm_allow_email']) && 'yes' === $bcm_general_setting['bcm_allow_email']
        ) {
            foreach (array_unique($bcm_admin_multiuser_mail) as $bcm_email) {
                wp_mail($bcm_email, $subject, nl2br( $user_content ), $headers);
            }
        }
    }


    /**
     * Call BuddyPress Contact Me shortcode
     *
     * @since 1.0.0
     */
    public function bp_contact_me_form( $atts )
    {
        ob_start();
        $output_string = include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/bp-contact-me-tab-content.php';
        $output_string = ob_get_contents();
        ob_end_clean();
        return $output_string;
    }

    /**
     * Handle BuddyPress Contact Me Form submission.
     *
     * @since 1.0.0
     */
    public function bp_contact_me_form_submitted()
    {
        if ( isset( $_COOKIE['bcm_notice_message'] ) ) {
            setcookie( 'bcm_notice_message', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
            setcookie( 'bcm_notice_type', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
        }
        if (
            !isset($_POST['bcm_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bcm_nonce'])), 'bcm_form_nonce')
        ) {
            return false;
        }

       

        if (
            !empty($_POST)
        ) {
            global $wpdb;

            $bp_sender_user_id = get_current_user_id();

            // Determine the displayed user ID based on the submitted form data.
            if (isset($_POST['bcm_shortcode_user_id']) && '' !== $_POST['bcm_shortcode_user_id']) {
                $bp_display_user_id = sanitize_text_field(wp_unslash($_POST['bcm_shortcode_user_id']));
            } elseif (isset($_POST['bcm_shortcode_username']) && '' !== sanitize_text_field(wp_unslash($_POST['bcm_shortcode_username']))) {
                $bcm_get_user_data = get_user_by('login', sanitize_text_field(wp_unslash($_POST['bcm_shortcode_username'])));
                $bp_display_user_id = $bcm_get_user_data->ID;
            } else {
                $bp_display_user_id = bp_displayed_user_id();
            }

            // Sanitize and retrieve form data.
            $bp_contact_me_subject = isset($_POST['bp_contact_me_subject']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_subject'])) : '';
            $bp_contact_me_msg     = isset($_POST['bp_contact_me_msg']) ? sanitize_textarea_field(wp_unslash($_POST['bp_contact_me_msg'])) : '';
            $bp_contact_me_fname   = is_user_logged_in()
                ? (isset($_POST['bp_contact_me_login_name']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_login_name'])) : '')
                : (isset($_POST['bp_contact_me_first_name']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_first_name'])) : '');
            $bp_contact_me_email   = isset($_POST['bp_contact_me_email']) ? sanitize_email(wp_unslash($_POST['bp_contact_me_email'])) : '';
            $bp_contact_me_datetime = current_datetime()->format('Y-m-d H:i:s');

            // Initialize validation errors array
            $validation_errors = array();

            // Validate captcha FIRST (before other validations)
            $captcha_answer = isset($_POST['bcm_captcha_answer']) ? intval($_POST['bcm_captcha_answer']) : '';
            $captcha_hash = isset($_POST['bcm_captcha_hash']) ? sanitize_text_field(wp_unslash($_POST['bcm_captcha_hash'])) : '';
            
            // Validate captcha answer
            if (empty($captcha_answer)) {
                $validation_errors[] = __('Please answer the security question.', 'buddypress-contact-me');
            } else {
                // Check multiple possible answers to prevent timing attacks
                $valid_captcha = false;
                // Check reasonable range of answers (prevents brute force)
                for ($i = 1; $i <= 40; $i++) {
                    if (wp_hash($i) === $captcha_hash && $i === $captcha_answer) {
                        $valid_captcha = true;
                        break;
                    }
                }
                
                if (!$valid_captcha) {
                    $validation_errors[] = __('The security question answer is incorrect. Please try again.', 'buddypress-contact-me');
                }
            }

            // Validate name
            if (empty($bp_contact_me_fname)) {
                $validation_errors[] = __('Name is required.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_fname) < 2) {
                $validation_errors[] = __('Name must be at least 2 characters long.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_fname) > 100) {
                $validation_errors[] = __('Name cannot exceed 100 characters.', 'buddypress-contact-me');
            }

            // Validate email for non-logged-in users
            if (!is_user_logged_in()) {
                if (empty($bp_contact_me_email)) {
                    $validation_errors[] = __('Email is required.', 'buddypress-contact-me');
                } elseif (!is_email($bp_contact_me_email)) {
                    $validation_errors[] = __('Please enter a valid email address.', 'buddypress-contact-me');
                }
            }

            // Validate subject
            if (empty($bp_contact_me_subject)) {
                $validation_errors[] = __('Subject is required.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_subject) < 3) {
                $validation_errors[] = __('Subject must be at least 3 characters long.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_subject) > 200) {
                $validation_errors[] = __('Subject cannot exceed 200 characters.', 'buddypress-contact-me');
            }

            // Validate message
            if (empty($bp_contact_me_msg)) {
                $validation_errors[] = __('Message is required.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_msg) < 10) {
                $validation_errors[] = __('Message must be at least 10 characters long.', 'buddypress-contact-me');
            } elseif (strlen($bp_contact_me_msg) > 5000) {
                $validation_errors[] = __('Message cannot exceed 5000 characters.', 'buddypress-contact-me');
            }

            // Check for spam patterns in message
            if ($this->bcm_check_spam_patterns($bp_contact_me_msg)) {
                $validation_errors[] = __('Your message appears to contain spam content.', 'buddypress-contact-me');
            }

            // Add rate limiting check (optional but recommended)
            // if ($this->bcm_check_rate_limit($bp_display_user_id)) {
            //     $validation_errors[] = __('You are sending messages too quickly. Please wait a moment and try again.', 'buddypress-contact-me');
            // }

            // If there are validation errors, show them and redirect back
            if (!empty($validation_errors)) {
                foreach ($validation_errors as $error) {
                    $this->bcm_handle_the_notice($error, 'error');
                }
                
                // Determine the redirect URL for errors
                $disp_user_url = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) ? bp_core_get_user_domain($bp_display_user_id) : bp_members_get_user_url($bp_display_user_id);
                $error_redirect_url = (isset($_POST['bcm_shortcode_user_id']) && '' !== $_POST['bcm_shortcode_user_id']) || (isset($_POST['bcm_shortcode_username']) && '' !== $_POST['bcm_shortcode_username'])
                    ? wp_get_referer()
                    : $disp_user_url . 'contact-me/';
                    
                wp_redirect($error_redirect_url);
                exit;
            }

            // Sanitize data one more time before insertion
            $bp_contact_me_subject = substr($bp_contact_me_subject, 0, 200);
            $bp_contact_me_msg = substr($bp_contact_me_msg, 0, 5000);
            $bp_contact_me_fname = substr($bp_contact_me_fname, 0, 100);

            // Insert contact message data into the database.
            // phpcs:disable
            $bp_contact_me_table = $wpdb->prefix . 'contact_me';
            $insert_data_contact_me = $wpdb->insert(
                $bp_contact_me_table,
                array(
                    'sender'   => $bp_sender_user_id,
                    'reciever' => $bp_display_user_id,
                    'subject'  => $bp_contact_me_subject,
                    'message'  => $bp_contact_me_msg,
                    'name'     => $bp_contact_me_fname,
                    'email'    => $bp_contact_me_email,
                    'datetime' => $bp_contact_me_datetime,
                ),
                array('%d', '%d', '%s', '%s', '%s', '%s', '%s')
            );
            // phpcs:enable
            if ($insert_data_contact_me) {
                $this->bcm_handle_the_notice(__('Message sent successfully.', 'buddypress-contact-me'));
                $get_contact_id = $wpdb->insert_id;
                do_action('bp_contact_me_form_save', $get_contact_id, $bp_display_user_id, $bp_sender_user_id);

                // Determine the redirect URL.
                $disp_user_url = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) ) ? bp_core_get_user_domain($bp_display_user_id) : bp_members_get_user_url($bp_display_user_id);

                $contact_me_url = (isset($_POST['bcm_shortcode_user_id']) && '' !== $_POST['bcm_shortcode_user_id']) || (isset($_POST['bcm_shortcode_username']) && '' !== $_POST['bcm_shortcode_username'])
                    ? wp_get_referer()
                    : $disp_user_url . 'contact-me/';

                $contact_me_url_qp = add_query_arg('output', 'success', $contact_me_url);
                wp_redirect($contact_me_url_qp);
                exit;
            } else {
                $this->bcm_handle_the_notice(__('An error occurred while sending your message. Please try again.', 'buddypress-contact-me'), 'error');
                wp_redirect(wp_get_referer());
                exit;
            }
        }
    }
    
    /**
     * Check for common spam patterns in message content.
     *
     * @since 1.3.1
     * @param string $message The message to check.
     * @return bool True if spam patterns detected, false otherwise.
     */
    private function bcm_check_spam_patterns($message) {
        // Common spam patterns
        $spam_patterns = array(
            '/\b(?:viagra|cialis|levitra|pharmacy|pills|medication)\b/i',
            '/\b(?:casino|poker|blackjack|slots|gambling)\b/i',
            '/\b(?:loan|mortgage|credit|debt|finance)\s*(?:offer|approval|rate)/i',
            '/(?:click\s*here|buy\s*now|order\s*now|limited\s*time)/i',
            '/\b(?:million\s*dollar|you\s*won|congratulations\s*winner)\b/i',
            '/https?:\/\/[^\s]+/i', // URLs - you might want to adjust this based on your needs
            '/\b[A-Z]{5,}\b/', // Multiple consecutive capital letters
            '/(.)\1{4,}/', // Same character repeated 5+ times
        );

        foreach ($spam_patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        // Check for excessive special characters
        $special_char_count = preg_match_all('/[!@#$%^&*()_+=\[\]{};\':"\\|,.<>\/?]/', $message);
        if ($special_char_count > strlen($message) * 0.3) { // More than 30% special characters
            return true;
        }

        return apply_filters('bcm_spam_check', false, $message);
    }


    /**
     * Call for deleted contact messages
     *
     * @since 1.0.0
     */
    public function bcm_message_delete()
    {
        // Check if the required data is present
        if (!isset($_POST['nonce']) || !isset($_POST['rowid'])) {
            wp_send_json_error(array(
                'message' => __('Invalid request. Missing required data.', 'buddypress-contact-me')
            ));
            return;
        }
        
        // Sanitize the row ID
        $rowid = absint($_POST['rowid']);
        
        // Verify the nonce with the specific action for this message
        if (!wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'bcm_delete_message_' . $rowid)) {
            wp_send_json_error(array(
                'message' => __('Security check failed. Please refresh the page and try again.', 'buddypress-contact-me')
            ));
            return;
        }
        
        // Additional permission check - ensure user can only delete their own messages
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_me';
        
        // First, verify the message belongs to the current user
        $message_owner = $wpdb->get_var($wpdb->prepare(
            "SELECT reciever FROM $table_name WHERE id = %d",
            $rowid
        ));
        
        if ($message_owner != get_current_user_id()) {
            wp_send_json_error(array(
                'message' => __('You do not have permission to delete this message.', 'buddypress-contact-me')
            ));
            return;
        }
        
        // Perform the deletion
        $deleted = $wpdb->delete(
            $table_name,
            array('id' => $rowid),
            array('%d')
        );
        
        if ($deleted) {
            wp_send_json_success(array(
                'message' => __('Message deleted successfully.', 'buddypress-contact-me')
            ));
        } else {
            wp_send_json_error(array(
                'message' => __('Failed to delete message. Please try again.', 'buddypress-contact-me')
            ));
        }
    }

    /**
     * Call for bulk deleted contact messages
     *
     * @since 1.0.0
     */
    public function bcm_contact_action_bulk_manage()
    {
        if ('contact' != bp_current_action()) {
            return false;
        }
        
        $nonce = !empty($_POST['bcm_contact_bulk_nonce']) ? sanitize_text_field(wp_unslash($_POST['bcm_contact_bulk_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'bcm_contact_bulk_nonce')) {
            return false;
        }
        
        $action = !empty($_POST['bcm_contact_bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bcm_contact_bulk_action'])) : '';
        $items = !empty($_POST['bcm_messages']) ? array_map('absint', $_POST['bcm_messages']) : array();
        $redirect = !empty($_POST['_wp_http_referer']) ? sanitize_text_field(wp_unslash($_POST['_wp_http_referer'])) : '';
        
        if (!is_array($items) || empty($items)) {
            return false;
        }
        
        if ('delete' == $action) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'contact_me';
            $current_user_id = get_current_user_id();
            
            // Verify ownership of all messages before deletion
            $placeholders = array_fill(0, count($items), '%d');
            $query = $wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE id IN (" . implode(',', $placeholders) . ") AND reciever = %d",
                array_merge($items, array($current_user_id))
            );
            
            $owned_count = $wpdb->get_var($query);
            
            if ($owned_count != count($items)) {
                $this->bcm_handle_the_notice(__('You can only delete your own messages.', 'buddypress-contact-me'), 'error');
                bp_core_redirect($redirect);
                return;
            }
            
            // Delete only messages owned by current user
            $deleted = $wpdb->query(
                $wpdb->prepare(
                    "DELETE FROM $table_name WHERE id IN (" . implode(',', $placeholders) . ") AND reciever = %d",
                    array_merge($items, array($current_user_id))
                )
            );
            
            if ($deleted > 0) {
                $this->bcm_handle_the_notice(sprintf(_n('%d message deleted successfully.', '%d messages deleted successfully.', $deleted, 'buddypress-contact-me'), $deleted));
            } else {
                $this->bcm_handle_the_notice(__('No messages were deleted.', 'buddypress-contact-me'), 'error');
            }
        }
        
        bp_core_redirect($redirect);
    }

    /**
     * Call for contact messages popup
     *
     * @since 1.0.0
     */
    public function bcm_contact_message_popup()
    {
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'bcm-contact-nonce') ) {
            return false;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_me';
        $rowid      = isset($_POST['rowid']) ? sanitize_text_field(wp_unslash($_POST['rowid'])) : 0;
        $query      = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $rowid));    // phpcs:ignore
        if (0 != $query->sender ) {
            $name = bp_core_get_user_displayname($query->sender);
        } else {
            $name = $query->name;
        }
        if (0 != $query->sender ) {
            $mail = get_the_author_meta('user_email', $query->sender);
        } else {
            $mail = $query->email;
        }
        $subject   = $query->subject;
        $message   = $query->message;
        $date_time = explode(' ', $query->datetime);
        $bcm_date  = $date_time[0];
        $bcm_time  = $date_time[1];
        if ($query ) {
            $bcm_html  = '<ul class="bp-contact-me-popup-message">';
            $bcm_html .= '<li><strong>Name : </strong><span>' . $name . '</span></li>';
            $bcm_html .= '<li><strong>Email : </strong><span>' . $mail . '</span></li>';
            $bcm_html .= '<li><strong>Subject : </strong><span>' . $subject . '</span></li>';
            $bcm_html .= '<li><strong>Message : </strong><span>' . $message . '</span></li>';
            $bcm_html .= '<li><strong>Submitted on : </strong><span>' . $bcm_date . ' at ' . $bcm_time . '</span></li>';
            $bcm_html .= '</ul>';
        }
        wp_send_json_success(
            array(
            'html' => $bcm_html,
            )
        );
    }

    /**
     * Call for add class in body
     *
     * @since 1.0.0
     */
    public function bcm_body_class( $classes )
    {
        if (bp_is_user() && 'contact' == bp_current_action() ) {
            $classes[] = 'wbcom-bp-contact';
        }
        return $classes;
    }

    /**
     * Call for add class in body
     *
     * @since 1.0.0
     */
    public function bcm_handle_the_notice( $message, $type = 'success' )
    {
        if(is_user_logged_in()){
            bp_core_add_message( $message, $type);
        }else{
            // Store notice in cookie for next request
            setcookie( 'bcm_notice_message', $message, time() + 60, COOKIEPATH, COOKIE_DOMAIN );
            setcookie( 'bcm_notice_type', $type, time() + 60, COOKIEPATH, COOKIE_DOMAIN );
        }
    }
}
