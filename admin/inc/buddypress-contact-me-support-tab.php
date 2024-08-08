<?php

/**
 * FAQ support template file.
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="wbcom-tab-content">
	<div class="wbcom-faq-admin-setting">
		<div class="wbcom-admin-title-section">
			<h3><?php esc_html_e('Have some questions?', 'buddypress-contact-me'); ?></h3>
		</div>
		<div class="wbcom-faq-admin-settings-block">
			<div id="wbcom-faq-settings-section" class="blpro-faqs-block-contain">
				<!-- FAQ Item 1 -->
				<div class="wbcom-faq-admin-row">
					<div class="wbcom-faq-section-row">
						<button class="blpro-accordion wbcom-faq-accordion">
							<?php esc_html_e('Does this plugin support the BuddyBoss Platform?', 'buddypress-contact-me'); ?>
						</button>
						<div class="wbcom-faq-panel">
							<p>
								<?php esc_html_e('Yes! The BuddyPress Contact Me plugin is compatible with the BuddyBoss Platform.', 'buddypress-contact-me'); ?>
							</p>
						</div>
					</div>
				</div>
				<!-- FAQ Item 2 -->
				<div class="wbcom-faq-admin-row">
					<div class="wbcom-faq-section-row">
						<button class="blpro-accordion wbcom-faq-accordion">
							<?php esc_html_e('How can we display the contact form on any post or page?', 'buddypress-contact-me'); ?>
						</button>
						<div class="wbcom-faq-panel">
							<p>
								<?php esc_html_e('You can use the shortcode [bp-contact-me id="USER_ID"] to display a user-specific contact form on any post or page. Replace "USER_ID" with the ID of the user.', 'buddypress-contact-me'); ?>
							</p>
						</div>
					</div>
				</div>
				<!-- FAQ Item 3 -->
				<div class="wbcom-faq-admin-row">
					<div class="wbcom-faq-section-row">
						<button class="blpro-accordion wbcom-faq-accordion">
							<?php esc_html_e('Does this plugin work for logged-out users?', 'buddypress-contact-me'); ?>
						</button>
						<div class="wbcom-faq-panel">
							<p>
								<?php esc_html_e('Yes! Visitors who are not logged in can also contact site users using this plugin.', 'buddypress-contact-me'); ?>
							</p>
						</div>
					</div>
				</div>
				<!-- FAQ Item 4 -->
				<div class="wbcom-faq-admin-row">
					<div class="wbcom-faq-section-row">
						<button class="blpro-accordion wbcom-faq-accordion">
							<?php esc_html_e('What are the requirements for this plugin to work?', 'buddypress-contact-me'); ?>
						</button>
						<div class="wbcom-faq-panel">
							<p>
								<?php esc_html_e('BuddyPress must be installed and activated on your site for this plugin to function properly.', 'buddypress-contact-me'); ?>
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>