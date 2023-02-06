<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link  https://wbcomdesigns.com/
 * @since 1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/public/partials
 */
global $wpdb;
$loggedin_user_id         = get_current_user_id();
$bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
$get_contact_row          = "SELECT * FROM $bp_contact_me_table_name  WHERE `reciever` = $loggedin_user_id";
$get_contact_allrow       = $wpdb->get_results( $get_contact_row, ARRAY_A );
?>
<label><h1><?php echo esc_html( 'Contact Message' ); ?></h1></label>
<?php if ( $get_contact_allrow ) { ?>
<table class="bp_contact-me-messages">
		<thead>
			<tr>
				<th><?php esc_html_e( 'Sender ID', 'bp-contact-me' ); ?></th>                
				<th><?php esc_html_e( 'Subject', 'bp-contact-me' ); ?></th>
				<th><?php esc_html_e( 'Message', 'bp-contact-me' ); ?></th>
				<th><?php esc_html_e( 'Friend Request', 'bp-contact-me' ); ?></th>            
				<th><?php esc_html_e( 'Private Message', 'bp-contact-me' ); ?></th>            
			</tr>
		</thead>
		<tbody>
			
			<?php
			foreach ( $get_contact_allrow as $get_contact_allrow_val ) {
				$sender_id = $get_contact_allrow_val['sender'];
				$subject   = $get_contact_allrow_val['subject'];
				$message   = $get_contact_allrow_val['message'];
				?>
			<tr>
				<td><?php echo $sender_id; ?></td>
				<td><?php echo $subject; ?></td>
				<td><?php echo $message; ?></td>
				<td>
					<?php
					$members_args = array(
						'include'         => $sender_id,
						'exclude'         => array( bp_loggedin_user_id() ),
						'populate_extras' => true,
						'search_terms'    => false,
					);
					if ( bp_has_members( $members_args ) && isset( $sender_id ) ) {
						?>
					<ul id="members-list" class="item-list members-list bp-contact-me-list" aria-live="polite" aria-relevant="all" aria-atomic="true">
						<?php
						while ( bp_members() ) :
							bp_the_member();
							?>
							<li <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">
								<div class="list-wrap">
									<div class="item">
										<div class="item-meta">
											<ul class="bp-contact-me-ul">
												<li>
													<?php
														echo wp_kses_post( bp_get_add_friend_button( bp_get_member_user_id() ) );
													?>
												</li>
											</ul>
										</div>
									</div>
								</div>
							</li>
							<?php
						endwhile;
						?>
					</ul>
						<?php
					} else {
						?>
						<p><?php esc_html_e( '-', 'buddypress-contact-me' ); ?></p>
						<?php
					}
					?>
				</td>
				<td>
					<?php if ( $sender_id ) { ?>
					<a href="<?php echo esc_url( bp_contact_me_get_send_private_message_link( $sender_id ) ); ?>" target="_blank">
						<input type="button" class="bp_contact_me_message" value="<?php echo esc_attr( 'Send Message' ); ?>">
					</a>
					<?php } else { ?>
						<p><?php esc_html_e( '-', 'buddypress-contact-me' ); ?></p>
						<?php } ?>
				</td>
			</tr>
			<?php } ?>
		</tbody>
</table>
<?php } else { ?>
	<div class="bp-member-blog-container bpmb-blog-posts">
	<div id="message" class="info bp-feedback bp-messages bp-template-notice">
		<span class="bp-icon" aria-hidden="true"></span>
		<p><?php echo esc_html( 'No any contact message found.' ); ?></p>
	</div>
	</div>
	<?php
}
