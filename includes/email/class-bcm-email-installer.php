<?php
/**
 * Install / migrate the BP email post for contact-message notifications.
 *
 * Creates a `bp-email` post + `bcm-contact-message` term so the email
 * renders through BuddyPress's email template (header, body card, CTA,
 * footer, unsubscribe) and site admins can customise it from
 * Dashboard → Emails → "A member received a contact message".
 *
 * Idempotent — safe to re-run on every upgrade. Will not overwrite the
 * admin's edits once the post exists.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Installs / migrates the BP email post used by this plugin.
 */
class BCM_Email_Installer {

	const TYPE = 'bcm-contact-message';

	/**
	 * Install the email post + term if they don't already exist. Safe to
	 * call on activation and on every version bump.
	 */
	public static function install() {
		if ( ! function_exists( 'bp_get_email_post_type' ) || ! function_exists( 'bp_get_email_tax_type' ) ) {
			return;
		}

		$tax_type = bp_get_email_tax_type();

		// If the email-type term already exists, the admin owns the post
		// now. Keep their edits intact.
		if ( term_exists( self::TYPE, $tax_type ) ) {
			return;
		}

		$schema = self::schema();

		$post_id = wp_insert_post(
			wp_parse_args(
				$schema,
				array(
					'post_status' => 'publish',
					'post_type'   => bp_get_email_post_type(),
				)
			)
		);

		if ( ! $post_id || is_wp_error( $post_id ) ) {
			return;
		}

		$tt_ids = wp_set_object_terms( $post_id, self::TYPE, $tax_type );
		if ( is_wp_error( $tt_ids ) ) {
			return;
		}

		foreach ( (array) $tt_ids as $tt_id ) {
			$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, $tax_type );
			if ( $term ) {
				wp_update_term(
					(int) $term->term_id,
					$tax_type,
					array( 'description' => self::description() )
				);
			}
		}
	}

	/**
	 * Post title / content / excerpt for the email, using BP token syntax.
	 *
	 * Tokens we register at send time:
	 *   - {{sender.name}}     — display name of the sender (or guest name)
	 *   - {{contact.subject}} — message subject
	 *   - {{contact.message}} — message body (auto-paragraphed)
	 *   - {{recipient.name}}  — recipient's display name
	 *   - {{{inbox.url}}}     — deep link to the message in the recipient's inbox
	 *
	 * BP-native tokens like {{site.name}} and {{unsubscribe}} are already
	 * provided by the framework.
	 *
	 * @return array
	 */
	private static function schema() {
		return array(
			/* translators: do not remove {} brackets or translate their contents. */
			'post_title'   => __( '[{{{site.name}}}] {{sender.name}} sent you a contact message', 'buddypress-contact-me' ),
			/* translators: do not remove {} brackets or translate their contents. */
			'post_content' => __(
				"<a href=\"{{{inbox.url}}}\">{{sender.name}}</a> sent you a contact message:\n\n<strong>{{contact.subject}}</strong>\n\n<blockquote>{{{contact.message}}}</blockquote>\n\n<a href=\"{{{inbox.url}}}\">Open the message in your inbox</a> to reply or delete it.",
				'buddypress-contact-me'
			),
			/* translators: do not remove {} brackets or translate their contents. */
			'post_excerpt' => __(
				"{{sender.name}} sent you a contact message:\n\n{{contact.subject}}\n\n\"{{contact.message}}\"\n\nOpen the message in your inbox to reply or delete it: {{{inbox.url}}}",
				'buddypress-contact-me'
			),
		);
	}

	/**
	 * Description shown in BP → Emails admin list, so admins know which
	 * email this post controls.
	 *
	 * @return string
	 */
	private static function description() {
		return __( 'A member (or visitor) sent a contact message from your profile.', 'buddypress-contact-me' );
	}
}
