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
class Buddypress_Contact_Me_Public
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
         * defined in Buddypress_Contact_Me_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Buddypress_Contact_Me_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */

        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/buddypress-contact-me-public.css', array(), $this->version, 'all');

    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
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
        wp_enqueue_script($this->plugin_name . '-sweetalert', plugin_dir_url(__FILE__) . 'js/sweetalert.min.js', array( 'jquery' ), $this->version, false);
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/buddypress-contact-me-public.js', array( 'jquery', $this->plugin_name . '-sweetalert' ), $this->version, false);
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
            )
        );
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
                    if( 'on' == $bcm_user[0] ){
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
        if (! empty( $bcm_who_contact ) && in_array('visitors', $bcm_who_contact, true) && ! is_user_logged_in() ) {
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
        if (is_user_logged_in() && bp_displayed_user_id() === bp_loggedin_user_id() ) {
            $bcm_get_contact = get_option('bcm_admin_general_setting');
            if (array_key_exists('bcm_allow_contact_tab', $bcm_get_contact) ) {
                bp_core_new_nav_item(
                    array(
                    'name'                    => esc_html__('Contact', 'buddypress-contact-me'),
                    'slug'                    => 'contact',
                    'screen_function'         => array( $this, 'bp_contact_me_show_data_screen' ),
                    'position'                => 75,
                    'default_subnav_slug'     => 'contact',
                    'show_for_displayed_user' => true,
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
            <label><?php esc_html_e('Enable/Disable Contact me tab', 'bp-contact-me'); ?></label>
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
            update_user_meta(bp_loggedin_user_id(), 'contact_me_button', $_POST['contact_me_button']);
        } else {
            update_user_meta(bp_loggedin_user_id(), 'contact_me_button', '');
        }
        $redirect_to = trailingslashit(bp_displayed_user_domain() . bp_get_settings_slug() . '/general');
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
     * Function will trigger format notifications
     */
    public function bp_contact_me_notification_format( $action, $item_id, $secondary_item_id, $total_items, $format = 'string' )
    {
        global $wpdb;
        $return                   = '';
        $bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
        $get_contact_q_noti       = "SELECT * FROM $bp_contact_me_table_name  WHERE `id` = $item_id";
        $get_contact_r_noti       = $wpdb->get_row($get_contact_q_noti, ARRAY_A);
        $get_contact_r_name       = isset($get_contact_r_noti['name']) && ! empty($get_contact_r_noti['name']) ? $get_contact_r_noti['name'] : '';
        $sender_id                = isset($get_contact_r_noti['sender']) ? $get_contact_r_noti['sender'] : '';
        $sender_data              = get_userdata($sender_id);
        $author_name              = isset($sender_data->data->user_login) && is_user_logged_in() ? $sender_data->data->user_login : $get_contact_r_name;
        $loggedin_user_id         = get_current_user_id();
        $username                 = bp_core_get_username($loggedin_user_id);
        $user_link                = get_site_url() . '/members/' . $username . '/contact/';
        if ('bcm_user_notifications_action' === $action ) {
            $notification_string = sprintf(__(' %1$s has contacted you.', 'bp-contact-me'), $author_name);
            if ('string' === $format ) {
                $return = "<a href='" . esc_url($user_link) . "'>" . $notification_string . '</a>';
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
    public function bp_contact_me_notification( $get_contact_id, $bp_display_user_id )
    {
        if (bp_is_active('notifications') ) {
            $args = array(
            'user_id'           => $bp_display_user_id,
            'item_id'           => $get_contact_id,
            'secondary_item_id' => $bp_display_user_id,
            'component_name'    => 'bcm_user_notifications',
            'component_action'  => 'bcm_user_notifications_action',
            'date_notified'     => bp_core_current_time(),
            'is_new'            => 1,
            'allow_duplicate'   => true,
            );
        }
        $bcm_general_setting = get_option('bcm_admin_general_setting');
        if (isset($bcm_general_setting['bcm_allow_notification']) && 'yes' === $bcm_general_setting['bcm_allow_notification'] ) {
            bp_notifications_add_notification($args);
        }
    }

    /**
     * Formate email subject
     *
     * Dynamically replace tags with corsponsding string
     *
     * @param  array $email_subject
     * @return string
     */
    public function bcm_get_email_subject( $email_subject )
    {
        $subject = '';
        if (isset($email_subject['bcm_email_subject']) && ! empty($email_subject['bcm_email_subject']) ) {
            $subject         = $email_subject['bcm_email_subject'];
            $current_user_id = get_current_user_id();
            $author_name     = get_the_author_meta('display_name', $current_user_id);
            if (strpos($subject, '{user_name}') !== false ) {
                $subject = str_replace('{user_name}', $author_name, $subject);
            }
        }
        return apply_filters('bcm_email_subject', $subject, $email_subject);
    }

    /**
     * Function will trigger to send email notifiction
     */
    public function bp_contact_me_email( $get_contact_id, $bp_display_user_id )
    {
        global $wpdb;
        $bcm_general_setting        = get_option('bcm_admin_general_setting');
        $bcm_sender_email_id        = isset($bcm_general_setting['bcm_user_email']) && '' != $bcm_general_setting['bcm_user_email'] ? $bcm_general_setting['bcm_user_email'] : get_option('admin_email');
        $current_user_id            = get_current_user_id();
        $username                   = bp_core_get_username($current_user_id);
        $login_username             = bp_core_get_username($bp_display_user_id);
        $user_contact_link          = bp_core_get_user_domain($bp_display_user_id) . 'contact';
        $user_contact_me_link       = bp_core_get_user_domain($current_user_id) . 'contact-me';
        $bcm_contact_link           = '<a href="' . esc_url($user_contact_link) . '">' . esc_html('Click here') . '</a>';
        $bcm_contact_me_link        = '<a href="' . esc_url($user_contact_me_link) . '">' . esc_html('contact form') . '</a>';
        $bp_contact_me_table_name   = $wpdb->prefix . 'contact_me';
        $get_contact_q_noti         = "SELECT * FROM $bp_contact_me_table_name  WHERE `id` = $get_contact_id";
        $get_contact_r_noti         = $wpdb->get_row($get_contact_q_noti, ARRAY_A);
        $subject                    = $this->bcm_get_email_subject($bcm_general_setting);
        $bcm_admin_multiuser_mail   = array();
        $to                         = get_the_author_meta('user_email', $bp_display_user_id);
        $bcm_admin_multiuser_mail[] = $to;

        // sender mail.

        if (array_key_exists('bcm_allow_sender_copy_email', $bcm_general_setting) ) {
            $sender_mail_id             = '';
            $sender_mail_id             = get_the_author_meta('user_email', $current_user_id);
            $bcm_admin_multiuser_mail[] = $sender_mail_id;
        }

        // admin mail.
        if (! empty($bcm_general_setting['bcm_allow_admin_copy_email']) && 'yes' === $bcm_general_setting['bcm_allow_admin_copy_email'] ) {
            $admin_users = get_users(
                array(
                'role'   => 'administrator',
                'fields' => array( 'ID', 'display_name' ),
                )
            );
            foreach ( $admin_users as $admin_user ) {
                $bcm_admin_mail_id          = get_the_author_meta('user_email', $admin_user->ID);
                $bcm_admin_multiuser_mail[] = $bcm_admin_mail_id;
            }
        }
        // multiple users.
        if (array_key_exists('bcm_multiple_user_copy_email', $bcm_general_setting) ) {
            $bcm_multi_data_users = $bcm_general_setting['bcm_multiple_user_copy_email'];
            foreach ( $bcm_multi_data_users as $bcm_multi_data_key => $bcm_multi_data_val ) {
                $bcm_multi_users_mail_id    = get_the_author_meta('user_email', $bcm_multi_data_val);
                $bcm_admin_multiuser_mail[] = $bcm_multi_users_mail_id;
            }
        }
        if (is_user_logged_in() ) {
            $author_name = get_the_author_meta('display_name', $current_user_id);
        } else {
            $author_name = $get_contact_r_noti['name'];
        }
        $user_content = isset($bcm_general_setting['bcm_email_content']) && '' != $bcm_general_setting['bcm_email_content'] ? $bcm_general_setting['bcm_email_content'] : '';
        $content      = sprintf(__('Hi %1$s,<br>%2$s has contacted you.<br>%3$s to check the messages.<br>You can also go to the %4$s.<br>Thanks', 'bp-contact-me'), $login_username, $author_name, $bcm_contact_link, $bcm_contact_me_link);
        $headers      = "Content-Type: text/html; charset=UTF-8\r\n";
        $headers     .= 'From: ' . $bcm_sender_email_id . "\r\n";
        // add all mails in cc.
        $bcm_cc_mails = '';
        foreach ( $bcm_admin_multiuser_mail as $bcm_admin_multiuser_mail_key => $bcm_admin_multiuser_mail_val ) {
            $bcm_cc_mails .= $bcm_admin_multiuser_mail_val . ',';
        }
        $headers .= 'Cc:' . $bcm_cc_mails;
        if (! empty($bcm_admin_multiuser_mail) ) {
            $bcm_emails = array_unique($bcm_admin_multiuser_mail);
        }
        $bcm_general_setting = get_option('bcm_admin_general_setting');
        if (isset($bcm_general_setting['bcm_allow_email']) && 'yes' === $bcm_general_setting['bcm_allow_email'] ) {
            foreach ( $bcm_emails as $bcm_email ) {
                wp_mail($bcm_email, $subject, $content, $headers);
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
     * Call BuddyPress Contact Me Form submission
     *
     * @since 1.0.0
     */
    public function bp_contact_me_form_submitted()
    {
        if (! isset($_POST['bcm_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['bcm_nonce'])), 'bcm_form_nonce') ) {
            return false;
        }
        if (isset($_POST['bp_contact_me_form_save']) ) {
            global $wpdb;
            $bp_sender_user_id = get_current_user_id();
            if (isset($_POST['bcm_shortcode_user_id']) && '' !== $_POST['bcm_shortcode_user_id'] ) {
                $bp_display_user_id = isset($_POST['bcm_shortcode_user_id']) ? sanitize_text_field(wp_unslash($_POST['bcm_shortcode_user_id'])) : '';
            } elseif (isset($_POST['bcm_shortcode_username']) && '' !== sanitize_text_field(wp_unslash($_POST['bcm_shortcode_username'])) ) {
                $bcm_get_user_data  = get_user_by('login', sanitize_text_field(wp_unslash($_POST['bcm_shortcode_username'])));
                $bp_display_user_id = $bcm_get_user_data->data->ID;
            } else {
                $bp_display_user_id = bp_displayed_user_id();
            }
            $bp_contact_me_subject = isset($_POST['bp_contact_me_subject']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_subject'])) : '';
            $bp_contact_me_msg     = isset($_POST['bp_contact_me_msg']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_msg'])) : '';
            if (is_user_logged_in() ) {
                $bp_contact_me_fname = isset($_POST['bp_contact_me_login_name']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_login_name'])) : '';
            } else {
                $bp_contact_me_fname = isset($_POST['bp_contact_me_first_name']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_first_name'])) : '';
            }
            $bp_contact_me_email    = isset($_POST['bp_contact_me_email']) ? sanitize_text_field(wp_unslash($_POST['bp_contact_me_email'])) : '';
            $bp_contact_me_datetime = current_datetime()->format('Y-m-d H:i:s');
            $bp_contact_me_table    = $wpdb->prefix . 'contact_me';
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
                array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
            );
            if (isset($insert_data_contact_me) && '' !== $insert_data_contact_me ) {
                bp_core_add_message(__('Message sent successfully.', 'buddypress-contact-me'));
                $get_contact_id = $wpdb->insert_id;
                do_action('bp_contact_me_form_save', $get_contact_id, $bp_display_user_id);
                $disp_user_url = bp_core_get_user_domain($bp_display_user_id);
                if ('' !== $_POST['bcm_shortcode_user_id'] || '' !== $_POST['bcm_shortcode_username'] ) {
                    global $wp;
                    $contact_me_url = home_url($wp->request);
                } else {
                    $contact_me_url = $disp_user_url . '/contact-me/';
                }
                $contact_me_url_qp = add_query_arg('output', 'success', $contact_me_url);
                wp_redirect($contact_me_url_qp);
                die();
            }
        }
    }

    /**
     * Call for deleted contact messages
     *
     * @since 1.0.0
     */
    public function bcm_message_delete()
    {
        if (! isset($_POST['nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'bcm-contact-nonce') ) {
            return false;
        }
        global $wpdb;
        $table_name = $wpdb->prefix . 'contact_me';
        $rowid      = $_POST['rowid'];
        $query      = $wpdb->query("DELETE FROM $table_name WHERE id =" . $rowid);
        if (1 == $query ) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * Call for bulk deleted contact messages
     *
     * @since 1.0.0
     */
    public function bcm_contact_action_bulk_manage()
    {
        if ('contact' != bp_current_action() ) {
            return false;
        }
        $nonce = ! empty($_POST['bcm_contact_bulk_nonce']) ? sanitize_text_field(wp_unslash($_POST['bcm_contact_bulk_nonce'])) : '';
        if (! wp_verify_nonce($nonce, 'bcm_contact_bulk_nonce') ) {
            return false;
        }
        $action   = ! empty($_POST['bcm_contact_bulk_action']) ? sanitize_text_field(wp_unslash($_POST['bcm_contact_bulk_action'])) : '';
        $items    = ! empty($_POST['bcm_messages']) ? $_POST['bcm_messages'] : '';
        $redirect = ! empty($_POST['_wp_http_referer']) ? sanitize_text_field(wp_unslash($_POST['_wp_http_referer'])) : '';
        $_items   = implode(',', wp_parse_id_list($items));
        if (! is_array($items) ) {
            return false;
        }
        if ('delete' == $action ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'contact_me';
            $wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE id IN " . '(' . $_items . ')'));
        }
        bp_core_add_message(__('Delete successfully.', 'buddypress'));
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
        $rowid      = isset($_POST['rowid']) ? sanitize_text_field(wp_unslash($_POST['rowid'])) : '';
        $query      = $wpdb->get_row("SELECT * FROM $table_name WHERE id =" . $rowid);
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

}
