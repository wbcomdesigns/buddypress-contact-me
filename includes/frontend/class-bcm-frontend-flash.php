<?php
/**
 * Cross-request flash notices for the contact form.
 *
 * For logged-in members we use bp_core_add_message (which already survives
 * a redirect via BP's cookie). For logged-out visitors we store the notice
 * in a short-lived transient keyed by a cookie — never read $_COOKIE at
 * render time outside this class.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Session-less flash-notice helper for both logged-in and logged-out users.
 */
class BCM_Frontend_Flash {

	const COOKIE = 'bcm_flash';
	const TTL    = 60;
	const PREFIX = 'bcm_flash_';

	/**
	 * Queue a notice to show on the next request.
	 *
	 * @param string $message Plain text notice.
	 * @param string $type    'success' or 'error'.
	 */
	public static function add( $message, $type = 'success' ) {
		$message = trim( (string) $message );
		if ( '' === $message ) {
			return;
		}
		$type = in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ? $type : 'success';

		if ( is_user_logged_in() && function_exists( 'bp_core_add_message' ) ) {
			bp_core_add_message( $message, $type );
			return;
		}

		$token = wp_generate_password( 20, false, false );
		set_transient(
			self::PREFIX . $token,
			array(
				'message' => $message,
				'type'    => $type,
			),
			self::TTL
		);

		// Not HttpOnly — the cookie itself is just a pointer; data lives in the transient.
		setcookie(
			self::COOKIE,
			$token,
			time() + self::TTL,
			COOKIEPATH ? COOKIEPATH : '/',
			COOKIE_DOMAIN,
			is_ssl(),
			false
		);
		$_COOKIE[ self::COOKIE ] = $token;
	}

	/**
	 * Consume the currently-queued anonymous notice, if any.
	 *
	 * @return array{message:string,type:string}|null
	 */
	public static function consume() {
		if ( empty( $_COOKIE[ self::COOKIE ] ) ) {
			return null;
		}
		$token = preg_replace( '/[^A-Za-z0-9]/', '', sanitize_text_field( wp_unslash( $_COOKIE[ self::COOKIE ] ) ) );
		if ( '' === $token ) {
			return null;
		}
		$key  = self::PREFIX . $token;
		$data = get_transient( $key );

		delete_transient( $key );
		unset( $_COOKIE[ self::COOKIE ] );
		if ( ! headers_sent() ) {
			setcookie(
				self::COOKIE,
				'',
				time() - 3600,
				COOKIEPATH ? COOKIEPATH : '/',
				COOKIE_DOMAIN,
				is_ssl(),
				false
			);
		}

		if ( ! is_array( $data ) || empty( $data['message'] ) ) {
			return null;
		}

		return array(
			'message' => (string) $data['message'],
			'type'    => in_array( $data['type'] ?? 'success', array( 'success', 'error', 'warning', 'info' ), true ) ? $data['type'] : 'success',
		);
	}
}
