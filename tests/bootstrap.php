<?php
/**
 * WordPress test bootstrap.
 *
 * @package WidgetsManager
 */

$_tests_directory = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_directory ) {
	$_tests_directory = '/tmp/wordpress-tests-lib';
}

require_once $_tests_directory . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	static function() {
		require dirname( __DIR__ ) . '/widgets-manager.php';
	}
);

require $_tests_directory . '/includes/bootstrap.php';
