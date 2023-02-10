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
						<?php esc_html_e( 'Does This Plugin supports BB Platform?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes! BuddyPress Contact Me plugin works with BB Platform.', 'buddypress-contact-me' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="blpro-accordion wbcom-faq-accordion">
						<?php esc_html_e( 'How can we show contact me form on any post or page?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'You can use [bp-contact-me id="USER_ID"] to show user specific contact form on any post or page.  replace id of the user with the parameter `USER_ID` .', 'buddypress-contact-me' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="blpro-accordion wbcom-faq-accordion">
						<?php esc_html_e( 'Does this plugin works in logged out mode?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'Yes! Logged out users(Visitors) can also contect with the site users.', 'buddypress-contact-me' ); ?>
						</p>
					</div>
				</div>
			</div>
			<div class="wbcom-faq-admin-row">
				<div class="wbcom-faq-section-row">
					<button class="blpro-accordion wbcom-faq-accordion">
						<?php esc_html_e( 'What are the requirements for this plugin to work?', 'buddypress-contact-me' ); ?>
					</button>
					<div class="wbcom-faq-panel">
						<p>
							<?php esc_html_e( 'BuddyPress should be installed and activated on your site for this plugin to work.', 'buddypress-contact-me' ); ?>
						</p>
					</div>
				</div>
			</div>
			</div>
		</div>
	</div>
</div>
