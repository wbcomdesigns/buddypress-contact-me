<?php
/**
 * Shared helpers: the one date formatter and the one notice markup.
 *
 * @package BuddyPress_Contact_Me
 */

defined( 'ABSPATH' ) || exit;

/**
 * The timezone every Contact Me date is shown in.
 *
 * @since 1.5.1
 *
 * @param int $user_id Member the date is shown to (0 for the current request).
 * @return DateTimeZone
 */
function bcm_display_timezone( $user_id = 0 ) {
	/**
	 * Filters the timezone Contact Me dates are shown in.
	 *
	 * @since 1.5.1
	 *
	 * @param DateTimeZone $timezone Site timezone.
	 * @param int          $user_id  Member the date is shown to, 0 when unknown.
	 */
	return apply_filters( 'bcm_display_timezone', wp_timezone(), (int) $user_id );
}

/**
 * Format a stored time for display. The only place Contact Me formats a date.
 *
 * @since 1.5.1
 *
 * @param string|int|null $utc      Stored UTC `Y-m-d H:i:s` (a naive string is read as UTC), any date string, or a Unix timestamp.
 * @param string          $fallback Returned for empty or unreadable values.
 * @param string|null     $format   PHP date format; default the site date and time formats.
 * @param int             $user_id  Member the date is shown to.
 * @return string
 */
function bcm_date_label( $utc, $fallback = '', $format = null, $user_id = 0 ) {
	if ( is_int( $utc ) || ( is_string( $utc ) && ctype_digit( $utc ) ) ) {
		$ts = (int) $utc;
	} else {
		$utc  = (string) $utc;
		$date = ( '' === $utc || 0 === strpos( $utc, '0000-00-00' ) ) ? false : date_create( $utc, new DateTimeZone( 'UTC' ) );
		$ts   = $date ? $date->getTimestamp() : 0;
	}
	if ( $ts <= 0 ) {
		return $fallback;
	}
	$format = $format ? $format : get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
	return (string) wp_date( $format, $ts, bcm_display_timezone( $user_id ) );
}

/**
 * Print a notice in BuddyPress `.bp-feedback` markup so it looks like every other BuddyPress notice.
 *
 * @since 1.5.1
 *
 * @param string $text Plain text.
 * @param string $type success, error, warning or info.
 */
function bcm_notice( $text, $type = 'info' ) {
	$type = in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ? $type : 'info';
	printf(
		'<div class="bp-feedback bp-messages %1$s bcm-alert bcm-alert--%1$s" role="%2$s"><span class="bp-icon" aria-hidden="true"></span><p>%3$s</p></div>',
		esc_attr( $type ),
		'error' === $type ? 'alert' : 'status',
		esc_html( $text )
	);
}
