(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize BuddyBoss contact count
        if(bcm_ajax_object.is_buddyboss_active){
            var count = '<span class="count">'+ bcm_ajax_object.contact_count +'</span>';
            $('#user-bp_contact_count').append(count);
        }

        // Form validation initialization
        initializeFormValidation();
        
        // Character counter for message field
        $('.bp_contact_me_msg').on('input', function() {
            var charCount = $(this).val().length;
            var maxLength = $(this).attr('maxlength');
            $(this).closest('.bp-content-me-fieldset').find('.bcm-char-count').text(charCount + '/' + maxLength);
            
            // Add warning class if near limit
            if (charCount > maxLength * 0.9) {
                $(this).closest('.bp-content-me-fieldset').find('.bcm-char-count').addClass('bcm-warning');
            } else {
                $(this).closest('.bp-content-me-fieldset').find('.bcm-char-count').removeClass('bcm-warning');
            }
            
            // Check form validity
            checkFormValidity();
        });

        // Real-time email validation
        $('.bp_contact_me_email').on('blur', function() {
            var email = $(this).val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (email && !emailRegex.test(email)) {
                $(this).addClass('bcm-error');
                if (!$(this).next('.bcm-error-message').length) {
                    $(this).after('<span class="bcm-error-message">' + (bcm_ajax_object.email_error || 'Please enter a valid email address') + '</span>');
                }
            } else {
                $(this).removeClass('bcm-error');
                $(this).next('.bcm-error-message').remove();
            }
            
            checkFormValidity();
        });
        
        // Real-time validation for all fields
        $('.bp_contact_me_first_name, .bp_contact_me_login_name, .bp_contact_me_subject').on('input blur', function() {
            validateField($(this));
            checkFormValidity();
        });

        // Improved captcha validation
        if ($('.bp-contact-me-form').length) {
            const captchaInput = document.querySelector('input[name="bcm_captcha_answer"]');
            if (captchaInput) {
                captchaInput.addEventListener("input", function(e) {
                    validateCaptcha();
                });
            }
        }
        
        // Form submission handling
        $('.bp-contact-me-form').on('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
                return false;
            }
            
            // Show spinner
            $('.bcm-submit-spinner').show();
            $('.bp-contact-me-btn').prop('disabled', true);
        });

        // Contact tab delete functionality with unique nonce
        $(document).on('click', '.bcm_message_delete', function(e) {
            e.preventDefault();
            var $this = $(this);
            var rowid = $this.data('id');
            var nonce = $this.data('nonce');
            var $row = $this.closest('tr');
            
            if (confirm(bcm_ajax_object.delete_confirm || 'Are you sure you want to delete this message?')) {
                $.ajax({
                    url: bcm_ajax_object.ajax_url,
                    type: "post",
                    data: {
                        'action': 'bcm_message_del',
                        'rowid': rowid,
                        'nonce': nonce,  // Send the unique nonce
                    },
                    beforeSend: function() {
                        $row.css('opacity', '0.5');
                    },
                    success: function(data) {
                        if (data.success) {
                            $row.fadeOut(300, function() {
                                $(this).remove();
                                updateContactCount();
                                // Check if no more rows exist
                                if ($('.bp_contact-me-messages tbody tr').length === 0) {
                                    location.reload(); // Reload to show "no messages" state
                                }
                            });
                        } else {
                            $row.css('opacity', '1');
                            alert(data.data && data.data.message ? data.data.message : (bcm_ajax_object.delete_error || 'Error deleting message. Please try again.'));
                        }
                    },
                    error: function() {
                        $row.css('opacity', '1');
                        alert(bcm_ajax_object.delete_error || 'Error deleting message. Please try again.');
                    }
                });
            }
        });

        // Bulk action handling
        $('#bcm-bulk-manage').attr('disabled', 'disabled');
        
        $('#bcm-select').on('change', function() {
            $('#bcm-bulk-manage').attr('disabled', $(this).val().length <= 0);
        });
        
        // Select/Deselect all checkboxes
        $('#bcm-select-all-contact').on('click', function() {
            $('.bcm-all-check').prop('checked', this.checked);
        });
        
        // Contact message popup
        $(document).on('click', '.bcm_message_seen', function(e) {
            e.preventDefault();
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
                success: function(response) {
                    $('.bp-contact-me-loader').css('display', 'none');
                    if (response.success) {
                        Swal.fire({ 
                            html: response.data.html,
                            width: '600px',
                            showCloseButton: true,
                            confirmButtonText: bcm_ajax_object.close_text || 'Close'
                        });
                    }
                },
                error: function() {
                    $('.bp-contact-me-loader').css('display', 'none');
                    alert(bcm_ajax_object.popup_error || 'Error loading message. Please try again.');
                }
            });
        });
        
        // Helper Functions
        
        function initializeFormValidation() {
            checkFormValidity();
            
            // Initialize character count
            var $messageField = $('.bp_contact_me_msg');
            if ($messageField.length) {
                var charCount = $messageField.val().length;
                var maxLength = $messageField.attr('maxlength');
                $messageField.closest('.bp-content-me-fieldset').find('.bcm-char-count').text(charCount + '/' + maxLength);
            }
        }
        
        function validateField($field) {
            var value = $field.val().trim();
            var minLength = parseInt($field.attr('minlength')) || 0;
            var maxLength = parseInt($field.attr('maxlength')) || Infinity;
            var isValid = true;
            var errorMessage = '';
            
            // Remove existing error
            $field.removeClass('bcm-error');
            $field.next('.bcm-error-message').remove();
            
            // Check required
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = bcm_ajax_object.field_required || 'This field is required';
            }
            // Check min length
            else if (value && value.length < minLength) {
                isValid = false;
                errorMessage = (bcm_ajax_object.min_length || 'Minimum {min} characters required').replace('{min}', minLength);
            }
            // Check max length
            else if (value && value.length > maxLength) {
                isValid = false;
                errorMessage = (bcm_ajax_object.max_length || 'Maximum {max} characters allowed').replace('{max}', maxLength);
            }
            
            if (!isValid) {
                $field.addClass('bcm-error');
                $field.after('<span class="bcm-error-message">' + errorMessage + '</span>');
            }
            
            return isValid;
        }
        
        function validateCaptcha() {
            const captchaInput = $('input[name="bcm_captcha_answer"]');
            const submitButton = $('.bp-contact-me-btn');
            const captchaAnswer = parseInt(captchaInput.val());
            const expectedAnswer = parseInt(submitButton.data('captcha'));
            
            if (captchaAnswer === expectedAnswer) {
                captchaInput.removeClass('bcm-error');
                captchaInput.next('.bcm-error-message').remove();
                return true;
            } else if (captchaInput.val()) {
                captchaInput.addClass('bcm-error');
                if (!captchaInput.next('.bcm-error-message').length) {
                    captchaInput.after('<span class="bcm-error-message">' + (bcm_ajax_object.captcha_error || 'Incorrect answer') + '</span>');
                }
                return false;
            }
            
            return false;
        }
        
        function checkFormValidity() {
            var isValid = true;
            var userlog = bcm_ajax_object.user_log;
            
            // Check all required fields
            $('.bp-contact-me-form input[required], .bp-contact-me-form textarea[required]').each(function() {
                if (!$(this).val().trim() && !$(this).hasClass('captcha-control') && $(this).attr('name') !== 'bcm_captcha_answer') {
                    isValid = false;
                }
            });
            
            // Check email if not logged in
            if (userlog == 0) {
                var email = $('.bp_contact_me_email').val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!email || !emailRegex.test(email)) {
                    isValid = false;
                }
            }
            
            // Check captcha
            if (!validateCaptcha()) {
                isValid = false;
            }
            
            // Enable/disable submit button
            if (isValid) {
                $('.bp-contact-me-btn').prop('disabled', false).removeClass('not-allowed');
            } else {
                $('.bp-contact-me-btn').prop('disabled', true).addClass('not-allowed');
            }
            
            return isValid;
        }

        $(document).on('change keyup', '.bp-contact-me-fields', function(e) {
            var isValid = true;
            if (!validateCaptcha()) {
                isValid = false;
            }
            console.log(isValid);
            // Enable/disable submit button
            if (isValid) {
                $('.bp-contact-me-btn').prop('disabled', false).removeClass('not-allowed');
            } else {
                $('.bp-contact-me-btn').prop('disabled', true).addClass('not-allowed');
            }
        })
        
        function validateForm() {
            var isValid = true;
            
            // Validate all fields
            $('.bp-contact-me-form input[required], .bp-contact-me-form textarea[required]').each(function() {
                if ($(this).attr('name') !== 'bcm_captcha_answer' && !validateField($(this))) {
                    isValid = false;
                }
            });
            
            // Validate email separately if exists
            var $emailField = $('.bp_contact_me_email');
            if ($emailField.length) {
                var email = $emailField.val();
                var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (email && !emailRegex.test(email)) {
                    isValid = false;
                }
            }
            
            // Validate captcha
            if (!validateCaptcha()) {
                isValid = false;
                $('input[name="bcm_captcha_answer"]').focus();
            }
            
            return isValid;
        }
        
        function updateContactCount() {
            var currentCount = parseInt($('#bp_contact_count .count').text()) || 0;
            if (currentCount > 0) {
                currentCount--;
                $('#bp_contact_count .count').text(currentCount);
                if (bcm_ajax_object.is_buddyboss_active) {
                    $('#user-bp_contact_count .count').text(currentCount);
                }
            }
        }
    });

})(jQuery);