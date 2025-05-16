<?php

/**
 * This file is used for rendering and saving plugin welcome settings.
 *
 * @since    1.0.0
 * @package  BuddyPress_Contact_Me
 * @author   Wbcom Designs
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
?>

<div class="wbcom-tab-content">
	<div class="wbcom-welcome-main-wrapper">
		<div class="wbcom-welcome-head">
			<p class="wbcom-welcome-description">
				<?php esc_html_e('BuddyPress Contact Me displays a contact form on members\' profiles, allowing both logged-in and non-logged-in visitors to connect with community members.', 'buddypress-contact-me'); ?>
			</p>
		</div><!-- .wbcom-welcome-head -->

		<div class="wbcom-welcome-content">
			<div class="wbcom-welcome-support-info">
				<h3><?php esc_html_e('Help & Support Resources', 'buddypress-contact-me'); ?></h3>
				<p><?php esc_html_e('If you need assistance, here are some helpful resources. Our documentation is a great place to start, and our support team is available if you require further help.', 'buddypress-contact-me'); ?></p>

				<div class="wbcom-support-info-wrap">
					<!-- Documentation Widget -->
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
							<h3><span class="dashicons dashicons-book"></span><?php esc_html_e('Documentation', 'buddypress-contact-me'); ?></h3>
							<p><?php esc_html_e('Explore our detailed guide on BuddyPress Contact Me to understand all the features and how to make the most of them.', 'buddypress-contact-me'); ?></p>
							<a href="<?php echo esc_url('https://docs.wbcomdesigns.com/doc_category/bp-contact-me/'); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e('Read Documentation', 'buddypress-contact-me'); ?></a>
						</div>
					</div>

					<!-- Support Center Widget -->
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
							<h3><span class="dashicons dashicons-sos"></span><?php esc_html_e('Support Center', 'buddypress-contact-me'); ?></h3>
							<p><?php esc_html_e('Our support team is here to assist you with any questions or issues. Feel free to contact us anytime through our support center.', 'buddypress-contact-me'); ?></p>
							<a href="<?php echo esc_url('https://wbcomdesigns.com/support/'); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e('Get Support', 'buddypress-contact-me'); ?></a>
						</div>
					</div>

					<!-- Feedback Widget -->
					<div class="wbcom-support-info-widgets">
						<div class="wbcom-support-inner">
							<h3><span class="dashicons dashicons-admin-comments"></span><?php esc_html_e('Share Your Feedback', 'buddypress-contact-me'); ?></h3>
							<p><?php esc_html_e('We’d love to hear about your experience with the plugin. Your feedback and suggestions help us improve future updates.', 'buddypress-contact-me'); ?></p>
							<a href="<?php echo esc_url('https://wbcomdesigns.com/submit-review/'); ?>" class="button button-primary button-welcome-support" target="_blank"><?php esc_html_e('Send Feedback', 'buddypress-contact-me'); ?></a>
						</div>
					</div>
				</div><!-- .wbcom-support-info-wrap -->
			</div><!-- .wbcom-welcome-support-info -->
		</div><!-- .wbcom-welcome-content -->
	</div><!-- .wbcom-welcome-main-wrapper -->
</div><!-- .wbcom-tab-content -->