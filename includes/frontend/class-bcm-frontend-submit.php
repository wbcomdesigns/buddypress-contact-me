<?php
/**
 * Classic-POST handler for the Contact form.
 *
 * The form progressively enhances to JS fetch when available (see
 * buddypress-contact-me-public.js), but this handler is the non-JS
 * fallback and the single source of truth for validation.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Classic-POST handler for the contact form + BP Settings toggle.
 */
class BCM_Frontend_Submit {

	const NONCE_ACTION = 'bcm_form_nonce';

	/**
	 * Hook the submit handlers into BP's action lifecycle.
	 */
	public function register() {
		add_action( 'bp_actions', array( $this, 'maybe_handle' ), 5 );
		add_action( 'bp_core_general_settings_before_submit', array( $this, 'render_settings_toggle' ) );
		add_action( 'bp_actions', array( $this, 'save_settings_toggle' ) );
	}

	/**
	 * Handle a classic (non-REST) POST of the contact form.
	 */
	public function maybe_handle() {
		if ( ! isset( $_POST['bcm_nonce'], $_POST['bp_contact_me_form_save'] ) ) {
			return;
		}
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcm_nonce'] ) ), self::NONCE_ACTION ) ) {
			return;
		}

		$result = self::process_submission( $_POST );

		if ( is_wp_error( $result ) ) {
			foreach ( $result->get_error_messages() as $msg ) {
				BCM_Frontend_Flash::add( $msg, 'error' );
			}
			$redirect = wp_get_referer() ? wp_get_referer() : home_url( '/' );
			wp_safe_redirect( $redirect );
			exit;
		}

		BCM_Frontend_Flash::add( __( 'Message sent.', 'buddypress-contact-me' ), 'success' );
		$recipient_id = (int) $result['recipient'];
		$redirect     = $this->recipient_contact_url( $recipient_id );
		wp_safe_redirect( add_query_arg( 'bcm', 'sent', $redirect ) );
		exit;
	}

	/**
	 * Validate + insert a contact submission.
	 *
	 * Shared by the classic POST handler and the REST endpoint so both
	 * paths enforce identical rules.
	 *
	 * @param array $input Raw input (sanitized inside).
	 * @return array{id:int,recipient:int}|WP_Error
	 */
	public static function process_submission( array $input ) {
		if ( ! BCM_Frontend_Nav::viewer_can_send() ) {
			return new WP_Error( 'bcm_not_allowed', __( 'You are not allowed to send messages.', 'buddypress-contact-me' ) );
		}

		// Resolve recipient.
		$recipient_id = self::resolve_recipient( $input );
		if ( ! $recipient_id ) {
			return new WP_Error( 'bcm_recipient_missing', __( 'Recipient not found.', 'buddypress-contact-me' ) );
		}
		if ( ! BCM_Frontend_Nav::user_accepts_contact( $recipient_id ) ) {
			return new WP_Error( 'bcm_recipient_closed', __( 'This member is not accepting contact messages right now.', 'buddypress-contact-me' ) );
		}

		// Captcha — logged-out only, and always enforced when present.
		$captcha_check = self::validate_captcha( $input );
		if ( is_wp_error( $captcha_check ) ) {
			return $captcha_check;
		}

		// Fields.
		$subject = isset( $input['bp_contact_me_subject'] ) ? sanitize_text_field( wp_unslash( $input['bp_contact_me_subject'] ) ) : '';
		$message = isset( $input['bp_contact_me_msg'] ) ? sanitize_textarea_field( wp_unslash( $input['bp_contact_me_msg'] ) ) : '';

		$sender_id = get_current_user_id();

		if ( $sender_id ) {
			$name  = bp_core_get_user_displayname( $sender_id );
			$email = get_the_author_meta( 'user_email', $sender_id );
		} else {
			$name  = isset( $input['bp_contact_me_first_name'] ) ? sanitize_text_field( wp_unslash( $input['bp_contact_me_first_name'] ) ) : '';
			$email = isset( $input['bp_contact_me_email'] ) ? sanitize_email( wp_unslash( $input['bp_contact_me_email'] ) ) : '';
		}

		$errors = new WP_Error();

		if ( mb_strlen( $name ) < 2 || mb_strlen( $name ) > 100 ) {
			$errors->add( 'bcm_name', __( 'Name must be 2 to 100 characters.', 'buddypress-contact-me' ) );
		}
		if ( ! $sender_id ) {
			if ( '' === $email || ! is_email( $email ) ) {
				$errors->add( 'bcm_email', __( 'Enter a valid email address.', 'buddypress-contact-me' ) );
			}
		}
		if ( mb_strlen( $subject ) < 3 || mb_strlen( $subject ) > 200 ) {
			$errors->add( 'bcm_subject', __( 'Subject must be 3 to 200 characters.', 'buddypress-contact-me' ) );
		}
		if ( mb_strlen( $message ) < 10 || mb_strlen( $message ) > 5000 ) {
			$errors->add( 'bcm_message', __( 'Message must be 10 to 5000 characters.', 'buddypress-contact-me' ) );
		}
		if ( self::looks_like_spam( $message ) ) {
			$errors->add( 'bcm_spam', __( 'Your message was flagged as spam. Please rephrase and try again.', 'buddypress-contact-me' ) );
		}

		if ( $errors->has_errors() ) {
			return $errors;
		}

		$id = BCM_Messages_Repo::insert(
			array(
				'sender'    => $sender_id,
				'recipient' => $recipient_id,
				'subject'   => mb_substr( $subject, 0, 200 ),
				'message'   => mb_substr( $message, 0, 5000 ),
				'name'      => mb_substr( $name, 0, 100 ),
				'email'     => $email,
			)
		);

		if ( ! $id ) {
			return new WP_Error( 'bcm_insert_failed', __( 'Could not save your message. Please try again.', 'buddypress-contact-me' ) );
		}

		/**
		 * Fires after a contact message is stored.
		 *
		 * @param int $id           Inserted row ID.
		 * @param int $recipient_id Recipient user ID.
		 * @param int $sender_id    Sender user ID (0 for anonymous).
		 */
		do_action( 'bp_contact_me_form_save', $id, $recipient_id, $sender_id );

		return array(
			'id'        => $id,
			'recipient' => $recipient_id,
		);
	}

	/**
	 * Determine the recipient for this submission.
	 *
	 * Priority: shortcode id → shortcode username → displayed user.
	 *
	 * @param array $input Sanitized form input.
	 * @return int
	 */
	private static function resolve_recipient( array $input ) {
		if ( ! empty( $input['bcm_shortcode_user_id'] ) ) {
			return (int) $input['bcm_shortcode_user_id'];
		}
		if ( ! empty( $input['bcm_shortcode_username'] ) ) {
			$user = get_user_by( 'login', sanitize_text_field( wp_unslash( $input['bcm_shortcode_username'] ) ) );
			return $user ? (int) $user->ID : 0;
		}
		if ( function_exists( 'bp_displayed_user_id' ) && bp_displayed_user_id() ) {
			return (int) bp_displayed_user_id();
		}
		return 0;
	}

	/**
	 * Enforce the math captcha.
	 *
	 * Logged-in users bypass the captcha entirely — their identity is
	 * established by auth. Logged-out visitors must pass.
	 *
	 * @param array $input Sanitized form input.
	 * @return true|WP_Error
	 */
	private static function validate_captcha( array $input ) {
		if ( is_user_logged_in() ) {
			return true;
		}
		$answer = isset( $input['bcm_captcha_answer'] ) ? (int) $input['bcm_captcha_answer'] : 0;
		$hash   = isset( $input['bcm_captcha_hash'] ) ? sanitize_text_field( wp_unslash( $input['bcm_captcha_hash'] ) ) : '';
		if ( $answer <= 0 || '' === $hash ) {
			return new WP_Error( 'bcm_captcha_missing', __( 'Please solve the security question.', 'buddypress-contact-me' ) );
		}
		if ( ! hash_equals( wp_hash( (string) $answer ), $hash ) ) {
			return new WP_Error( 'bcm_captcha_wrong', __( 'The security answer is incorrect.', 'buddypress-contact-me' ) );
		}
		return true;
	}

	/**
	 * Low-key spam heuristic: obvious pharma/gambling/brute-url messages.
	 *
	 * @param string $message Message body to screen.
	 * @return bool
	 */
	private static function looks_like_spam( $message ) {
		$patterns = array(
			'/\b(?:viagra|cialis|levitra|pharmacy|pills|medication)\b/i',
			'/\b(?:casino|poker|blackjack|slots|gambling)\b/i',
			'/\b(?:loan|mortgage|credit|debt|finance)\s*(?:offer|approval|rate)/i',
			'/(?:click\s*here|buy\s*now|order\s*now|limited\s*time)/i',
			'/\b(?:million\s*dollar|you\s*won|congratulations\s*winner)\b/i',
		);

		foreach ( $patterns as $p ) {
			if ( preg_match( $p, $message ) ) {
				return (bool) apply_filters( 'bcm_spam_check', true, $message, $p );
			}
		}

		$url_count = preg_match_all( '~https?://~i', $message );
		if ( $url_count >= 3 ) {
			return (bool) apply_filters( 'bcm_spam_check', true, $message, 'too_many_urls' );
		}

		return (bool) apply_filters( 'bcm_spam_check', false, $message, '' );
	}

	/**
	 * Build the recipient's public Contact tab URL.
	 *
	 * @param int $recipient_id Recipient user ID.
	 * @return string
	 */
	private function recipient_contact_url( $recipient_id ) {
		$domain = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $recipient_id )
			: bp_core_get_user_domain( $recipient_id );
		return trailingslashit( $domain ) . BCM_Frontend_Nav::SLUG . '/';
	}

	/**
	 * Per-user "accept / decline contact" toggle on BP Settings → General.
	 */
	public function render_settings_toggle() {
		if ( ! is_user_logged_in() || bp_displayed_user_id() !== bp_loggedin_user_id() ) {
			return;
		}
		$meta    = get_user_meta( bp_loggedin_user_id(), 'contact_me_button', true );
		$enabled = ( 'off' !== $meta );
		?>
		<div class="bp-contact-me-settings-toggle">
			<label>
				<input type="checkbox" name="bcm_accept_contact" value="on" <?php checked( $enabled ); ?> />
				<?php esc_html_e( 'Let other members contact me through a form on my profile', 'buddypress-contact-me' ); ?>
			</label>
		</div>
		<?php
	}

	/**
	 * Persist the per-user toggle.
	 */
	public function save_settings_toggle() {
		if ( ! bp_is_post_request() || ! isset( $_POST['submit'] ) ) {
			return;
		}
		if ( ! function_exists( 'bp_is_settings_component' ) || ! bp_is_settings_component() || ! bp_is_current_action( 'general' ) ) {
			return;
		}
		if ( ! check_admin_referer( 'bp_settings_general' ) ) {
			return;
		}

		$enabled = ! empty( $_POST['bcm_accept_contact'] );
		update_user_meta( bp_loggedin_user_id(), 'contact_me_button', $enabled ? 'on' : 'off' );
	}
}
