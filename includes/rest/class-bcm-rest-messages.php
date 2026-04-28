<?php
/**
 * REST endpoints for the Contact form + inbox.
 *
 * Namespace: bcm/v1
 *   POST   /messages                       — submit a new contact message
 *   DELETE /messages/(?P<id>\d+)           — delete one of my own messages
 *   POST   /preferences/intro-dismiss      — dismiss the inbox intro panel
 *
 * Permission: delete requires the current user to be the recipient of
 * that message. Submit is open but gated by captcha and the role
 * allow-list.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * REST controller for contact-message CRUD and the inbox intro-dismiss flag.
 */
class BCM_Rest_Messages {

	const NAMESPACE_ = 'bcm/v1';

	/**
	 * Hook the REST route registrar.
	 */
	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register all routes exposed by this controller.
	 */
	public function register_routes() {
		register_rest_route(
			self::NAMESPACE_,
			'/messages',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'bp_contact_me_subject'    => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'bp_contact_me_msg'        => array(
						'required'          => true,
						'sanitize_callback' => 'sanitize_textarea_field',
					),
					'bp_contact_me_first_name' => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'bp_contact_me_email'      => array( 'sanitize_callback' => 'sanitize_email' ),
					'bcm_captcha_answer'       => array( 'sanitize_callback' => 'absint' ),
					'bcm_captcha_hash'         => array( 'sanitize_callback' => 'sanitize_text_field' ),
					'bcm_shortcode_user_id'    => array( 'sanitize_callback' => 'absint' ),
					'bcm_shortcode_username'   => array( 'sanitize_callback' => 'sanitize_text_field' ),
				),
			)
		);

		register_rest_route(
			self::NAMESPACE_,
			'/messages/(?P<id>\d+)',
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_one' ),
				'permission_callback' => array( $this, 'require_message_owner' ),
			)
		);

		register_rest_route(
			self::NAMESPACE_,
			'/preferences/intro-dismiss',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'dismiss_intro' ),
				'permission_callback' => array( $this, 'require_logged_in' ),
			)
		);
	}

	/**
	 * Persist the "I've seen the inbox intro" flag for the current user.
	 *
	 * @return WP_REST_Response
	 */
	public function dismiss_intro() {
		update_user_meta( get_current_user_id(), 'bcm_intro_dismissed', 1 );
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	/**
	 * Permission callback: require a logged-in user.
	 *
	 * @return bool
	 */
	public function require_logged_in() {
		return is_user_logged_in();
	}

	/**
	 * Permission callback: only the recipient of the message may act on it.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return bool|WP_Error
	 */
	public function require_message_owner( $request ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'bcm_auth_required', __( 'Authentication required.', 'buddypress-contact-me' ), array( 'status' => 401 ) );
		}
		$msg = BCM_Messages_Repo::find( (int) $request['id'] );
		if ( ! $msg ) {
			return new WP_Error( 'bcm_not_found', __( 'Message not found.', 'buddypress-contact-me' ), array( 'status' => 404 ) );
		}
		if ( get_current_user_id() !== (int) $msg->recipient ) {
			return new WP_Error( 'bcm_forbidden', __( 'You may only act on your own messages.', 'buddypress-contact-me' ), array( 'status' => 403 ) );
		}
		return true;
	}

	/**
	 * Handle a contact-form submission via REST.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function submit( WP_REST_Request $request ) {
		$params = $request->get_params();

		// REST nonce (X-WP-Nonce) is enforced by rest_cookie_check_errors;
		// we additionally require the form nonce for CSRF defence-in-depth.
		if ( empty( $params['bcm_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $params['bcm_nonce'] ) ), BCM_Frontend_Submit::NONCE_ACTION ) ) {
			return new WP_Error( 'bcm_bad_nonce', __( 'Session expired. Please refresh the page and try again.', 'buddypress-contact-me' ), array( 'status' => 403 ) );
		}

		$result = BCM_Frontend_Submit::process_submission( $params );

		if ( is_wp_error( $result ) ) {
			return new WP_REST_Response(
				array(
					'ok'     => false,
					'errors' => array_map(
						static function ( $code, $messages ) {
							return array(
								'field'   => $code,
								'message' => is_array( $messages ) ? implode( ' ', $messages ) : (string) $messages,
							);
						},
						array_keys( $result->errors ),
						array_values( $result->errors )
					),
				),
				422
			);
		}

		return new WP_REST_Response(
			array(
				'ok'      => true,
				'id'      => (int) $result['id'],
				'message' => __( 'Message sent.', 'buddypress-contact-me' ),
			),
			201
		);
	}

	/**
	 * Delete one message owned by the current user.
	 *
	 * @param WP_REST_Request $request Incoming REST request.
	 * @return WP_REST_Response
	 */
	public function delete_one( WP_REST_Request $request ) {
		$deleted = BCM_Messages_Repo::delete_for_recipient( (int) $request['id'], get_current_user_id() );
		return new WP_REST_Response(
			array(
				'ok'      => (bool) $deleted,
				'message' => $deleted
					? __( 'Message deleted.', 'buddypress-contact-me' )
					: __( 'Could not delete the message.', 'buddypress-contact-me' ),
			),
			$deleted ? 200 : 400
		);
	}
}
