
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
$max  = 300;
$num1 = rand( $min, $max );
$num2 = rand( $min, $max );
$sum  = $num1 + $num2;
?>
<div class="bp-content-me-container">
    <h3><?php esc_html_e("Contact Me Form", 'bp-contact-me'); ?></h3>
    <div class="bp-member-blog-post-form">
        <form id="bp-member-post" class="bp-contact-me-form" method="post" action="" enctype="multipart/form-data" >
            <label for="bp_contact_me_subject"><?php esc_html_e('Subject:', 'bp-contact-me'); ?>
                <input type="text" name="bp_contact_me_subject" value="" required/>
            </label>
            <label for="bp_contact_me_message"><?php esc_html_e('Message:', 'bp-contact-me'); ?>
                <textarea name="bp_contact_me_msg" rows="10" cols="100" required></textarea>
            </label>
            <label for="captchasum" class="captchasum">
                <?php echo $num1 . '+' . $num2; ?>?
            </label>
            <div class="bp_contact_me_captcha_text">
                <input type="text" class="form-control captcha-control" id="captcha-val">
            </div>
            <input data-captcha="<?php echo $sum; ?>" name="bp_contact_me_form_save" class="bp-contact-me-btn" type="submit" value="<?php echo esc_attr__('Submit', 'bp-contact-me'); ?>"/>
        </form>
    </div>
</div>
