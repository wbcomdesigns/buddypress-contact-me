<?php
/**
 *
 * This template file is used for fetching desired options page file at admin settings.
 *
 * @package BuddyPress_Contact_Me
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( filter_input( INPUT_GET, 'tab' ) ) {
	$blpro_tab = filter_input( INPUT_GET, 'tab' );
} else {
	$blpro_tab = 'welcome';
}

bpsp_include_setting_tabs( $blpro_tab );

/**
 *
 * Function to select desired file for tab option.
 *
 * @param string $blpro_tab The current tab string.
 */
function bpsp_include_setting_tabs( $blpro_tab ) {

	switch ( $blpro_tab ) {
		case 'welcome':
			include 'buddypress-contact-me-welcome-page.php';
			break;
		case 'general':
			include 'buddypress-contact-me-general-page.php';
			break;
		case 'support':
			include 'buddypress-contact-me-support-tab.php';
			break;
		default:
			include 'buddypress-contact-me-welcome-page.php';
			break;
	}

}

