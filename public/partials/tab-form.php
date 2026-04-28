<?php
/**
 * Contact form — rendered on other members' profiles, also via shortcode.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bcm_atts      = isset( $GLOBALS['bcm_shortcode_atts'] ) && is_array( $GLOBALS['bcm_shortcode_atts'] )
	? $GLOBALS['bcm_shortcode_atts']
	: array();
$bcm_target_id = (int) ( $bcm_atts['id'] ?? bp_displayed_user_id() );

if ( ! BCM_Frontend_Nav::viewer_can_send() ) {
	?>
	<div class="bcm-card bcm-card--closed" role="status">
		<p class="bcm-card__title"><?php esc_html_e( 'Sign in to send a message', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__body"><?php esc_html_e( 'This community does not accept messages from visitors. Please sign in and try again.', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__actions">
			<a class="button" href="<?php echo esc_url( wp_login_url( home_url( add_query_arg( null, null ) ) ) ); ?>">
				<?php esc_html_e( 'Sign in', 'buddypress-contact-me' ); ?>
			</a>
		</p>
	</div>
	<?php
	return;
}

$bcm_flash = BCM_Frontend_Flash::consume();
if ( $bcm_flash ) :
	?>
	<div class="bcm-alert bcm-alert--<?php echo esc_attr( $bcm_flash['type'] ); ?>" role="status" aria-live="polite">
		<?php echo esc_html( $bcm_flash['message'] ); ?>
	</div>
	<?php
endif;

// Show an inline success banner when we arrive with ?bcm=sent (for non-JS fallback).
if ( isset( $_GET['bcm'] ) && 'sent' === $_GET['bcm'] ) : // phpcs:ignore WordPress.Security.NonceVerification
	?>
	<div class="bcm-alert bcm-alert--success" role="status" aria-live="polite">
		<?php esc_html_e( 'Message sent.', 'buddypress-contact-me' ); ?>
	</div>
	<?php
endif;

$bcm_min         = 1;
$bcm_max         = 20;
$bcm_num1        = wp_rand( $bcm_min, $bcm_max );
$bcm_num2        = wp_rand( $bcm_min, $bcm_max );
$bcm_captcha     = $bcm_num1 + $bcm_num2;
$bcm_logged_in   = is_user_logged_in();
$bcm_user_name   = $bcm_logged_in ? bp_core_get_user_displayname( bp_loggedin_user_id() ) : '';
$bcm_target      = get_userdata( $bcm_target_id );
$bcm_target_name = $bcm_target ? bp_core_get_user_displayname( $bcm_target_id ) : '';
?>

<div class="bcm-form-wrap">
	<header class="bcm-form__header">
		<h3 class="bcm-form__title">
			<?php
			/* translators: %s: recipient display name */
			printf( esc_html__( 'Send %s a message', 'buddypress-contact-me' ), esc_html( $bcm_target_name ) );
			?>
		</h3>
		<p class="bcm-form__hint">
			<?php
			if ( $bcm_logged_in ) {
				printf(
					wp_kses(
						/* translators: %s: sender display name (bolded). */
						__( 'You are sending as %s. Your name and profile link are shared with the recipient.', 'buddypress-contact-me' ),
						array( 'strong' => array() )
					),
					'<strong>' . esc_html( $bcm_user_name ) . '</strong>'
				);
			} else {
				esc_html_e( 'Your message is delivered privately — only the recipient sees it. Your email is shared so they can reply.', 'buddypress-contact-me' );
			}
			?>
		</p>
	</header>

	<form class="bcm-form" method="post" action="" novalidate data-bcm-form>
		<?php if ( ! $bcm_logged_in ) : ?>
			<div class="bcm-field">
				<label for="bcm-name"><?php esc_html_e( 'Your name', 'buddypress-contact-me' ); ?> <span aria-hidden="true" class="bcm-field__required">*</span></label>
				<input
					type="text"
					id="bcm-name"
					name="bp_contact_me_first_name"
					minlength="2"
					maxlength="100"
					required
					autocomplete="name"
					aria-describedby="bcm-name-hint bcm-name-error"
				/>
				<span class="bcm-field__hint" id="bcm-name-hint"><?php esc_html_e( '2 to 100 characters.', 'buddypress-contact-me' ); ?></span>
				<span class="bcm-field__error" id="bcm-name-error" role="alert"></span>
			</div>
			<div class="bcm-field">
				<label for="bcm-email"><?php esc_html_e( 'Email', 'buddypress-contact-me' ); ?> <span aria-hidden="true" class="bcm-field__required">*</span></label>
				<input
					type="email"
					id="bcm-email"
					name="bp_contact_me_email"
					required
					autocomplete="email"
					aria-describedby="bcm-email-hint bcm-email-error"
				/>
				<span class="bcm-field__hint" id="bcm-email-hint"><?php esc_html_e( 'So the member can reply to you.', 'buddypress-contact-me' ); ?></span>
				<span class="bcm-field__error" id="bcm-email-error" role="alert"></span>
			</div>
		<?php endif; ?>

		<div class="bcm-field">
			<label for="bcm-subject"><?php esc_html_e( 'Subject', 'buddypress-contact-me' ); ?> <span aria-hidden="true" class="bcm-field__required">*</span></label>
			<input
				type="text"
				id="bcm-subject"
				name="bp_contact_me_subject"
				minlength="3"
				maxlength="200"
				required
				aria-describedby="bcm-subject-hint bcm-subject-error"
			/>
			<span class="bcm-field__hint" id="bcm-subject-hint"><?php esc_html_e( '3 to 200 characters.', 'buddypress-contact-me' ); ?></span>
			<span class="bcm-field__error" id="bcm-subject-error" role="alert"></span>
		</div>

		<div class="bcm-field">
			<label for="bcm-message"><?php esc_html_e( 'Message', 'buddypress-contact-me' ); ?> <span aria-hidden="true" class="bcm-field__required">*</span></label>
			<textarea
				id="bcm-message"
				name="bp_contact_me_msg"
				minlength="10"
				maxlength="5000"
				rows="8"
				required
				aria-describedby="bcm-message-hint bcm-message-error"
			></textarea>
			<span class="bcm-field__hint" id="bcm-message-hint">
				<?php esc_html_e( '10 to 5000 characters.', 'buddypress-contact-me' ); ?>
				<span class="bcm-field__counter" data-bcm-counter aria-live="polite">0 / 5000</span>
			</span>
			<span class="bcm-field__error" id="bcm-message-error" role="alert"></span>
		</div>

		<?php if ( ! $bcm_logged_in ) : ?>
			<div class="bcm-field bcm-field--captcha">
				<label for="bcm-captcha">
					<?php esc_html_e( 'Security check', 'buddypress-contact-me' ); ?>
					<span aria-hidden="true" class="bcm-field__required">*</span>
				</label>
				<div class="bcm-captcha">
					<span class="bcm-captcha__sum" aria-hidden="true"><?php echo esc_html( $bcm_num1 . ' + ' . $bcm_num2 . ' = ?' ); ?></span>
					<input
						type="number"
						inputmode="numeric"
						id="bcm-captcha"
						name="bcm_captcha_answer"
						required
						min="2"
						max="40"
						aria-describedby="bcm-captcha-hint bcm-captcha-error"
						aria-label="
						<?php
							/* translators: %1$d, %2$d: numbers in the math question */
							echo esc_attr( sprintf( __( 'What is %1$d plus %2$d?', 'buddypress-contact-me' ), $bcm_num1, $bcm_num2 ) );
						?>
						"
					/>
				</div>
				<span class="bcm-field__hint" id="bcm-captcha-hint"><?php esc_html_e( 'Please solve the math problem.', 'buddypress-contact-me' ); ?></span>
				<span class="bcm-field__error" id="bcm-captcha-error" role="alert"></span>
			</div>
		<?php endif; ?>

		<input type="hidden" name="bcm_captcha_hash" value="<?php echo esc_attr( wp_hash( (string) $bcm_captcha ) ); ?>" />
		<input type="hidden" name="bcm_shortcode_user_id" value="<?php echo esc_attr( (int) $bcm_target_id ); ?>" />
		<input type="hidden" name="bcm_shortcode_username" value="<?php echo esc_attr( $bcm_atts['user'] ?? '' ); ?>" />
		<input type="hidden" name="bcm_nonce" value="<?php echo esc_attr( wp_create_nonce( BCM_Frontend_Submit::NONCE_ACTION ) ); ?>" />
		<input type="hidden" name="bp_contact_me_form_save" value="1" />

		<div class="bcm-form__actions">
			<button type="submit" class="button bcm-submit" data-bcm-submit>
				<span class="bcm-submit__label"><?php esc_html_e( 'Send message', 'buddypress-contact-me' ); ?></span>
				<span class="bcm-submit__spinner" aria-hidden="true"></span>
			</button>
		</div>
	</form>
</div>
