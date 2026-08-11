<?php
/**
 * WP Features Manager admin screen.
 *
 * @package WPFeaturesManager
 *
 * @var array<int,array<string,mixed>>  $module_catalog Module metadata.
 * @var array<int,array<string,string>> $widget_catalog Widget metadata.
 * @var array<int,string>               $enabled_module_ids Enabled module IDs.
 * @var array<int,string>               $enabled_widget_ids Enabled widget IDs.
 * @var int                             $enabled_count Enabled feature count.
 * @var int                             $feature_count Total feature count.
 * @var bool                            $is_empty Whether the catalogs are empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap wp-features-manager">
	<div class="wp-features-manager__masthead">
		<div>
			<p class="wp-features-manager__eyebrow"><?php esc_html_e( 'Modular site controls', 'wp-features-manager' ); ?></p>
			<h1><?php esc_html_e( 'WP Features Manager', 'wp-features-manager' ); ?></h1>
			<p class="wp-features-manager__intro"><?php esc_html_e( 'Choose which modules and Elementor widgets this site loads. New features stay off until you enable them.', 'wp-features-manager' ); ?></p>
		</div>
		<div class="wp-features-manager__masthead-actions">
			<p class="wp-features-manager__summary" id="wp-features-manager-summary" data-total="<?php echo esc_attr( $feature_count ); ?>">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: enabled feature count, 2: total feature count. */
						__( '%1$d of %2$d enabled', 'wp-features-manager' ),
						$enabled_count,
						$feature_count
					)
				);
				?>
			</p>
			<p class="wp-features-manager__state" id="wp-features-manager-save-state" role="status">
				<?php esc_html_e( 'All changes saved', 'wp-features-manager' ); ?>
			</p>
			<button type="submit" form="wp-features-manager-form" class="wp-features-manager__button wp-features-manager__button--primary" <?php disabled( $is_empty ); ?>>
				<span><?php esc_html_e( 'Save changes', 'wp-features-manager' ); ?></span>
			</button>
		</div>
	</div>

	<?php $this->render_updated_notice(); ?>

	<form id="wp-features-manager-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="wp_features_manager_save_settings" />
		<?php wp_nonce_field( 'wp_features_manager_save_settings' ); ?>

		<div class="wp-features-manager__tabs" role="tablist" aria-label="<?php esc_attr_e( 'Feature types', 'wp-features-manager' ); ?>">
			<button
				type="button"
				class="wp-features-manager__tab is-active"
				id="wp-features-manager-tab-modules"
				role="tab"
				aria-selected="true"
				aria-controls="wp-features-manager-panel-modules"
				data-wp-features-manager-tab="modules"
			>
				<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M3 3h6v6H3V3Zm8 0h6v6h-6V3ZM3 11h6v6H3v-6Zm8 0h6v6h-6v-6Z" /></svg>
				<span><?php esc_html_e( 'Modules', 'wp-features-manager' ); ?></span>
				<span class="wp-features-manager__tab-count"><?php echo esc_html( count( $module_catalog ) ); ?></span>
			</button>
			<button
				type="button"
				class="wp-features-manager__tab"
				id="wp-features-manager-tab-widgets"
				role="tab"
				tabindex="-1"
				aria-selected="false"
				aria-controls="wp-features-manager-panel-widgets"
				data-wp-features-manager-tab="widgets"
			>
				<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M3 3h14v10H3V3Zm1.5 1.5v7h11v-7h-11ZM6 15h8v1.5H6V15Z" /></svg>
				<span><?php esc_html_e( 'Elementor Widgets', 'wp-features-manager' ); ?></span>
				<span class="wp-features-manager__tab-count"><?php echo esc_html( count( $widget_catalog ) ); ?></span>
			</button>
		</div>

		<div class="wp-features-manager__toolbar" aria-label="<?php esc_attr_e( 'Feature filters and bulk actions', 'wp-features-manager' ); ?>">
			<label class="wp-features-manager__field wp-features-manager__field--search" for="wp-features-manager-search">
				<span class="screen-reader-text"><?php esc_html_e( 'Search features', 'wp-features-manager' ); ?></span>
				<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M8.5 3a5.5 5.5 0 1 0 3.42 9.8l3.64 3.64 1.06-1.06-3.64-3.64A5.5 5.5 0 0 0 8.5 3Zm0 1.5a4 4 0 1 1 0 8 4 4 0 0 1 0-8Z" /></svg>
				<input id="wp-features-manager-search" class="wp-features-manager__search" type="search" placeholder="<?php esc_attr_e( 'Search features', 'wp-features-manager' ); ?>" <?php disabled( $is_empty ); ?> />
			</label>

			<div class="wp-features-manager__status-filter" data-wp-features-manager-status-filter>
				<span class="screen-reader-text" id="wp-features-manager-status-label"><?php esc_html_e( 'Filter by status', 'wp-features-manager' ); ?></span>
				<input id="wp-features-manager-status" type="hidden" value="all" />
				<button type="button" class="wp-features-manager__status-trigger" id="wp-features-manager-status-trigger" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="wp-features-manager-status-label wp-features-manager-status-value" <?php disabled( $is_empty ); ?>>
					<svg class="wp-features-manager__status-icon" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M3 4h14v1.5H3V4Zm2.5 5.25h9v1.5h-9v-1.5ZM8 14.5h4V16H8v-1.5Z" /></svg>
					<span id="wp-features-manager-status-value"><?php esc_html_e( 'All statuses', 'wp-features-manager' ); ?></span>
					<svg class="wp-features-manager__status-arrow" viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="m5.5 7.5 4.5 4.25 4.5-4.25 1 1.05L10 13.75 4.5 8.55l1-1.05Z" /></svg>
				</button>
				<div class="wp-features-manager__status-menu" id="wp-features-manager-status-menu" role="listbox" aria-labelledby="wp-features-manager-status-label" hidden>
					<button type="button" class="wp-features-manager__status-option is-selected" role="option" aria-selected="true" data-status-value="all"><?php esc_html_e( 'All statuses', 'wp-features-manager' ); ?></button>
					<button type="button" class="wp-features-manager__status-option" role="option" aria-selected="false" data-status-value="enabled"><?php esc_html_e( 'Enabled', 'wp-features-manager' ); ?></button>
					<button type="button" class="wp-features-manager__status-option" role="option" aria-selected="false" data-status-value="disabled"><?php esc_html_e( 'Disabled', 'wp-features-manager' ); ?></button>
					<button type="button" class="wp-features-manager__status-option" role="option" aria-selected="false" data-status-value="unavailable"><?php esc_html_e( 'Unavailable', 'wp-features-manager' ); ?></button>
				</div>
			</div>

			<div class="wp-features-manager__bulk-actions" aria-label="<?php esc_attr_e( 'Bulk actions', 'wp-features-manager' ); ?>">
				<button type="button" class="wp-features-manager__button wp-features-manager__button--enable" data-wp-features-manager-bulk="enable" <?php disabled( $is_empty ); ?>>
					<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15Zm0 1.5a6 6 0 1 1 0 12 6 6 0 0 1 0-12Zm-.75 2.75v2.5h-2.5v1.5h2.5v2.5h1.5v-2.5h2.5v-1.5h-2.5v-2.5h-1.5Z" /></svg>
					<span><?php esc_html_e( 'Enable visible', 'wp-features-manager' ); ?></span>
				</button>
				<button type="button" class="wp-features-manager__button wp-features-manager__button--disable" data-wp-features-manager-bulk="disable" <?php disabled( $is_empty ); ?>>
					<svg viewBox="0 0 20 20" aria-hidden="true" focusable="false"><path d="M10 2.5a7.5 7.5 0 1 0 0 15 7.5 7.5 0 0 0 0-15ZM10 4a6 6 0 0 1 4.56 9.9L6.1 5.44A5.97 5.97 0 0 1 10 4Zm-4.56 2.1 8.46 8.46A6 6 0 0 1 5.44 6.1Z" /></svg>
					<span><?php esc_html_e( 'Disable visible', 'wp-features-manager' ); ?></span>
				</button>
			</div>
		</div>

		<?php if ( $is_empty ) : ?>
			<section class="wp-features-manager__empty" aria-labelledby="wp-features-manager-empty-title">
				<span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
				<h2 id="wp-features-manager-empty-title"><?php esc_html_e( 'No features are available yet', 'wp-features-manager' ); ?></h2>
				<p><?php esc_html_e( 'Modules and widgets will appear here when they are added to the plugin catalogs.', 'wp-features-manager' ); ?></p>
			</section>
		<?php else : ?>
			<section
				class="wp-features-manager__tab-panel"
				id="wp-features-manager-panel-modules"
				role="tabpanel"
				aria-labelledby="wp-features-manager-tab-modules"
				data-wp-features-manager-panel="modules"
			>
				<?php if ( empty( $module_catalog ) ) : ?>
					<div class="wp-features-manager__panel-empty"><?php esc_html_e( 'No modules are included yet.', 'wp-features-manager' ); ?></div>
				<?php else : ?>
					<ul class="wp-features-manager__grid">
						<?php foreach ( $module_catalog as $module_metadata ) : ?>
							<?php $this->render_module_card( $module_metadata, $enabled_module_ids ); ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>

			<section
				class="wp-features-manager__tab-panel"
				id="wp-features-manager-panel-widgets"
				role="tabpanel"
				aria-labelledby="wp-features-manager-tab-widgets"
				data-wp-features-manager-panel="widgets"
				hidden
			>
				<?php if ( empty( $widget_catalog ) ) : ?>
					<div class="wp-features-manager__panel-empty"><?php esc_html_e( 'No Elementor widgets are included yet.', 'wp-features-manager' ); ?></div>
				<?php else : ?>
					<ul class="wp-features-manager__grid">
						<?php foreach ( $widget_catalog as $widget_metadata ) : ?>
							<?php $this->render_widget_card( $widget_metadata, $enabled_widget_ids ); ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</section>

			<p class="wp-features-manager__no-results" id="wp-features-manager-no-results" hidden><?php esc_html_e( 'No features in this tab match the current filters.', 'wp-features-manager' ); ?></p>
		<?php endif; ?>

		<div class="wp-features-manager__actions">
			<button type="submit" class="wp-features-manager__button wp-features-manager__button--primary" <?php disabled( $is_empty ); ?>>
				<span><?php esc_html_e( 'Save changes', 'wp-features-manager' ); ?></span>
			</button>
		</div>
	</form>
</div>
