<?php
/**
 * Settings tab: Email template.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

/** @var array $settings */

$subject       = isset( $settings['bcm_email_subject'] ) ? (string) $settings['bcm_email_subject'] : '';
$content       = isset( $settings['bcm_email_content'] ) ? (string) $settings['bcm_email_content'] : '';
$multi_copy_on = ! empty( $settings['bcm_multiple_user_copy_email'] ) && 'yes' === $settings['bcm_multiple_user_copy_email'];
$from_email    = isset( $settings['bcm_user_email'] ) ? (string) $settings['bcm_user_email'] : '';
$admin_email   = (string) get_option( 'admin_email' );
?>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Message the Recipient Gets', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'This is the email that is sent to members when someone contacts them. Use the placeholders below to include the sender\'s name, message, and your site name automatically.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="bcm_user_email"><?php esc_html_e( 'From address', 'buddypress-contact-me' ); ?></label></th>
			<td>
				<input type="email"
					id="bcm_user_email"
					name="bcm_admin_general_setting[bcm_user_email]"
					value="<?php echo esc_attr( $from_email ); ?>"
					class="regular-text"
					style="width: 100%; max-width: 520px;"
					placeholder="<?php echo esc_attr( $admin_email ); ?>">
				<p class="description">
					<?php
					printf(
						/* translators: %s: site admin email used as fallback. */
						esc_html__( 'Email address that recipients will see in the "From" header. Leave blank to use the site admin email (%s).', 'buddypress-contact-me' ),
						'<code>' . esc_html( $admin_email ) . '</code>'
					);
					?>
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bcm_email_subject"><?php esc_html_e( 'Subject line', 'buddypress-contact-me' ); ?></label></th>
			<td>
				<input type="text"
					id="bcm_email_subject"
					name="bcm_admin_general_setting[bcm_email_subject]"
					value="<?php echo esc_attr( $subject ); ?>"
					class="regular-text"
					style="width: 100%; max-width: 520px;">
				<p class="description"><?php esc_html_e( 'Keep it short and action-oriented. Members scan subject lines — they will open the email if it names the sender or what they want.', 'buddypress-contact-me' ); ?></p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="bcm_email_content"><?php esc_html_e( 'Message body', 'buddypress-contact-me' ); ?></label></th>
			<td>
				<textarea id="bcm_email_content"
					name="bcm_admin_general_setting[bcm_email_content]"
					rows="10"
					class="large-text"><?php echo esc_textarea( $content ); ?></textarea>
				<p class="description">
					<?php esc_html_e( 'Available placeholders:', 'buddypress-contact-me' ); ?>
					<code>{user_name}</code> &mdash; <?php esc_html_e( 'recipient username', 'buddypress-contact-me' ); ?>
					<code>{sender_user_name}</code> &mdash; <?php esc_html_e( 'sender display name', 'buddypress-contact-me' ); ?>
					<code>{site_name}</code> &mdash; <?php esc_html_e( 'your site name', 'buddypress-contact-me' ); ?>
				</p>
			</td>
		</tr>
	</table>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Multi-Recipient Delivery', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Controls how emails are addressed when a sender includes several recipients in one message.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Show other recipients', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_multiple_user_copy_email]"
						value="yes"
						<?php checked( $multi_copy_on ); ?>>
					<?php esc_html_e( 'Put every recipient in the To: field so they can see who else received the same message', 'buddypress-contact-me' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Off = each recipient gets their own individual email (BCC style, private). On = a single email to everyone with all addresses visible.', 'buddypress-contact-me' ); ?></p>
			</td>
		</tr>
	</table>
</div>
