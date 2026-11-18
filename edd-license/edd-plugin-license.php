<?php
/**
 * BuddyPress Contact Me - License handling.
 *
 * Licence screen (activate, deactivate, status). Every licence request goes
 * through buddypress_contact_me_licence_request() in the main file (bundled
 * EDD SL SDK: updates.wbcomdesigns.com with the store as fallback); updates
 * come from the SDK registration there. Every redirect lands on
 * `?page=buddypress-contact-me&tab=license`.
 *
 * Public surface (registered actions):
 *   admin_init    -> edd_BCM_register_option
 *   admin_init    -> edd_BCM_handle_activate
 *   admin_init    -> edd_BCM_handle_deactivate
 *   License tab   -> edd_BCM_render_admin_notices() (result notices)
 *
 * @package BuddyPress_Contact_Me
 */

defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------- *
 * Constants                                                        *
 * ---------------------------------------------------------------- */

if ( ! defined( 'EDD_BP_CONTACT_ME_ITEM_NAME' ) ) {
	define( 'EDD_BP_CONTACT_ME_ITEM_NAME', 'BuddyPress Contact Me' );
}

// Canonical license screen for this plugin.
if ( ! defined( 'EDD_BP_CONTACT_ME_PLUGIN_LICENSE_PAGE' ) ) {
	define( 'EDD_BP_CONTACT_ME_PLUGIN_LICENSE_PAGE', 'buddypress-contact-me' );
}
if ( ! defined( 'EDD_BP_CONTACT_ME_PLUGIN_LICENSE_TAB' ) ) {
	define( 'EDD_BP_CONTACT_ME_PLUGIN_LICENSE_TAB', 'license' );
}

const EDD_BCM_LICENSE_KEY_OPTION    = 'edd_wbcom_bp_contact_me_license_key';
const EDD_BCM_LICENSE_STATUS_OPTION = 'edd_wbcom_bp_contact_me_license_status';
const EDD_BCM_LICENSE_EXPIRES_OPTION = 'edd_wbcom_bp_contact_me_license_expires';
const EDD_BCM_LICENSE_CACHE_KEY     = 'edd_wbcom_bp_contact_me_license_key_data';
const EDD_BCM_LICENSE_CACHE_TTL     = 12 * HOUR_IN_SECONDS;
const EDD_BCM_LICENSE_NONCE_ACTION  = 'edd_wbcom_contact_me_nonce';
const EDD_BCM_LICENSE_NONCE_NAME    = 'edd_wbcom_contact_me_nonce';

/* ---------------------------------------------------------------- *
 * Settings registration                                            *
 * ---------------------------------------------------------------- */

function edd_BCM_register_option() {
	register_setting(
		'edd_wbcom_bp_contact_me_license',
		EDD_BCM_LICENSE_KEY_OPTION,
		array(
			'type'              => 'string',
			'sanitize_callback' => 'edd_BCM_sanitize_license',
		)
	);
}
add_action( 'admin_init', 'edd_BCM_register_option' );

function edd_BCM_sanitize_license( $new ) {
	$new = sanitize_text_field( (string) $new );
	$old = (string) get_option( EDD_BCM_LICENSE_KEY_OPTION );

	if ( '' !== $old && $old !== $new ) {
		delete_option( EDD_BCM_LICENSE_STATUS_OPTION );
		delete_option( EDD_BCM_LICENSE_EXPIRES_OPTION );
		delete_transient( EDD_BCM_LICENSE_CACHE_KEY );
	}

	return $new;
}

/* ---------------------------------------------------------------- *
 * EDD store API client                                             *
 * ---------------------------------------------------------------- */

/**
 * Single entry point for every license API round-trip. Returns
 * stdClass on a reply (a licence "no" is a reply) or WP_Error when
 * no licence host answered - callers branch on `is_wp_error()`.
 *
 * @param string $action  EDD action: activate_license, deactivate_license, check_license.
 * @param string $license Key to send.
 * @return stdClass|WP_Error
 */
function edd_BCM_call_api( $action, $license ) {
	$result = buddypress_contact_me_licence_request(
		array(
			'edd_action' => $action,
			'license'    => $license,
			'item_id'    => BUDDYPRESS_CONTACT_ME_ITEM_ID,
		)
	);

	if ( false === $result ) {
		return new WP_Error(
			'bcm_license_unreachable',
			__( 'Could not reach the license server. Please try again later.', 'buddypress-contact-me' )
		);
	}

	return $result;
}

/* ---------------------------------------------------------------- *
 * Activation handler                                               *
 * ---------------------------------------------------------------- */

function edd_BCM_handle_activate() {
	if ( ! isset( $_POST['edd_bp_contact_me_license_activate'] ) ) {
		return;
	}
	// License activation writes options + calls the EDD API on this site's behalf;
	// gate to admins. Nonce alone is CSRF protection, not authorization.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! check_admin_referer( EDD_BCM_LICENSE_NONCE_ACTION, EDD_BCM_LICENSE_NONCE_NAME ) ) {
		return;
	}

	$license = isset( $_POST[ EDD_BCM_LICENSE_KEY_OPTION ] )
		? sanitize_text_field( wp_unslash( $_POST[ EDD_BCM_LICENSE_KEY_OPTION ] ) )
		: '';

	update_option( EDD_BCM_LICENSE_KEY_OPTION, $license );

	if ( '' === $license ) {
		edd_BCM_redirect_with(
			array(
				'BCM_activation' => 'false',
				'message'        => __( 'Please enter a license key.', 'buddypress-contact-me' ),
			)
		);
	}

	$result = edd_BCM_call_api( 'activate_license', $license );

	if ( is_wp_error( $result ) ) {
		edd_BCM_redirect_with(
			array(
				'BCM_activation' => 'false',
				'message'        => $result->get_error_message(),
			)
		);
	}

	if ( empty( $result->success ) ) {
		$code    = isset( $result->error ) ? (string) $result->error : '';
		$message = edd_BCM_format_activation_error( $code, $result );
		edd_BCM_redirect_with(
			array(
				'BCM_activation' => 'false',
				'message'        => $message,
			)
		);
	}

	$state = isset( $result->license ) ? (string) $result->license : 'invalid';
	update_option( EDD_BCM_LICENSE_STATUS_OPTION, $state );
	if ( isset( $result->expires ) && '' !== $result->expires ) {
		update_option( EDD_BCM_LICENSE_EXPIRES_OPTION, (string) $result->expires );
	} else {
		delete_option( EDD_BCM_LICENSE_EXPIRES_OPTION );
	}
	set_transient( EDD_BCM_LICENSE_CACHE_KEY, $result, EDD_BCM_LICENSE_CACHE_TTL );

	edd_BCM_redirect_with( array( 'BCM_activation' => 'true' ) );
}
add_action( 'admin_init', 'edd_BCM_handle_activate' );

function edd_BCM_format_activation_error( $code, $result ) {
	switch ( $code ) {
		case 'expired':
			$expires_ts = isset( $result->expires ) ? strtotime( $result->expires ) : false;
			return $expires_ts
				? sprintf(
					/* translators: %s: expiration date */
					__( 'Your license key expired on %s.', 'buddypress-contact-me' ),
					bcm_date_label( $expires_ts, '', get_option( 'date_format' ) )
				)
				: __( 'Your license key has expired.', 'buddypress-contact-me' );

		case 'revoked':
			return __( 'Your license key has been disabled.', 'buddypress-contact-me' );

		case 'missing':
			return __( 'Invalid license.', 'buddypress-contact-me' );

		case 'invalid':
		case 'site_inactive':
			return __( 'Your license is not active for this URL.', 'buddypress-contact-me' );

		case 'item_name_mismatch':
			return sprintf(
				/* translators: %s: product name */
				__( 'This appears to be an invalid license key for %s.', 'buddypress-contact-me' ),
				EDD_BP_CONTACT_ME_ITEM_NAME
			);

		case 'no_activations_left':
			return __( 'Your license key has reached its activation limit.', 'buddypress-contact-me' );

		default:
			return __( 'An error occurred, please try again.', 'buddypress-contact-me' );
	}
}

/* ---------------------------------------------------------------- *
 * Deactivation handler                                             *
 * ---------------------------------------------------------------- */

function edd_BCM_handle_deactivate() {
	if ( ! isset( $_POST['edd_BCM_license_deactivate'] ) ) {
		return;
	}
	// License deactivation calls the remote EDD API and deletes license options;
	// gate to admins. Nonce alone is CSRF protection, not authorization.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	if ( ! check_admin_referer( EDD_BCM_LICENSE_NONCE_ACTION, EDD_BCM_LICENSE_NONCE_NAME ) ) {
		return;
	}

	$license = trim( (string) get_option( EDD_BCM_LICENSE_KEY_OPTION ) );
	$result  = edd_BCM_call_api( 'deactivate_license', $license );

	if ( is_wp_error( $result ) ) {
		edd_BCM_redirect_with(
			array(
				'BCM_deactivation' => 'false',
				'message'          => $result->get_error_message(),
			)
		);
	}

	delete_transient( EDD_BCM_LICENSE_CACHE_KEY );
	if ( isset( $result->license ) && in_array( (string) $result->license, array( 'deactivated', 'failed' ), true ) ) {
		delete_option( EDD_BCM_LICENSE_STATUS_OPTION );
		delete_option( EDD_BCM_LICENSE_EXPIRES_OPTION );
	}

	edd_BCM_redirect_with( array( 'BCM_deactivation' => 'true' ) );
}
add_action( 'admin_init', 'edd_BCM_handle_deactivate' );

/* ---------------------------------------------------------------- *
 * Redirect helper                                                  *
 * ---------------------------------------------------------------- */

function edd_BCM_redirect_with( array $args ) {
	$url = admin_url(
		'admin.php?page=' . rawurlencode( EDD_BP_CONTACT_ME_PLUGIN_LICENSE_PAGE )
		. '&tab=' . rawurlencode( EDD_BP_CONTACT_ME_PLUGIN_LICENSE_TAB )
	);

	$url = remove_query_arg(
		array( 'BCM_activation', 'BCM_deactivation', 'message', '_wpnonce', '_wp_http_referer' ),
		$url
	);

	wp_safe_redirect( add_query_arg( $args, $url ) );
	exit;
}

/* ---------------------------------------------------------------- *
 * Status snapshot used by the renderer                             *
 * ---------------------------------------------------------------- */

/**
 * Return the current license status snapshot. Reads through the
 * transient cache when present, only hits the remote when the
 * cache is empty AND a key is configured. Always returns a
 * fully-populated array.
 *
 * @return array { license: string, data: stdClass|null, message: string }
 */
function edd_BCM_get_license_status() {
	$snapshot = array(
		'license' => (string) get_option( EDD_BCM_LICENSE_STATUS_OPTION, '' ),
		'data'    => null,
		'message' => '',
	);

	$key = trim( (string) get_option( EDD_BCM_LICENSE_KEY_OPTION ) );
	if ( '' === $key ) {
		return $snapshot;
	}

	$cached = get_transient( EDD_BCM_LICENSE_CACHE_KEY );
	if ( is_object( $cached ) ) {
		$snapshot['data']    = $cached;
		$snapshot['license'] = isset( $cached->license ) ? (string) $cached->license : $snapshot['license'];
		return $snapshot;
	}

	$result = edd_BCM_call_api( 'check_license', $key );
	if ( is_wp_error( $result ) ) {
		$snapshot['message'] = $result->get_error_message();
		return $snapshot;
	}

	set_transient( EDD_BCM_LICENSE_CACHE_KEY, $result, EDD_BCM_LICENSE_CACHE_TTL );

	$snapshot['data']    = $result;
	$snapshot['license'] = isset( $result->license ) ? (string) $result->license : $snapshot['license'];

	return $snapshot;
}

/* ---------------------------------------------------------------- *
 * Result notices                                                   *
 * ---------------------------------------------------------------- */

/**
 * Print the activate / deactivate result on the License tab. Called by the
 * tab view: the plugin's screens drop every admin_notices callback.
 *
 * @param string $snapshot_message Status-check note from edd_BCM_get_license_status().
 */
function edd_BCM_render_admin_notices( $snapshot_message = '' ) {
	// phpcs:disable WordPress.Security.NonceVerification.Recommended
	$activation   = isset( $_GET['BCM_activation'] ) ? sanitize_text_field( wp_unslash( $_GET['BCM_activation'] ) ) : '';
	$deactivation = isset( $_GET['BCM_deactivation'] ) ? sanitize_text_field( wp_unslash( $_GET['BCM_deactivation'] ) ) : '';
	$message      = isset( $_GET['message'] ) ? rawurldecode( sanitize_text_field( wp_unslash( $_GET['message'] ) ) ) : '';
	// phpcs:enable

	$notice = static function ( $text, $type ) {
		printf( '<div class="notice notice-%1$s inline"><p>%2$s</p></div>', esc_attr( $type ), esc_html( $text ) );
	};

	if ( 'true' === $activation ) {
		$notice( __( 'License activated successfully.', 'buddypress-contact-me' ), 'success' );
	} elseif ( 'false' === $activation && '' !== $message ) {
		$notice( $message, 'error' );
	}

	if ( 'true' === $deactivation ) {
		$notice( __( 'License deactivated successfully.', 'buddypress-contact-me' ), 'success' );
	} elseif ( 'false' === $deactivation && '' !== $message ) {
		$notice( $message, 'error' );
	}

	// The status check got no reply: the last-known status stays on the card, with this note.
	if ( '' !== $snapshot_message && $snapshot_message !== $message ) {
		$notice( $snapshot_message, 'warning' );
	}
}
