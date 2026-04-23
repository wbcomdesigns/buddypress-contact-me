<?php
/**
 * WB Plugins hub — landing dashboard at ?page=wbcomplugins.
 *
 * Lists every Wbcom plugin that has registered a submenu under the
 * shared wbcomplugins parent. No manual registration needed: peer
 * plugins appear automatically once their admin page is in the
 * WordPress menu table.
 *
 * Legacy wbcom-wrapper plugins register four boilerplate helper
 * pages (Our Plugins, Our Themes, Support, License) under this hub.
 * Those are not real plugins and must not appear as cards — we
 * filter them out via wbcom_hub_wrapper_helper_slugs.
 * See references/wbcom-wrapper-migration.md Part 15.
 *
 * @package Buddypress_Contact_Me
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

$submenu_entries = isset( $GLOBALS['submenu']['wbcomplugins'] ) && is_array( $GLOBALS['submenu']['wbcomplugins'] )
	? $GLOBALS['submenu']['wbcomplugins']
	: array();

// Slugs of the legacy wbcom-wrapper boilerplate helper pages. Every
// un-migrated Wbcom plugin still registers these four shared pages
// under the hub. They are not plugins, so they must not appear as
// cards on the dashboard — they would read like duplicate tiles.
// Filterable so Pro modules or future wrapper slugs can be added.
$wrapper_helper_slugs = apply_filters(
	'wbcom_hub_wrapper_helper_slugs',
	array(
		'wbcom-plugins-page',
		'wbcom-themes-page',
		'wbcom-support-page',
		'wbcom-license-page',
	)
);

$bcm_plugins = array();
foreach ( $submenu_entries as $entry ) {
	$slug = isset( $entry[2] ) ? (string) $entry[2] : '';
	if ( '' === $slug || 'wbcomplugins' === $slug ) {
		continue;
	}
	if ( in_array( $slug, $wrapper_helper_slugs, true ) ) {
		continue;
	}
	$bcm_plugins[] = array(
		'slug'       => $slug,
		'menu_title' => isset( $entry[0] ) ? wp_strip_all_tags( (string) $entry[0] ) : $slug,
		'page_title' => isset( $entry[3] ) ? wp_strip_all_tags( (string) $entry[3] ) : '',
		'url'        => admin_url( 'admin.php?page=' . rawurlencode( $slug ) ),
	);
}

$plugin_count = count( $bcm_plugins );
?>
<div class="wrap bcm-admin">
	<header class="bcm-page-header">
		<div class="bcm-page-header__title">
			<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
			<div>
				<h1><?php esc_html_e( 'WB Plugins', 'buddypress-contact-me' ); ?></h1>
				<p class="bcm-page-header__subtitle">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %d: active Wbcom plugin count */
							_n(
								'%d Wbcom plugin active on this site.',
								'%d Wbcom plugins active on this site.',
								$plugin_count,
								'buddypress-contact-me'
							),
							$plugin_count
						)
					);
					?>
				</p>
			</div>
		</div>
	</header>

	<?php if ( 0 === $plugin_count ) : ?>
		<div class="bcm-empty-state">
			<span class="bcm-empty-state__icon" aria-hidden="true">
				<span class="dashicons dashicons-lightbulb"></span>
			</span>
			<p class="bcm-empty-state__title"><?php esc_html_e( 'No Wbcom plugins attached to this hub yet', 'buddypress-contact-me' ); ?></p>
			<p class="bcm-empty-state__desc">
				<?php esc_html_e( 'Activate one or more Wbcom plugins and they will appear here automatically.', 'buddypress-contact-me' ); ?>
			</p>
		</div>
	<?php else : ?>
		<div class="bcm-hub-grid">
			<?php foreach ( $bcm_plugins as $p ) : ?>
				<a href="<?php echo esc_url( $p['url'] ); ?>" class="bcm-hub-card">
					<span class="bcm-hub-card__icon" aria-hidden="true">
						<span class="dashicons dashicons-admin-plugins"></span>
					</span>
					<span class="bcm-hub-card__title"><?php echo esc_html( $p['menu_title'] ); ?></span>
					<?php if ( ! empty( $p['page_title'] ) && $p['page_title'] !== $p['menu_title'] ) : ?>
						<span class="bcm-hub-card__subtitle"><?php echo esc_html( $p['page_title'] ); ?></span>
					<?php endif; ?>
					<span class="bcm-hub-card__cta">
						<?php esc_html_e( 'Open settings', 'buddypress-contact-me' ); ?>
						<span class="dashicons dashicons-arrow-right-alt" aria-hidden="true"></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>
	<?php endif; ?>

	<div class="bcm-card" style="margin-top: 20px;">
		<div class="bcm-card__head">
			<p class="bcm-card__title"><?php esc_html_e( 'About WB Plugins', 'buddypress-contact-me' ); ?></p>
		</div>
		<div class="bcm-card__body">
			<p style="margin: 0 0 8px;">
				<?php esc_html_e( 'This hub is the single entry point for every Wbcom Designs plugin installed on your site. Each plugin lives on its own page under this menu and keeps its own settings, licence, and data.', 'buddypress-contact-me' ); ?>
			</p>
			<p style="margin: 0;">
				<a href="https://wbcomdesigns.com/" target="_blank" rel="noopener noreferrer">
					<?php esc_html_e( 'Visit wbcomdesigns.com for more plugins and themes →', 'buddypress-contact-me' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>
