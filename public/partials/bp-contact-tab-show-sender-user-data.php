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
<?php if ( $get_contact_allrow ) { ?>
	<table class="bp_contact-me-messages">
			<thead>
				<tr>
					<th class="contact-me-sender-id"><input type="checkbox"/></th>                
					<th class="contact-me-subject"><?php esc_html_e( 'Name', 'bp-contact-me' ); ?></th>
					<th class="contact-me-message"><?php esc_html_e( 'Data Received', 'bp-contact-me' ); ?></th>
					<th class="contact-me-btn"><?php esc_html_e( 'Action', 'bp-contact-me' ); ?></th>            
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $get_contact_allrow as $get_contact_allrow_val ) {
					$sender_id = $get_contact_allrow_val['sender'];
					$subject   = $get_contact_allrow_val['subject'];
					$message   = $get_contact_allrow_val['message'];
					if ( 0 != $sender_id ) {
						$bcm_first_name = get_user_meta( $sender_id, 'first_name', true );
						$bcm_last_name  = get_user_meta( $sender_id, 'last_name', true );
					} else {
						$bcm_first_name = $get_contact_allrow_val['first_name'];
						$bcm_last_name  = $get_contact_allrow_val['last_name'];
					}
					?>
				<tr>
					<td><input type="checkbox" name="bcm_messages[]" value="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>"/></td>
					<td>
						<a href="<?php echo esc_attr( bp_core_get_user_domain( $sender_id ) ); ?>" title="<?php echo esc_attr( bp_core_get_user_displayname( $sender_id ) ); ?>">
					<?php
						echo bp_core_fetch_avatar(
							array(
								'item_id' => $sender_id,
								'type'    => 'full',
							)
						);
						echo esc_html( $bcm_first_name );
					?>
						</a>
					</td>
					<td>
						<div class="bcm-user-subject">
							<?php echo esc_html( $subject ); ?>
						</div>
						<div class="bcm-user-message">
							<?php echo esc_html( $message ); ?>
						</div>
					</td>
					<td>		
						<div class="bcm_action">
							<span class="dashicons dashicons-visibility bcm_message_seen" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
							<span><?php echo esc_html( ' | ' ); ?></span>
							<button id="bcm_message_delete">
								<span class="dashicons dashicons-dismiss bcm_message_delete" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
							</button>
						</div>
					</td>
				</tr>
					<?php
				}
				?>
			</tbody>
	</table>
</div>
<?php
} else { ?>
	<div class="bp-contact-me-container contact-me-not-found">
	<div id="message" class="info bp-feedback bp-messages bp-template-notice">
		<span class="bp-icon" aria-hidden="true"></span>
		<p><?php echo esc_html( 'No any contact message found.' ); ?></p>
	</div>
	</div>
	<?php
}
