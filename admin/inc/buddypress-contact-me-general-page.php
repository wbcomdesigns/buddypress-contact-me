<?php

/**
 * This file is used for rendering and saving plugin settings for logged-in users.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Fetch plugin settings and user data.
$bcm_admin_general_setting = get_option( 'bcm_admin_general_setting' );
$bcm_admin_email           = get_option( 'admin_email' );
$bcm_users_except_admin    = get_users(
	array(
		'role__not_in' => 'administrator',
		'fields'       => array( 'ID', 'display_name' ),
	)
);
if ( current_user_can( 'edit_users' ) ) {
	$user_roles = array_reverse( get_editable_roles() );
}
// print_r( $user_roles );
?>

<div class="wbcom-tab-content">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'General Settings', 'buddypress-contact-me' ); ?></h3>
	</div>
	<div class="wbcom-admin-option-wrap">
		<form method="post" action="options.php">
			<?php
			settings_fields( 'bcm_admin_general_email_notification_setting' );
			do_settings_sections( 'bcm_admin_general_setting' );
			?>
			<div class="form-table contact-me-general">
				<!-- BuddyPress Notifications Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_allow_notification"><?php esc_html_e( 'Enable BuddyPress Notifications', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Allow members to receive notifications when someone contacts them.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="wb-switch">
							<input name='bcm_admin_general_setting[bcm_allow_notification]' type='checkbox' id="bcm_notification" class="bcm_notification" value='yes' <?php isset( $bcm_admin_general_setting['bcm_allow_notification'] ) ? checked( $bcm_admin_general_setting['bcm_allow_notification'], 'yes' ) : ''; ?> />
							<div class="wb-slider wb-round"></div>
						</label>
					</div>
				</div>

				<!-- Email Notifications Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_allow_email"><?php esc_html_e( 'Enable Email Notifications', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Send email notifications to members when someone contacts them.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="bcm-switch">
							<input type="checkbox" id="bcm_allow_email" name="bcm_admin_general_setting[bcm_allow_email]" value="yes" <?php isset( $bcm_admin_general_setting['bcm_allow_email'] ) ? checked( $bcm_admin_general_setting['bcm_allow_email'], 'yes' ) : ''; ?>>
							<div class="bcm-slider bcm-round"></div>
						</label>
					</div>
				</div>

				<!-- Enable Contact Tab Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_allow_contact_tab"><?php esc_html_e( 'Show Contact Tab', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Display the contact tab on member profiles.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="bcm-switch">
							<input type="checkbox" id="bcm_allow_contact_tab" name="bcm_admin_general_setting[bcm_allow_contact_tab]" value="yes" <?php isset( $bcm_admin_general_setting['bcm_allow_contact_tab'] ) ? checked( $bcm_admin_general_setting['bcm_allow_contact_tab'], 'yes' ) : ''; ?>>
							<div class="bcm-slider bcm-round"></div>
						</label>
					</div>
				</div>

				<!-- User Email Customization Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_user_email"><?php esc_html_e( 'Customize Sender Email', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Allow users to modify the email address used as the sender.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input type="text" id="bcm_user_email" name="bcm_admin_general_setting[bcm_user_email]" value="<?php echo esc_attr( isset( $bcm_admin_general_setting['bcm_user_email'] ) ? $bcm_admin_general_setting['bcm_user_email'] : $bcm_admin_email ); ?>">
					</div>
				</div>

				<!-- Sender Copy Email Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_sender_copy_email"><?php esc_html_e( 'Send Copy to Sender', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Send a copy of the email to the sender.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="bcm-switch">
							<input type="checkbox" id="bcm_sender_copy_email" name="bcm_admin_general_setting[bcm_allow_sender_copy_email]" value="yes" <?php isset( $bcm_admin_general_setting['bcm_allow_sender_copy_email'] ) ? checked( $bcm_admin_general_setting['bcm_allow_sender_copy_email'], 'yes' ) : ''; ?>>
							<div class="bcm-slider bcm-round"></div>
						</label>
					</div>
				</div>

				<!-- Admin Copy Email Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_admin_copy_email"><?php esc_html_e( 'Send Copy to Admin', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Send a copy of the email to the admin.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<label class="bcm-switch">
							<input type="checkbox" id="bcm_admin_copy_email" name="bcm_admin_general_setting[bcm_allow_admin_copy_email]" value="yes" <?php isset( $bcm_admin_general_setting['bcm_allow_admin_copy_email'] ) ? checked( $bcm_admin_general_setting['bcm_allow_admin_copy_email'], 'yes' ) : ''; ?>>
							<div class="bcm-slider bcm-round"></div>
						</label>
					</div>
				</div>

				<!-- Multiple User Copy Email Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_multiple_user_copy_email"><?php esc_html_e( 'Send Notifications to Multiple Users', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Select additional users to receive email notifications.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<select name="bcm_admin_general_setting[bcm_multiple_user_copy_email][]" id="bcm-multiple-user-copy-email" multiple>
							<?php foreach ( $bcm_users_except_admin as $bcm_user_except_admin ) : ?>
								<option value="<?php echo esc_attr( $bcm_user_except_admin->ID ); ?>" <?php selected( isset( $bcm_admin_general_setting['bcm_multiple_user_copy_email'] ) && in_array( $bcm_user_except_admin->ID, $bcm_admin_general_setting['bcm_multiple_user_copy_email'] ) ); ?>><?php echo esc_html( $bcm_user_except_admin->display_name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Who Can Initiate Contact Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_who_contact"><?php esc_html_e( 'Allowed to Initiate Contact', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Select the user roles allowed to initiate contact requests.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-optio-options">

						<select name="bcm_admin_general_setting[bcm_who_contact][]" id="bcm-who-contact" class="bcm_who_contact" multiple>
							<option value="visitors" <?php selected( isset( $bcm_admin_general_setting['bcm_who_contact'] ) && in_array( 'visitors', $bcm_admin_general_setting['bcm_who_contact'] ) ); ?>><?php esc_html_e( 'Visitors (not logged in)', 'buddypress-contact-me' ); ?></option>
							<?php foreach ( $user_roles as $role => $details ) : ?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php selected( isset( $bcm_admin_general_setting['bcm_who_contact'] ) && in_array( $role, $bcm_admin_general_setting['bcm_who_contact'] ) ); ?>><?php echo esc_html( $details['name'] ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Who Can Be Contacted Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_who_contacted"><?php esc_html_e( 'Allowed to Be Contacted', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Select the user roles who can be contacted.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<select name="bcm_admin_general_setting[bcm_who_contacted][]" id="bcm-who-contacted" class="bcm_who_contacted" multiple>
							<?php foreach ( $user_roles as $role => $details ) : ?>
								<option value="<?php echo esc_attr( $role ); ?>" <?php selected( isset( $bcm_admin_general_setting['bcm_who_contacted'] ) && in_array( $role, $bcm_admin_general_setting['bcm_who_contacted'] ) ); ?>><?php echo esc_html( translate_user_role( $details['name'] ) ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- Email Subject Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_email_subject"><?php esc_html_e( 'Email Subject', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the subject line for email notifications.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<input id="bcm_email_subject" class="bcm_email_subject" name="bcm_admin_general_setting[bcm_email_subject]" value="<?php echo esc_attr( isset( $bcm_admin_general_setting['bcm_email_subject'] ) ? $bcm_admin_general_setting['bcm_email_subject'] : '' ); ?>" placeholder="<?php esc_attr_e( '{user_name} has contacted you.', 'buddypress-contact-me' ); ?>">
					</div>
				</div>

				<!-- Email Body Content Setting -->
				<div class="wbcom-settings-section-wrap">
					<div class="wbcom-settings-section-options-heading">
						<label for="bcm_email_content"><?php esc_html_e( 'Email Body Content', 'buddypress-contact-me' ); ?></label>
						<p class="description"><?php esc_html_e( 'Enter the text for the email notification.', 'buddypress-contact-me' ); ?></p>
					</div>
					<div class="wbcom-settings-section-options">
						<?php
						if ( function_exists( 'buddypress' ) && version_compare( buddypress()->version, '12.0', '>=' ) ) {
							$bcm_contact_link = bp_members_get_user_url( bp_loggedin_user_id() ) . 'contact';
						} else {
							$bcm_contact_link = bp_core_get_user_domain( bp_loggedin_user_id() ) . 'contact';
						}
						$bcm_click           = '<a href="' . esc_url( $bcm_contact_link ) . '">' . esc_html__( 'Click here', 'buddypress-contact-me' ) . '</a>';
						$bcm_default_content = sprintf(
							esc_html__(
								// Translators: %1$s.
								'Hi {user_name}, {sender_user_name} has contacted you. %1$s to check the message. You can also go to the contact form. Thanks.',
								'buddypress-contact-me'
							),
							$bcm_click
						);
						$settings = array(
							'media_buttons' => true,
							'editor_height' => 200,
							'textarea_name' => 'bcm_admin_general_setting[bcm_email_content]',
						);
						wp_editor( isset( $bcm_admin_general_setting['bcm_email_content'] ) && ! empty( $bcm_admin_general_setting['bcm_email_content'] ) ? $bcm_admin_general_setting['bcm_email_content'] : $bcm_default_content, 'bcm-email-content', $settings );
						?>
					</div>
					<code>
						{user_name} - <?php esc_html_e( 'User Name', 'buddypress-contact-me' ); ?><br>
						{sender_user_name} - <?php esc_html_e( 'Sender User Name', 'buddypress-contact-me' ); ?>
					</code>
				</div>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
</div>
