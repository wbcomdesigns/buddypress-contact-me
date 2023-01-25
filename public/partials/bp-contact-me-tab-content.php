
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
$num1 = rand($min, $max);
$num2 = rand($min, $max);
$sum  = $num1 + $num2;
if(isset($_POST['bp_contact_me_form_save']) ) {
    global $wpdb;
    $bp_sender_user_id      = get_current_user_id();
    $bp_display_user_id     = bp_displayed_user_id();
    $bp_contact_me_subject  = $_POST['bp_contact_me_subject'];
    $bp_contact_me_msg      = $_POST['bp_contact_me_msg'];
    $bp_contact_me_table    = $wpdb->prefix.'contact_me';
    $insert_data_contact_me = $wpdb->insert( 
        $bp_contact_me_table, 
        array( 
            'sender'    => $bp_sender_user_id,
            'reciever'  => $bp_display_user_id, 
            'subject'   => $bp_contact_me_subject, 
            'message'   => $bp_contact_me_msg
        ), 
        array( 
            '%d', 
            '%d', 
            '%s', 
            '%s'
        ) 
    );
    if(isset($insert_data_contact_me) && '' !== $insert_data_contact_me ) {
        $get_contact_id = $wpdb->insert_id;
        do_action('bp_contact_me_form_save', $get_contact_id,  $bp_display_user_id);
    }
    
}
?>
<div class="bp-content-me-container">
    <h3><?php esc_html_e("Contact Me Form", 'bp-contact-me'); ?></h3>
    <div class="bp-member-blog-post-form">
        <?php
        global $wpdb;
        $loggedin_user_id   = get_current_user_id();
        $displayed_id       = bp_displayed_user_id();
        $get_contact_row    = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}contact_me` WHERE sender = $loggedin_user_id"));
        foreach( $get_contact_row as $get_contact_row_val  ){
            if($displayed_id == $get_contact_row_val->reciever ) {
                $contact_sub = $get_contact_row_val->subject;
                $contact_msg = $get_contact_row_val->message;
            }
        }
        
        ?>
        <form id="bp-member-post" class="bp-contact-me-form" method="post" action="" enctype="multipart/form-data" >
            <label for="bp_contact_me_subject"><?php esc_html_e('Subject:', 'bp-contact-me'); ?>
                <input type="text" name="bp_contact_me_subject" value="<?php echo esc_attr(isset($contact_sub) ) ? $contact_sub : ''; ?>" required/>
            </label>
            <label for="bp_contact_me_message"><?php esc_html_e('Message:', 'bp-contact-me'); ?>
                <textarea name="bp_contact_me_msg" rows="10" cols="100" required><?php echo isset($contact_msg) ? $contact_msg : ''; ?></textarea>
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
