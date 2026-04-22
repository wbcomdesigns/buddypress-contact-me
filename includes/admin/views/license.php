<?php
/**
 * License tab: EDD software licensing form (partial rendered inside shell.php).
 *
 * Backed by the handlers in edd-license/edd-plugin-license.php:
 *   - edd_wbcom_bcm_activate_license()    on admin_init
 *   - edd_wbcom_BCM_deactivate_license()  on admin_init
 * Both verify the shared nonce `edd_wbcom_contact_me_nonce` and toggle
 * the edd_wbcom_bp_contact_me_license_key / _status options accordingly.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

$license_key     = (string) get_option( 'edd_wbcom_bp_contact_me_license_key', '' );
$license_status  = (string) get_option( 'edd_wbcom_bp_contact_me_license_status', '' );
$license_expires = (string) get_option( 'edd_wbcom_bp_contact_me_license_expires', '' );
$has_key         = '' !== $license_key;
$is_valid        = 'valid' === $license_status;
$is_lifetime     = 'lifetime' === $license_expires;

// How many days until expiry (0 if unknown/lifetime).
$days_remaining = 0;
if ( $is_valid && $license_expires && ! $is_lifetime ) {
	$expires_ts = strtotime( $license_expires );
	if ( $expires_ts ) {
		$days_remaining = (int) floor( ( $expires_ts - time() ) / DAY_IN_SECONDS );
	}
}
$expiring_soon = $is_valid && ! $is_lifetime && $license_expires && $days_remaining > 0 && $days_remaining <= 30;

// Surface any activation feedback the EDD handler redirects back with.
$activation_msg = '';
if ( isset( $_GET['BCM_activation'] ) && 'false' === $_GET['BCM_activation'] && isset( $_GET['message'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$activation_msg = sanitize_text_field( wp_unslash( $_GET['message'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
}

// Mask everything but the last 4 characters of the saved key for display.
$masked_key = '';
if ( $has_key ) {
	$len        = strlen( $license_key );
	$masked_key = $len > 4
		? str_repeat( '•', max( 8, $len - 4 ) ) . substr( $license_key, -4 )
		: $license_key;
}
?>

<?php if ( $activation_msg ) : ?>
	<div class="bcm-notice bcm-notice--warn">
		<span class="dashicons dashicons-warning" aria-hidden="true"></span>
		<div><?php echo esc_html( $activation_msg ); ?></div>
	</div>
<?php endif; ?>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Plugin License', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Activate your license to receive automatic updates and support. The key is emailed with your purchase receipt.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Status', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $is_valid ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;">
						<span class="dashicons dashicons-yes-alt" aria-hidden="true"></span>
						<?php esc_html_e( 'Active: receiving updates', 'buddypress-contact-me' ); ?>
					</span>
					<?php if ( $is_lifetime ) : ?>
						<p class="description" style="color: var(--bcm-admin-success);">
							<?php esc_html_e( 'Lifetime license — never expires.', 'buddypress-contact-me' ); ?>
						</p>
					<?php elseif ( $license_expires && $expiring_soon ) : ?>
						<p class="description" style="color: var(--bcm-admin-warn);">
							<?php
							printf(
								/* translators: 1: expiry date, 2: days remaining */
								esc_html( _n( 'Renews on %1$s — only %2$d day left.', 'Renews on %1$s — only %2$d days left. Renew to keep receiving updates.', $days_remaining, 'buddypress-contact-me' ) ),
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_expires ) ) ),
								(int) $days_remaining
							);
							?>
						</p>
					<?php elseif ( $license_expires ) : ?>
						<p class="description">
							<?php
							printf(
								/* translators: %s: expiry date. */
								esc_html__( 'Renews on %s.', 'buddypress-contact-me' ),
								esc_html( date_i18n( get_option( 'date_format' ), strtotime( $license_expires ) ) )
							);
							?>
						</p>
					<?php endif; ?>
				<?php elseif ( $has_key ) : ?>
					<span style="color: var(--bcm-admin-warn); font-weight: 600;">
						<span class="dashicons dashicons-warning" aria-hidden="true"></span>
						<?php esc_html_e( 'Key saved but not activated', 'buddypress-contact-me' ); ?>
					</span>
					<p class="description"><?php esc_html_e( 'Click "Activate License" below to link this key to your site.', 'buddypress-contact-me' ); ?></p>
				<?php else : ?>
					<span style="color: var(--bcm-admin-danger); font-weight: 600;">
						<span class="dashicons dashicons-dismiss" aria-hidden="true"></span>
						<?php esc_html_e( 'Not activated: updates are paused', 'buddypress-contact-me' ); ?>
					</span>
					<p class="description">
						<?php
						printf(
							/* translators: %s: URL to customer account */
							esc_html__( 'You can find your license key in your %s.', 'buddypress-contact-me' ),
							'<a href="https://wbcomdesigns.com/profile/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Wbcom Designs account', 'buddypress-contact-me' ) . '</a>'
						);
						?>
					</p>
				<?php endif; ?>
			</td>
		</tr>
	</table>

	<form method="post" class="bcm-license-form">
		<?php wp_nonce_field( 'edd_wbcom_contact_me_nonce', 'edd_wbcom_contact_me_nonce' ); ?>
		<table class="form-table">
			<tr>
				<th scope="row"><label for="bcm-license-key"><?php esc_html_e( 'License key', 'buddypress-contact-me' ); ?></label></th>
				<td>
					<input type="text"
						id="bcm-license-key"
						name="edd_wbcom_bp_contact_me_license_key"
						value="<?php echo esc_attr( $is_valid ? $masked_key : $license_key ); ?>"
						class="regular-text"
						autocomplete="off"
						spellcheck="false"
						<?php echo $is_valid ? 'readonly' : ''; ?>
						placeholder="<?php esc_attr_e( 'Paste your license key', 'buddypress-contact-me' ); ?>">
					<?php if ( $is_valid ) : ?>
						<p class="description"><?php esc_html_e( 'Deactivate first to change the key.', 'buddypress-contact-me' ); ?></p>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<div class="bcm-save-bar">
			<?php if ( $is_valid ) : ?>
				<button type="submit"
					name="edd_BCM_license_deactivate"
					class="bcm-btn bcm-btn-danger"
					data-bcm-confirm="<?php esc_attr_e( 'Deactivating will stop automatic updates for this site until you activate again. Continue?', 'buddypress-contact-me' ); ?>">
					<span class="dashicons dashicons-unlock" aria-hidden="true"></span>
					<?php esc_html_e( 'Deactivate License', 'buddypress-contact-me' ); ?>
				</button>
			<?php else : ?>
				<button type="submit" name="edd_bp_contact_me_license_activate" class="bcm-btn bcm-btn-primary">
					<span class="dashicons dashicons-yes" aria-hidden="true"></span>
					<?php esc_html_e( 'Activate License', 'buddypress-contact-me' ); ?>
				</button>
			<?php endif; ?>
		</div>
	</form>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'What activation unlocks', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<ul style="margin: 0; padding-left: 18px; color: var(--bcm-admin-text-2); line-height: 1.7;">
			<li><?php esc_html_e( 'Automatic update notifications in the WordPress Plugins screen.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Priority support from the Wbcom Designs team.', 'buddypress-contact-me' ); ?></li>
			<li><?php esc_html_e( 'Access to premium integrations as they ship.', 'buddypress-contact-me' ); ?></li>
		</ul>
	</div>
</div>
