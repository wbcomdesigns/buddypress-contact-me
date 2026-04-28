<?php
/**
 * Settings tab: Notifications (BuddyPress + email).
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parent view provides $settings.
 *
 * @var array $settings
 */

$bp_on           = ! empty( $settings['bcm_allow_notification'] ) && 'yes' === $settings['bcm_allow_notification'];
$email_on        = ! empty( $settings['bcm_allow_email'] ) && 'yes' === $settings['bcm_allow_email'];
$admin_copy      = ! empty( $settings['bcm_allow_admin_copy_email'] ) && 'yes' === $settings['bcm_allow_admin_copy_email'];
$sender_copy     = ! empty( $settings['bcm_allow_sender_copy_email'] ) && 'yes' === $settings['bcm_allow_sender_copy_email'];
$bp_active       = function_exists( 'bp_is_active' );
$bp_notif_active = $bp_active && bp_is_active( 'notifications' );

// Sentinel inputs so the sanitizer can tell "user unchecked me" apart
// from "this tab never rendered me". The sanitizer inspects the keys
// listed here and explicitly clears any that were rendered-but-absent
// from $_POST. See class-bcm-admin.php::sanitize_settings() and
// Basecamp card 9823496113.
$tab_rendered_keys = array(
	'bcm_allow_notification',
	'bcm_allow_email',
	'bcm_allow_admin_copy_email',
	'bcm_allow_sender_copy_email',
);
foreach ( $tab_rendered_keys as $tab_rendered_key ) :
	?>
	<input type="hidden" name="bcm_admin_general_setting[bcm_tab_rendered_keys][]" value="<?php echo esc_attr( $tab_rendered_key ); ?>">
<?php endforeach; ?>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Notify the Recipient', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'How the member receiving a message finds out about it. Turn on both channels if your community is split between people who live in the BuddyPress notifications bell and people who only check email.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'In-site notification', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_allow_notification]"
						value="yes"
						<?php checked( $bp_on ); ?>
						<?php disabled( ! $bp_notif_active ); ?>>
					<?php esc_html_e( 'Send a BuddyPress notification to the recipient', 'buddypress-contact-me' ); ?>
				</label>
				<?php if ( ! $bp_notif_active ) : ?>
					<p class="description" style="color: var(--bcm-admin-danger);">
						<?php esc_html_e( 'The BuddyPress "Notifications" component is not active. Enable it in BuddyPress → Settings → Components to use this option.', 'buddypress-contact-me' ); ?>
					</p>
				<?php else : ?>
					<p class="description"><?php esc_html_e( 'Appears in the member\'s notifications bell the next time they visit the site.', 'buddypress-contact-me' ); ?></p>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Email notification', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_allow_email]"
						value="yes"
						<?php checked( $email_on ); ?>>
					<?php esc_html_e( 'Email the recipient when a new message arrives', 'buddypress-contact-me' ); ?>
				</label>
				<?php
				$bcm_email_term = function_exists( 'get_term_by' ) ? get_term_by( 'slug', 'bcm-contact-message', 'bp-email-type' ) : false;
				$bcm_email_id   = 0;
				if ( $bcm_email_term ) {
					// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Admin-only, runs once per settings render.
					$bcm_email_posts = get_posts(
						array(
							'post_type'   => 'bp-email',
							'tax_query'   => array(
								array(
									'taxonomy' => 'bp-email-type',
									'field'    => 'term_id',
									'terms'    => (int) $bcm_email_term->term_id,
								),
							),
							'numberposts' => 1,
							'fields'      => 'ids',
						)
					);
					$bcm_email_id    = ! empty( $bcm_email_posts ) ? (int) $bcm_email_posts[0] : 0;
				}
				?>
				<p class="description">
					<?php
					if ( $bcm_email_id ) {
						printf(
							/* translators: %s: edit-email URL */
							wp_kses_post( __( 'The subject, body, and branding use BuddyPress\'s email template. <a href="%s">Edit the contact-message email →</a>', 'buddypress-contact-me' ) ),
							esc_url( get_edit_post_link( $bcm_email_id ) )
						);
					} else {
						esc_html_e( 'Customize subject, body, and branding under Dashboard → Emails.', 'buddypress-contact-me' );
					}
					?>
				</p>
			</td>
		</tr>
	</table>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Send a Copy To…', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Who else should receive a copy of the message when it is sent. Useful for monitoring, support audit trails, or giving the sender a receipt.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Site admin', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_allow_admin_copy_email]"
						value="yes"
						<?php checked( $admin_copy ); ?>>
					<?php esc_html_e( 'BCC the site admin on every message', 'buddypress-contact-me' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Helpful for moderation — you can see what members are sending each other without opening every conversation.', 'buddypress-contact-me' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Sender', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_allow_sender_copy_email]"
						value="yes"
						<?php checked( $sender_copy ); ?>>
					<?php esc_html_e( 'Also send a copy to the sender so they have a record', 'buddypress-contact-me' ); ?>
				</label>
			</td>
		</tr>
	</table>
</div>
