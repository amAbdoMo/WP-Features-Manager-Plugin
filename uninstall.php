<?php
/**
 * Removes Widgets Manager settings during plugin uninstall.
 *
 * @package WidgetsManager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$widgets_manager_option = 'widgets_manager_enabled_widgets';

if ( ! is_multisite() ) {
	delete_option( $widgets_manager_option );
	return;
}

$widgets_manager_site_ids = get_sites(
	array(
		'fields' => 'ids',
		'number' => 0,
	)
);

foreach ( $widgets_manager_site_ids as $widgets_manager_site_id ) {
	switch_to_blog( (int) $widgets_manager_site_id );
	delete_option( $widgets_manager_option );
	restore_current_blog();
}
