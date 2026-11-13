<?php
/**
 * BuddyPress notifications + email for new contact messages.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * BuddyPress notifications + BP-email sender for new contact messages.
 */
class BCM_Frontend_Notifications {

	const COMPONENT = 'bcm_user_notifications';
	const ACTION    = 'bcm_user_notifications_action';

	/**
	 * Register component + filters and the form-save action handlers.
	 */
	public function register() {
		add_filter( 'bp_notifications_get_registered_components', array( $this, 'register_component' ) );
		add_filter( 'bp_notifications_get_notifications_for_user', array( $this, 'format_notification' ), 10, 7 );
		add_action( 'bp_contact_me_form_save', array( $this, 'queue_notification' ), 10, 3 );
		add_action( 'bp_contact_me_form_save', array( $this, 'send_email' ), 20, 3 );
	}

	/**
	 * Register our custom BP notifications component.
	 *
	 * @param array $components Registered components.
	 * @return array
	 */
	public function register_component( $components = array() ) {
		if ( ! is_array( $components ) ) {
			$components = array();
		}
		$components[] = self::COMPONENT;
		return $components;
	}

	/**
	 * Format the notification row text + link for BP's notifications UI.
	 *
	 * @param string $action                Default formatted text.
	 * @param int    $item_id               Primary item ID (message ID).
	 * @param int    $secondary_item_id     Secondary item ID (sender ID).
	 * @param int    $total_items           Aggregated count.
	 * @param string $format                'string' or 'array'.
	 * @param string $component_action_name BP action slug being rendered.
	 * @param string $component_name        BP component slug being rendered.
	 * @return string|array
	 */
	public function format_notification( $action, $item_id, $secondary_item_id, $total_items, $format, $component_action_name, $component_name ) {
		unset( $secondary_item_id, $total_items, $component_name );
		if ( self::ACTION !== $component_action_name ) {
			return $action;
		}

		$message = BCM_Messages_Repo::find( $item_id );
		if ( ! $message ) {
			// Message deleted before this notification was (rows from older versions): show readable text, not the raw action slug.
			$text = __( 'A contact message that has since been deleted.', 'buddypress-contact-me' );
			return 'string' === $format ? esc_html( $text ) : array(
				'text' => $text,
				'link' => '',
			);
		}

		$sender_name = $message->sender
			? bp_core_get_user_displayname( $message->sender )
			: $message->name;

		// Deep-link: the notification is shown to the recipient, so take them to their own inbox.
		$recipient_id = get_current_user_id();
		$recipient    = $recipient_id ? $recipient_id : (int) $message->recipient;
		$user_domain  = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $recipient )
			: bp_core_get_user_domain( $recipient );

		$link = trailingslashit( $user_domain ) . BCM_Frontend_Nav::SLUG . '/' . BCM_Frontend_Nav::SUB_INBOX . '/' . (int) $message->id . '/';

		/* translators: %s: sender display name. */
		$text = sprintf( __( '%s sent you a contact message.', 'buddypress-contact-me' ), $sender_name );

		if ( 'string' === $format ) {
			return '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';
		}

		return array(
			'text' => $text,
			'link' => $link,
		);
	}

	/**
	 * Queue a BP notification for the recipient after a message is saved.
	 *
	 * @param int $message_id   Message ID.
	 * @param int $recipient_id Recipient user ID.
	 * @param int $sender_id    Sender user ID (0 for visitor).
	 */
	public function queue_notification( $message_id, $recipient_id, $sender_id ) {
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'notifications' ) ) {
			return;
		}
		$settings = get_option( 'bcm_admin_general_setting', array() );
		if ( ! isset( $settings['bcm_allow_notification'] ) || 'yes' !== $settings['bcm_allow_notification'] ) {
			return;
		}
		bp_notifications_add_notification(
			array(
				'user_id'           => (int) $recipient_id,
				'item_id'           => (int) $message_id,
				'secondary_item_id' => (int) $sender_id,
				'component_name'    => self::COMPONENT,
				'component_action'  => self::ACTION,
				'date_notified'     => bp_core_current_time(),
				'is_new'            => 1,
				'allow_duplicate'   => true,
			)
		);
	}

	/**
	 * Send the BP-templated email to the recipient, then each enabled copy
	 * (sender, administrators) as its own email.
	 *
	 * @param int $message_id   Message ID.
	 * @param int $recipient_id Recipient user ID.
	 * @param int $sender_id    Sender user ID (0 for visitor).
	 */
	public function send_email( $message_id, $recipient_id, $sender_id ) {
		$settings = get_option( 'bcm_admin_general_setting', array() );
		if ( ! isset( $settings['bcm_allow_email'] ) || 'yes' !== $settings['bcm_allow_email'] ) {
			return;
		}
		if ( ! function_exists( 'bp_send_email' ) ) {
			return;
		}

		$message = BCM_Messages_Repo::find( $message_id );
		if ( ! $message ) {
			return;
		}

		$tokens = $this->build_tokens( $message, $recipient_id, $sender_id );

		// One email per reader, so no address is exposed to anyone else and
		// each copy is worded for the person reading it.
		$this->deliver( BCM_Email_Installer::TYPE, (int) $recipient_id, $tokens );

		$skip_ids = array( (int) $recipient_id );

		if ( ! empty( $settings['bcm_allow_sender_copy_email'] ) && 'yes' === $settings['bcm_allow_sender_copy_email'] ) {
			if ( $sender_id ) {
				$skip_ids[] = (int) $sender_id;
				$this->deliver( BCM_Email_Installer::TYPE_SENDER_COPY, (int) $sender_id, $tokens );
			} elseif ( is_email( $message->email ) ) {
				// Guest: BP_Email::set_to() turns each array entry into a
				// recipient, so wrap address => name in an outer array.
				$this->deliver( BCM_Email_Installer::TYPE_SENDER_COPY, array( array( $message->email => (string) $message->name ) ), $tokens );
			}
		}

		if ( ! empty( $settings['bcm_allow_admin_copy_email'] ) && 'yes' === $settings['bcm_allow_admin_copy_email'] ) {
			$admin_ids = get_users(
				array(
					'role'    => 'administrator',
					'fields'  => 'ID',
					'exclude' => $skip_ids,
				)
			);
			foreach ( $admin_ids as $admin_id ) {
				$this->deliver( BCM_Email_Installer::TYPE_ADMIN_COPY, (int) $admin_id, $tokens );
			}
		}
	}

	/**
	 * Send one BP email to one reader.
	 *
	 * @param string    $type   BP email type.
	 * @param int|array $to     User ID, or array( array( email => name ) ) for a guest.
	 * @param array     $tokens Email tokens.
	 */
	private function deliver( $type, $to, $tokens ) {
		// Output buffer the BP send. Two reasons:
		//
		// 1. send_email() runs inside the bp_contact_me_form_save action,
		// which fires from the REST callback (and the classic-POST
		// handler). bp_send_email() walks third-party hooks and BP
		// internals — any of them emitting a PHP notice/warning with
		// WP_DEBUG_DISPLAY on would echo HTML into the response stream
		// BEFORE wp_send_json fires, breaking apiFetch with the
		// "response is not a valid JSON response" error visitors saw
		// in card 9828092913.
		// 2. Notices from bp-core internals we can't fix from here —
		// silencing display while still preserving error_log() is the
		// right boundary.
		$ob_started = ob_start();
		try {
			bp_send_email( $type, $to, array( 'tokens' => $tokens ) );
		} finally {
			if ( $ob_started ) {
				$leaked = ob_get_clean();
				if ( $leaked && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					// Surface what leaked so we can chase root causes
					// without letting it pollute the response.
					error_log( '[bcm] bp_send_email leaked output: ' . wp_strip_all_tags( $leaked ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}
	}

	/**
	 * Build the token array for the BP email template.
	 *
	 * @param object $message      Message row from the repo.
	 * @param int    $recipient_id Recipient user ID.
	 * @param int    $sender_id    Sender user ID (0 for visitor).
	 * @return array
	 */
	private function build_tokens( $message, $recipient_id, $sender_id ) {
		$sender_name = $sender_id
			? bp_core_get_user_displayname( $sender_id )
			: $message->name;
		$recipient   = get_userdata( $recipient_id );

		$user_domain = function_exists( 'bp_members_get_user_url' )
			? bp_members_get_user_url( $recipient_id )
			: bp_core_get_user_domain( $recipient_id );
		$inbox_url   = trailingslashit( $user_domain ) . BCM_Frontend_Nav::SLUG . '/' . BCM_Frontend_Nav::SUB_INBOX . '/' . (int) $message->id . '/';

		return array(
			'sender.name'       => $sender_name,
			'contact.recipient' => $recipient ? $recipient->display_name : '',
			'contact.subject'   => wp_strip_all_tags( stripslashes( (string) $message->subject ) ),
			'contact.message'   => wpautop( wp_strip_all_tags( stripslashes( (string) $message->message ) ) ),
			'inbox.url'         => esc_url( $inbox_url ),
		);
	}
}
