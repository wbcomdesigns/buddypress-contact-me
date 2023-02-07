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
		<div class="form-table">
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
					<label for="bcm_email_subject">
						<?php esc_html_e( 'Email Subject', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'Enter the subject line for email notification subject.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<input id="bcm_email_subject" class="bcm_email_subject" name="bcm_admin_general_setting[bcm_email_subject]" value="<?php echo isset( $bcm_admin_general_setting['bcm_email_subject'] ) ? $bcm_admin_general_setting['bcm_email_subject'] : ''; ?>">
				</div>				
			</div>
			<div class="wbcom-settings-section-wrap">
				<div class="wbcom-settings-section-options-heading">
					<label for="bcm_email_content">
						<?php esc_html_e( 'Email Body Content', 'buddypress-contact-me' ); ?>
					</label>
					<p class="description"><?php esc_html_e( 'This option is for email body content.', 'buddypress-contact-me' ); ?></p>
				</div>
				<div class="wbcom-settings-section-options">
					<?php
						$settings = array(
							'media_buttons' => true,
							'editor_height' => 200,
							'textarea_name' => 'bcm_admin_general_setting[bcm_email_content]',
						);
						wp_editor( isset( $bcm_admin_general_setting['bcm_email_content'] ) ? $bcm_admin_general_setting['bcm_email_content'] : '', 'bcm-email-content', $settings );
						?>
				</div>
			</div>			
		</div>
		<?php submit_button(); ?>
	</form>
</div>
</div>
