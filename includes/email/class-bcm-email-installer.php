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
	 * Copy of the message sent to the member or visitor who wrote it.
	 *
	 * @since 1.5.1
	 */
	const TYPE_SENDER_COPY = 'bcm-contact-message-sender-copy';

	/**
	 * Copy of the message sent to each site administrator.
	 *
	 * @since 1.5.1
	 */
	const TYPE_ADMIN_COPY = 'bcm-contact-message-admin-copy';

	/**
	 * Install each email post + term that doesn't exist yet. Safe to call
	 * on activation and on every version bump.
	 */
	public static function install() {
		if ( ! function_exists( 'bp_get_email_post_type' ) || ! function_exists( 'bp_get_email_tax_type' ) ) {
			return;
		}

		$tax_type = bp_get_email_tax_type();

		foreach ( self::schema() as $type => $email ) {
			// If the email-type term already exists, the admin owns the post
			// now. Keep their edits intact.
			if ( term_exists( $type, $tax_type ) ) {
				continue;
			}

			$post_id = wp_insert_post(
				array(
					'post_title'   => $email['post_title'],
					'post_content' => $email['post_content'],
					'post_excerpt' => $email['post_excerpt'],
					'post_status'  => 'publish',
					'post_type'    => bp_get_email_post_type(),
				)
			);

			if ( ! $post_id ) {
				continue;
			}

			$tt_ids = wp_set_object_terms( $post_id, $type, $tax_type );
			if ( is_wp_error( $tt_ids ) ) {
				continue;
			}

			foreach ( (array) $tt_ids as $tt_id ) {
				$term = get_term_by( 'term_taxonomy_id', (int) $tt_id, $tax_type );
				if ( $term ) {
					wp_update_term(
						(int) $term->term_id,
						$tax_type,
						array( 'description' => $email['description'] )
					);
				}
			}
		}
	}

	/**
	 * Title / content / excerpt / description per email type, using BP
	 * token syntax.
	 *
	 * Tokens we register at send time:
	 *   - {{sender.name}}       — display name of the sender (or guest name)
	 *   - {{contact.recipient}} — display name of the member the message was sent to
	 *   - {{contact.subject}}   — message subject
	 *   - {{contact.message}}   — message body (auto-paragraphed)
	 *   - {{{inbox.url}}}       — deep link to the message in the recipient's inbox
	 *
	 * BP-native tokens like {{site.name}} and {{unsubscribe}} are already
	 * provided by the framework.
	 *
	 * @return array<string,array<string,string>>
	 */
	private static function schema() {
		return array(
			self::TYPE             => array(
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
				'description'  => __( 'A member (or visitor) sent a contact message from your profile.', 'buddypress-contact-me' ),
			),
			self::TYPE_SENDER_COPY => array(
				/* translators: do not remove {} brackets or translate their contents. */
				'post_title'   => __( '[{{{site.name}}}] Copy of your message to {{contact.recipient}}', 'buddypress-contact-me' ),
				/* translators: do not remove {} brackets or translate their contents. */
				'post_content' => __(
					"Here is a copy of the contact message you sent to {{contact.recipient}}:\n\n<strong>{{contact.subject}}</strong>\n\n<blockquote>{{{contact.message}}}</blockquote>",
					'buddypress-contact-me'
				),
				/* translators: do not remove {} brackets or translate their contents. */
				'post_excerpt' => __(
					"Here is a copy of the contact message you sent to {{contact.recipient}}:\n\n{{contact.subject}}\n\n\"{{contact.message}}\"",
					'buddypress-contact-me'
				),
				'description'  => __( 'A copy of a contact message, sent to the member or visitor who wrote it.', 'buddypress-contact-me' ),
			),
			self::TYPE_ADMIN_COPY  => array(
				/* translators: do not remove {} brackets or translate their contents. */
				'post_title'   => __( '[{{{site.name}}}] {{sender.name}} sent {{contact.recipient}} a contact message', 'buddypress-contact-me' ),
				/* translators: do not remove {} brackets or translate their contents. */
				'post_content' => __(
					"{{sender.name}} sent {{contact.recipient}} a contact message:\n\n<strong>{{contact.subject}}</strong>\n\n<blockquote>{{{contact.message}}}</blockquote>",
					'buddypress-contact-me'
				),
				/* translators: do not remove {} brackets or translate their contents. */
				'post_excerpt' => __(
					"{{sender.name}} sent {{contact.recipient}} a contact message:\n\n{{contact.subject}}\n\n\"{{contact.message}}\"",
					'buddypress-contact-me'
				),
				'description'  => __( 'A copy of a contact message, sent to site administrators.', 'buddypress-contact-me' ),
			),
		);
	}
}
