<?php
/**
 *
 * This file is used for rendering and saving plugin settings logged-in user.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bcm_admin_general_setting = get_option( 'bcm_admin_general_setting' );
$bcm_admin_email           = get_option( 'admin_email' );
$bcm_users_except_admin               = get_users(
	array(
		'role__not_in'   => 'administrator',
		'fields' => array( 'ID', 'display_name' ),
	)
);
$user_roles                = array_reverse( get_editable_roles() );
?>
<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'General Setting', 'buddypress-private-community-pro' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap">
	<form method="post" action="options.php">
		<?php
		settings_fields( 'bcm_admin_general_email_notification_setting' );
		do_settings_sections( 'bcm_admin_general_setting' );
		?>
		<div class="form-table contact-me-general">
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_allow_notification"><?php esc_html_e( 'BuddyPress Notifications', 'buddypress-contact-me' ); ?></label>
					<p class="description">
						<?php esc_html_e( 'Enable this option, if you want the member to receive a BuddyPress Notification when someone contact you.', 'buddypress-contact-me' ); ?>
					</p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="wb-switch">
						<input name='bcm_admin_general_setting[bcm_allow_notification]' type='checkbox' id="bcm_notification" class="bcm_notification" value='yes' <?php ( isset( $bcm_admin_general_setting['bcm_allow_notification'] ) ) ? checked( $bcm_admin_general_setting['bcm_allow_notification'], 'yes' ) : ''; ?>/>
						<div class="wb-slider wb-round"></div>
					</label>					
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_allow_email">
						<?php esc_html_e( 'Emails Notification', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, if you want the member to receive an email when someone contact you', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="bcm-switch">
						<input type="checkbox" id="bcm_allow_email" name="bcm_admin_general_setting[bcm_allow_email]" value="yes"<?php ( isset( $bcm_admin_general_setting['bcm_allow_email'] ) ) ? checked( $bcm_admin_general_setting['bcm_allow_email'], 'yes' ) : ''; ?>>
						<div class="bcm-slider bcm-round"></div>
					</label>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_allow_email">
						<?php esc_html_e( 'Enable/Disable Contact Tab', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, if you want the member to receive an email when someone contact you', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="bcm-switch">
						<input type="checkbox" id="bcm_allow_contact_tab" name="bcm_admin_general_setting[bcm_allow_contact_tab]" value="yes"<?php ( isset( $bcm_admin_general_setting['bcm_allow_contact_tab'] ) ) ? checked( $bcm_admin_general_setting['bcm_allow_contact_tab'], 'yes' ) : ''; ?>>
						<div class="bcm-slider bcm-round"></div>
					</label>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'User Email', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'User change the sender mail id.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="bcm-switch">
						<input type="text" id="bcm_user_email" name="bcm_admin_general_setting[bcm_user_email]" value="<?php echo isset( $bcm_admin_general_setting['bcm_user_email'] ) && ! empty( $bcm_admin_general_setting['bcm_user_email'] ) ? esc_html( $bcm_admin_general_setting['bcm_user_email'] ) : esc_html( $bcm_admin_email ); ?>">
						<div class="bcm-slider bcm-round"></div>
					</label>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'Allow sender to receive a copy of email?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, copy of mail also send to the sender.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="bcm-switch">
						<input type="checkbox" id="bcm_sender_copy_email" name="bcm_admin_general_setting[bcm_allow_sender_copy_email]" value="yes"<?php ( isset( $bcm_admin_general_setting['bcm_allow_sender_copy_email'] ) ) ? checked( $bcm_admin_general_setting['bcm_allow_sender_copy_email'], 'yes' ) : 'no'; ?>>
						<div class="bcm-slider bcm-round"></div>
					</label>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'Send a copy to the admin?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, copy of mail also send to the admin.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<label class="bcm-switch">
						<input type="checkbox" id="bcm_admin_copy_email" name="bcm_admin_general_setting[bcm_allow_admin_copy_email]" value="yes"<?php ( isset( $bcm_admin_general_setting['bcm_allow_admin_copy_email'] ) ) ? checked( $bcm_admin_general_setting['bcm_allow_admin_copy_email'], 'yes' ) : 'no'; ?>>
						<div class="bcm-slider bcm-round"></div>
					</label>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'Send admin notification Emails to more people?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, copy of mail also send to the more people.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<select name="bcm_admin_general_setting[bcm_multiple_user_copy_email][]" id="bcm-multiple-user-copy-email" multiple>
						<?php foreach ( $bcm_users_except_admin as $bcm_user_except_admin ) : ?>
							<option value="<?php echo esc_attr( $bcm_user_except_admin->id ); ?>" <?php echo isset( $bcm_admin_general_setting['bcm_multiple_user_copy_email'] ) && in_array( $bcm_user_except_admin->id, $bcm_admin_general_setting['bcm_multiple_user_copy_email'] ) ? 'selected' : ''; ?>><?php echo esc_html( $bcm_user_except_admin->display_name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'Who can be contact? ', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, manage the contact with user roles.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<select name="bcm_admin_general_setting[bcm_who_contact][]" id="bcm-who-contacted" class="bcm_who_contact" multiple>
						<?php
						foreach ( $user_roles as $role => $details ) {
							$name = translate_user_role( $details['name'] );
							?>
							<option value="<?php echo esc_attr( $role ); ?>" <?php echo isset( $bcm_admin_general_setting['bcm_who_contact'] ) && in_array( $role, $bcm_admin_general_setting['bcm_who_contact'] ) ? 'selected' : ''; ?>><?php echo esc_html( $name ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
				<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_user_email">
						<?php esc_html_e( 'Who can be contacted? ', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enable this option, manage the contact with user roles.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<select name="bcm_admin_general_setting[bcm_who_contacted][]" id="bcm-who-contacted" class="bcm_who_contacted" multiple>
						<?php
						foreach ( $user_roles as $role => $details ) {
							$name = translate_user_role( $details['name'] );
							?>
							<option value="<?php echo esc_attr( $role ); ?>" <?php echo isset( $bcm_admin_general_setting['bcm_who_contacted'] ) && in_array( $role, $bcm_admin_general_setting['bcm_who_contacted'] ) ? 'selected' : ''; ?>><?php echo esc_html( $name ); ?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>					
			<div class="wbcom-settings-section-wrap">				
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_email_subject">
						<?php esc_html_e( 'Email Subject', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enter the subject line for email notification subject.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input id="bcm_email_subject" class="bcm_email_subject" name="bcm_admin_general_setting[bcm_email_subject]" value="<?php echo isset( $bcm_admin_general_setting['bcm_email_subject'] ) ? esc_html( $bcm_admin_general_setting['bcm_email_subject'] ) : ''; ?>" placeholder="{user_name} has contacted you.">
				</div>				
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_email_content">
						<?php esc_html_e( 'Email Body Content', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enter text to send email to the user', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<?php
					$bcm_default_content = "Hi {user_name},

										{sender_user_name} has contacted you.

										Click Here to check the message.

										You can also go to the contact form

										Thanks.";
						$settings = array(
							'media_buttons' => true,
							'editor_height' => 200,
							'textarea_name' => 'bcm_admin_general_setting[bcm_email_content]',
						);
						wp_editor( isset( $bcm_admin_general_setting['bcm_email_content'] ) && '' != $bcm_admin_general_setting['bcm_email_content'] ? $bcm_admin_general_setting['bcm_email_content'] : $bcm_default_content, 'bcm-email-content', $settings );
						?>
				</div>
								<code>
									{user_name} - <?php esc_html_e( 'User Name', 'buddypress-contact-me' ); ?> </br>
									{sender_user_name} - <?php esc_html_e( 'Sender User Name', 'buddypress-contact-me' ); ?>
								</code>
			</div>			
		</div>
		<?php submit_button(); ?>
	</form>
</div>
</div>
