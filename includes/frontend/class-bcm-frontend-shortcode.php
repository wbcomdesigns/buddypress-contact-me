<?php
/**
 * [buddypress-contact-me] shortcode — renders the Contact form targeting
 * a specific user (by id or login).
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders the [buddypress-contact-me] shortcode.
 */
class BCM_Frontend_Shortcode {

	/**
	 * Attach the shortcode handler.
	 */
	public function register() {
		add_shortcode( 'buddypress-contact-me', array( $this, 'render' ) );
	}

	/**
	 * Render the contact form for a shortcode-specified user.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function render( $atts ) {
		$atts = shortcode_atts(
			array(
				'id'   => 0,
				'user' => '',
			),
			$atts,
			'buddypress-contact-me'
		);

		$recipient_id = (int) $atts['id'];
		if ( ! $recipient_id && ! empty( $atts['user'] ) ) {
			$user         = get_user_by( 'login', sanitize_text_field( $atts['user'] ) );
			$recipient_id = $user ? (int) $user->ID : 0;
		}
		if ( ! $recipient_id && function_exists( 'bp_displayed_user_id' ) ) {
			$recipient_id = bp_displayed_user_id();
		}

		if ( ! $recipient_id || ! BCM_Frontend_Nav::user_accepts_contact( $recipient_id ) ) {
			return '';
		}

		ob_start();
		$shortcode_atts         = $atts;
		$shortcode_atts['id']   = $recipient_id;
		$shortcode_atts['user'] = $atts['user'];

		// The partial reads $shortcode_atts if set.
		$GLOBALS['bcm_shortcode_atts'] = $shortcode_atts;
		include BUDDYPRESS_CONTACT_ME_PLUGIN_PATH . 'public/partials/tab-form.php';
		unset( $GLOBALS['bcm_shortcode_atts'] );

		return ob_get_clean();
	}
}
