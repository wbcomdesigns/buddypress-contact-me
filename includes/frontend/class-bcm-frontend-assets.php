<?php
/**
 * Enqueue public-facing assets for the Contact tab.
 *
 * Loads only on the Contact tab and BP Settings screen — zero footprint
 * elsewhere on the site.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueues the public CSS + JS for the Contact tab.
 */
class BCM_Frontend_Assets {

	/**
	 * Plugin slug (used as a handle).
	 *
	 * @var string
	 */
	private $plugin_name;

	/**
	 * Plugin version (cache-bust).
	 *
	 * @var string
	 */
	private $version;

	/**
	 * Store the slug + version for later enqueue calls.
	 *
	 * @param string $plugin_name Plugin slug.
	 * @param string $version     Plugin version.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Attach the enqueue hook.
	 */
	public function register() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'wp_footer', array( $this, 'output_dark_mode_mirror' ), 5 );
	}

	/**
	 * Mirror the active theme's runtime dark-mode class onto `<body>` as
	 * `bcm-dark` so our CSS only needs one selector to react to dark mode.
	 *
	 * Themes ship inconsistent dark-mode triggers — Reign sets
	 * `html.dark-mode`, BuddyX sets `body.buddyx-dark-theme`, BuddyBoss
	 * sets `body.bb-dark-mode`, and the wp-dark-mode plugin sets
	 * `html.wp-dark-mode-active`. A static class list in CSS misses any
	 * theme it wasn't built for; this observer-based bridge mirrors all
	 * known triggers (and stays in sync with runtime toggles, which the
	 * Reign and BuddyX pickers do via JS+cookie) onto a single sentinel
	 * class our stylesheet can rely on.
	 *
	 * Pattern lifted from Jetonomy's class-theme-integration.php.
	 *
	 * Reference: Basecamp card 9823548013.
	 *
	 * @return void
	 */
	public function output_dark_mode_mirror() {
		if ( ! $this->should_load() ) {
			return;
		}

		$script = <<<'JS'
(function () {
	var html = document.documentElement;
	var body = document.body;
	if ( ! body ) { return; }
	var darkClasses = [
		'dark-mode',
		'theme-dark',
		'wp-dark-mode-active',
		'bb-dark-mode',
		'bb-dark-mode-on',
		'bx-dark-theme',
		'buddyx-dark-theme',
		'buddyx-dark-mode'
	];
	function sync() {
		var isDark = false;
		for ( var i = 0; i < darkClasses.length; i++ ) {
			if ( html.classList.contains( darkClasses[ i ] ) || body.classList.contains( darkClasses[ i ] ) ) {
				isDark = true;
				break;
			}
		}
		body.classList.toggle( 'bcm-dark', isDark );
	}
	sync();
	if ( typeof MutationObserver === 'function' ) {
		var opts = { attributes: true, attributeFilter: [ 'class' ] };
		new MutationObserver( sync ).observe( html, opts );
		new MutationObserver( sync ).observe( body, opts );
	}
})();
JS;

		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $script, array( 'id' => 'bcm-dark-mode-mirror' ) );
		} else {
			echo '<script id="bcm-dark-mode-mirror">' . $script . '</script>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Enqueue styles + scripts when the Contact tab (or BP Settings) is loaded.
	 */
	public function enqueue() {
		if ( ! $this->should_load() ) {
			return;
		}

		// Prefer the minified build when the grunt output exists, otherwise
		// fall back to the source file. This keeps local dev working
		// without a grunt watch, and production fast after release build.
		$plugin_dir = untrailingslashit( BUDDYPRESS_CONTACT_ME_PLUGIN_PATH );
		$css_min_fs = $plugin_dir . '/public/css/min/buddypress-contact-me-public.min.css';
		$js_min_fs  = $plugin_dir . '/public/js/min/buddypress-contact-me-public.min.js';
		$css_url    = file_exists( $css_min_fs )
			? BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/css/min/buddypress-contact-me-public.min.css'
			: BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/css/buddypress-contact-me-public.css';
		$js_url     = file_exists( $js_min_fs )
			? BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/min/buddypress-contact-me-public.min.js'
			: BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/buddypress-contact-me-public.js';

		wp_enqueue_style(
			$this->plugin_name,
			$css_url,
			array( 'dashicons' ),
			$this->version,
			'all'
		);

		wp_enqueue_script(
			'bcm-toast',
			BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/toast.js',
			array(),
			$this->version,
			true
		);

		wp_enqueue_script(
			'bcm-confirm',
			BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/confirm.js',
			array( 'bcm-toast' ),
			$this->version,
			true
		);
		wp_localize_script(
			'bcm-confirm',
			'bcmConfirmI18n',
			array(
				'confirm' => __( 'Confirm', 'buddypress-contact-me' ),
				'cancel'  => __( 'Cancel', 'buddypress-contact-me' ),
			)
		);

		wp_enqueue_script(
			$this->plugin_name,
			$js_url,
			array( 'wp-api-fetch', 'bcm-toast', 'bcm-confirm' ),
			$this->version,
			true
		);

		$current_user_id = get_current_user_id();
		$unread_count    = $current_user_id
			? count( BCM_Messages_Repo::unread_message_ids( $current_user_id ) )
			: 0;

		wp_localize_script(
			$this->plugin_name,
			'bcmContactMe',
			array(
				'restUrl'     => esc_url_raw( rest_url( 'bcm/v1/messages' ) ),
				'restNonce'   => wp_create_nonce( 'wp_rest' ),
				'isLoggedIn'  => is_user_logged_in(),
				'unreadCount' => $unread_count,
				'inboxSlug'   => BCM_Frontend_Nav::SUB_INBOX,
				'i18n'        => array(
					'confirmDelete' => __( 'Delete this message? This cannot be undone.', 'buddypress-contact-me' ),
					'deleteError'   => __( 'Could not delete the message. Please try again.', 'buddypress-contact-me' ),
					'sending'       => __( 'Sending…', 'buddypress-contact-me' ),
					'sent'          => __( 'Message sent.', 'buddypress-contact-me' ),
					'cancel'        => __( 'Cancel', 'buddypress-contact-me' ),
					'deleteLabel'   => __( 'Delete', 'buddypress-contact-me' ),
					'fieldRequired' => __( 'This field is required.', 'buddypress-contact-me' ),
					'emailInvalid'  => __( 'Enter a valid email address.', 'buddypress-contact-me' ),
					/* translators: %d: minimum character count. */
					'tooShort'      => __( 'At least %d characters.', 'buddypress-contact-me' ),
					/* translators: %d: maximum character count. */
					'tooLong'       => __( 'No more than %d characters.', 'buddypress-contact-me' ),
				),
			)
		);
	}

	/**
	 * Whether the current page is one that needs our assets.
	 *
	 * @return bool
	 */
	private function should_load() {
		if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
			return false;
		}
		return bp_is_current_component( BCM_Frontend_Nav::SLUG )
			|| bp_is_current_component( BCM_Frontend_Nav::LEGACY_SLUG )
			|| bp_is_current_component( 'settings' );
	}
}
