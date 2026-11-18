<?php
/**
 * Single "Contact" tab on every member profile + its sub-tabs.
 *
 * Parent slug: `contact` (same URL for everyone).
 * Sub-tabs:
 *   - Own profile:      Inbox (default) | Preferences
 *   - Others' profile:  Send message    (the only sub-tab, auto-active)
 *
 * The legacy `contact-me` slug redirects to `contact` so old bookmarks,
 * email links, and notification URLs continue to work.
 *
 * Default behaviour: every role-allowed member has contact enabled by
 * default. A member opts out from Contact > Preferences
 * (contact_me_button === 'off'), the one opt-out switch.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress profile nav + sub-nav for the Contact tab.
 */
class BCM_Frontend_Nav {

	const SLUG            = 'contact';
	const LEGACY_SLUG     = 'contact-me';
	const SUB_INBOX       = 'inbox';
	const SUB_PREFERENCES = 'preferences';
	const SUB_SEND        = 'send';

	/**
	 * Attach all BP nav hooks.
	 */
	public function register() {
		add_action( 'bp_setup_nav', array( $this, 'setup_nav' ), 20 );
		add_action( 'template_redirect', array( $this, 'redirect_legacy_slug' ), 1 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'bp_setup_admin_bar', array( $this, 'admin_bar' ), 90 );
		add_filter( 'reign_bp_nav_icons', array( $this, 'reign_nav_icons' ) );
	}

	/**
	 * Icons for the Contact tab and its sub-tabs in Reign's icon navigation
	 * (instead of Reign's generic info glyph). Keys are nav item CSS ids,
	 * values are Reign icon names. Sub-tab slugs are generic, so they are
	 * only mapped on Contact screens; icons another plugin already set win.
	 *
	 * @param array $icons Nav item id => Reign icon name.
	 * @return array
	 */
	public function reign_nav_icons( $icons ) {
		$icons = (array) $icons + array(
			'bp_contact_count' => 'contact',
			self::SLUG         => 'contact',
		);
		if ( function_exists( 'bp_is_current_component' ) && bp_is_current_component( self::SLUG ) ) {
			$icons += array(
				self::SUB_INBOX       => 'inbox',
				self::SUB_SEND        => 'send',
				self::SUB_PREFERENCES => 'sliders-horizontal',
			);
		}
		return $icons;
	}

	/**
	 * Register the Contact tab + its sub-tabs: always on the member's own
	 * profile, on other profiles only when they accept contact.
	 */
	public function setup_nav() {
		if ( ! function_exists( 'bp_displayed_user_id' ) ) {
			return;
		}

		$displayed_user_id = bp_displayed_user_id();
		if ( ! $displayed_user_id ) {
			return;
		}

		$is_own = ( bp_loggedin_user_id() === $displayed_user_id );

		// Members always keep their own Inbox + Preferences (even after opting
		// out, or with the profile tab off) so old messages and notification
		// links stay reachable. Only the visitor-facing Send tab is gated.
		if ( ! $is_own && ( ! self::contact_tab_enabled() || ! self::user_accepts_contact( $displayed_user_id ) ) ) {
			return;
		}

		$user_domain = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $displayed_user_id )
			: bp_core_get_user_domain( $displayed_user_id );
		$parent_url  = trailingslashit( $user_domain ) . self::SLUG;

		$parent_unread = $is_own ? BCM_Messages_Repo::count_unread_for_recipient( $displayed_user_id ) : 0;
		$parent_label  = __( 'Contact', 'buddypress-contact-me' );
		if ( $parent_unread > 0 ) {
			$parent_label .= ' <span class="count">' . (int) $parent_unread . '</span>';
		}

		$default_sub = $is_own ? self::SUB_INBOX : self::SUB_SEND;

		bp_core_new_nav_item(
			array(
				'name'                    => $parent_label,
				'slug'                    => self::SLUG,
				'position'                => 80,
				'default_subnav_slug'     => $default_sub,
				'show_for_displayed_user' => true,
				'item_css_id'             => 'bp_contact_count',
				'screen_function'         => array( $this, $is_own ? 'screen_inbox' : 'screen_send' ),
			)
		);

		if ( $is_own ) {
			bp_core_new_subnav_item(
				array(
					'name'            => __( 'Inbox', 'buddypress-contact-me' ),
					'slug'            => self::SUB_INBOX,
					'parent_slug'     => self::SLUG,
					'parent_url'      => trailingslashit( $parent_url ),
					'position'        => 10,
					'screen_function' => array( $this, 'screen_inbox' ),
					'user_has_access' => true,
				)
			);
			bp_core_new_subnav_item(
				array(
					'name'            => __( 'Preferences', 'buddypress-contact-me' ),
					'slug'            => self::SUB_PREFERENCES,
					'parent_slug'     => self::SLUG,
					'parent_url'      => trailingslashit( $parent_url ),
					'position'        => 20,
					'screen_function' => array( $this, 'screen_preferences' ),
					'user_has_access' => true,
				)
			);
		} else {
			bp_core_new_subnav_item(
				array(
					'name'            => __( 'Send message', 'buddypress-contact-me' ),
					'slug'            => self::SUB_SEND,
					'parent_slug'     => self::SLUG,
					'parent_url'      => trailingslashit( $parent_url ),
					'position'        => 10,
					'screen_function' => array( $this, 'screen_send' ),
					'user_has_access' => true,
				)
			);
		}
	}

	/**
	 * BP screen callback: Inbox sub-tab.
	 */
	public function screen_inbox() {
		add_action( 'bp_template_content', array( $this, 'render_inbox' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * BP screen callback: Preferences sub-tab.
	 */
	public function screen_preferences() {
		$this->save_preferences();
		add_action( 'bp_template_content', array( $this, 'render_preferences' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Save the Preferences form. Runs in the screen callback, before any
	 * output, so the redirect and the BuddyPress notice always work.
	 */
	private function save_preferences() {
		if ( ! bp_is_post_request() || ! isset( $_POST['bcm_preferences_nonce'] ) || ! bp_is_my_profile() ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcm_preferences_nonce'] ) ), 'bcm_preferences_save' ) ) {
			bp_core_add_message( __( 'Session expired. Please refresh the page and try again.', 'buddypress-contact-me' ), 'error' );
			return;
		}
		// Own profile only (bp_is_my_profile above); the target is always the logged-in member.
		update_user_meta( bp_loggedin_user_id(), 'contact_me_button', empty( $_POST['bcm_accept_contact'] ) ? 'off' : 'on' );
		bp_core_add_message( __( 'Preferences saved.', 'buddypress-contact-me' ) );
		bp_core_redirect( bp_get_requested_url() );
	}

	/**
	 * BP screen callback: Send-message sub-tab (viewing another member).
	 */
	public function screen_send() {
		add_action( 'bp_template_content', array( $this, 'render_send' ) );
		bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
	}

	/**
	 * Render the Inbox listing or a single-message deep-link view.
	 */
	public function render_inbox() {
		$action_vars = function_exists( 'bp_action_variables' ) ? (array) bp_action_variables() : array();
		$maybe_id    = isset( $action_vars[0] ) ? (int) $action_vars[0] : 0;

		// Deep link to a single message — /contact/inbox/{id}/.
		if ( $maybe_id > 0 ) {
			$GLOBALS['bcm_view_message_id'] = $maybe_id;
			include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/tab-message.php';
			unset( $GLOBALS['bcm_view_message_id'] );

			// Mark just this message's notification as read.
			self::mark_message_read( $maybe_id );
			return;
		}

		// Render the inbox listing — messages stay "unread" until the user
		// opens them in the single-message view. The Inbox/Unread filter
		// and the sub-nav count depend on this.
		include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/tab-inbox.php';
	}

	/**
	 * Mark the BP notification tied to a single contact message as read.
	 *
	 * @param int $message_id Contact-message ID.
	 */
	public static function mark_message_read( $message_id ) {
		if ( ! function_exists( 'bp_notifications_mark_notifications_by_item_id' ) || ! bp_is_active( 'notifications' ) ) {
			return;
		}
		bp_notifications_mark_notifications_by_item_id( get_current_user_id(), (int) $message_id, BCM_Frontend_Notifications::COMPONENT, BCM_Frontend_Notifications::ACTION, false, 0 );
	}

	/**
	 * Render the Preferences sub-tab partial.
	 */
	public function render_preferences() {
		include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/tab-preferences.php';
	}

	/**
	 * Render the Send-message form partial.
	 */
	public function render_send() {
		include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/tab-form.php';
	}

	/**
	 * Whether the given user accepts contact messages right now.
	 *
	 * @param int $user_id User ID to test.
	 * @return bool
	 */
	public static function user_accepts_contact( $user_id ) {
		$user = $user_id > 0 ? get_userdata( (int) $user_id ) : false;
		if ( ! $user ) {
			return false;
		}

		$meta = get_user_meta( $user->ID, 'contact_me_button', true );
		if ( 'off' === $meta ) {
			return false;
		}

		$settings = get_option( 'bcm_admin_general_setting', array() );
		$allowed  = isset( $settings['bcm_who_contacted'] ) && is_array( $settings['bcm_who_contacted'] )
			? $settings['bcm_who_contacted']
			: array();
		if ( empty( $allowed ) ) {
			return true;
		}

		$roles = (array) $user->roles;
		if ( in_array( 'administrator', $roles, true ) ) {
			return true;
		}

		return (bool) array_intersect( $roles, $allowed );
	}

	/**
	 * Whether the site shows the Contact tab on member profiles.
	 *
	 * Only controls the profile tab. The shortcode and REST sending work
	 * either way, so the tab can be turned off for shortcode-only sites.
	 *
	 * @return bool
	 */
	public static function contact_tab_enabled() {
		$settings = get_option( 'bcm_admin_general_setting', array() );
		return ! empty( $settings['bcm_allow_contact_tab'] ) && 'yes' === $settings['bcm_allow_contact_tab'];
	}

	/**
	 * Whether the current viewer is allowed to send messages.
	 */
	public static function viewer_can_send() {
		$settings = get_option( 'bcm_admin_general_setting', array() );
		$allowed  = isset( $settings['bcm_who_contact'] ) && is_array( $settings['bcm_who_contact'] )
			? $settings['bcm_who_contact']
			: array();

		// Visitors must be ticked explicitly: an empty list means every logged-in member, never guests.
		if ( ! is_user_logged_in() ) {
			return in_array( 'visitors', $allowed, true );
		}

		if ( empty( $allowed ) ) {
			return true;
		}

		$user  = wp_get_current_user();
		$roles = (array) $user->roles;
		if ( in_array( 'administrator', $roles, true ) ) {
			return true;
		}

		return (bool) array_intersect( $roles, $allowed );
	}

	/**
	 * Redirect legacy /contact-me/ URLs to /contact/.
	 */
	public function redirect_legacy_slug() {
		if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
			return;
		}
		if ( self::LEGACY_SLUG !== bp_current_component() ) {
			return;
		}

		$user_id = bp_displayed_user_id();
		if ( ! $user_id ) {
			return;
		}

		$user_domain = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $user_id )
			: bp_core_get_user_domain( $user_id );

		wp_safe_redirect( trailingslashit( $user_domain ) . self::SLUG . '/', 301 );
		exit;
	}

	/**
	 * Add a body class when the Contact tab is active.
	 *
	 * @param string[] $classes Current body classes.
	 * @return string[]
	 */
	public function body_class( $classes ) {
		if ( function_exists( 'bp_is_user' ) && bp_is_user() && bp_is_current_component( self::SLUG ) ) {
			$classes[] = 'wbcom-bp-contact';
		}
		return $classes;
	}

	/**
	 * Add a "Contact" shortcut (the member's own inbox) under the BP admin bar entry.
	 */
	public function admin_bar() {
		if ( ! is_user_logged_in() ) {
			return;
		}
		global $wp_admin_bar;
		if ( ! isset( $wp_admin_bar ) ) {
			return;
		}

		$user_id = get_current_user_id();

		$user_domain = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $user_id )
			: bp_core_get_user_domain( $user_id );

		$wp_admin_bar->add_menu(
			array(
				'parent' => 'my-account-buddypress',
				'id'     => 'my-account-contact',
				'title'  => esc_html__( 'Contact', 'buddypress-contact-me' ),
				'href'   => trailingslashit( $user_domain ) . self::SLUG . '/',
			)
		);
	}
}
