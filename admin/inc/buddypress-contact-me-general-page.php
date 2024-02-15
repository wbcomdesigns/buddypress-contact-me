<?php
/**
 * This file is used for rendering and saving plugin settings logged-in user.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$bcm_admin_general_setting = get_option( 'bcm_admin_general_setting' );
$bcm_admin_email           = get_option( 'admin_email' );
$bcm_users_except_admin    = get_users(
	array(
		'role__not_in' => 'administrator',
		'fields'       => array( 'ID', 'display_name' ),
	)
);
$user_roles                = array_reverse( get_editable_roles() );
?>
<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'General', 'buddypress-contact-me' ); ?></h3>
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
					<label for="bcm_allow_notification"><?php esc_html_e( 'BuddyPress notifications', 'buddypress-contact-me' ); ?></label>
					<p class="description">
						<?php esc_html_e( 'Activate this setting to allow members to receive BuddyPress notifications when someone contacts them.', 'buddypress-contact-me' ); ?>
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
						<?php esc_html_e( 'Emails notification', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Activate this option to ensure that members receive email notifications when someone contacts them.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Enable contact tab', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Activate this option if you wish to display the contact tab on a member BuddyPress profile.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'User email customization', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Users can modify the email address used as the sender identity.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Should the sender receive a copy of the email?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'By enabling this option, a duplicate of the email will also be sent to the sender.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Would you like to forward a copy to the admin?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'By enabling this option, a duplicate of the email will also be sent to the admin.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Send admin notification emails to more people?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Select the users to send a copy of the mail.', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Who has the ability to initiate contact request?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Choose the user roles that are allowed to contact other users.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<select name="bcm_admin_general_setting[bcm_who_contact][]" id="bcm-who-contacted" class="bcm_who_contact" multiple>
					<option value="visitors" <?php echo isset( $bcm_admin_general_setting['bcm_who_contact'] ) && in_array( 'visitors', $bcm_admin_general_setting['bcm_who_contact'] ) ? 'selected' : ''; ?>><?php esc_html_e( 'Visitors(not logged in to the site)', 'buddypress-contact-me' ); ?></option>
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
						<?php esc_html_e( 'Who can be contacted?', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Select the user roles who can be contacted', 'buddypress-contact-me' ); ?></p>
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
						<?php esc_html_e( 'Email subject', 'buddypress-contact-me' ); ?>
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
						<?php esc_html_e( 'Email body content', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enter text to send email to the user', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<?php
					$bcm_contact_link    = bp_members_get_user_url( bp_loggedin_user_id() ) . 'contact';
					$bcm_click           = '<a href=" ' . $bcm_contact_link . '">Click here</a>';
					$bcm_default_content = sprintf(
						esc_html__(
							'Hi {user_name},
											{sender_user_name} has contacted you. 
											%1$s to check the message. 
											You can also go to the contact form. Thanks.',
							'buddypress-contact-me'
						),
						$bcm_click
					);
					$settings            = array(
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
