<?php
/**
 * Contact → Inbox → Single message view (/contact/inbox/{id}/).
 *
 * Full-page render with actions: Reply by email, Send private message,
 * Visit profile, Delete, Back to inbox.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bcm_user_id    = get_current_user_id();
$bcm_message_id = isset( $GLOBALS['bcm_view_message_id'] ) ? (int) $GLOBALS['bcm_view_message_id'] : 0;
$bcm_inbox_url  = function_exists( 'bp_members_get_user_url' )
	? bp_members_get_user_url( $bcm_user_id )
	: bp_core_get_user_domain( $bcm_user_id );
$bcm_inbox_url  = trailingslashit( $bcm_inbox_url ) . BCM_Frontend_Nav::SLUG . '/' . BCM_Frontend_Nav::SUB_INBOX . '/';

$bcm_msg = BCM_Messages_Repo::find( $bcm_message_id );

if ( ! $bcm_msg || (int) $bcm_msg->recipient !== $bcm_user_id ) {
	?>
	<div class="bcm-empty" role="status">
		<h3 class="bcm-empty__title"><?php esc_html_e( 'Message not found', 'buddypress-contact-me' ); ?></h3>
		<p class="bcm-empty__body"><?php esc_html_e( 'This message may have been deleted or it was never addressed to you.', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-empty__actions">
			<a class="button" href="<?php echo esc_url( $bcm_inbox_url ); ?>">
				<?php esc_html_e( 'Back to inbox', 'buddypress-contact-me' ); ?>
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

$bcm_sender_id    = (int) $bcm_msg->sender;
$bcm_sender_name  = $bcm_sender_id ? bp_core_get_user_displayname( $bcm_sender_id ) : $bcm_msg->name;
$bcm_sender_email = $bcm_sender_id ? get_the_author_meta( 'user_email', $bcm_sender_id ) : $bcm_msg->email;
$bcm_avatar       = $bcm_sender_id
	? bp_core_fetch_avatar(
		array(
			'item_id' => $bcm_sender_id,
			'type'    => 'full',
			'width'   => 64,
			'height'  => 64,
		)
	)
	: get_avatar( $bcm_sender_email ? $bcm_sender_email : '', 64, 'mystery' );

$bcm_profile_url = '';
$bcm_pm_url      = '';
if ( $bcm_sender_id ) {
	$bcm_profile_url = function_exists( 'bp_members_get_user_url' )
		? bp_members_get_user_url( $bcm_sender_id )
		: bp_core_get_user_domain( $bcm_sender_id );

	if ( function_exists( 'bp_is_active' ) && bp_is_active( 'messages' ) && function_exists( 'bp_loggedin_user_domain' ) ) {
		$slug_fn = ( function_exists( 'buddypress' ) && isset( buddypress()->buddyboss ) )
			? 'bp_core_get_username'
			: 'bp_members_get_user_slug';
		if ( function_exists( $slug_fn ) ) {
			$compose    = trailingslashit( bp_loggedin_user_domain() ) . bp_get_messages_slug() . '/compose/';
			$bcm_pm_url = wp_nonce_url( add_query_arg( 'r', $slug_fn( $bcm_sender_id ), $compose ) );
		}
	}
}

$bcm_mailto = $bcm_sender_email
	? 'mailto:' . rawurlencode( $bcm_sender_email ) . '?subject=' . rawurlencode( 'Re: ' . $bcm_msg->subject )
	: '';

$bcm_stamp        = strtotime( $bcm_msg->datetime ) ? strtotime( $bcm_msg->datetime ) : time();
$bcm_time_display = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $bcm_stamp );
?>

<div class="bcm-message-view">
	<p class="bcm-message-view__back">
		<a href="<?php echo esc_url( $bcm_inbox_url ); ?>">
			<span class="dashicons dashicons-arrow-left-alt" aria-hidden="true"></span>
			<?php esc_html_e( 'Back to inbox', 'buddypress-contact-me' ); ?>
		</a>
	</p>

	<header class="bcm-message-view__header">
		<div class="bcm-message-view__avatar">
			<?php if ( $bcm_profile_url ) : ?>
				<a href="<?php echo esc_url( $bcm_profile_url ); ?>">
					<?php echo wp_kses_post( $bcm_avatar ); ?>
				</a>
			<?php else : ?>
				<?php echo wp_kses_post( $bcm_avatar ); ?>
			<?php endif; ?>
		</div>
		<div class="bcm-message-view__meta">
			<h2 class="bcm-message-view__subject"><?php echo esc_html( $bcm_msg->subject ); ?></h2>
			<p class="bcm-message-view__from">
				<?php esc_html_e( 'From', 'buddypress-contact-me' ); ?>
				<?php if ( $bcm_profile_url ) : ?>
					<a href="<?php echo esc_url( $bcm_profile_url ); ?>"><?php echo esc_html( $bcm_sender_name ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $bcm_sender_name ); ?></span>
					<span class="bcm-badge bcm-badge--guest" title="<?php esc_attr_e( 'This person is not a registered member.', 'buddypress-contact-me' ); ?>">
						<?php esc_html_e( 'Guest', 'buddypress-contact-me' ); ?>
					</span>
				<?php endif; ?>
				<?php if ( $bcm_sender_email ) : ?>
					· <a href="mailto:<?php echo esc_attr( $bcm_sender_email ); ?>"><?php echo esc_html( $bcm_sender_email ); ?></a>
				<?php endif; ?>
			</p>
			<p class="bcm-message-view__date">
				<time datetime="<?php echo esc_attr( gmdate( 'c', $bcm_stamp ) ); ?>"><?php echo esc_html( $bcm_time_display ); ?></time>
			</p>
		</div>
	</header>

	<article class="bcm-message-view__body">
		<?php echo nl2br( esc_html( $bcm_msg->message ) ); ?>
	</article>

	<?php if ( ! $bcm_pm_url && ! $bcm_mailto ) : ?>
		<div class="bcm-alert bcm-alert--warning" role="status">
			<?php esc_html_e( "This sender didn't leave any contact details, so there's no way to reply. You can still visit their profile or delete the message below.", 'buddypress-contact-me' ); ?>
		</div>
	<?php endif; ?>

	<footer class="bcm-message-view__actions">
		<?php
		// Primary action: BP private message if sender is a member,
		// otherwise fall back to email reply. Everything else is secondary.
		$primary_class   = 'bcm-message-view__action bcm-message-view__action--primary';
		$secondary_class = 'bcm-message-view__action';
		?>

		<?php if ( $bcm_pm_url ) : ?>
			<a class="<?php echo esc_attr( $primary_class ); ?>" href="<?php echo esc_url( $bcm_pm_url ); ?>">
				<span class="dashicons dashicons-format-chat" aria-hidden="true"></span>
				<?php esc_html_e( 'Send private message', 'buddypress-contact-me' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $bcm_mailto ) : ?>
			<a class="<?php echo esc_attr( $bcm_pm_url ? $secondary_class : $primary_class ); ?>" href="<?php echo esc_url( $bcm_mailto ); ?>">
				<span class="dashicons dashicons-email-alt" aria-hidden="true"></span>
				<?php esc_html_e( 'Reply by email', 'buddypress-contact-me' ); ?>
			</a>
		<?php endif; ?>

		<?php if ( $bcm_profile_url ) : ?>
			<a class="<?php echo esc_attr( $secondary_class ); ?>" href="<?php echo esc_url( $bcm_profile_url ); ?>">
				<span class="dashicons dashicons-admin-users" aria-hidden="true"></span>
				<?php esc_html_e( 'Visit profile', 'buddypress-contact-me' ); ?>
			</a>
		<?php endif; ?>

		<button type="button" class="bcm-message-view__action bcm-message-view__action--danger" data-bcm-delete-page="<?php echo esc_attr( $bcm_msg->id ); ?>" data-bcm-back="<?php echo esc_attr( $bcm_inbox_url ); ?>">
			<span class="dashicons dashicons-trash" aria-hidden="true"></span>
			<?php esc_html_e( 'Delete', 'buddypress-contact-me' ); ?>
		</button>
	</footer>
</div>
