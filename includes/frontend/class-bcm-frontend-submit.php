<?php
/**
 * Contact form submission: validation, flood control and the classic-POST fallback.
 *
 * The form posts to REST through JS when it can (see
 * buddypress-contact-me-public.js). Both paths call process_submission(),
 * the single source of truth for validation.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contact form submission handler.
 */
class BCM_Frontend_Submit {

	const NONCE_ACTION = 'bcm_form_nonce';

	/**
	 * Transient prefix for one-time guest captcha tokens.
	 */
	const CAPTCHA_PREFIX = 'bcm_captcha_';

	/**
	 * Transient prefix for the per-sender send counter.
	 */
	const THROTTLE_PREFIX = 'bcm_throttle_';

	/**
	 * Honeypot field: hidden from people, bots fill it in.
	 */
	const HONEYPOT = 'bcm_website';

	/**
	 * Error messages from a classic POST on this request. The form partial
	 * prints them above the form and refills what the visitor typed.
	 *
	 * @var string[]
	 */
	public static $errors = array();

	/**
	 * Create a one-time math question for a guest form.
	 *
	 * The answer is stored server side under a random token, so a solved
	 * question cannot be replayed once a message has been saved with it.
	 *
	 * @return array{a:int,b:int,token:string,label:string}
	 */
	public static function new_captcha() {
		$a     = wp_rand( 1, 20 );
		$b     = wp_rand( 1, 20 );
		$token = wp_generate_password( 20, false );
		set_transient( self::CAPTCHA_PREFIX . $token, $a + $b, 30 * MINUTE_IN_SECONDS );

		return array(
			'a'     => $a,
			'b'     => $b,
			'token' => $token,
			/* translators: %1$d, %2$d: numbers in the math question */
			'label' => sprintf( __( 'What is %1$d plus %2$d?', 'buddypress-contact-me' ), $a, $b ),
		);
	}

	/**
	 * Whether guests get the site's CAPTCHA (Wbcom CAPTCHA Manager) instead of the math question.
	 *
	 * True when CAPTCHA Manager is active, has a provider set up, and its
	 * "Contact Me Form" switch is on. Otherwise the math question is used.
	 *
	 * @return bool
	 */
	public static function uses_site_captcha() {
		if ( ! function_exists( 'wbc_captcha_service_manager' ) ) {
			return false;
		}
		$manager = wbc_captcha_service_manager();
		return $manager && $manager->is_configured() && $manager->is_enabled_for_context( 'contact_me' );
	}

	/**
	 * A submitted text value, or '' when it is missing or not a string.
	 *
	 * @param array  $input Unslashed form input.
	 * @param string $key   Field name.
	 * @return string
	 */
	private static function field( array $input, $key ) {
		return isset( $input[ $key ] ) && is_scalar( $input[ $key ] ) ? (string) $input[ $key ] : '';
	}

	/**
	 * Read the guest captcha token from the submitted form.
	 *
	 * @param array $input Unslashed form input.
	 * @return string Token, or '' when absent or malformed.
	 */
	private static function captcha_token( array $input ) {
		$token = self::field( $input, 'bcm_captcha_hash' );
		return ( 20 === strlen( $token ) && ctype_alnum( $token ) ) ? $token : '';
	}

	/**
	 * Hook the classic-POST handler into BP's action lifecycle.
	 */
	public function register() {
		add_action( 'bp_actions', array( $this, 'maybe_handle' ), 5 );
	}

	/**
	 * Handle a classic (non-JS) POST of the contact form.
	 *
	 * Success redirects back to the same page with `?bcm=sent`. An error does
	 * not redirect: the page renders again with the error and the typed text.
	 */
	public function maybe_handle() {
		if ( ! isset( $_POST['bcm_nonce'], $_POST['bp_contact_me_form_save'] ) ) {
			return;
		}
		// PUBLIC path - guests submit with a captcha. The capability gate is
		// BCM_Frontend_Nav::viewer_can_send() inside process_submission().
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcm_nonce'] ) ), self::NONCE_ACTION ) ) {
			self::$errors = array( __( 'Session expired. Please refresh the page and try again.', 'buddypress-contact-me' ) );
			return;
		}

		$result = self::process_submission( wp_unslash( $_POST ) );

		if ( is_wp_error( $result ) ) {
			self::$errors = $result->get_error_messages();
			return;
		}

		// The form posts to its own URL, so this is the page it was on (profile tab or shortcode page).
		wp_safe_redirect( add_query_arg( 'bcm', 'sent' ) );
		exit;
	}

	/**
	 * Validate + insert a contact submission.
	 *
	 * Shared by the classic POST handler and the REST endpoint so both
	 * paths enforce identical rules.
	 *
	 * @param array $input Unslashed input (sanitized inside).
	 * @return array{id:int,recipient:int,captcha:array{a:int,b:int,token:string,label:string}|null}|WP_Error
	 */
	public static function process_submission( array $input ) {
		if ( ! BCM_Frontend_Nav::viewer_can_send() ) {
			return new WP_Error( 'bcm_not_allowed', __( 'You are not allowed to send messages.', 'buddypress-contact-me' ) );
		}

		$sender_id = get_current_user_id();

		// Honeypot: people never see this field, so anything in it came from a bot.
		if ( ! $sender_id && '' !== self::field( $input, self::HONEYPOT ) ) {
			return new WP_Error( 'bcm_spam', __( 'Your message was flagged as spam. Please rephrase and try again.', 'buddypress-contact-me' ) );
		}

		// Resolve recipient.
		$recipient_id = self::resolve_recipient( $input );
		if ( ! $recipient_id ) {
			return new WP_Error( 'bcm_recipient_missing', __( 'Recipient not found.', 'buddypress-contact-me' ) );
		}
		if ( $sender_id === $recipient_id ) {
			return new WP_Error( 'bcm_self_contact', __( 'You cannot send a contact message to yourself.', 'buddypress-contact-me' ) );
		}
		if ( ! BCM_Frontend_Nav::user_accepts_contact( $recipient_id ) ) {
			return new WP_Error( 'bcm_recipient_closed', __( 'This member is not accepting contact messages right now.', 'buddypress-contact-me' ) );
		}

		$throttle = self::throttle();
		if ( $throttle['limit'] > 0 && $throttle['hit']['count'] >= $throttle['limit'] ) {
			return new WP_Error( 'bcm_throttled', __( 'You have sent several messages in a short time. Please wait a few minutes and try again.', 'buddypress-contact-me' ) );
		}

		// Captcha: guests only.
		$captcha_check = self::validate_captcha( $input );
		if ( is_wp_error( $captcha_check ) ) {
			return $captcha_check;
		}

		$subject = sanitize_text_field( self::field( $input, 'bp_contact_me_subject' ) );
		$message = sanitize_textarea_field( self::field( $input, 'bp_contact_me_msg' ) );

		if ( $sender_id ) {
			// A member's account email is never stored or shown: the recipient
			// replies through the profile link or a private message.
			$name  = bp_core_get_user_displayname( $sender_id );
			$email = '';
		} else {
			$name  = sanitize_text_field( self::field( $input, 'bp_contact_me_first_name' ) );
			$email = sanitize_email( self::field( $input, 'bp_contact_me_email' ) );
		}

		$errors = new WP_Error();

		if ( mb_strlen( $name ) < 2 || mb_strlen( $name ) > 100 ) {
			$errors->add( 'bcm_name', __( 'Name must be 2 to 100 characters.', 'buddypress-contact-me' ) );
		}
		if ( ! $sender_id && ( '' === $email || ! is_email( $email ) ) ) {
			$errors->add( 'bcm_email', __( 'Enter a valid email address.', 'buddypress-contact-me' ) );
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

		// Spend the guest's question once every field is valid, so a typo elsewhere does not burn it.
		// The delete is one DELETE query: when two requests race with one token, only one removes the row.
		if ( ! $sender_id && ! self::uses_site_captcha() && ! delete_transient( self::CAPTCHA_PREFIX . self::captcha_token( $input ) ) ) {
			return new WP_Error( 'bcm_captcha_expired', __( 'The security question has expired. Please reload the page and try again.', 'buddypress-contact-me' ) );
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

		// Ponytail: read-then-write counter, so two sends in the same instant can both pass. Fine for flood control.
		++$throttle['hit']['count'];
		set_transient( $throttle['key'], $throttle['hit'], max( 1, $throttle['hit']['start'] + $throttle['window'] - time() ) );

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
			'captcha'   => ( $sender_id || self::uses_site_captcha() ) ? null : self::new_captcha(),
		);
	}

	/**
	 * The current sender's send counter: per member ID, per IP address for guests.
	 *
	 * @return array{key:string,limit:int,window:int,hit:array{count:int,start:int}}
	 */
	private static function throttle() {
		$sender_id = get_current_user_id();
		$ip        = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key       = self::THROTTLE_PREFIX . ( $sender_id ? 'u' . $sender_id : 'ip' . md5( $ip ) );

		/**
		 * Filters how many contact messages one sender may send in a time window.
		 *
		 * Members are counted by user ID, guests by IP address. Return a limit of
		 * 0 to turn the limit off.
		 *
		 * @since 1.5.1
		 *
		 * @param array $rule      { @type int $limit Messages allowed. @type int $window Window in seconds. }
		 * @param int   $sender_id Sender user ID, 0 for a guest.
		 */
		$rule = (array) apply_filters(
			'bcm_rate_limit',
			array(
				'limit'  => 5,
				'window' => 10 * MINUTE_IN_SECONDS,
			),
			$sender_id
		);

		$hit = get_transient( $key );
		if ( ! is_array( $hit ) || ! isset( $hit['count'], $hit['start'] ) ) {
			$hit = array(
				'count' => 0,
				'start' => time(),
			);
		}

		return array(
			'key'    => $key,
			'limit'  => max( 0, (int) ( $rule['limit'] ?? 5 ) ),
			'window' => max( 1, (int) ( $rule['window'] ?? 10 * MINUTE_IN_SECONDS ) ),
			'hit'    => array(
				'count' => (int) $hit['count'],
				'start' => (int) $hit['start'],
			),
		);
	}

	/**
	 * Determine the recipient for this submission.
	 *
	 * Priority: shortcode id, then shortcode username, then the displayed user.
	 *
	 * @param array $input Unslashed form input.
	 * @return int
	 */
	private static function resolve_recipient( array $input ) {
		$id = absint( self::field( $input, 'bcm_shortcode_user_id' ) );
		if ( $id ) {
			return $id;
		}
		$login = sanitize_text_field( self::field( $input, 'bcm_shortcode_username' ) );
		if ( '' !== $login ) {
			$user = get_user_by( 'login', $login );
			return $user ? (int) $user->ID : 0;
		}
		if ( function_exists( 'bp_displayed_user_id' ) && bp_displayed_user_id() ) {
			return (int) bp_displayed_user_id();
		}
		return 0;
	}

	/**
	 * Enforce the guest captcha. Logged-in members skip it.
	 *
	 * @param array $input Unslashed form input.
	 * @return true|WP_Error
	 */
	private static function validate_captcha( array $input ) {
		if ( is_user_logged_in() ) {
			return true;
		}
		if ( self::uses_site_captcha() ) {
			// CAPTCHA Manager reads the provider's token from the posted form and checks it once.
			return wbc_captcha_service_manager()->verify( 'contact_me' )
				? true
				: new WP_Error( 'bcm_captcha_wrong', __( 'Please complete the security check and try again.', 'buddypress-contact-me' ) );
		}
		$answer = absint( self::field( $input, 'bcm_captcha_answer' ) );
		$token  = self::captcha_token( $input );
		if ( $answer <= 0 || '' === $token ) {
			return new WP_Error( 'bcm_captcha_missing', __( 'Please solve the security question.', 'buddypress-contact-me' ) );
		}
		$expected = get_transient( self::CAPTCHA_PREFIX . $token );
		if ( false === $expected ) {
			return new WP_Error( 'bcm_captcha_expired', __( 'The security question has expired. Please reload the page and try again.', 'buddypress-contact-me' ) );
		}
		if ( (int) $expected !== $answer ) {
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
}
