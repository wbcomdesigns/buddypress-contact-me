<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link  https://www.wbcomdesigns.com
 * @since 1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/includes
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Contact_Me {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    Buddypress_Contact_Me_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		if ( defined( 'BUDDYPRESS_CONTACT_ME_VERSION' ) ) {
			$this->version = BUDDYPRESS_CONTACT_ME_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'buddypress-contact-me';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Buddypress_Contact_Me_Loader. Orchestrates the hooks of the plugin.
	 * - Buddypress_Contact_Me_i18n. Defines internationalization functionality.
	 * - Buddypress_Contact_Me_Admin. Defines all hooks for the admin area.
	 * - Buddypress_Contact_Me_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-contact-me-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-buddypress-contact-me-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-buddypress-contact-me-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-buddypress-contact-me-public.php';

		/* Enqueue wbcom plugin folder file. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wbcom/wbcom-admin-settings.php';

		/* Enqueue wbcom plugin folder file. */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/wbcom/wbcom-paid-plugin-settings.php';

		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'edd-license/edd-plugin-license.php';

		$this->loader = new Buddypress_Contact_Me_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Buddypress_Contact_Me_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new Buddypress_Contact_Me_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Buddypress_Contact_Me_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'bp_contact_me_add_admin_menu' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {

		$plugin_public = new Buddypress_Contact_Me_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'bp_setup_nav', $plugin_public, 'bp_contact_me_tab' );
		$this->loader->add_action( 'bp_core_general_settings_before_submit', $plugin_public, 'bp_contact_me_button' );
		$this->loader->add_action( 'bp_actions', $plugin_public, 'bp_contact_enbale_disable_option_save' );
		$this->loader->add_action( 'bp_notifications_get_registered_components', $plugin_public, 'bp_contact_me_notifications_get_registered_components' );
		$this->loader->add_filter( 'bp_notifications_get_notifications_for_user', $plugin_public, 'bp_contact_me_notification_format', 10, 7 );
		$this->loader->add_action( 'bp_contact_me_form_save', $plugin_public, 'bp_contact_me_notification', 10, 2 );
		$this->loader->add_action( 'bp_contact_me_form_save', $plugin_public, 'bp_contact_me_email', 10, 2 );
		$this->loader->add_action( 'bp_setup_nav', $plugin_public, 'bp_contact_me_show_data' );
		$this->loader->add_action( 'bp_setup_admin_bar', $plugin_public, 'bp_contact_me_setup_admin_bar', 10 );
		$this->loader->add_shortcode( 'buddypress-contact-me', $plugin_public, 'bp_contact_me_form' );
		$this->loader->add_action( 'bp_actions', $plugin_public, 'bp_contact_me_form_submitted' );
		$this->loader->add_action( 'wp_ajax_bcm_message_del', $plugin_public, 'bcm_message_delete' );
		$this->loader->add_action( 'bp_actions', $plugin_public, 'bcm_contact_action_bulk_manage', 10, 3 );
		$this->loader->add_action( 'wp_ajax_bcm_message_popup', $plugin_public, 'bcm_contact_message_popup' );
		$this->loader->add_filter( 'body_class', $plugin_public, 'bcm_body_class', 10, 1 );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since 1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since  1.0.0
	 * @return string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since  1.0.0
	 * @return Buddypress_Contact_Me_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since  1.0.0
	 * @return string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
