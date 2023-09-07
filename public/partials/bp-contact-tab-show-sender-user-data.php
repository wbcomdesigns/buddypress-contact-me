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
$get_contact_row          = "SELECT * FROM $bp_contact_me_table_name  WHERE `reciever` = $loggedin_user_id ORDER BY `id` DESC";
$get_contact_allrow       = $wpdb->get_results( $get_contact_row, ARRAY_A );
?>
<div class="bp-contact-me-detials">
<?php if ( $get_contact_allrow ) { ?>
	<div class="bp-contact-me-loader" style="display:none;">
		<div class="bp-contact-me-loader-img">
			<img src="<?php echo esc_url( BUDDYPRESS_CONTACT_ME_PLUGIN_URL . '/public/images/loader.gif' ); ?>"/>
		</div>
	</div>
	<form method="post">		
	<table class="bp_contact-me-messages">
			<thead>
				<tr>
					<th class="contact-me-sender-id"><input type="checkbox" id="bcm-select-all-contact"/></th>                
					<th class="contact-me-subject"><?php esc_html_e( 'Name', 'buddypress-contact-me' ); ?></th>
					<th class="contact-me-message"><?php esc_html_e( 'Message', 'buddypress-contact-me' ); ?></th>
					<th class="contact-me-btn"><?php esc_html_e( 'Action', 'buddypress-contact-me' ); ?></th>            
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $get_contact_allrow as $get_contact_allrow_val ) {
					$sender_id = $get_contact_allrow_val['sender'];
					$subject   = $get_contact_allrow_val['subject'];
					$message   = $get_contact_allrow_val['message'];
					if ( 0 != $sender_id ) {
						$bcm_first_name = bp_core_get_user_displayname( $sender_id );
					} else {
						$bcm_first_name = $get_contact_allrow_val['name'];
					}
					?>
				<tr>
					<td class="contact-me-sender-id"><input type="checkbox" name="bcm_messages[]" class="bcm-all-check" value="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>"/></td>
					<td class="user_displayname">
						<a href="<?php echo esc_attr( bp_core_get_user_domain( $sender_id ) ); ?>" title="<?php echo esc_attr( bp_core_get_user_displayname( $sender_id ) ); ?>">
					<?php
						echo wp_kses_post(
							bp_core_fetch_avatar(
								array(
									'item_id' => $sender_id,
									'type'    => 'full',
								)
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
							<div class="bcm_action_btn">
								<span class="dashicons dashicons-visibility bcm_message_seen" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
								<small class="bcm-tooltip-text"><?php esc_html_e( 'View Message', 'buddypress-contact-me' ); ?></small>
							</div>
							<button class="bcm_action_btn" id="bcm_message_delete">
								<span class="dashicons dashicons-dismiss bcm_message_delete" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
								<small class="bcm-tooltip-text"><?php esc_html_e( 'Delete Message', 'buddypress-contact-me' ); ?></small>
							</button>
						</div>
					</td>
				</tr>
					<?php
				}
				?>
			</tbody>
	</table>
	<div class="bcm-contact-options-nav">
		<div class="select-wrap">
			<label class="bp-screen-reader-text" for="bcm-select">
				<?php esc_html_e( 'Select Bulk Action', 'buddypress-contact-me' ); ?>
			</label>
			<select name="bcm_contact_bulk_action" id="bcm-select">
				<option value="" selected="selected"><?php esc_html_e( 'Bulk Actions', 'buddypress-contact-me' ); ?></option>
				<option value="delete"><?php esc_html_e( 'Delete', 'buddypress-contact-me' ); ?></option>
			</select>
		</div>
		<input type="submit" id="bcm-bulk-manage" class="button action" value="<?php esc_attr_e( 'Apply', 'buddypress-contact-me' ); ?>">
	</div>	
	<?php wp_nonce_field( 'bcm_contact_bulk_nonce', 'bcm_contact_bulk_nonce' ); ?>
	</form>
</div>
	<?php
} else {
	?>
	<div class="bp-contact-me-container contact-me-not-found">
	<div id="message" class="info bp-feedback bp-messages bp-template-notice">
		<span class="bp-icon" aria-hidden="true"></span>
		<p><?php echo esc_html( 'No any contact message found.' ); ?></p>
	</div>
	</div>
	</div>
	<?php } ?>
