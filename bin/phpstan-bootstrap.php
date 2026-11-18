<?php
/**
 * Constants the plugin defines at runtime, for static analysis only.
 *
 * @package BuddyPress_Contact_Me
 */

define( 'BUDDYPRESS_CONTACT_ME_VERSION', '0' );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_PATH', __DIR__ . '/../' );
define( 'BUDDYPRESS_CONTACT_ME_PLUGIN_URL', '' );
define( 'BUDDYPRESS_CONTACT_ME_FILE', __DIR__ . '/../buddypress-contact-me.php' );
define( 'COOKIEPATH', '/' );

if ( ! function_exists( 'wbc_captcha_service_manager' ) ) {
	/**
	 * Stub: Wbcom CAPTCHA Manager (optional plugin) service locator.
	 *
	 * @return object|null Manager with is_configured(), is_enabled_for_context(), render(), verify().
	 */
	function wbc_captcha_service_manager() {
		return null;
	}
}
