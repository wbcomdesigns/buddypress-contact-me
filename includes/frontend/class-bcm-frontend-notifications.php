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
			return $action;
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
	 * Send the BP-templated email to the recipient (+ copies).
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

		$recipients = $this->collect_recipients( $settings, $message, $recipient_id, $sender_id );
		if ( empty( $recipients ) ) {
			return;
		}

		$tokens = $this->build_tokens( $message, $recipient_id, $sender_id );

		bp_send_email( BCM_Email_Installer::TYPE, $recipients, array( 'tokens' => $tokens ) );
	}

	/**
	 * Build the recipient list: the recipient of the message, plus any
	 * copy addresses (sender copy, admin copy, multi-user copy) the admin
	 * has enabled. Each entry is either a user ID (for members) or a
	 * BP_Email_Recipient (for guests / raw emails).
	 *
	 * @param array  $settings     Plugin settings array.
	 * @param object $message      Message row from the repo.
	 * @param int    $recipient_id Recipient user ID.
	 * @param int    $sender_id    Sender user ID (0 for visitor).
	 * @return array<int|BP_Email_Recipient>
	 */
	private function collect_recipients( $settings, $message, $recipient_id, $sender_id ) {
		$recipients  = array();
		$seen_ids    = array();
		$seen_emails = array();

		$add_user = static function ( $uid ) use ( &$recipients, &$seen_ids ) {
			$uid = (int) $uid;
			if ( $uid <= 0 || in_array( $uid, $seen_ids, true ) ) {
				return;
			}
			$seen_ids[]   = $uid;
			$recipients[] = $uid;
		};

		/*
		 * Construct BP_Email_Recipient with (address, name) so BP can skip
		 * its search-email fallback. When we pass just an email, BP tries
		 * get_user_by('email', $address) and accesses user_object->ID /
		 * user_object->user_email — which raises undefined-property warnings
		 * on guest emails (no matching WP user) under PHP 8.2+.
		 *
		 * Using the sender's saved name here also means the "Copy of your
		 * message" email the guest receives is addressed to them by name.
		 *
		 * Basecamp card 9823362550.
		 */
		$add_email = static function ( $email, $name = '' ) use ( &$recipients, &$seen_emails ) {
			$email = sanitize_email( $email );
			if ( ! $email || in_array( strtolower( $email ), $seen_emails, true ) ) {
				return;
			}
			$seen_emails[] = strtolower( $email );
			$name          = (string) $name;
			$recipients[]  = class_exists( 'BP_Email_Recipient' )
				? new BP_Email_Recipient( $email, $name )
				: $email;
		};

		$add_user( $recipient_id );

		if ( ! empty( $settings['bcm_allow_sender_copy_email'] ) && 'yes' === $settings['bcm_allow_sender_copy_email'] ) {
			if ( $sender_id ) {
				$add_user( $sender_id );
			} elseif ( ! empty( $message->email ) ) {
				$guest_name = isset( $message->name ) ? (string) $message->name : '';
				$add_email( $message->email, $guest_name );
			}
		}

		if ( ! empty( $settings['bcm_allow_admin_copy_email'] ) && 'yes' === $settings['bcm_allow_admin_copy_email'] ) {
			foreach ( get_users(
				array(
					'role'   => 'administrator',
					'fields' => array( 'ID' ),
				)
			) as $admin ) {
				$add_user( $admin->ID );
			}
		}

		return $recipients;
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
			'sender.name'     => $sender_name,
			'recipient.name'  => $recipient ? $recipient->display_name : '',
			'contact.subject' => wp_strip_all_tags( stripslashes( (string) $message->subject ) ),
			'contact.message' => wpautop( wp_strip_all_tags( stripslashes( (string) $message->message ) ) ),
			'inbox.url'       => esc_url( $inbox_url ),
		);
	}
}
