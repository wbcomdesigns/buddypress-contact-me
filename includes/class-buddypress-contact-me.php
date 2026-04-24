<?php
/**
 * Core plugin class loader.
 *
 * @package BuddyPress_Contact_Me
 */

defined( 'ABSPATH' ) || exit;

/**
 * Defines internationalization, admin hooks, and public-facing site hooks.
 *
 * Also maintains the plugin identifier and current version.
 *
 * @since 1.0.0
 */
class BuddyPress_Contact_Me {


	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since  1.0.0
	 * @access protected
	 * @var    BuddyPress_Contact_Me_Loader    $loader    Maintains and registers all hooks for the plugin.
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
	 * - BuddyPress_Contact_Me_Loader: orchestrates hooks.
	 * - BuddyPress_Contact_Me_I18n: internationalization.
	 * - BCM_Admin: admin card-panel UI.
	 * - BCM_Messages_Repo: data access for the contact_me table.
	 * - BCM_Frontend_*: nav, assets, submit, notifications, flash, shortcode.
	 * - BCM_Rest_Messages: REST endpoints.
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
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-buddypress-contact-me-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/class-buddypress-contact-me-i18n.php';

		/**
		 * The 1.5.0 card-panel admin class. Replaces the legacy wbcom
		 * wrapper admin entirely — owns menu, enqueue, settings
		 * registration, and notice suppression. See
		 * references/wbcom-wrapper-migration.md (skill reference).
		 */
		include_once plugin_dir_path( __DIR__ ) . 'includes/admin/class-bcm-admin.php';

		/**
		 * Frontend + REST layer. Each class is focused on a single
		 * concern — data access, navigation, assets, submit, REST,
		 * notifications, flash notices, shortcode.
		 */
		$base = plugin_dir_path( __DIR__ );
		include_once $base . 'includes/data/class-bcm-messages-repo.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-flash.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-nav.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-assets.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-submit.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-notifications.php';
		include_once $base . 'includes/frontend/class-bcm-frontend-shortcode.php';
		include_once $base . 'includes/rest/class-bcm-rest-messages.php';
		include_once $base . 'includes/email/class-bcm-email-installer.php';

		include_once $base . 'edd-license/edd-plugin-license.php';

		$this->loader = new BuddyPress_Contact_Me_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the BuddyPress_Contact_Me_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function set_locale() {

		$plugin_i18n = new BuddyPress_Contact_Me_I18n();

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

		// 1.5.0 card-panel admin — owns menu, enqueue, notice
		// suppression, register_setting, and the hub landing takeover.
		// The legacy BuddyPress_Contact_Me_Admin class has been
		// retired; its entire surface moved into BCM_Admin.
		$panel = new BCM_Admin();
		$panel->register();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 */
	private function define_public_hooks() {
		// Each class self-registers its hooks. The loader is reserved for
		// the few cross-cutting wires we still need (currently none).
		( new BCM_Frontend_Nav() )->register();
		( new BCM_Frontend_Assets( $this->get_plugin_name(), $this->get_version() ) )->register();
		( new BCM_Frontend_Submit() )->register();
		( new BCM_Frontend_Notifications() )->register();
		( new BCM_Frontend_Shortcode() )->register();
		( new BCM_Rest_Messages() )->register();
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
	 * @return BuddyPress_Contact_Me_Loader    Orchestrates the hooks of the plugin.
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
