<?php

/**
 * Provide a public-facing view for the plugin.
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
$get_contact_row          = $wpdb->prepare(
	"SELECT * FROM $bp_contact_me_table_name WHERE `reciever` = %d ORDER BY `id` DESC",
	$loggedin_user_id
);
$get_contact_allrow       = $wpdb->get_results( $get_contact_row, ARRAY_A );
?>
<div class="bp-contact-me-details">
	<?php if ( $get_contact_allrow ) : ?>
		<div class="bp-contact-me-loader" style="display:none;">
			<div class="bp-contact-me-loader-img">
				<img src="<?php echo esc_url( BUDDYPRESS_CONTACT_ME_PLUGIN_URL . 'public/images/loader.gif' ); ?>" alt="<?php esc_attr_e( 'Loading...', 'buddypress-contact-me' ); ?>" />
			</div>
		</div>
		<form method="post">
			<table class="bp_contact-me-messages">
				<thead>
					<tr>
						<th class="contact-me-sender-id"><input type="checkbox" id="bcm-select-all-contact" /></th>
						<th class="contact-me-subject"><?php esc_html_e( 'Name', 'buddypress-contact-me' ); ?></th>
						<th class="contact-me-message"><?php esc_html_e( 'Message', 'buddypress-contact-me' ); ?></th>
						<th class="contact-me-btn"><?php esc_html_e( 'Action', 'buddypress-contact-me' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $get_contact_allrow as $get_contact_allrow_val ) : ?>
						<?php
						$sender_id      = $get_contact_allrow_val['sender'];
						$subject        = $get_contact_allrow_val['subject'];
						$message        = $get_contact_allrow_val['message'];
						$bcm_first_name = $sender_id ? bp_core_get_user_displayname( $sender_id ) : $get_contact_allrow_val['name'];
						?>
						<tr>
							<td class="contact-me-sender-id"><input type="checkbox" name="bcm_messages[]" class="bcm-all-check" value="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" /></td>
							<td class="user_displayname">
								 <?php if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) { ?>
								<a href="<?php echo esc_url( bp_members_get_user_url( $sender_id ) ); ?>" title="<?php echo esc_attr( bp_core_get_user_displayname( $sender_id ) ); ?>">
										<?php
										echo wp_kses_post(
											bp_core_fetch_avatar(
												array(
													'item_id' => $sender_id,
													'type' => 'full',
												)
											)
										);
										echo esc_html( $bcm_first_name );
										?>
								</a>
								<?php } else { ?>
									<a href="<?php echo esc_url( bp_core_get_user_domain( $sender_id ) ); ?>" title="<?php echo esc_attr( bp_core_get_user_displayname( $sender_id ) ); ?>">
									 <?php
										echo wp_kses_post(
											bp_core_fetch_avatar(
												array(
													'item_id' => $sender_id,
													'type' => 'full',
												)
											)
										);
									 echo esc_html( $bcm_first_name );
										?>
								</a>
							<?php	} ?>
							</td>
							<td>
								<div class="bcm-user-subject"><?php echo esc_html( $subject ); ?></div>
								<div class="bcm-user-message"><?php echo esc_html( $message ); ?></div>
							</td>
							<td>
								<div class="bcm_action">
									<div class="bcm_action_btn">
										<span class="dashicons dashicons-visibility bcm_message_seen" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
										<small class="bcm-tooltip-text"><?php esc_html_e( 'View Message', 'buddypress-contact-me' ); ?></small>
									</div>
									<button class="bcm_action_btn" id="bcm_message_delete" type="button">
										<span class="dashicons dashicons-dismiss bcm_message_delete" data-id="<?php echo esc_attr( $get_contact_allrow_val['id'] ); ?>" aria-hidden="true"></span>
										<small class="bcm-tooltip-text"><?php esc_html_e( 'Delete Message', 'buddypress-contact-me' ); ?></small>
									</button>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
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
	<?php else : ?>
		<div class="bp-contact-me-container contact-me-not-found">
			<div id="message" class="info bp-feedback bp-messages bp-template-notice">
				<span class="bp-icon" aria-hidden="true"></span>
				<p><?php esc_html_e( 'Unfortunately, there are no contact messages found.', 'buddypress-contact-me' ); ?></p>
			</div>
		</div>
	<?php endif; ?>
</div>
