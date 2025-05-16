<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link  https://wbcomdesigns.com/
 * @since 1.0.0
 *
 * @package    BuddyPress_Contact_Me
 * @subpackage BuddyPress_Contact_Me/public/partials
 */

$min  = 1;
$max  = 20;
$num1 = wp_rand($min, $max);
$num2 = wp_rand($min, $max);
$sum  = $num1 + $num2;
?>
<div class="bp-content-me-container">
	<div class="bp-member-blog-post-form">
		<form id="bp-member-post" class="bp-contact-me-form" method="post" action="" enctype="multipart/form-data">
			<?php if (!is_user_logged_in()) : ?>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_first_name">
						<?php esc_html_e('Name', 'buddypress-contact-me'); ?>
						<span><?php esc_html_e('*', 'buddypress-contact-me'); ?></span>
					</label>
					<input type="text" class="bp_contact_me_first_name" name="bp_contact_me_first_name" required />
				</div>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_email">
						<?php esc_html_e('Email', 'buddypress-contact-me'); ?>
						<span><?php esc_html_e('*', 'buddypress-contact-me'); ?></span>
					</label>
					<input type="email" class="bp_contact_me_email" name="bp_contact_me_email" required />
				</div>
			<?php else :
				$name = bp_core_get_user_displayname(bp_loggedin_user_id());
			?>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_login_name">
						<?php esc_html_e('Name', 'buddypress-contact-me'); ?>
						<span><?php esc_html_e('*', 'buddypress-contact-me'); ?></span>
					</label>
					<input type="text" name="bp_contact_me_login_name" class="bp_contact_me_login_name" value="<?php echo esc_attr($name); ?>" required />
				</div>
			<?php endif; ?>

			<div class="bp-content-me-fieldset">
				<label for="bp_contact_me_subject">
					<?php esc_html_e('Subject', 'buddypress-contact-me'); ?>
					<span><?php esc_html_e('*', 'buddypress-contact-me'); ?></span>
				</label>
				<input type="text" class="bp_contact_me_subject" name="bp_contact_me_subject" required />
			</div>
			<div class="bp-content-me-fieldset">
				<label for="bp_contact_me_message">
					<?php esc_html_e('Message', 'buddypress-contact-me'); ?>
					<span><?php esc_html_e('*', 'buddypress-contact-me'); ?></span>
				</label>
				<textarea name="bp_contact_me_msg" class="bp_contact_me_msg" rows="10" cols="100" required></textarea>
			</div>
			<div id="fieldset-captchasum" class="bp-content-me-fieldset">
				<div class="captchasum">
					<?php echo esc_html("$num1 + $num2"); ?>?
				</div>
				<input type="hidden" name="bcm_shortcode_user_id" value="<?php echo isset($atts['id']) ? esc_attr($atts['id']) : ''; ?>" />
				<input type="hidden" name="bcm_shortcode_username" value="<?php echo isset($atts['user']) ? esc_attr($atts['user']) : ''; ?>" />
				<input type="hidden" name="bcm_nonce" value="<?php echo esc_attr(wp_create_nonce('bcm_form_nonce')); ?>">
				<div class="bp_contact_me_captcha_text">
					<input type="text" class="form-control captcha-control" id="captcha-val" required>
				</div>
			</div>
			<input data-captcha="<?php echo esc_attr($sum); ?>" name="bp_contact_me_form_save" class="bp-contact-me-btn" type="submit" value="<?php esc_attr_e('Submit', 'buddypress-contact-me'); ?>" />
		</form>
	</div>
</div>