<?php
/**
 * Widget toggle card.
 *
 * @package WPFeaturesManager
 *
 * @var string $widget_id Widget ID.
 * @var string $widget_title Widget title.
 * @var string $widget_description Widget description.
 * @var string $input_id Checkbox element ID.
 * @var string $search_text Lowercase search content.
 * @var bool   $is_enabled Whether the widget is enabled.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li class="wp-features-manager__card" data-wp-features-manager-card data-search="<?php echo esc_attr( $search_text ); ?>" data-status="<?php echo esc_attr( $is_enabled ? 'enabled' : 'disabled' ); ?>">
	<div class="wp-features-manager__card-copy">
		<h2><?php echo esc_html( $widget_title ); ?></h2>
		<p><?php echo esc_html( $widget_description ); ?></p>
	</div>
	<div class="wp-features-manager__switch">
		<span class="wp-features-manager__status-text" data-wp-features-manager-status-text><?php echo esc_html( $is_enabled ? __( 'Enabled', 'wp-features-manager' ) : __( 'Disabled', 'wp-features-manager' ) ); ?></span>
		<input id="<?php echo esc_attr( $input_id ); ?>" class="wp-features-manager__toggle" type="checkbox" name="enabled_widgets[]" value="<?php echo esc_attr( $widget_id ); ?>" <?php checked( $is_enabled ); ?> />
		<label for="<?php echo esc_attr( $input_id ); ?>">
			<span class="wp-features-manager__switch-track" aria-hidden="true"></span>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: widget title. */
				echo esc_html( sprintf( __( 'Enable %s', 'wp-features-manager' ), $widget_title ) );
				?>
			</span>
		</label>
	</div>
</li>
