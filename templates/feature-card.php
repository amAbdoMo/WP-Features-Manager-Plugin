<?php
/**
 * Module toggle card.
 *
 * @package WPFeaturesManager
 *
 * @var array<string,mixed> $module_metadata Module metadata.
 * @var array<string,mixed> $status Availability status.
 * @var string              $module_id Module ID.
 * @var string              $input_id Checkbox element ID.
 * @var string              $search_text Lowercase search content.
 * @var string              $card_status Current status.
 * @var bool                $is_enabled Whether the module is enabled.
 * @var bool                $toggle_locked Whether the unavailable toggle is locked.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li
	class="wp-features-manager__card"
	data-wp-features-manager-card
	data-search="<?php echo esc_attr( $search_text ); ?>"
	data-status="<?php echo esc_attr( $card_status ); ?>"
	data-availability="<?php echo esc_attr( $status['available'] ? 'available' : 'unavailable' ); ?>"
>
	<div class="wp-features-manager__card-copy">
		<span class="wp-features-manager__card-type"><?php esc_html_e( 'Module', 'wp-features-manager' ); ?></span>
		<h2><?php echo esc_html( $module_metadata['title'] ); ?></h2>
		<p><?php echo esc_html( $module_metadata['description'] ); ?></p>
		<?php if ( ! $status['available'] ) : ?>
			<p class="wp-features-manager__requirement"><?php echo esc_html( $status['reason'] ); ?></p>
		<?php endif; ?>
	</div>
	<div class="wp-features-manager__switch">
		<span class="wp-features-manager__status-text" data-wp-features-manager-status-text>
			<?php echo esc_html( $status['available'] ? ( $is_enabled ? __( 'Enabled', 'wp-features-manager' ) : __( 'Disabled', 'wp-features-manager' ) ) : __( 'Unavailable', 'wp-features-manager' ) ); ?>
		</span>
		<input
			id="<?php echo esc_attr( $input_id ); ?>"
			class="wp-features-manager__toggle"
			type="checkbox"
			name="enabled_modules[]"
			value="<?php echo esc_attr( $module_id ); ?>"
			<?php checked( $is_enabled ); ?>
			<?php disabled( $toggle_locked ); ?>
		/>
		<label for="<?php echo esc_attr( $input_id ); ?>">
			<span class="wp-features-manager__switch-track" aria-hidden="true"></span>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: module title. */
				echo esc_html( sprintf( __( 'Enable %s', 'wp-features-manager' ), $module_metadata['title'] ) );
				?>
			</span>
		</label>
	</div>
</li>
