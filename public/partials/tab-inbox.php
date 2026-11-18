<?php
/**
 * Inbox: list of contact messages the current user has received.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bcm_user_id  = get_current_user_id();
$bcm_per_page = 10;
$bcm_filter   = isset( $_GET['filter'] ) && 'unread' === $_GET['filter'] ? 'unread' : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$bcm_total    = BCM_Messages_Repo::count_for_recipient( $bcm_user_id );
$bcm_unread_n = BCM_Messages_Repo::count_unread_for_recipient( $bcm_user_id );
$bcm_pages    = (int) ceil( max( 1, 'unread' === $bcm_filter ? $bcm_unread_n : $bcm_total ) / $bcm_per_page );
// A page number past the end (an old link after deletes) shows the last page.
$bcm_page     = isset( $_GET['cpage'] ) ? min( $bcm_pages, max( 1, (int) $_GET['cpage'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$bcm_messages = BCM_Messages_Repo::list_for_recipient( $bcm_user_id, $bcm_per_page, $bcm_page, 'unread' === $bcm_filter );

// Batch-prefetch every sender on this page so the foreach below doesn't
// fire N+1 user lookups against a cold cache. cache_users() primes the
// userdata + usermeta caches in a single query each, so subsequent
// bp_core_get_user_displayname() / bp_members_get_user_url() calls are
// served from object cache instead of hitting the DB per row.
if ( ! empty( $bcm_messages ) ) {
	$bcm_sender_ids = array_values(
		array_unique(
			array_filter(
				array_map(
					static function ( $m ) {
						return (int) $m->sender;
					},
					$bcm_messages
				)
			)
		)
	);
	if ( ! empty( $bcm_sender_ids ) && function_exists( 'cache_users' ) ) {
		cache_users( $bcm_sender_ids );
	}
}
$bcm_profile_url     = function_exists( 'bp_members_get_user_url' )
	? bp_members_get_user_url( $bcm_user_id )
	: bp_core_get_user_domain( $bcm_user_id );
$bcm_prefs_url       = trailingslashit( $bcm_profile_url ) . BCM_Frontend_Nav::SLUG . '/' . BCM_Frontend_Nav::SUB_PREFERENCES . '/';
$bcm_contact_url     = trailingslashit( $bcm_profile_url ) . BCM_Frontend_Nav::SLUG . '/';
$bcm_intro_dismissed = (bool) get_user_meta( $bcm_user_id, 'bcm_intro_dismissed', true );

if ( ! $bcm_intro_dismissed ) :
	?>
	<div class="bcm-info" data-bcm-info>
		<div class="bcm-info__icon" aria-hidden="true">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
		</div>
		<div class="bcm-info__body">
			<p class="bcm-info__title"><?php esc_html_e( 'How Contact works', 'buddypress-contact-me' ); ?></p>
			<p class="bcm-info__text">
				<?php esc_html_e( 'Members and visitors can send you a private message from your profile, no email address needed. New messages land here, trigger a notification, and can also send you an email.', 'buddypress-contact-me' ); ?>
			</p>
			<p class="bcm-info__links">
				<a href="<?php echo esc_url( $bcm_prefs_url ); ?>"><?php esc_html_e( 'Manage preferences', 'buddypress-contact-me' ); ?></a>
				<span aria-hidden="true">·</span>
				<button type="button" class="button-link bcm-copy-link" data-url="<?php echo esc_attr( $bcm_contact_url ); ?>">
					<?php esc_html_e( 'Copy my contact link', 'buddypress-contact-me' ); ?>
				</button>
			</p>
		</div>
		<button type="button" class="bcm-info__close" data-bcm-info-dismiss data-nonce="<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>" aria-label="<?php esc_attr_e( 'Dismiss this tip', 'buddypress-contact-me' ); ?>">
			<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>
		</button>
	</div>
	<?php
endif;

$bcm_all_url    = trailingslashit( $bcm_profile_url ) . BCM_Frontend_Nav::SLUG . '/' . BCM_Frontend_Nav::SUB_INBOX . '/';
$bcm_unread_url = add_query_arg( 'filter', 'unread', $bcm_all_url );

if ( empty( $bcm_messages ) && 'all' === $bcm_filter ) :
	?>
	<div class="bcm-empty" role="status">
		<div class="bcm-empty__icon" aria-hidden="true">
			<svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
				<path d="M4 4h16v12H5.5L4 17.5z" />
				<path d="M8 9h8" />
				<path d="M8 12h5" />
			</svg>
		</div>
		<h3 class="bcm-empty__title" tabindex="-1"><?php esc_html_e( 'Your inbox is quiet for now.', 'buddypress-contact-me' ); ?></h3>
		<p class="bcm-empty__body">
			<?php esc_html_e( "When someone sends you a message from your profile's Contact tab, it'll show up here.", 'buddypress-contact-me' ); ?>
		</p>
		<p class="bcm-empty__actions">
			<a class="button" href="<?php echo esc_url( $bcm_profile_url ); ?>">
				<?php esc_html_e( 'View my profile', 'buddypress-contact-me' ); ?>
			</a>
			<button type="button" class="button button-link bcm-copy-link" data-url="<?php echo esc_attr( trailingslashit( $bcm_profile_url ) . BCM_Frontend_Nav::SLUG . '/' ); ?>">
				<?php esc_html_e( 'Copy my contact link', 'buddypress-contact-me' ); ?>
			</button>
		</p>
	</div>
	<?php
	return;
endif;

?>
<div class="bcm-inbox" data-bcm-inbox>
	<header class="bcm-inbox__header">
		<h3 class="bcm-inbox__title" tabindex="-1">
			<?php
			if ( 'unread' === $bcm_filter ) {
				/* translators: %d: unread message count */
				printf( esc_html( _n( '%d unread message', '%d unread messages', $bcm_unread_n, 'buddypress-contact-me' ) ), (int) $bcm_unread_n );
			} else {
				/* translators: %d: total messages */
				printf( esc_html( _n( '%d message', '%d messages', $bcm_total, 'buddypress-contact-me' ) ), (int) $bcm_total );
			}
			?>
		</h3>
		<nav class="bcm-inbox__filters" aria-label="<?php esc_attr_e( 'Filter messages', 'buddypress-contact-me' ); ?>">
			<a class="bcm-inbox__filter<?php echo 'all' === $bcm_filter ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $bcm_all_url ); ?>" data-bcm-filter="all"
				<?php echo 'all' === $bcm_filter ? 'aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'All', 'buddypress-contact-me' ); ?>
				<span class="bcm-inbox__filter-count"><?php echo (int) $bcm_total; ?></span>
			</a>
			<a class="bcm-inbox__filter<?php echo 'unread' === $bcm_filter ? ' is-active' : ''; ?>"
				href="<?php echo esc_url( $bcm_unread_url ); ?>" data-bcm-filter="unread"
				<?php echo 'unread' === $bcm_filter ? 'aria-current="page"' : ''; ?>>
				<?php esc_html_e( 'Unread', 'buddypress-contact-me' ); ?>
				<span class="bcm-inbox__filter-count"><?php echo (int) $bcm_unread_n; ?></span>
			</a>
		</nav>
	</header>

	<?php if ( empty( $bcm_messages ) ) : // Only the Unread filter gets here empty. ?>
		<div class="bcm-empty bcm-empty--inline" role="status">
			<p class="bcm-empty__body">
				<?php esc_html_e( 'You have no unread messages right now.', 'buddypress-contact-me' ); ?>
			</p>
			<p class="bcm-empty__actions">
				<a class="button" href="<?php echo esc_url( $bcm_all_url ); ?>">
					<?php esc_html_e( 'Back to all messages', 'buddypress-contact-me' ); ?>
				</a>
			</p>
		</div>
	<?php else : ?>

		<ul class="bcm-messages" role="list">
			<?php
			foreach ( $bcm_messages as $msg ) :
				$is_unread   = ! empty( $msg->is_unread );
				$sender_deleted = $msg->sender && ! get_userdata( (int) $msg->sender ); // Users are primed by cache_users() above.
				$sender_id      = $sender_deleted ? 0 : (int) $msg->sender;
				$sender_name    = $sender_id
					? bp_core_get_user_displayname( $sender_id )
					: ( $sender_deleted ? __( 'Deleted member', 'buddypress-contact-me' ) : $msg->name );
				if ( $sender_id && function_exists( 'bp_core_fetch_avatar' ) ) {
					$avatar = bp_core_fetch_avatar(
						array(
							'item_id' => $sender_id,
							'type'    => 'thumb',
						)
					);
				} else {
					$avatar = get_avatar( ( ! $sender_deleted && $msg->email ) ? $msg->email : '', 48, 'mystery' );
				}
				$sender_url   = $sender_id
					? ( function_exists( 'bp_members_get_user_url' ) ? bp_members_get_user_url( $sender_id ) : bp_core_get_user_domain( $sender_id ) )
					: '';
				$stamp        = BCM_Messages_Repo::timestamp( $msg->datetime );
				$time_display = ( time() - $stamp < DAY_IN_SECONDS )
					? sprintf(
						/* translators: %s: time-ago string (e.g. "3 hours") */
						__( '%s ago', 'buddypress-contact-me' ),
						human_time_diff( $stamp )
					)
					: bcm_date_label( $msg->datetime, '', get_option( 'date_format' ) );
				/* translators: %s: sender name */
				$delete_label = sprintf( __( 'Delete message from %s', 'buddypress-contact-me' ), $sender_name );
				?>
				<li class="bcm-message<?php echo $is_unread ? ' bcm-message--unread' : ''; ?>" data-bcm-row="<?php echo esc_attr( $msg->id ); ?>">
					<div class="bcm-message__avatar">
						<?php if ( $sender_url ) : ?>
							<a href="<?php echo esc_url( $sender_url ); ?>" aria-label="<?php echo esc_attr( $sender_name ); ?>">
								<?php echo wp_kses_post( $avatar ); ?>
							</a>
						<?php else : ?>
							<?php echo wp_kses_post( $avatar ); ?>
						<?php endif; ?>
					</div>
					<div class="bcm-message__body">
						<p class="bcm-message__from">
							<?php if ( $sender_url ) : ?>
								<a href="<?php echo esc_url( $sender_url ); ?>"><?php echo esc_html( $sender_name ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $sender_name ); ?>
								<?php if ( ! $sender_deleted ) : ?>
								<span class="bcm-badge bcm-badge--guest"><?php esc_html_e( 'Guest', 'buddypress-contact-me' ); ?></span>
								<?php endif; ?>
							<?php endif; ?>
							<?php if ( $is_unread ) : ?>
								<span class="bcm-message__badge"><?php esc_html_e( 'New', 'buddypress-contact-me' ); ?></span>
							<?php endif; ?>
						</p>
						<a class="bcm-message__subject" href="<?php echo esc_url( trailingslashit( $bcm_contact_url ) . BCM_Frontend_Nav::SUB_INBOX . '/' . $msg->id . '/' ); ?>">
							<?php echo esc_html( $msg->subject ); ?>
						</a>
						<p class="bcm-message__excerpt"><?php echo esc_html( wp_trim_words( $msg->message, 22, '…' ) ); ?></p>
						<p class="bcm-message__meta">
							<time datetime="<?php echo esc_attr( gmdate( 'c', $stamp ) ); ?>"><?php echo esc_html( $time_display ); ?></time>
						</p>
					</div>
					<div class="bcm-message__actions">
						<button type="button" class="bcm-message__action bcm-message__action--delete" data-bcm-delete="<?php echo esc_attr( $msg->id ); ?>" title="<?php esc_attr_e( 'Delete', 'buddypress-contact-me' ); ?>">
							<span class="dashicons dashicons-trash" aria-hidden="true"></span>
							<span class="bcm-sr-only"><?php echo esc_html( $delete_label ); ?></span>
						</button>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>

		<?php if ( $bcm_pages > 1 ) : ?>
		<nav class="bcm-pagination" aria-label="<?php esc_attr_e( 'Inbox pagination', 'buddypress-contact-me' ); ?>">
			<?php
			echo wp_kses_post(
				paginate_links(
					array(
						'base'      => add_query_arg( 'cpage', '%#%' ),
						'format'    => '',
						'current'   => $bcm_page,
						'total'     => $bcm_pages,
						'prev_text' => __( '« Prev', 'buddypress-contact-me' ),
						'next_text' => __( 'Next »', 'buddypress-contact-me' ),
					)
				)
			);
			?>
		</nav>
	<?php endif; ?>

	<?php endif; // End not-empty-unread block. ?>
</div>
