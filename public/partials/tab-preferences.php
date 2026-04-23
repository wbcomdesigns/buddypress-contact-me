<?php
/**
 * Contact → Preferences sub-tab.
 *
 * Lets a member opt-out of receiving contact messages, copy their
 * public contact link, and jump to notification/email settings.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bcm_user_id = get_current_user_id();
if ( ! $bcm_user_id || bp_displayed_user_id() !== $bcm_user_id ) {
	return;
}

// Save handler: tiny form, own nonce, own action.
$bcm_request_method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';
if ( 'POST' === $bcm_request_method && isset( $_POST['bcm_preferences_nonce'] ) ) {
	if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bcm_preferences_nonce'] ) ), 'bcm_preferences_save' ) ) {
		$accept = ! empty( $_POST['bcm_accept_contact'] );
		update_user_meta( $bcm_user_id, 'contact_me_button', $accept ? 'on' : 'off' );
		BCM_Frontend_Flash::add( __( 'Preferences saved.', 'buddypress-contact-me' ), 'success' );
		$redirect = add_query_arg( 'saved', '1' );
		wp_safe_redirect( $redirect );
		exit;
	}
}

$bcm_meta         = get_user_meta( $bcm_user_id, 'contact_me_button', true );
$bcm_accept       = ( 'off' !== $bcm_meta );
$bcm_profile      = function_exists( 'bp_members_get_user_url' )
	? bp_members_get_user_url( $bcm_user_id )
	: bp_core_get_user_domain( $bcm_user_id );
$bcm_link         = trailingslashit( $bcm_profile ) . BCM_Frontend_Nav::SLUG . '/';
$bcm_settings_url = function_exists( 'bp_get_settings_slug' )
	? trailingslashit( $bcm_profile ) . bp_get_settings_slug() . '/notifications/'
	: '';

$bcm_flash = BCM_Frontend_Flash::consume();
if ( $bcm_flash ) :
	?>
	<div class="bcm-alert bcm-alert--<?php echo esc_attr( $bcm_flash['type'] ); ?>" role="status" aria-live="polite">
		<?php echo esc_html( $bcm_flash['message'] ); ?>
	</div>
	<?php
endif;
?>

<div class="bcm-preferences">
	<header class="bcm-preferences__header">
		<h3 class="bcm-preferences__title"><?php esc_html_e( 'Contact preferences', 'buddypress-contact-me' ); ?></h3>
		<p class="bcm-preferences__intro">
			<?php esc_html_e( 'Control who can reach you through your profile contact form.', 'buddypress-contact-me' ); ?>
		</p>
	</header>

	<form method="post" class="bcm-preferences__form">
		<?php wp_nonce_field( 'bcm_preferences_save', 'bcm_preferences_nonce' ); ?>

		<div class="bcm-preferences__row">
			<label class="bcm-preferences__toggle">
				<input type="checkbox" name="bcm_accept_contact" value="on" <?php checked( $bcm_accept ); ?> />
				<span class="bcm-preferences__toggle-label">
					<span class="bcm-preferences__toggle-title"><?php esc_html_e( 'Let other members contact me', 'buddypress-contact-me' ); ?></span>
					<span class="bcm-preferences__toggle-hint">
						<?php esc_html_e( 'When enabled, a Contact form appears on your profile and both members and visitors can send you messages. You can turn this off at any time.', 'buddypress-contact-me' ); ?>
					</span>
				</span>
			</label>
		</div>

		<div class="bcm-preferences__actions">
			<button type="submit" class="button button-primary">
				<?php esc_html_e( 'Save preferences', 'buddypress-contact-me' ); ?>
			</button>
		</div>
	</form>

	<section class="bcm-preferences__panel">
		<h4 class="bcm-preferences__panel-title"><?php esc_html_e( 'Share your contact link', 'buddypress-contact-me' ); ?></h4>
		<p class="bcm-preferences__panel-body">
			<?php esc_html_e( 'Paste this link into your bio, email signature or social media to let people reach you without giving out your email address.', 'buddypress-contact-me' ); ?>
		</p>
		<div class="bcm-preferences__linkbox">
			<input type="text" readonly value="<?php echo esc_attr( $bcm_link ); ?>" onfocus="this.select()" aria-label="<?php esc_attr_e( 'Your public contact link', 'buddypress-contact-me' ); ?>" />
			<button type="button" class="button bcm-copy-link" data-url="<?php echo esc_attr( $bcm_link ); ?>">
				<?php esc_html_e( 'Copy link', 'buddypress-contact-me' ); ?>
			</button>
		</div>
	</section>

	<?php if ( $bcm_settings_url ) : ?>
		<section class="bcm-preferences__panel">
			<h4 class="bcm-preferences__panel-title"><?php esc_html_e( 'Email + notification settings', 'buddypress-contact-me' ); ?></h4>
			<p class="bcm-preferences__panel-body">
				<?php esc_html_e( 'Choose whether new contact messages should also send you an email or appear in your BuddyPress notifications.', 'buddypress-contact-me' ); ?>
			</p>
			<p>
				<a class="button" href="<?php echo esc_url( $bcm_settings_url ); ?>">
					<?php esc_html_e( 'Open notification settings', 'buddypress-contact-me' ); ?>
				</a>
			</p>
		</section>
	<?php endif; ?>

	<section class="bcm-preferences__panel bcm-preferences__panel--tips">
		<h4 class="bcm-preferences__panel-title"><?php esc_html_e( 'Not receiving messages?', 'buddypress-contact-me' ); ?></h4>
		<ul class="bcm-preferences__tips">
			<li><?php esc_html_e( 'Check your spam folder — some providers route contact-form emails there.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Make sure the toggle above is on so your Contact tab is visible.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( "If the form won't submit, reload the page — your session may have expired.", 'buddypress-contact-me' ); ?></li>
		</ul>
	</section>
</div>
