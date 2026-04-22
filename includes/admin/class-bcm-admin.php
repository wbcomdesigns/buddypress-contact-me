<?php
/**
 * BCM Admin: menu, enqueue, page rendering.
 *
 * Replaces the legacy Wbcom wrapper chrome. Follows the
 * wp-plugin-development skill Part 6 card-panel pattern and the
 * references/wbcom-wrapper-migration.md playbook (Parts 5, 6, 15).
 *
 * @package Buddypress_Contact_Me
 * @since 1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class BCM_Admin
 *
 * @since 1.5.0
 */
class BCM_Admin {

	/**
	 * Menu slug (kept identical to legacy so bookmarks still work).
	 */
	const MENU_SLUG = 'buddypress-contact-me';

	/**
	 * Option group + option name used by register_setting.
	 */
	const OPTION_GROUP = 'bcm_admin_general_email_notification_setting';
	const OPTION_NAME  = 'bcm_admin_general_setting';

	/**
	 * All sidebar tabs rendered inside the one admin page.
	 * Grouped into main + settings + account.
	 *
	 * @return array<string, array{label:string, icon:string, group:string}>
	 */
	public static function get_tabs() {
		$tabs = array(
			'overview'      => array(
				'label' => __( 'Overview', 'buddypress-contact-me' ),
				'icon'  => 'dashicons-chart-bar',
				'group' => 'main',
			),
			'notifications' => array(
				'label' => __( 'Notifications', 'buddypress-contact-me' ),
				'icon'  => 'dashicons-bell',
				'group' => 'settings',
			),
			'email'         => array(
				'label' => __( 'Email Template', 'buddypress-contact-me' ),
				'icon'  => 'dashicons-email-alt',
				'group' => 'settings',
			),
			'access'        => array(
				'label' => __( 'Access', 'buddypress-contact-me' ),
				'icon'  => 'dashicons-shield',
				'group' => 'settings',
			),
			'license'       => array(
				'label' => __( 'License', 'buddypress-contact-me' ),
				'icon'  => 'dashicons-shield-alt',
				'group' => 'account',
			),
		);
		return apply_filters( 'bcm_admin_tabs', $tabs );
	}

	/**
	 * Bootstrap hooks.
	 */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		// Priority 999 runs after every other plugin has registered its
		// admin_menu entries. Used to reclaim the hub's landing render
		// when a legacy wbcom-wrapper plugin was the first to register
		// the wbcomplugins top-level menu.
		add_action( 'admin_menu', array( $this, 'takeover_hub_landing' ), 999 );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'in_admin_header', array( $this, 'suppress_foreign_notices' ), 1 );
	}

	/**
	 * Attach as a single submenu under the shared WB Plugins hub.
	 */
	public function add_menu(): void {
		$cap = 'manage_options';

		// First Wbcom plugin to load creates the shared WB Plugins hub.
		// Whichever plugin wins the race provides the hub's landing
		// dashboard (a card grid of every Wbcom plugin attached to the
		// hub). Peer plugins are auto-discovered via
		// $GLOBALS['submenu']['wbcomplugins'].
		if ( empty( $GLOBALS['admin_page_hooks']['wbcomplugins'] ) ) {
			add_menu_page(
				esc_html__( 'WB Plugins', 'buddypress-contact-me' ),
				esc_html__( 'WB Plugins', 'buddypress-contact-me' ),
				$cap,
				'wbcomplugins',
				array( $this, 'render_hub' ),
				'dashicons-lightbulb',
				59
			);
		}

		add_submenu_page(
			'wbcomplugins',
			esc_html__( 'BuddyPress Contact Me', 'buddypress-contact-me' ),
			esc_html__( 'Contact Me', 'buddypress-contact-me' ),
			$cap,
			self::MENU_SLUG,
			array( $this, 'render_page' )
		);
	}

	/**
	 * Force the shared hub's landing page to render our clean card-panel
	 * dashboard, overriding whatever a legacy wbcom-wrapper plugin may
	 * have registered. See references/wbcom-wrapper-migration.md Part 15.
	 *
	 * Mixed installs are the common case: a client site may have 2
	 * migrated plugins and 18 still on the old wrapper. Whoever wins the
	 * admin_menu race creates the wbcomplugins top-level and points its
	 * landing at their own dashboard. A legacy wrapper's dashboard shows
	 * generic Wbcom brand chrome, not the card grid clients expect.
	 *
	 * This runs at admin_menu priority 999 so every other registration
	 * is complete; we strip any existing callback on the hub's page
	 * action and install our render_hub. Migrated peer plugins run the
	 * same routine — they overwrite each other harmlessly because the
	 * hub views are visually equivalent across plugins.
	 */
	public function takeover_hub_landing(): void {
		global $admin_page_hooks;
		if ( empty( $admin_page_hooks['wbcomplugins'] ) ) {
			return;
		}
		remove_all_actions( 'toplevel_page_wbcomplugins' );
		add_action( 'toplevel_page_wbcomplugins', array( $this, 'render_hub' ) );
	}

	/**
	 * Register the settings option used by the form. Sanitization is
	 * the legacy admin's concern — we just hand it back as-is so the
	 * save contract is byte-identical to the pre-1.5.0 form.
	 */
	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);
	}

	/**
	 * Sanitizer. Keeps the schema simple: booleans stay as 'yes'/absent,
	 * text fields are sanitized by key.
	 *
	 * @param mixed $input Raw form input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_settings( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$output        = array();
		$bool_keys     = array(
			'bcm_allow_notification',
			'bcm_allow_email',
			'bcm_allow_admin_copy_email',
			'bcm_allow_sender_copy_email',
			'bcm_allow_contact_tab',
			'bcm_multiple_user_copy_email',
		);
		$textarea_keys = array( 'bcm_email_content' );
		$email_key     = array( 'bcm_user_email' );

		foreach ( $bool_keys as $key ) {
			if ( isset( $input[ $key ] ) && 'yes' === $input[ $key ] ) {
				$output[ $key ] = 'yes';
			}
		}
		foreach ( $textarea_keys as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = wp_kses_post( wp_unslash( $input[ $key ] ) );
			}
		}
		if ( isset( $input['bcm_email_subject'] ) ) {
			$output['bcm_email_subject'] = sanitize_text_field( wp_unslash( $input['bcm_email_subject'] ) );
		}
		foreach ( $email_key as $key ) {
			if ( isset( $input[ $key ] ) ) {
				$output[ $key ] = sanitize_email( wp_unslash( $input[ $key ] ) );
			}
		}
		if ( isset( $input['bcm_who_contact'] ) && is_array( $input['bcm_who_contact'] ) ) {
			$output['bcm_who_contact'] = array_map( 'sanitize_text_field', wp_unslash( $input['bcm_who_contact'] ) );
		}
		if ( isset( $input['bcm_who_contacted'] ) && is_array( $input['bcm_who_contacted'] ) ) {
			$output['bcm_who_contacted'] = array_map( 'sanitize_text_field', wp_unslash( $input['bcm_who_contacted'] ) );
		}

		return $output;
	}

	/**
	 * Enqueue admin assets only on our screen + the shared hub landing.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}

		$version = defined( 'BUDDYPRESS_CONTACT_ME_VERSION' ) ? BUDDYPRESS_CONTACT_ME_VERSION : '1.0.0';

		wp_enqueue_style(
			'bcm-admin',
			plugins_url( 'assets/css/admin.css', BUDDYPRESS_CONTACT_ME_FILE ),
			array(),
			$version
		);

		wp_enqueue_script(
			'bcm-admin',
			plugins_url( 'assets/js/admin.js', BUDDYPRESS_CONTACT_ME_FILE ),
			array( 'jquery' ),
			$version,
			true
		);

		wp_localize_script(
			'bcm-admin',
			'bcmAdmin',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'saved'           => __( 'Settings saved.', 'buddypress-contact-me' ),
					'saveFailed'      => __( 'Could not save. Please try again.', 'buddypress-contact-me' ),
					'confirmDanger'   => __( 'Are you sure? This cannot be undone.', 'buddypress-contact-me' ),
					'confirmContinue' => __( 'Continue', 'buddypress-contact-me' ),
					'confirmCancel'   => __( 'Cancel', 'buddypress-contact-me' ),
				),
			)
		);
	}

	/**
	 * Suppress 3rd-party admin notices on our screen.
	 */
	public function suppress_foreign_notices(): void {
		$screen = get_current_screen();
		if ( ! $screen || ! $this->is_our_screen( $screen ) ) {
			return;
		}
		remove_all_actions( 'admin_notices' );
		remove_all_actions( 'all_admin_notices' );
	}

	/**
	 * True if the current screen is the plugin's admin page OR the
	 * shared hub landing (our callback owns the hub render too, so
	 * our CSS/JS must load there).
	 *
	 * @param WP_Screen $screen Current screen.
	 */
	private function is_our_screen( $screen ): bool {
		if ( empty( $screen->id ) ) {
			return false;
		}
		return (bool) preg_match( '/_page_' . preg_quote( self::MENU_SLUG, '/' ) . '$/', $screen->id )
			|| 'toplevel_page_wbcomplugins' === $screen->id;
	}

	/**
	 * Render the shared WB Plugins hub landing page.
	 */
	public function render_hub(): void {
		$view = plugin_dir_path( BUDDYPRESS_CONTACT_ME_FILE ) . 'includes/admin/views/hub.php';
		if ( file_exists( $view ) ) {
			include $view;
		}
	}

	/**
	 * Render the single admin page. Routes to the active tab.
	 */
	public function render_page(): void {
		$tabs      = self::get_tabs();
		$tab_slugs = array_keys( $tabs );
		$active    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : $tab_slugs[0]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $tabs[ $active ] ) ) {
			$active = $tab_slugs[0];
		}

		$page_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$settings = get_option( self::OPTION_NAME, array() );

		$view_map            = array(
			'overview'      => 'overview',
			'notifications' => 'settings-notifications',
			'email'         => 'settings-email',
			'access'        => 'settings-access',
			'license'       => 'license',
		);
		$view                = isset( $view_map[ $active ] ) ? $view_map[ $active ] : 'overview';
		$in_settings_group   = isset( $tabs[ $active ]['group'] ) && 'settings' === $tabs[ $active ]['group'];
		$settings_form_group = self::OPTION_GROUP;

		$view_path = plugin_dir_path( BUDDYPRESS_CONTACT_ME_FILE ) . 'includes/admin/views/' . $view . '.php';
		$shell     = plugin_dir_path( BUDDYPRESS_CONTACT_ME_FILE ) . 'includes/admin/views/shell.php';

		include $shell;
	}
}
