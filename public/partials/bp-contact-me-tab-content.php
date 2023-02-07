<?php
/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link  https://wbcomdesigns.com/
 * @since 1.0.0
 *
 * @package    Buddypress_Contact_Me
 * @subpackage Buddypress_Contact_Me/public/partials
 */
$min   = 1;
$max   = 20;
$num1  = rand( $min, $max );
$num2  = rand( $min, $max );
$sum   = $num1 + $num2;
?>
<div class="bp-content-me-container">
	<h3><?php esc_html_e( 'Contact Me Form', 'bp-contact-me' ); ?></h3>
	<div class="bp-member-blog-post-form">
		<form id="bp-member-post" class="bp-contact-me-form" method="post" action="" enctype="multipart/form-data" >
			<?php if ( ! is_user_logged_in() ) { ?>
				<div for="bp_contact_me_email"><?php esc_html_e( 'Email:', 'bp-contact-me' ); ?>
					<input type="email" name="bp_contact_me_email"/>
				</div>
			<?php } ?>
			<div for="bp_contact_me_subject"><?php esc_html_e( 'Subject:', 'bp-contact-me' ); ?>
				<input type="text" name="bp_contact_me_subject"/>
			</div>			
			<div for="bp_contact_me_message"><?php esc_html_e( 'Message:', 'bp-contact-me' ); ?>
				<textarea name="bp_contact_me_msg" rows="10" cols="100" required></textarea>
			</div>
			<div for="captchasum" class="captchasum">
				<?php echo $num1 . '+' . $num2; ?>?
			</div>
			<input type="hidden" name="bcm_shortcode_user_id" value="<?php echo isset( $atts['id'] ) ? $atts['id'] : '';?>"/>			
			<input type="hidden" id="_wpnonce" name="_wpnonce" value="37b392c8a0" />
			<div class="bp_contact_me_captcha_text">
				<input type="text" class="form-control captcha-control" id="captcha-val">
			</div>
			<input data-captcha="<?php echo $sum; ?>" name="bp_contact_me_form_save" class="bp-contact-me-btn" type="submit" value="<?php echo esc_attr__( 'Submit', 'bp-contact-me' ); ?>"/>
		</form>
	</div>
</div>
