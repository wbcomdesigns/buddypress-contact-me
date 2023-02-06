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
$min  = 1;
$max  = 20;
$num1 = rand( $min, $max );
$num2 = rand( $min, $max );
$sum  = $num1 + $num2;
if ( isset( $_POST['bp_contact_me_form_save'] ) ) {
	global $wpdb;
	$bp_sender_user_id      = get_current_user_id();
	$bp_display_user_id     = bp_displayed_user_id();
	$bp_contact_me_subject  = isset( $_POST['bp_contact_me_subject'] ) ? $_POST['bp_contact_me_subject'] : '';
	$bp_contact_me_msg      = isset( $_POST['bp_contact_me_msg'] ) ? $_POST['bp_contact_me_msg'] : '' ;
	$bp_contact_me_email    = isset( $_POST['bp_contact_me_email'] ) ? $_POST['bp_contact_me_email'] : '' ;
	$bp_contact_me_table    = $wpdb->prefix . 'contact_me';
	$insert_data_contact_me = $wpdb->insert(
		$bp_contact_me_table,
		array(
			'sender'   => $bp_sender_user_id,
			'reciever' => $bp_display_user_id,
			'subject'  => $bp_contact_me_subject,
			'message'  => $bp_contact_me_msg,
			'email'    => $bp_contact_me_email,
		),
		array( '%d', '%d', '%s', '%s', '%s', '%s' )
	);
	if ( isset( $insert_data_contact_me ) && '' !== $insert_data_contact_me ) {
		$get_contact_id = $wpdb->insert_id;
		do_action( 'bp_contact_me_form_save', $get_contact_id, $bp_display_user_id );
		?>
		<aside id="contact-success" class="bp-feedback saved-successfully bp-messages bp-template-notice success">
			<span id="contact-icon" class="bp-icon" aria-hidden="true"></span>
			<p id="contact-msg"><?php echo esc_html( 'Form Successfully Submitted.' ); ?></p>
		</aside>
		<?php
	}
}
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
			<div class="bp_contact_me_captcha_text">
				<input type="text" class="form-control captcha-control" id="captcha-val">
			</div>
			<input data-captcha="<?php echo $sum; ?>" name="bp_contact_me_form_save" class="bp-contact-me-btn" type="submit" value="<?php echo esc_attr__( 'Submit', 'bp-contact-me' ); ?>"/>
		</form>
	</div>
</div>
