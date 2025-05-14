(function($) {
    'use strict';

    /**
     * All of the code for your public-facing JavaScript source
     * should reside in this file.
     *
     * Note: It has been assumed you will write jQuery code here, so the
     * $ function reference has been prepared for usage within the scope
     * of this function.
     *
     * This enables you to define handlers, for when the DOM is ready:
     *
     * $(function() {
     *
     * });
     *
     * When the window is loaded:
     *
     * $( window ).load(function() {
     *
     * });
     *
     * ...and/or other possibilities.
     *
     * Ideally, it is not considered best practise to attach more than a
     * single DOM-ready or window-load handler for a particular page.
     * Although scripts in the WordPress core, Plugins and Themes may be
     * practising this, we should strive to set a better example in our own work.
     */
    $(document).ready(function() {
        if(bcm_ajax_object.is_buddyboss_active){
            var count = '<span class = "count">'+ bcm_ajax_object.contact_count +'</span>';
            $('#user-bp_contact_count').append(count);
        }
        var userlog = bcm_ajax_object.user_log;       
        if( userlog == 1 ){
            var name    = $('.bp_contact_me_login_name').val();           
            var subject = $('.bp_contact_me_subject').val();
            var message = $('.bp_contact_me_msg').val();
            if( name == '' || subject == '' && message == '' ){
                $('.bp-contact-me-form .bp-contact-me-btn').addClass('not-allowed');
            }
        }
        if( userlog == 0 ){
            var name    = $('.bp_contact_me_first_name').val();
            var email   = $('.bp_contact_me_email').val();
            var subject = $('.bp_contact_me_subject').val();
            var message = $('.bp_contact_me_msg').val();
            if( name == '' && email == '' && subject == '' && message == '' ){
                $('.bp-contact-me-form .bp-contact-me-btn').addClass('not-allowed');
            }
        }       

        // capthca validation
        if ($('.bp-contact-me-form').length) {
            const submitButton = document.querySelector('[type="submit"]');
            $('.bp-contact-me-form').find('[type=submit]').attr('disabled', 'disabled');
            const captchaInput = document.querySelector(".captcha-control");
            captchaInput.addEventListener("input", function(e) {
                const captcha = $('.bp-contact-me-form').find('[type=submit]').data('captcha');
                if (this.value == captcha) {
                    $('.bp-contact-me-form').find('[type=submit]').removeAttr("disabled");
                    $('.bp-contact-me-form').find('[type=submit]').removeClass('not-allowed');
                } else {
                    $('.bp-contact-me-form').find('[type=submit]').attr('disabled', 'disabled');
                    $('.bp-contact-me-form').find('[type=submit]').addClass('not-allowed');
                }
            });
        }
        /* Contact tab daat deleted */
        $('#bcm_message_delete span').on('click', function(e) {
            var rowid = $(this).data('id');
            $.ajax({
                url: bcm_ajax_object.ajax_url,
                type: "post",
                data: {
                    'action': 'bcm_message_del',
                    'rowid': rowid,
                    'nonce': bcm_ajax_object.ajax_nonce,
                },
                success: function(data) {
                    location.reload();
                }
            });
        });
        /* Make sure a 'Bulk Action' is selected before submitting the form */
        $('#bcm-bulk-manage').attr('disabled', 'disabled');
        /* Remove the disabled attribute from the form submit button when bulk action has a value */
        $('#bcm-select').on('change', function() {
            $('#bcm-bulk-manage').attr('disabled', $(this).val().length <= 0);
        });
        /* Selecting/Deselecting all notifications */
        $('#bcm-select-all-contact').on('click', function() {
            if (this.checked) {
                $('.bcm-all-check').each(function() {
                    this.checked = true;
                });
            } else {
                $('.bcm-all-check').each(function() {
                    this.checked = false;
                });
            }
        });
        // contact message popup
        $('.bcm_message_seen').on('click', function(e) {
            e.preventDefault;
            $('.bp-contact-me-loader').css('display', 'block');
            var rowid = $(this).data('id');
            $.ajax({
                url: bcm_ajax_object.ajax_url,
                type: "post",
                data: {
                    'action': 'bcm_message_popup',
                    'rowid': rowid,
                    'nonce': bcm_ajax_object.ajax_nonce,
                },
                beforeSend: function() {
                    jQuery('#loader').show();
                },
                success: function(response) {
                    $('.bp-contact-me-loader').css('display', 'none');
                    var rowData = response.data.html;
                    Swal.fire({ 'html': rowData });
                }
            });
        });
    });


})(jQuery);