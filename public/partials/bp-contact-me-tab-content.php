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
if ( ! empty( $_COOKIE['bcm_notice_message'] ) ) {
    $message = sanitize_text_field( $_COOKIE['bcm_notice_message'] );
    $type    = sanitize_key( $_COOKIE['bcm_notice_type'] ?? 'success' );

    echo '<div class="bcm-notice bp-feedback bp-messages ' . esc_attr( $type ) . '">';
    echo '<span class="bp-icon" aria-hidden="true"></span>';
    echo '<p>' . esc_html( $message ) . '</p>';
    echo '</div>';

	            // Also unset from $_COOKIE so PHP doesn't use them during this request
            unset( $_COOKIE['bcm_notice_message'], $_COOKIE['bcm_notice_type'] );
}
?>
<div class="bp-content-me-container">
	<div class="bp-member-blog-post-form">
		<form id="bp-member-post" class="bp-contact-me-form" method="post" action="" enctype="multipart/form-data" novalidate>
			<?php if (!is_user_logged_in()) : ?>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_first_name">
						<?php esc_html_e('Name', 'buddypress-contact-me'); ?>
						<span class="required">*</span>
					</label>
					<input type="text" 
					       id="bp_contact_me_first_name"
					       class="bp_contact_me_first_name bp-contact-me-fields" 
					       name="bp_contact_me_first_name" 
					       minlength="2" 
					       maxlength="100" 
					       placeholder="<?php esc_attr_e('Enter your name', 'buddypress-contact-me'); ?>"
					       required />
					<span class="bcm-field-description"><?php esc_html_e('2-100 characters', 'buddypress-contact-me'); ?></span>
				</div>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_email">
						<?php esc_html_e('Email', 'buddypress-contact-me'); ?>
						<span class="required">*</span>
					</label>
					<input type="email" 
					       id="bp_contact_me_email"
					       class="bp_contact_me_email bp-contact-me-fields" 
					       name="bp_contact_me_email" 
					       placeholder="<?php esc_attr_e('your.email@example.com', 'buddypress-contact-me'); ?>"
					       pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
					       required />
					<span class="bcm-field-description"><?php esc_html_e('Enter a valid email address', 'buddypress-contact-me'); ?></span>
				</div>
			<?php else :
				$name = bp_core_get_user_displayname(bp_loggedin_user_id());
			?>
				<div class="bp-content-me-fieldset">
					<label for="bp_contact_me_login_name">
						<?php esc_html_e('Name', 'buddypress-contact-me'); ?>
						<span class="required">*</span>
					</label>
					<input type="text" 
					       id="bp_contact_me_login_name"
					       name="bp_contact_me_login_name" 
					       class="bp_contact_me_login_name bp-contact-me-fields" 
					       value="<?php echo esc_attr($name); ?>" 
					       minlength="2" 
					       maxlength="100"
					       required />
					<span class="bcm-field-description"><?php esc_html_e('2-100 characters', 'buddypress-contact-me'); ?></span>
				</div>
			<?php endif; ?>

			<div class="bp-content-me-fieldset">
				<label for="bp_contact_me_subject">
					<?php esc_html_e('Subject', 'buddypress-contact-me'); ?>
					<span class="required">*</span>
				</label>
				<input type="text" 
				       id="bp_contact_me_subject"
				       class="bp_contact_me_subject bp-contact-me-fields" 
				       name="bp_contact_me_subject" 
				       minlength="3" 
				       maxlength="200" 
				       placeholder="<?php esc_attr_e('Brief subject of your message', 'buddypress-contact-me'); ?>"
				       required />
				<span class="bcm-field-description"><?php esc_html_e('3-200 characters', 'buddypress-contact-me'); ?></span>
			</div>
			<div class="bp-content-me-fieldset">
				<label for="bp_contact_me_message">
					<?php esc_html_e('Message', 'buddypress-contact-me'); ?>
					<span class="required">*</span>
				</label>
				<textarea name="bp_contact_me_msg" 
				          id="bp_contact_me_message"
				          class="bp_contact_me_msg bp-contact-me-fields" 
				          rows="10" 
				          cols="100" 
				          minlength="10" 
				          maxlength="5000" 
				          placeholder="<?php esc_attr_e('Type your message here...', 'buddypress-contact-me'); ?>"
				          required></textarea>
				<span class="bcm-field-description">
					<?php esc_html_e('10-5000 characters', 'buddypress-contact-me'); ?> 
					<span class="bcm-char-count">0/5000</span>
				</span>
			</div>
			<div id="fieldset-captchasum" class="bp-content-me-fieldset">
				<label for="captcha-val">
					<?php esc_html_e('Security Question', 'buddypress-contact-me'); ?>
					<span class="required">*</span>
				</label>
				<div class="captcha-wrapper">
					<div class="captchasum">
						<?php echo esc_html("$num1 + $num2"); ?> = ?
					</div>
					<div class="bp_contact_me_captcha_text">
						<input type="text" 
							class="form-control captcha-control bp-contact-me-fields" 
							id="captcha-val"
							name="bcm_captcha_answer"
							placeholder="<?php esc_attr_e('Enter the answer', 'buddypress-contact-me'); ?>"
							required>
						<span class="bcm-field-description"><?php esc_html_e('Please solve the math problem', 'buddypress-contact-me'); ?></span>
					</div>
				</div>
				<input type="hidden" name="bcm_captcha_hash" value="<?php echo esc_attr(wp_hash($sum)); ?>" />
				<input type="hidden" name="bcm_shortcode_user_id" value="<?php echo isset($atts['id']) ? esc_attr($atts['id']) : ''; ?>" />
				<input type="hidden" name="bcm_shortcode_username" value="<?php echo isset($atts['user']) ? esc_attr($atts['user']) : ''; ?>" />
				<input type="hidden" name="bcm_nonce" value="<?php echo esc_attr(wp_create_nonce('bcm_form_nonce')); ?>">
			</div>
			<div class="bp-content-me-submit">
				<input data-captcha="<?php echo esc_attr($sum); ?>" 
				       name="bp_contact_me_form_save" 
				       class="bp-contact-me-btn" 
				       type="submit" 
				       value="<?php esc_attr_e('Send Message', 'buddypress-contact-me'); ?>" />
				<span class="bcm-submit-spinner" style="display:none;">
					<img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" alt="<?php esc_attr_e('Sending...', 'buddypress-contact-me'); ?>" />
				</span>
			</div>
		</form>
	</div>
</div>