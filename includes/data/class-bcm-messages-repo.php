<?php
/**
 * Data access for contact messages.
 *
 * All SQL against the {$prefix}contact_me table lives here — no raw $wpdb
 * in templates, controllers, or REST endpoints.
 *
 * @package BuddyPress_Contact_Me
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data-access layer for the `contact_me` table.
 */
class BCM_Messages_Repo {

	/**
	 * Return the fully-prefixed table name.
	 *
	 * @return string
	 */
	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'contact_me';
	}

	/**
	 * Paged list of messages received by a user.
	 *
	 * @param int $user_id Recipient user ID.
	 * @param int $per_page Items per page.
	 * @param int $page 1-based page number.
	 * @return array<int, object> Messages (newest first).
	 */
	public static function list_for_recipient( $user_id, $per_page = 10, $page = 1 ) {
		global $wpdb;

		$user_id  = (int) $user_id;
		$per_page = max( 1, (int) $per_page );
		$page     = max( 1, (int) $page );
		$offset   = ( $page - 1 ) * $per_page;
		$table    = self::table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, sender, reciever AS recipient, subject, message, name, email, datetime
				 FROM {$table}
				 WHERE reciever = %d
				 ORDER BY datetime DESC, id DESC
				 LIMIT %d OFFSET %d",
				$user_id,
				$per_page,
				$offset
			)
		);
		// phpcs:enable
	}

	/**
	 * Paged list of messages received by a user, restricted to the given
	 * IDs (used for the "Unread" filter).
	 *
	 * @param int   $user_id Recipient user ID.
	 * @param int[] $ids Message IDs to limit to.
	 * @param int   $per_page Items per page.
	 * @param int   $page 1-based page number.
	 * @return array<int, object> Messages (newest first).
	 */
	public static function list_for_recipient_in_ids( $user_id, array $ids, $per_page = 10, $page = 1 ) {
		global $wpdb;

		$ids = array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) );
		if ( empty( $ids ) ) {
			return array();
		}
		$user_id      = (int) $user_id;
		$per_page     = max( 1, (int) $per_page );
		$page         = max( 1, (int) $page );
		$offset       = ( $page - 1 ) * $per_page;
		$table        = self::table();
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, sender, reciever AS recipient, subject, message, name, email, datetime
				 FROM {$table}
				 WHERE reciever = %d AND id IN ({$placeholders})
				 ORDER BY datetime DESC, id DESC
				 LIMIT %d OFFSET %d",
				array_merge( array( $user_id ), $ids, array( $per_page, $offset ) )
			)
		);
		// phpcs:enable
	}

	/**
	 * Total messages received by a user.
	 *
	 * @param int $user_id Recipient user ID.
	 * @return int
	 */
	public static function count_for_recipient( $user_id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE reciever = %d", (int) $user_id )
		);
		// phpcs:enable
	}

	/**
	 * Fetch a single message by ID.
	 *
	 * @param int $id Message ID.
	 * @return object|null
	 */
	public static function find( $id ) {
		global $wpdb;
		$table = self::table();

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, sender, reciever AS recipient, subject, message, name, email, datetime
				 FROM {$table} WHERE id = %d",
				(int) $id
			)
		);
		// phpcs:enable

		return $row ? $row : null;
	}

	/**
	 * Insert a new message.
	 *
	 * @param array $data Associative array with keys: sender (int), recipient (int), subject, message, name, email.
	 * @return int|false Insert ID, or false on failure.
	 */
	public static function insert( $data ) {
		global $wpdb;

		$row = array(
			'sender'   => (int) ( $data['sender'] ?? 0 ),
			'reciever' => (int) ( $data['recipient'] ?? 0 ),
			'subject'  => (string) ( $data['subject'] ?? '' ),
			'message'  => (string) ( $data['message'] ?? '' ),
			'name'     => (string) ( $data['name'] ?? '' ),
			'email'    => (string) ( $data['email'] ?? '' ),
			'datetime' => current_datetime()->format( 'Y-m-d H:i:s' ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ok = $wpdb->insert(
			self::table(),
			$row,
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		return $ok ? (int) $wpdb->insert_id : false;
	}

	/**
	 * Delete a single message, scoped to its recipient.
	 *
	 * @param int $id           Message ID.
	 * @param int $recipient_id Expected recipient (current user).
	 * @return bool True if deleted, false if not found or not owned.
	 */
	public static function delete_for_recipient( $id, $recipient_id ) {
		global $wpdb;

		$id           = (int) $id;
		$recipient_id = (int) $recipient_id;
		if ( $id <= 0 || $recipient_id <= 0 ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$deleted = $wpdb->delete(
			self::table(),
			array(
				'id'       => $id,
				'reciever' => $recipient_id,
			),
			array( '%d', '%d' )
		);

		return (bool) $deleted;
	}

	/**
	 * IDs of messages that still have an unread BP notification for the
	 * given recipient. Returns empty array if notifications component is
	 * unavailable.
	 *
	 * @param int $recipient_id Recipient user ID.
	 * @return int[]
	 */
	public static function unread_message_ids( $recipient_id ) {
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'notifications' ) ) {
			return array();
		}
		global $wpdb;

		$table = buddypress()->notifications->table_name;

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery
		$ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT item_id FROM {$table}
				 WHERE user_id = %d
				   AND component_name = %s
				   AND component_action = %s
				   AND is_new = 1",
				(int) $recipient_id,
				'bcm_user_notifications',
				'bcm_user_notifications_action'
			)
		);
		// phpcs:enable

		return array_map( 'intval', (array) $ids );
	}
}
