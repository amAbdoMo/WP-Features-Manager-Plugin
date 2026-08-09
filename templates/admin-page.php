<?php
/**
 * Widgets Manager admin screen.
 *
 * @package WidgetsManager
 *
 * @var array<int,array<string,string>> $widget_catalog Widget metadata.
 * @var array<int,string>               $enabled_ids Enabled widget IDs.
 * @var int                             $enabled_count Enabled widget count.
 * @var int                             $widget_count Total widget count.
 * @var bool                            $is_empty Whether the catalog is empty.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="wrap widgets-manager">
	<div class="widgets-manager__masthead">
		<div>
			<p class="widgets-manager__eyebrow"><?php esc_html_e( 'Elementor controls', 'widgets-manager' ); ?></p>
			<h1><?php esc_html_e( 'Widgets Manager', 'widgets-manager' ); ?></h1>
			<p class="widgets-manager__intro"><?php esc_html_e( 'Choose which Widgets Manager widgets appear in Elementor. New widgets stay off until you enable them.', 'widgets-manager' ); ?></p>
		</div>
		<div class="widgets-manager__masthead-actions">
			<p class="widgets-manager__summary" id="widgets-manager-summary" data-total="<?php echo esc_attr( $widget_count ); ?>">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: enabled widget count, 2: total widget count. */
						__( '%1$d of %2$d enabled', 'widgets-manager' ),
						$enabled_count,
						$widget_count
					)
				);
				?>
			</p>
			<p class="widgets-manager__state" id="widgets-manager-save-state" role="status">
				<?php esc_html_e( 'All changes saved', 'widgets-manager' ); ?>
			</p>
			<button type="submit" form="widgets-manager-form" class="button button-primary" <?php disabled( $is_empty ); ?>><?php esc_html_e( 'Save changes', 'widgets-manager' ); ?></button>
		</div>
	</div>

	<?php $this->render_updated_notice(); ?>

	<form id="widgets-manager-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="widgets_manager_save_widgets" />
		<?php wp_nonce_field( 'widgets_manager_save_widgets' ); ?>

		<div class="widgets-manager__toolbar" aria-label="<?php esc_attr_e( 'Widget filters and bulk actions', 'widgets-manager' ); ?>">
			<label class="screen-reader-text" for="widgets-manager-search"><?php esc_html_e( 'Search widgets', 'widgets-manager' ); ?></label>
			<input id="widgets-manager-search" class="widgets-manager__search" type="search" placeholder="<?php esc_attr_e( 'Search widgets', 'widgets-manager' ); ?>" <?php disabled( $is_empty ); ?> />

			<label class="screen-reader-text" for="widgets-manager-status"><?php esc_html_e( 'Filter by status', 'widgets-manager' ); ?></label>
			<select id="widgets-manager-status" <?php disabled( $is_empty ); ?>>
				<option value="all"><?php esc_html_e( 'All statuses', 'widgets-manager' ); ?></option>
				<option value="enabled"><?php esc_html_e( 'Enabled', 'widgets-manager' ); ?></option>
				<option value="disabled"><?php esc_html_e( 'Disabled', 'widgets-manager' ); ?></option>
			</select>

			<div class="widgets-manager__bulk-actions" aria-label="<?php esc_attr_e( 'Bulk actions', 'widgets-manager' ); ?>">
				<button type="button" class="button" data-widgets-manager-bulk="enable" <?php disabled( $is_empty ); ?>><?php esc_html_e( 'Enable visible', 'widgets-manager' ); ?></button>
				<button type="button" class="button" data-widgets-manager-bulk="disable" <?php disabled( $is_empty ); ?>><?php esc_html_e( 'Disable visible', 'widgets-manager' ); ?></button>
			</div>
		</div>

		<?php if ( $is_empty ) : ?>
			<section class="widgets-manager__empty" aria-labelledby="widgets-manager-empty-title">
				<span class="dashicons dashicons-screenoptions" aria-hidden="true"></span>
				<h2 id="widgets-manager-empty-title"><?php esc_html_e( 'No widgets are available yet', 'widgets-manager' ); ?></h2>
				<p><?php esc_html_e( 'The catalog is ready. Widgets will appear here when they are added to this plugin.', 'widgets-manager' ); ?></p>
			</section>
		<?php else : ?>
			<ul class="widgets-manager__grid" id="widgets-manager-catalog">
				<?php foreach ( $widget_catalog as $widget_metadata ) : ?>
					<?php $this->render_widget_card( $widget_metadata, $enabled_ids ); ?>
				<?php endforeach; ?>
			</ul>
			<p class="widgets-manager__no-results" id="widgets-manager-no-results" hidden><?php esc_html_e( 'No widgets match these filters.', 'widgets-manager' ); ?></p>
		<?php endif; ?>

		<div class="widgets-manager__actions">
			<button type="submit" class="button button-primary" <?php disabled( $is_empty ); ?>><?php esc_html_e( 'Save changes', 'widgets-manager' ); ?></button>
		</div>
	</form>
</div>
