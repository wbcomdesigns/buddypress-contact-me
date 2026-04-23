<?php
/**
 * Overview dashboard partial: rendered inside shell.php.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parent view provides $settings.
 *
 * @var array $settings bcm_admin_general_setting already loaded by parent view.
 */

global $wpdb;

// Contact messages table name (defined in the activator).
$table_name = $wpdb->prefix . 'contact_me';

// Count total messages, distinct senders, and distinct recipients.
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$total_messages = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$unique_senders = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT sender) FROM {$table_name} WHERE sender > 0" );
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$unique_recipients = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT reciever) FROM {$table_name} WHERE reciever > 0" );

// Count members who have the Contact Me button visible (usermeta contact_me_button = on).
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
$active_recipients = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s",
		'contact_me_button',
		'on'
	)
);

// Feature status flags.
$bp_notifications_on = ! empty( $settings['bcm_allow_notification'] ) && 'yes' === $settings['bcm_allow_notification'];
$email_on            = ! empty( $settings['bcm_allow_email'] ) && 'yes' === $settings['bcm_allow_email'];
$admin_copy_on       = ! empty( $settings['bcm_allow_admin_copy_email'] ) && 'yes' === $settings['bcm_allow_admin_copy_email'];
$sender_copy_on      = ! empty( $settings['bcm_allow_sender_copy_email'] ) && 'yes' === $settings['bcm_allow_sender_copy_email'];
$contact_tab_on      = ! empty( $settings['bcm_allow_contact_tab'] ) && 'yes' === $settings['bcm_allow_contact_tab'];

$notifications_url = admin_url( 'admin.php?page=buddypress-contact-me&tab=notifications' );
$email_url         = admin_url( 'admin.php?page=buddypress-contact-me&tab=email' );
$access_url        = admin_url( 'admin.php?page=buddypress-contact-me&tab=access' );
?>

<div class="bcm-stats-grid">
	<div class="bcm-stat">
		<p class="bcm-stat__label"><?php esc_html_e( 'Total Messages', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-stat__value"><?php echo esc_html( number_format_i18n( $total_messages ) ); ?></p>
		<p class="bcm-stat__trend"><?php esc_html_e( 'Messages sent through Contact Me', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-stat">
		<p class="bcm-stat__label"><?php esc_html_e( 'Unique Senders', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-stat__value"><?php echo esc_html( number_format_i18n( $unique_senders ) ); ?></p>
		<p class="bcm-stat__trend"><?php esc_html_e( 'Members who have contacted someone', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-stat">
		<p class="bcm-stat__label"><?php esc_html_e( 'Unique Recipients', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-stat__value"><?php echo esc_html( number_format_i18n( $unique_recipients ) ); ?></p>
		<p class="bcm-stat__trend"><?php esc_html_e( 'Members who have received messages', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-stat">
		<p class="bcm-stat__label"><?php esc_html_e( 'Active Recipients', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-stat__value"><?php echo esc_html( number_format_i18n( $active_recipients ) ); ?></p>
		<p class="bcm-stat__trend"><?php esc_html_e( 'Members with the Contact Me button on', 'buddypress-contact-me' ); ?></p>
	</div>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Current Configuration', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'What happens right now when someone sends a message from a member profile.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Profile contact tab', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $contact_tab_on ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;"><?php esc_html_e( 'On: members see a "Contact" tab on every profile', 'buddypress-contact-me' ); ?></span>
				<?php else : ?>
					<span style="color: var(--bcm-admin-text-3);"><?php esc_html_e( 'Off', 'buddypress-contact-me' ); ?></span>
					<a href="<?php echo esc_url( $access_url ); ?>" class="bcm-inline-hint"><?php esc_html_e( 'Turn on', 'buddypress-contact-me' ); ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'BuddyPress notifications', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $bp_notifications_on ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;"><?php esc_html_e( 'On: recipients get a BuddyPress notification for every new message', 'buddypress-contact-me' ); ?></span>
				<?php else : ?>
					<span style="color: var(--bcm-admin-text-3);"><?php esc_html_e( 'Off', 'buddypress-contact-me' ); ?></span>
					<a href="<?php echo esc_url( $notifications_url ); ?>" class="bcm-inline-hint"><?php esc_html_e( 'Turn on', 'buddypress-contact-me' ); ?></a>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Email notifications', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $email_on ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;"><?php esc_html_e( 'On: recipients get an email for every new message', 'buddypress-contact-me' ); ?></span>
				<?php else : ?>
					<span style="color: var(--bcm-admin-text-3);"><?php esc_html_e( 'Off', 'buddypress-contact-me' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Admin copy', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $admin_copy_on ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;"><?php esc_html_e( 'On: every message is copied to the site admin', 'buddypress-contact-me' ); ?></span>
				<?php else : ?>
					<span style="color: var(--bcm-admin-text-3);"><?php esc_html_e( 'Off', 'buddypress-contact-me' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php esc_html_e( 'Sender copy', 'buddypress-contact-me' ); ?></th>
			<td>
				<?php if ( $sender_copy_on ) : ?>
					<span style="color: var(--bcm-admin-success); font-weight: 600;"><?php esc_html_e( 'On: the sender also gets a copy of their own message', 'buddypress-contact-me' ); ?></span>
				<?php else : ?>
					<span style="color: var(--bcm-admin-text-3);"><?php esc_html_e( 'Off', 'buddypress-contact-me' ); ?></span>
				<?php endif; ?>
			</td>
		</tr>
	</table>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Quick Actions', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<div class="bcm-save-bar" style="flex-wrap: wrap;">
			<a href="<?php echo esc_url( $notifications_url ); ?>" class="bcm-btn bcm-btn-secondary">
				<span class="dashicons dashicons-bell" aria-hidden="true"></span>
				<?php esc_html_e( 'Configure Notifications', 'buddypress-contact-me' ); ?>
			</a>
			<a href="<?php echo esc_url( $email_url ); ?>" class="bcm-btn bcm-btn-secondary">
				<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'Edit Email Template', 'buddypress-contact-me' ); ?>
			</a>
			<a href="<?php echo esc_url( $access_url ); ?>" class="bcm-btn bcm-btn-secondary">
				<span class="dashicons dashicons-shield" aria-hidden="true"></span>
				<?php esc_html_e( 'Access Control', 'buddypress-contact-me' ); ?>
			</a>
		</div>
	</div>
</div>
