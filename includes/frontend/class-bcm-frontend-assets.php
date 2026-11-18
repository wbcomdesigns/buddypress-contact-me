<?php
/**
 * Enqueue public-facing assets for the Contact tab.
 *
 * Loads on the Contact tab and any page that shows the
 * [buddypress-contact-me] shortcode - zero footprint elsewhere.
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
	}

	/**
	 * Enqueue styles + scripts when the current page needs them.
	 */
	public function enqueue() {
		if ( $this->should_load() ) {
			$this->enqueue_assets();
		}
	}

	/**
	 * Enqueue the form/inbox assets. Safe to call more than once; the
	 * shortcode calls it at render time for widgets and templates that
	 * should_load() cannot see in advance (the stylesheet then prints in
	 * the footer).
	 *
	 * @return void
	 */
	public function enqueue_assets() {
		if ( wp_script_is( $this->plugin_name, 'enqueued' ) ) {
			return;
		}

		// One readable stylesheet: logical properties cover right-to-left sites.
		wp_enqueue_style(
			$this->plugin_name,
			BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/css/buddypress-contact-me-public.css',
			array( 'dashicons' ),
			$this->version,
			'all'
		);

		wp_enqueue_script(
			'bcm-confirm',
			BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/confirm.js',
			array(),
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
			BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/js/buddypress-contact-me-public.js',
			array( 'wp-api-fetch', 'bcm-confirm' ),
			$this->version,
			true
		);

		$current_user_id = get_current_user_id();
		$unread_count    = $current_user_id ? BCM_Messages_Repo::count_unread_for_recipient( $current_user_id ) : 0;

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
					'dismiss'       => __( 'Dismiss', 'buddypress-contact-me' ),
					'cancel'        => __( 'Cancel', 'buddypress-contact-me' ),
					'deleteLabel'   => __( 'Delete', 'buddypress-contact-me' ),
					'fieldRequired' => __( 'This field is required.', 'buddypress-contact-me' ),
					'emailInvalid'  => __( 'Enter a valid email address.', 'buddypress-contact-me' ),
					/* translators: %d: minimum character count. */
					'tooShort'      => __( 'At least %d characters.', 'buddypress-contact-me' ),
					/* translators: %d: maximum character count. */
					'tooLong'       => __( 'No more than %d characters.', 'buddypress-contact-me' ),
					'linkCopied'    => __( 'Link copied.', 'buddypress-contact-me' ),
					'copyFailed'    => __( 'Copy not supported in this browser.', 'buddypress-contact-me' ),
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
		if ( is_singular() && has_shortcode( (string) get_post_field( 'post_content', get_queried_object_id() ), 'buddypress-contact-me' ) ) {
			return true;
		}
		if ( ! function_exists( 'bp_is_user' ) || ! bp_is_user() ) {
			return false;
		}
		return bp_is_current_component( BCM_Frontend_Nav::SLUG )
			|| bp_is_current_component( BCM_Frontend_Nav::LEGACY_SLUG );
	}
}
