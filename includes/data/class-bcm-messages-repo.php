<?php
/**
 * Data access for contact messages.
 *
 * All SQL against the {$prefix}contact_me table lives here: no raw $wpdb
 * in templates, controllers, admin views or REST endpoints.
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
	 * Unix timestamp of a stored message time (stored in UTC since 1.5.1).
	 *
	 * @param string $datetime Stored `Y-m-d H:i:s` value.
	 * @return int
	 */
	public static function timestamp( $datetime ) {
		$ts = strtotime( (string) $datetime . ' UTC' );
		return $ts ? $ts : time();
	}

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
	 * SQL that is 1 when message row `m` still has an unread BuddyPress
	 * notification for its recipient, or '0' without the notifications component.
	 *
	 * @return string Prepared SQL fragment.
	 */
	private static function unread_sql() {
		if ( ! function_exists( 'bp_is_active' ) || ! bp_is_active( 'notifications' ) ) {
			return '0';
		}
		global $wpdb;
		return $wpdb->prepare(
			'EXISTS ( SELECT 1 FROM %i n WHERE n.user_id = m.reciever AND n.item_id = m.id AND n.component_name = %s AND n.component_action = %s AND n.is_new = 1 )',
			buddypress()->notifications->table_name,
			BCM_Frontend_Notifications::COMPONENT,
			BCM_Frontend_Notifications::ACTION
		);
	}

	/**
	 * Paged list of messages received by a user, newest first. Each row carries `is_unread`.
	 *
	 * @param int  $user_id     Recipient user ID.
	 * @param int  $per_page    Items per page.
	 * @param int  $page        1-based page number.
	 * @param bool $unread_only Only messages with an unread notification.
	 * @return array<int, object>
	 */
	public static function list_for_recipient( $user_id, $per_page = 10, $page = 1, $unread_only = false ) {
		global $wpdb;

		$per_page = max( 1, (int) $per_page );
		$offset   = ( max( 1, (int) $page ) - 1 ) * $per_page;
		$unread   = self::unread_sql();
		$where    = $unread_only ? "AND {$unread}" : '';

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- $unread is prepared above.
		return $wpdb->get_results(
			$wpdb->prepare(
				"SELECT m.id, m.sender, m.reciever AS recipient, m.subject, m.message, m.name, m.email, m.datetime, {$unread} AS is_unread
				 FROM %i m
				 WHERE m.reciever = %d {$where}
				 ORDER BY m.datetime DESC, m.id DESC
				 LIMIT %d OFFSET %d",
				self::table(),
				(int) $user_id,
				$per_page,
				$offset
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
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE reciever = %d', self::table(), (int) $user_id ) );
	}

	/**
	 * Messages a user has not opened yet: rows that still exist and have an
	 * unread notification, so the count always matches the Unread list.
	 * Remembered for the request (the nav badge and the script data both ask).
	 *
	 * @param int $recipient_id Recipient user ID.
	 * @return int
	 */
	public static function count_unread_for_recipient( $recipient_id ) {
		static $memo = array();
		$recipient_id = (int) $recipient_id;
		if ( isset( $memo[ $recipient_id ] ) ) {
			return $memo[ $recipient_id ];
		}
		$unread = self::unread_sql();
		if ( '0' === $unread ) {
			return 0;
		}
		global $wpdb;
		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery -- $unread is prepared above.
		$memo[ $recipient_id ] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM %i m WHERE m.reciever = %d AND {$unread}", self::table(), $recipient_id )
		);
		// phpcs:enable
		return $memo[ $recipient_id ];
	}

	/**
	 * Fetch a single message by ID.
	 *
	 * @param int $id Message ID.
	 * @return object|null
	 */
	public static function find( $id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$row = $wpdb->get_row(
			$wpdb->prepare(
				'SELECT id, sender, reciever AS recipient, subject, message, name, email, datetime FROM %i WHERE id = %d',
				self::table(),
				(int) $id
			)
		);
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
			'datetime' => current_time( 'mysql', true ),
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ok = $wpdb->insert( self::table(), $row, array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' ) );

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

		// Drop the recipient's BuddyPress notification for this message so no orphan row is left behind.
		if ( $deleted && function_exists( 'bp_notifications_delete_notifications_by_item_id' ) ) {
			bp_notifications_delete_notifications_by_item_id( $recipient_id, $id, BCM_Frontend_Notifications::COMPONENT, BCM_Frontend_Notifications::ACTION );
		}

		return (bool) $deleted;
	}

	/**
	 * Delete every message a deleted member received, 500 rows per query.
	 *
	 * Hooked to `deleted_user` and `wpmu_delete_user`. BuddyPress removes the
	 * member's notifications itself. Messages the member sent stay with their
	 * recipients.
	 *
	 * @since 1.5.1
	 *
	 * @param int $user_id Deleted user ID.
	 */
	public static function delete_for_user( $user_id ) {
		global $wpdb;
		$user_id = (int) $user_id;
		if ( $user_id <= 0 ) {
			return;
		}
		do {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$deleted = (int) $wpdb->query( $wpdb->prepare( 'DELETE FROM %i WHERE reciever = %d LIMIT 500', self::table(), $user_id ) );
		} while ( $deleted > 0 );
	}

	/**
	 * Totals for the admin Overview tab.
	 *
	 * Ponytail: two full scans per Overview load (admin only, no cache). Cache
	 * in a short transient if a site with millions of messages finds it slow.
	 *
	 * @since 1.5.1
	 *
	 * @return array{total:int,senders:int,recipients:int,opted_out:int}
	 */
	public static function stats() {
		global $wpdb;
		// phpcs:disable WordPress.DB.DirectDatabaseQuery
		$row       = (array) $wpdb->get_row(
			$wpdb->prepare( 'SELECT COUNT(*) AS total, COUNT(DISTINCT NULLIF(sender, 0)) AS senders, COUNT(DISTINCT reciever) AS recipients FROM %i', self::table() ),
			ARRAY_A
		);
		$opted_out = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", 'contact_me_button', 'off' )
		);
		// phpcs:enable
		return array(
			'total'      => (int) ( $row['total'] ?? 0 ),
			'senders'    => (int) ( $row['senders'] ?? 0 ),
			'recipients' => (int) ( $row['recipients'] ?? 0 ),
			'opted_out'  => $opted_out,
		);
	}
}
