<?php
/**
 * Admin page shell: page header, sidebar nav, body slot.
 *
 * Receives from BCM_Admin::render_page():
 *
 * @var array  $bcm_tabs                Tab registry keyed by slug.
 * @var string $active              Active tab slug.
 * @var string $page_url            Base URL (admin.php?page=buddypress-contact-me).
 * @var array  $settings            bcm_admin_general_setting option.
 * @var string $view                View slug (e.g. 'overview', 'settings-notifications').
 * @var string $view_path           Absolute path to the partial.
 * @var bool   $in_settings_group   True when the active tab is a Settings tab.
 * @var string $settings_form_group register_setting() group to pass to settings_fields().
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

$version = defined( 'BUDDYPRESS_CONTACT_ME_VERSION' ) ? BUDDYPRESS_CONTACT_ME_VERSION : '';
?>
<div class="wrap bcm-admin">

	<header class="bcm-page-header">
		<div class="bcm-page-header__title">
			<span class="dashicons dashicons-email" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'BuddyPress Contact Me', 'buddypress-contact-me' ); ?></h1>
				<p class="bcm-page-header__subtitle"><?php esc_html_e( 'A private contact form on every member profile, with notifications, email copies, and role-based access control.', 'buddypress-contact-me' ); ?></p>
			</div>
		</div>
		<div class="bcm-page-header__actions">
			<?php if ( $version ) : ?>
				<span class="bcm-version-pill">v<?php echo esc_html( $version ); ?></span>
			<?php endif; ?>
		</div>
	</header>

	<?php
	/*
	 * Without this marker, core's common.js re-parents every .notice to
	 * sit right after the first <h1> it finds, which slots the "Settings
	 * saved" banner between our title and its subtitle instead of below
	 * the whole header. See basecamp card 9823543990.
	 */
	?>
	<hr class="wp-header-end">

	<?php settings_errors(); ?>

	<div class="bcm-settings-layout">

		<aside class="bcm-settings-sidebar">
			<div class="bcm-settings-sidebar-brand">
				<span class="bcm-settings-brand-icon" aria-hidden="true">
					<span class="dashicons dashicons-email"></span>
				</span>
				<div class="bcm-settings-brand-text">
					<p class="bcm-settings-brand-name"><?php esc_html_e( 'Contact Me', 'buddypress-contact-me' ); ?></p>
					<p class="bcm-settings-brand-sub"><?php esc_html_e( 'Plugin', 'buddypress-contact-me' ); ?></p>
				</div>
			</div>
			<nav class="bcm-settings-sidebar-nav" aria-label="<?php esc_attr_e( 'Contact Me navigation', 'buddypress-contact-me' ); ?>">
				<?php
				$printed_groups = array();
				$group_labels   = array(
					'settings' => esc_html__( 'Settings', 'buddypress-contact-me' ),
					'account'  => esc_html__( 'Account', 'buddypress-contact-me' ),
				);
				foreach ( $bcm_tabs as $slug => $bcm_tab ) {
					$group = isset( $bcm_tab['group'] ) ? $bcm_tab['group'] : 'main';
					if ( 'main' !== $group && ! in_array( $group, $printed_groups, true ) ) {
						echo '<div class="bcm-snav-divider" role="separator"></div>';
						if ( isset( $group_labels[ $group ] ) ) {
							echo '<p class="bcm-snav-section-label">' . esc_html( $group_labels[ $group ] ) . '</p>';
						}
						$printed_groups[] = $group;
					}
					$classes  = 'bcm-snav-link';
					$classes .= $active === $slug ? ' bcm-snav-link--active' : '';
					echo '<a href="' . esc_url( $page_url . '&tab=' . $slug ) . '" class="' . esc_attr( $classes ) . '">';
					echo '<span class="dashicons ' . esc_attr( $bcm_tab['icon'] ) . '" aria-hidden="true"></span>';
					echo esc_html( $bcm_tab['label'] );
					echo '</a>';
				}
				?>
			</nav>
		</aside>

		<div class="bcm-settings-main">
			<?php if ( $in_settings_group ) : ?>
				<form method="post" action="options.php" id="bcm-settings-form">
					<?php settings_fields( $settings_form_group ); ?>
					<?php
					if ( file_exists( $view_path ) ) {
						include $view_path; }
					?>
					<div class="bcm-save-bar">
						<?php submit_button( __( 'Save Settings', 'buddypress-contact-me' ), 'primary bcm-btn bcm-btn-primary', 'submit', false ); ?>
					</div>
				</form>
			<?php else : ?>
				<?php
				if ( file_exists( $view_path ) ) {
					include $view_path; }
				?>
			<?php endif; ?>
		</div>

	</div>
</div>
