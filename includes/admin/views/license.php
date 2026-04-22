<?php
/**
 * License tab: EDD Software Licensing.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

$license_key    = trim( (string) get_option( 'edd_wbcom_bp_contact_me_license_key', '' ) );
$license_status = (string) get_option( 'edd_wbcom_bp_contact_me_license_status', '' );
$is_active      = ( 'valid' === $license_status );
?>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Plugin License', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Activate your license to receive automatic updates and support. The key is emailed with your purchase receipt.', 'buddypress-contact-me' ); ?></p>
	</div>
	<form method="post" action="options.php">
		<?php settings_fields( 'edd_wbcom_bp_contact_me_license' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Status', 'buddypress-contact-me' ); ?></th>
				<td>
					<?php if ( $is_active ) : ?>
						<span style="color: var(--bcm-admin-success); font-weight: 600;">
							<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
							<?php esc_html_e( 'Active — updates and support are on', 'buddypress-contact-me' ); ?>
						</span>
					<?php else : ?>
						<span style="color: var(--bcm-admin-danger); font-weight: 600;">
							<span class="dashicons dashicons-warning" aria-hidden="true"></span>
							<?php esc_html_e( 'Not activated: updates are paused', 'buddypress-contact-me' ); ?>
						</span>
						<p class="description" style="margin-top: 6px;">
							<?php
							/* translators: %s: Wbcom Designs account URL. */
							printf(
								wp_kses_post( __( 'You can find your license key in your <a href="%s" target="_blank" rel="noopener">Wbcom Designs account</a>.', 'buddypress-contact-me' ) ),
								esc_url( 'https://wbcomdesigns.com/my-account/' )
							);
							?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="edd_wbcom_bp_contact_me_license_key"><?php esc_html_e( 'License key', 'buddypress-contact-me' ); ?></label>
				</th>
				<td>
					<input type="text"
						id="edd_wbcom_bp_contact_me_license_key"
						name="edd_wbcom_bp_contact_me_license_key"
						value="<?php echo esc_attr( $license_key ); ?>"
						class="regular-text"
						placeholder="<?php esc_attr_e( 'Paste your license key', 'buddypress-contact-me' ); ?>"
						style="width: 100%; max-width: 520px;">
				</td>
			</tr>
		</table>
		<div class="bcm-save-bar">
			<?php
			if ( $is_active ) {
				wp_nonce_field( 'edd_bp_contact_me_plugin_nonce', 'edd_bp_contact_me_plugin_nonce' );
				?>
				<button type="submit" name="edd_wbcom_bp_contact_me_license_deactivate" class="bcm-btn bcm-btn-secondary">
					<span class="dashicons dashicons-no" aria-hidden="true"></span>
					<?php esc_html_e( 'Deactivate License', 'buddypress-contact-me' ); ?>
				</button>
				<?php
			} else {
				wp_nonce_field( 'edd_bp_contact_me_plugin_nonce', 'edd_bp_contact_me_plugin_nonce' );
				?>
				<button type="submit" name="edd_wbcom_bp_contact_me_license_activate" class="bcm-btn bcm-btn-primary">
					<span class="dashicons dashicons-yes" aria-hidden="true"></span>
					<?php esc_html_e( 'Activate License', 'buddypress-contact-me' ); ?>
				</button>
				<?php
			}
			?>
			<button type="submit" class="bcm-btn bcm-btn-secondary">
				<?php esc_html_e( 'Save License Key', 'buddypress-contact-me' ); ?>
			</button>
		</div>
	</form>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'What Activation Unlocks', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<ul style="margin: 0; padding-left: 18px; line-height: 1.7;">
			<li><?php esc_html_e( 'Automatic update notifications in the WordPress Plugins screen.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Priority support from the Wbcom Designs team.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Access to premium integrations as they ship.', 'buddypress-contact-me' ); ?></li>
		</ul>
	</div>
</div>
