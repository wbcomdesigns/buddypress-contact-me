(function($) {
    'use strict';

    /**
     * All of the code for your admin-facing JavaScript source
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
    // Support tab
    $(document).ready(function() {
        var acc = document.getElementsByClassName("wbcom-faq-accordion");
        var i;
        for (i = 0; i < acc.length; i++) {
            acc[i].addEventListener('click', function() {
                this.classList.toggle("active");
                var panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            })
        }
        jQuery('#bcm-multiple-user-copy-email, #bcm-who-contacted, #bcm-who-contact').selectize({
            plugins: ["remove_button"],
        });


        // Make notices dismissible
        $(document).on('click', '.notice-dismiss', function () {
            $(this).closest('.bp_contact_me_settings_save_notice').fadeOut(300, function () {
                $(this).hide();
            });
        });

    });
})(jQuery);