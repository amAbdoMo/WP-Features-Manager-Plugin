<?php
/**
 * GitHub Release updater for WP Features Manager.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Self_Updater {
	const GITHUB_REPOSITORY = 'https://github.com/amAbdoMo/WP-Features-Manager-Plugin';
	const PLUGIN_SLUG = 'wp-features-manager';
	const RELEASE_ASSET = 'wp-features-manager.zip';
	private static $update_checker;

	public static function register() {
		if ( self::$update_checker ) {
			return;
		}
		$puc_file = WP_FEATURES_MANAGER_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
		if ( ! is_readable( $puc_file ) ) {
			return;
		}
		require_once $puc_file;
		self::$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker( self::GITHUB_REPOSITORY, WP_FEATURES_MANAGER_FILE, self::PLUGIN_SLUG );
		add_filter( self::$update_checker->getUniqueName( 'manual_check_link' ), '__return_empty_string' );
		self::$update_checker->getVcsApi()->enableReleaseAssets( '/^' . preg_quote( self::RELEASE_ASSET, '/' ) . '$/', \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::REQUIRE_RELEASE_ASSETS );
		add_filter( self::$update_checker->getUniqueName( 'vcs_update_detection_strategies' ), array( __CLASS__, 'release_only_strategies' ) );
	}

	public static function release_only_strategies( $strategies ) {
		$release = \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::STRATEGY_LATEST_RELEASE;
		return isset( $strategies[ $release ] ) ? array( $release => $strategies[ $release ] ) : array();
	}
}
