<?php
/**
 * Settings tab: Access control (who can contact whom).
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Parent view provides $settings.
 *
 * @var array $settings
 */

$contact_tab_on = ! empty( $settings['bcm_allow_contact_tab'] ) && 'yes' === $settings['bcm_allow_contact_tab'];
$who_contact    = isset( $settings['bcm_who_contact'] ) && is_array( $settings['bcm_who_contact'] )
	? $settings['bcm_who_contact']
	: array();
$who_contacted  = isset( $settings['bcm_who_contacted'] ) && is_array( $settings['bcm_who_contacted'] )
	? $settings['bcm_who_contacted']
	: array();

global $wp_roles;
$all_roles = isset( $wp_roles ) ? $wp_roles->get_names() : array();
unset( $all_roles['administrator'] );
?>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Where the Form Appears', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'The Contact Me form lives on each member\'s BuddyPress profile page as a dedicated tab. Turn it off here if you only want to use the manual shortcode placement.', 'buddypress-contact-me' ); ?></p>
	</div>
	<table class="form-table">
		<tr>
			<th scope="row"><?php esc_html_e( 'Profile contact tab', 'buddypress-contact-me' ); ?></th>
			<td>
				<label>
					<input type="checkbox"
						name="bcm_admin_general_setting[bcm_allow_contact_tab]"
						value="yes"
						<?php checked( $contact_tab_on ); ?>>
					<?php esc_html_e( 'Show a "Contact" tab on every member profile', 'buddypress-contact-me' ); ?>
				</label>
				<p class="description"><?php esc_html_e( 'Recommended ON. Each member can still individually opt out from their own profile settings.', 'buddypress-contact-me' ); ?></p>
			</td>
		</tr>
	</table>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Who Can Send Messages', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Only members in the roles you pick can see the Contact Me form. Leave all roles unchecked to allow every logged-in member.', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<fieldset class="bcm-role-grid-wrap" data-bcm-role-grid>
			<legend class="screen-reader-text"><?php esc_html_e( 'Roles allowed to send messages', 'buddypress-contact-me' ); ?></legend>
			<div class="bcm-role-grid-toolbar">
				<span class="bcm-role-grid-count" aria-live="polite">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: selected, 2: total */
							__( '%1$d of %2$d roles selected', 'buddypress-contact-me' ),
							count( array_intersect( array_keys( $all_roles ), $who_contact ) ),
							count( $all_roles )
						)
					);
					?>
				</span>
				<div class="bcm-role-grid-actions">
					<button type="button" class="bcm-link" data-bcm-role-action="select-all"><?php esc_html_e( 'Select all', 'buddypress-contact-me' ); ?></button>
					<span aria-hidden="true" class="bcm-role-grid-sep">·</span>
					<button type="button" class="bcm-link" data-bcm-role-action="clear-all"><?php esc_html_e( 'Clear all', 'buddypress-contact-me' ); ?></button>
				</div>
			</div>
			<div class="bcm-role-grid">
				<?php
				foreach ( $all_roles as $role_slug => $role_label ) :
					$input_id = 'bcm-sender-' . sanitize_html_class( $role_slug );
					?>
					<label class="bcm-role-chip" for="<?php echo esc_attr( $input_id ); ?>">
						<input type="checkbox"
							id="<?php echo esc_attr( $input_id ); ?>"
							name="bcm_admin_general_setting[bcm_who_contact][]"
							value="<?php echo esc_attr( $role_slug ); ?>"
							<?php checked( in_array( $role_slug, $who_contact, true ) ); ?>>
						<span class="bcm-role-chip__label"><?php echo esc_html( translate_user_role( $role_label ) ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
	</div>
</div>

<div class="bcm-card">
	<div class="bcm-card__head">
		<p class="bcm-card__title"><?php esc_html_e( 'Who Can Be Contacted', 'buddypress-contact-me' ); ?></p>
		<p class="bcm-card__desc"><?php esc_html_e( 'Only members in the roles you pick here will have a Contact Me form on their profile. Useful to restrict inbound messages to mentors, support staff, or paid members.', 'buddypress-contact-me' ); ?></p>
	</div>
	<div class="bcm-card__body">
		<fieldset class="bcm-role-grid-wrap" data-bcm-role-grid>
			<legend class="screen-reader-text"><?php esc_html_e( 'Roles whose profiles show the Contact Me form', 'buddypress-contact-me' ); ?></legend>
			<div class="bcm-role-grid-toolbar">
				<span class="bcm-role-grid-count" aria-live="polite">
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: selected, 2: total */
							__( '%1$d of %2$d roles selected', 'buddypress-contact-me' ),
							count( array_intersect( array_keys( $all_roles ), $who_contacted ) ),
							count( $all_roles )
						)
					);
					?>
				</span>
				<div class="bcm-role-grid-actions">
					<button type="button" class="bcm-link" data-bcm-role-action="select-all"><?php esc_html_e( 'Select all', 'buddypress-contact-me' ); ?></button>
					<span aria-hidden="true" class="bcm-role-grid-sep">·</span>
					<button type="button" class="bcm-link" data-bcm-role-action="clear-all"><?php esc_html_e( 'Clear all', 'buddypress-contact-me' ); ?></button>
				</div>
			</div>
			<div class="bcm-role-grid">
				<?php
				foreach ( $all_roles as $role_slug => $role_label ) :
					$input_id = 'bcm-recipient-' . sanitize_html_class( $role_slug );
					?>
					<label class="bcm-role-chip" for="<?php echo esc_attr( $input_id ); ?>">
						<input type="checkbox"
							id="<?php echo esc_attr( $input_id ); ?>"
							name="bcm_admin_general_setting[bcm_who_contacted][]"
							value="<?php echo esc_attr( $role_slug ); ?>"
							<?php checked( in_array( $role_slug, $who_contacted, true ) ); ?>>
						<span class="bcm-role-chip__label"><?php echo esc_html( translate_user_role( $role_label ) ); ?></span>
					</label>
				<?php endforeach; ?>
			</div>
		</fieldset>
	</div>
</div>

<div class="bcm-notice bcm-notice--info">
	<span class="dashicons dashicons-info" aria-hidden="true"></span>
	<div>
		<strong><?php esc_html_e( 'Members always have the final say.', 'buddypress-contact-me' ); ?></strong>
		<?php esc_html_e( 'Even if their role is allowed here, each member can hide their own Contact Me form from their profile settings.', 'buddypress-contact-me' ); ?>
	</div>
</div>
