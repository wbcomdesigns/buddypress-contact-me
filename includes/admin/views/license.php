<?php
/**
 * License tab: card-panel form for activate / deactivate.
 *
 * Pulls the renderable license fields through `edd_BCM_get_license_status()`
 * (defined in edd-license/edd-plugin-license.php) so status, expiry,
 * and activations all read from a single cached snapshot - no
 * duplicate API round-trips, no defensive null checks scattered
 * across the template.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

$license_key = (string) get_option( 'edd_wbcom_bp_contact_me_license_key', '' );
$snapshot    = function_exists( 'edd_BCM_get_license_status' )
	? edd_BCM_get_license_status()
	: array(
		'license' => (string) get_option( 'edd_wbcom_bp_contact_me_license_status', '' ),
		'data'    => null,
		'message' => '',
	);

$is_active = ( 'valid' === $snapshot['license'] );
$has_key   = '' !== $license_key;
$data      = $snapshot['data'];

// Mask all but the last 4 chars when active; show plaintext while
// editing so the admin can see what they pasted.
$display_key = $license_key;
if ( $is_active && strlen( $license_key ) > 4 ) {
	$display_key = str_repeat( '•', max( 8, strlen( $license_key ) - 4 ) ) . substr( $license_key, -4 );
}

// Expiration string. EDD reports either 'lifetime' or a timestamp
// string. Renders the localised date or an em-dash when unknown.
$expires_raw  = is_object( $data ) && isset( $data->expires )
	? (string) $data->expires
	: (string) get_option( 'edd_wbcom_bp_contact_me_license_expires', '' );
$is_lifetime  = ( 'lifetime' === $expires_raw );
$expires_text = '&mdash;';
$days_left    = 0;
if ( $is_lifetime ) {
	$expires_text = esc_html__( 'Lifetime', 'buddypress-contact-me' );
} elseif ( '' !== $expires_raw ) {
	$ts = strtotime( $expires_raw, current_time( 'timestamp' ) );
	if ( $ts ) {
		$expires_text = esc_html( date_i18n( get_option( 'date_format' ), $ts ) );
		$days_left    = (int) floor( ( $ts - time() ) / DAY_IN_SECONDS );
	}
}
$expiring_soon = $is_active && ! $is_lifetime && $days_left > 0 && $days_left <= 30;

// Activations counter. license_limit = 0 means unlimited - render
// that explicitly so customers don't see "1/0 sites" and panic.
$activations_text = '&mdash;';
if ( is_object( $data ) && isset( $data->site_count, $data->license_limit ) ) {
	$site_count    = (int) $data->site_count;
	$license_limit = (int) $data->license_limit;
	$activations_text = $license_limit > 0
		? esc_html(
			sprintf(
				/* translators: %1$d activated sites, %2$d max sites */
				__( '%1$d of %2$d sites', 'buddypress-contact-me' ),
				$site_count,
				$license_limit
			)
		)
		: esc_html(
			sprintf(
				/* translators: %d activated sites */
				_n( '%d site (unlimited)', '%d sites (unlimited)', $site_count, 'buddypress-contact-me' ),
				$site_count
			)
		);
}

// Status pill spec - mirrors EDD's `license` slug.
$status_map = array(
	'valid'         => array( 'class' => 'is-active',   'label' => __( 'Active', 'buddypress-contact-me' ) ),
	'expired'       => array( 'class' => 'is-expired',  'label' => __( 'Expired', 'buddypress-contact-me' ) ),
	'invalid'       => array( 'class' => 'is-invalid',  'label' => __( 'Invalid', 'buddypress-contact-me' ) ),
	'disabled'      => array( 'class' => 'is-invalid',  'label' => __( 'Disabled', 'buddypress-contact-me' ) ),
	'site_inactive' => array( 'class' => 'is-inactive', 'label' => __( 'Site inactive', 'buddypress-contact-me' ) ),
);
$status = isset( $status_map[ $snapshot['license'] ] )
	? $status_map[ $snapshot['license'] ]
	: array(
		'class' => $has_key ? 'is-pending' : 'is-inactive',
		'label' => $has_key ? __( 'Saved, not activated', 'buddypress-contact-me' ) : __( 'Not activated', 'buddypress-contact-me' ),
	);
?>
<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Plugin License', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Activate your license to receive automatic updates and priority support.', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<form method="post" class="bcm-license-form">
			<?php wp_nonce_field( 'edd_wbcom_contact_me_nonce', 'edd_wbcom_contact_me_nonce' ); ?>

			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">
						<label for="bcm-license-key"><?php esc_html_e( 'License key', 'buddypress-contact-me' ); ?></label>
					</th>
					<td>
						<input type="text"
							id="bcm-license-key"
							name="edd_wbcom_bp_contact_me_license_key"
							value="<?php echo esc_attr( $display_key ); ?>"
							class="regular-text"
							autocomplete="off"
							spellcheck="false"
							<?php echo $is_active ? 'readonly' : ''; ?>
							placeholder="<?php esc_attr_e( 'Paste your license key', 'buddypress-contact-me' ); ?>" />
						<p class="description">
							<?php
							if ( $is_active ) {
								esc_html_e( 'Deactivate first to change the key.', 'buddypress-contact-me' );
							} else {
								printf(
									wp_kses(
										/* translators: %s: URL to the Wbcom Designs profile page where the customer can copy their license key. */
										__( 'Find your key in your <a href="%s" target="_blank" rel="noopener noreferrer">Wbcom Designs account</a>.', 'buddypress-contact-me' ),
										array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) )
									),
									esc_url( 'https://wbcomdesigns.com/profile/' )
								);
							}
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Status', 'buddypress-contact-me' ); ?></th>
					<td>
						<span class="bcm-license-status <?php echo esc_attr( $status['class'] ); ?>"><?php echo esc_html( $status['label'] ); ?></span>
						<?php if ( $is_active || ( is_object( $data ) && isset( $data->expires ) ) ) : ?>
							<span class="bcm-license-meta-sep" aria-hidden="true">&middot;</span>
							<span class="bcm-license-meta">
								<strong><?php esc_html_e( 'Expires:', 'buddypress-contact-me' ); ?></strong>
								<?php echo $expires_text; // already escaped above. ?>
							</span>
						<?php endif; ?>
						<?php if ( $is_active || ( is_object( $data ) && isset( $data->site_count, $data->license_limit ) ) ) : ?>
							<span class="bcm-license-meta-sep" aria-hidden="true">&middot;</span>
							<span class="bcm-license-meta">
								<strong><?php esc_html_e( 'Activations:', 'buddypress-contact-me' ); ?></strong>
								<?php echo $activations_text; // already escaped above. ?>
							</span>
						<?php endif; ?>
						<?php if ( $expiring_soon ) : ?>
							<p class="bcm-license-renew-warn">
								<?php
								printf(
									/* translators: %d: days remaining. */
									esc_html( _n( 'Renews in %d day - renew to keep receiving updates.', 'Renews in %d days - renew to keep receiving updates.', $days_left, 'buddypress-contact-me' ) ),
									(int) $days_left
								);
								?>
							</p>
						<?php endif; ?>
					</td>
				</tr>
			</table>

			<div class="bcm-save-bar">
				<?php if ( $is_active ) : ?>
					<button type="submit" name="edd_BCM_license_deactivate" class="bcm-btn bcm-btn-danger">
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
</div>
