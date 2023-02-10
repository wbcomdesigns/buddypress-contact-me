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
<div class="bp-contact-me-detials">
<h2 class="bp-screen-title"><?php echo esc_html( 'Contact Message' ); ?></h2>
<?php if ( $get_contact_allrow ) { ?>
<table class="bp_contact-me-messages">
		<thead>
			<tr>
				<th class="contact-me-sender-id"><?php esc_html_e( 'Id', 'bp-contact-me' ); ?></th>                
				<th class="contact-me-subject"><?php esc_html_e( 'First Name', 'bp-contact-me' ); ?></th>
				<th class="contact-me-message"><?php esc_html_e( 'Last Name', 'bp-contact-me' ); ?></th>
				<th class="contact-me-btn"><?php esc_html_e( 'Friend Request', 'bp-contact-me' ); ?></th>            
				<th class="contact-me-btn"><?php esc_html_e( 'Private Message', 'bp-contact-me' ); ?></th>            
			</tr>
		</thead>
		<tbody>
			<?php
			$id = 1;
			foreach ( $get_contact_allrow as $get_contact_allrow_val ) {
				$sender_id = $get_contact_allrow_val['sender'];
				$subject   = $get_contact_allrow_val['subject'];
				$message   = $get_contact_allrow_val['message'];
				if ( 0 != $sender_id ) {
					$bcm_first_name = get_user_meta( $sender_id, 'first_name', true );
					$bcm_last_name  = get_user_meta( $sender_id, 'last_name', true );
				}else{
					$bcm_first_name = $get_contact_allrow_val['first_name'];
					$bcm_last_name  = $get_contact_allrow_val['last_name'];
				}
				?>
			<tr>
				<td><?php echo esc_html( $id++ ); ?></td>
				<td><?php echo esc_html( $bcm_first_name ); ?></td>
				<td><?php echo esc_html( $bcm_last_name ); ?></td>
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
						<?php
						while ( bp_members() ) :
							bp_the_member();
							?>
							<div class="add_friend_button" <?php bp_member_class( array( 'item-entry' ) ); ?> data-bp-item-id="<?php bp_member_user_id(); ?>" data-bp-item-component="members">
							<?php
								echo wp_kses_post( bp_get_add_friend_button( bp_get_member_user_id() ) );
							?>
							</li>
							<?php
						endwhile;
						?>
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
				<?php
			}
			?>
		</tbody>
</table>
</div>
<?php } else { ?>
	<div class="bp-contact-me-container contact-me-not-found">
	<div id="message" class="info bp-feedback bp-messages bp-template-notice">
		<span class="bp-icon" aria-hidden="true"></span>
		<p><?php echo esc_html( 'No any contact message found.' ); ?></p>
	</div>
	</div>
	<?php
}
