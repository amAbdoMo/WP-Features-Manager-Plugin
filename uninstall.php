<?php
/**
 * Removes WP Features Manager settings during plugin uninstall.
 *
 * @package WPFeaturesManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = array( 'wp_features_manager_enabled_modules', 'wp_features_manager_enabled_widgets', 'widgets_manager_enabled_widgets' );
if ( ! is_multisite() ) {
	foreach ( $options as $option ) { delete_option( $option ); }
	return;
}
foreach ( get_sites( array( 'fields' => 'ids', 'number' => 0 ) ) as $site_id ) {
	switch_to_blog( (int) $site_id );
	foreach ( $options as $option ) { delete_option( $option ); }
	restore_current_blog();
}
