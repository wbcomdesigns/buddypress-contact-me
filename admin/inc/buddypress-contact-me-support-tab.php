<?php
/**
 * Faqs support template file.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wbcom-tab-content">
<div class="wbcom-faq-adming-setting">
	<div class="wbcom-admin-title-section">
		<h3><?php esc_html_e( 'Have some questions?', 'buddypress-contact-me' ); ?></h3>
	</div>
	<div class="wbcom-faq-admin-settings-block">
		<div id="wbcom-faq-settings-section" class="blpro-faqs-block-contain">
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="blpro-accordion wbcom-faq-accordion">
						<?php esc_html_e( 'Does This plugin requires BuddyPress?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes, It needs you to have BuddyPress installed and activated.', 'buddypress-contact-me' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="blpro-accordion wbcom-faq-accordion">
						<?php esc_html_e( 'What are the settings for non logged-in users ?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'This plugin allows the site administrator to secure components of BuddyPress (if active), WordPress pages, custom post types from non-logged in users.', 'buddypress-contact-me' ); ?>
						</p>
						<p><?php _e( 'You can lockdown WordPress Pages, any Custom Post Type and any BuddyPress Component and can have some content displayed like if you want to show any shortcode content or any simple message.', 'buddypress-contact-me' );?></p>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
