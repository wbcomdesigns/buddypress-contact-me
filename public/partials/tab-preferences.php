<?php
/**
 * Contact > Preferences sub-tab: the one place a member opts out of
 * contact messages. Saved by BCM_Frontend_Nav::save_preferences().
 * Also lets the member copy their public contact link.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bcm_user_id = get_current_user_id();
// Own-profile gate: the sub-tab is only registered on the member's own profile.
if ( ! $bcm_user_id || bp_displayed_user_id() !== $bcm_user_id ) {
	return;
}

$bcm_meta    = get_user_meta( $bcm_user_id, 'contact_me_button', true );
$bcm_accept  = ( 'off' !== $bcm_meta );
$bcm_profile = function_exists( 'bp_members_get_user_url' )
	? bp_members_get_user_url( $bcm_user_id )
	: bp_core_get_user_domain( $bcm_user_id );
$bcm_link    = trailingslashit( $bcm_profile ) . BCM_Frontend_Nav::SLUG . '/';
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

	<section class="bcm-preferences__panel bcm-preferences__panel--tips">
		<h4 class="bcm-preferences__panel-title"><?php esc_html_e( 'Not receiving messages?', 'buddypress-contact-me' ); ?></h4>
		<ul class="bcm-preferences__tips">
			<li><?php esc_html_e( 'Check your spam folder. Some providers route contact-form emails there.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Make sure the toggle above is on so your Contact tab is visible.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( "If the form won't submit, reload the page. Your session may have expired.", 'buddypress-contact-me' ); ?></li>
		</ul>
	</section>
</div>
