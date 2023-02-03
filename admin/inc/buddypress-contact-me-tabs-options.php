<?php
/**
 *
 * This template file is used for fetching desired options page file at admin settings.
 *
 * @package Buddypress_Sticky_Post
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( isset( $_GET['tab'] ) ) {
	$blpro_tab = sanitize_text_field( wp_unslash( $_GET['tab'] ) );
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
		case 'support':
			include 'buddypress-contact-me-support-tab.php';
			break;
		default:
			include 'buddypress-contact-me-welcome-page.php';
			break;
	}

}

