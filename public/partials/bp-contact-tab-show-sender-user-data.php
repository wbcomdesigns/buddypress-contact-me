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
global $wpdb;
$loggedin_user_id = get_current_user_id();
$bp_contact_me_table_name = $wpdb->prefix . 'contact_me';
$get_contact_row = "SELECT * FROM $bp_contact_me_table_name  WHERE `reciever` = $loggedin_user_id";
$get_contact_allrow = $wpdb->get_results($get_contact_row, ARRAY_A);
?>
<label><h1><?php echo esc_html('Contact Message');?></h1></label>
<table class="">
        <thead>
            <tr>
                <th><?php esc_html_e('Sender ID', 'bp-contact-me');?></th>                
                <th><?php esc_html_e('Subject', 'bp-contact-me');?></th>
                <th><?php esc_html_e('Message', 'bp-contact-me'); ?></th>
                <th><?php esc_html_e('Friend', 'bp-contact-me'); ?></th>            
                <th><?php esc_html_e('Private Message', 'bp-contact-me'); ?></th>            
            </tr>
        </thead>
        <tbody>
            <?php foreach ($get_contact_allrow as $get_contact_allrow_val) {
                $sender_id = $get_contact_allrow_val['sender'];               
                $subject = $get_contact_allrow_val['subject'];               
                $message = $get_contact_allrow_val['message'];               
                ?>
            <tr>
                <td><?php echo $sender_id;?></td>
                <td><?php echo $subject;?></td>
                <td><?php echo $message;?></td>
                <td><a href=""><button><?php echo esc_html('Add Friend'); ?></button></a></td>
                <td><a href="<?php echo esc_url(bp_contact_me_get_send_private_message_link($sender_id));?>" target="_blank"><button><?php echo esc_html('Send Message'); ?></button></a></a></td>
            </tr>
            <?php } ?>
        </tbody>
</table>
