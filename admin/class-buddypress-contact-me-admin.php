<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.wbcomdesigns.com
 * @since      1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/admin
 * @author     WBCOM Designs <admin@wbcomdesigns.com>
 */
class Buddypress_Contact_Me_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->init_hooks();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/buddypress-contact-me-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'selectize', plugin_dir_url( __FILE__ ) . 'css/selectize.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/buddypress-contact-me-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'selectize', plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since  1.0.0
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( $this, 'bcm_add_plugin_settings' ) );
	}

	/**
	 * Bp_contact_me_add_admin_menu
	 *
	 * @since    1.0.0
	 */
	public function bp_contact_me_add_admin_menu() {
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page( esc_html__( 'WB Plugins', 'buddypress-contact-me' ), esc_html__( 'WB Plugins', 'buddypress-contact-me' ), 'manage_options', 'wbcomplugins', array( $this, 'bcm_settings_page' ), 'dashicons-lightbulb', 59 );
			add_submenu_page( 'wbcomplugins', esc_html__( 'General', 'buddypress-contact-me' ), esc_html__( 'General', 'buddypress-contact-me' ), 'manage_options', 'wbcomplugins' );
		}
		add_submenu_page( 'wbcomplugins', esc_html__( 'BuddyPress Contact Me', 'buddypress-contact-me' ), esc_html__( 'Contact Me', 'buddypress-contact-me' ), 'manage_options', 'buddypress-contact-me', array( $this, 'bcm_settings_page' ) );
	}

		/**
		 * Hide all notices from the setting page.
		 *
		 * @return void
		 */
	public function wbcom_hide_all_admin_notices_from_setting_page() {
		$wbcom_pages_array  = array( 'wbcomplugins', 'wbcom-plugins-page', 'wbcom-support-page', 'buddypress-contact-me' );
		$wbcom_setting_page = filter_input( INPUT_GET, 'page' ) ? filter_input( INPUT_GET, 'page' ) : '';

		if ( in_array( $wbcom_setting_page, $wbcom_pages_array, true ) ) {
			remove_all_actions( 'admin_notices' );
			remove_all_actions( 'all_admin_notices' );
		}

	}

	/**
	 * Bpsp_settings_page
	 *
	 * @since    1.0.0
	 */
	public function bcm_settings_page() {
		$current = filter_input( INPUT_GET, 'tab' ) ? filter_input( INPUT_GET, 'tab' ) : 'welcome';
		?>

		<div class="wrap">
			<div class="wbcom-bb-plugins-offer-wrapper">
				<div id="wb_admin_logo">
					<a href="https://wbcomdesigns.com/downloads/buddypress-community-bundle/?utm_source=pluginoffernotice&utm_medium=community_banner" target="_blank">
						<img src="<?php echo esc_url( BUDDYPRESS_CONTACT_ME_PLUGIN_URL ) . 'admin/wbcom/assets/imgs/wbcom-offer-notice.png'; ?>">
					</a>
				</div>
			</div>
			<div class="wbcom-wrap wbcom-plugin-wrapper">
				<div class="bupr-header">
					<div class="wbcom_admin_header-wrapper">
						<div id="wb_admin_plugin_name">
							<?php esc_html_e( 'BuddyPress Contact Me', 'buddypress-contact-me' ); ?>
							<?php /* translators: %s: */ ?>
							<span><?php printf( esc_html__( 'Version %s', 'buddypress-contact-me' ), esc_attr( BUDDYPRESS_CONTACT_ME_VERSION ) ); ?></span>
						</div>
						<?php echo do_shortcode( '[wbcom_admin_setting_header]' ); ?>
					</div>
				</div>
				<div class="wbcom-admin-settings-page">
				<?php
				$blpro_tabs = array(
					'welcome' => esc_html__( 'Welcome', 'buddypress-contact-me' ),
					'general' => esc_html__( 'General', 'buddypress-contact-me' ),
					'support' => esc_html__( 'FAQ', 'buddypress-contact-me' ),
				);

				$tab_html = '<div class="wbcom-tabs-section"><div class="nav-tab-wrapper"><div class="wb-responsive-menu"><span>' . esc_html( 'Menu' ) . '</span><input class="wb-toggle-btn" type="checkbox" id="wb-toggle-btn"><label class="wb-toggle-icon" for="wb-toggle-btn"><span class="wb-icon-bars"></span></label></div><ul>';
				foreach ( $blpro_tabs as $blpro_tab => $blpro_name ) {
					$class     = ( $blpro_tab == $current ) ? 'nav-tab-active' : '';
					$tab_html .= '<li class="' . $blpro_name . '"><a class="nav-tab ' . $class . '" href="admin.php?page=buddypress-contact-me&tab=' . $blpro_tab . '">' . $blpro_name . '</a></li>';
				}
				$tab_html .= '</div></ul></div>';
				echo wp_kses_post( $tab_html );
				include 'inc/buddypress-contact-me-tabs-options.php';
				echo '</div>';
				echo '</div>';
				echo '</div>';
				// echo '</div>';
	}

	/**
	 * Plugin register settings.
	 *
	 * @since 1.0.0
	 */
	public function bcm_add_plugin_settings() {
		register_setting( 'bcm_admin_general_email_notification_setting', 'bcm_admin_general_setting' );
	}
}
