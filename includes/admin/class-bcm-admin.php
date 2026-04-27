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
			$input = array();
		}

		// Start from the CURRENTLY saved option, not an empty array. Each
		// admin tab submits only its own fields — when the Access tab is
		// saved it does not include the Notifications-tab boolean keys,
		// so rebuilding from an empty $output wipes them. Merging on top
		// of the stored value keeps keys owned by OTHER tabs intact.
		//
		// Reference: Basecamp card 9823496113 ("Settings Overwrite —
		// Access Tab Resets Notifications"). Before this change, saving
		// the Access tab silently reset every notification preference.
		$output = get_option( self::OPTION_NAME, array() );
		if ( ! is_array( $output ) ) {
			$output = array();
		}

		$bool_keys = array(
			'bcm_allow_notification',
			'bcm_allow_email',
			'bcm_allow_admin_copy_email',
			'bcm_allow_sender_copy_email',
			'bcm_allow_contact_tab',
		);

		// Which tab is currently posting? Every admin tab submits its own
		// subset of fields — we only want to touch the subset that was
		// actually rendered, so an "Access"-tab save does NOT decide the
		// value of "bcm_allow_email" just because the key happens to be
		// absent from the POST body. For each tab-owned field we only
		// overwrite the stored value when the field's form group was
		// present in the submission.
		$posted_bool_keys = array();
		foreach ( $bool_keys as $key ) {
			if ( array_key_exists( $key, $input ) ) {
				$posted_bool_keys[] = $key;
				$output[ $key ]     = ( 'yes' === $input[ $key ] ) ? 'yes' : '';
			}
		}

		// A submitted form that RENDERED a checkbox but left it unchecked
		// won't include the field in $_POST at all. To distinguish "the
		// Access tab didn't render this field" from "the Notifications
		// tab rendered it but the user unchecked it", views should post
		// a hidden sentinel input `bcm_tab_rendered_keys[]` listing every
		// bool_key their form contains. When we see the sentinel, any
		// bool_key listed there but missing from $input is an explicit
		// "off".
		if ( isset( $input['bcm_tab_rendered_keys'] ) && is_array( $input['bcm_tab_rendered_keys'] ) ) {
			foreach ( $input['bcm_tab_rendered_keys'] as $rendered ) {
				$rendered = sanitize_key( $rendered );
				if ( in_array( $rendered, $bool_keys, true ) && ! in_array( $rendered, $posted_bool_keys, true ) ) {
					$output[ $rendered ] = '';
				}
			}
			unset( $output['bcm_tab_rendered_keys'] );
		}

		// Multi-checkbox arrays (role grids on the Access tab) need the
		// same "rendered-but-cleared" treatment as the bool keys above.
		// When the user clicks "Clear all" every checkbox unchecks, so
		// the array key drops out of $_POST entirely. Without a sentinel
		// we'd merge over the stored option and the cleared selection
		// would silently revert to the previous value on the very next
		// page load. Views that render a role grid emit
		// `bcm_array_rendered_keys[]` listing the array keys they own;
		// a key listed there but missing from $input becomes an empty
		// array, which is what "no roles selected" actually means.
		//
		// Reference: Basecamp card 9823496113 follow-up — "Clear all"
		// in the Access tab role grids did not persist after save.
		$array_keys        = array( 'bcm_who_contact', 'bcm_who_contacted' );
		$posted_array_keys = array();
		foreach ( $array_keys as $key ) {
			if ( isset( $input[ $key ] ) && is_array( $input[ $key ] ) ) {
				$posted_array_keys[] = $key;
				$output[ $key ]      = array_values(
					array_filter(
						array_map( 'sanitize_text_field', wp_unslash( $input[ $key ] ) ),
						static function ( $v ) {
							return '' !== $v;
						}
					)
				);
			}
		}
		if ( isset( $input['bcm_array_rendered_keys'] ) && is_array( $input['bcm_array_rendered_keys'] ) ) {
			foreach ( $input['bcm_array_rendered_keys'] as $rendered ) {
				$rendered = sanitize_key( $rendered );
				if ( in_array( $rendered, $array_keys, true ) && ! in_array( $rendered, $posted_array_keys, true ) ) {
					$output[ $rendered ] = array();
				}
			}
			unset( $output['bcm_array_rendered_keys'] );
		}

		return $output;
	}

	/**
	 * Enqueue admin assets only on our screen + the shared hub landing.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_assets( string $hook_suffix ): void {
		unset( $hook_suffix ); // We inspect screen object instead of the hook suffix.
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
		$bcm_tabs  = self::get_tabs();
		$tab_slugs = array_keys( $bcm_tabs );
		$active    = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : $tab_slugs[0]; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $bcm_tabs[ $active ] ) ) {
			$active = $tab_slugs[0];
		}

		$page_url = admin_url( 'admin.php?page=' . self::MENU_SLUG );
		$settings = get_option( self::OPTION_NAME, array() );

		$view_map            = array(
			'overview'      => 'overview',
			'notifications' => 'settings-notifications',
			'access'        => 'settings-access',
			'license'       => 'license',
		);
		$view                = isset( $view_map[ $active ] ) ? $view_map[ $active ] : 'overview';
		$in_settings_group   = isset( $bcm_tabs[ $active ]['group'] ) && 'settings' === $bcm_tabs[ $active ]['group'];
		$settings_form_group = self::OPTION_GROUP;

		$view_path = plugin_dir_path( BUDDYPRESS_CONTACT_ME_FILE ) . 'includes/admin/views/' . $view . '.php';
		$shell     = plugin_dir_path( BUDDYPRESS_CONTACT_ME_FILE ) . 'includes/admin/views/shell.php';

		include $shell;
	}
}
