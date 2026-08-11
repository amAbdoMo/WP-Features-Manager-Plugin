<?php
/**
 * GitHub Release updater for Widgets Manager.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers self-updates from the canonical GitHub Release asset.
 */
final class Self_Updater {
	const GITHUB_REPOSITORY = 'https://github.com/amAbdoMo/WP-Widgets-Manager-Plugin';
	const PLUGIN_SLUG       = 'widgets-manager';
	const RELEASE_ASSET     = 'widgets-manager.zip';

	/**
	 * Update checker retained for the request lifetime.
	 *
	 * @var object|null
	 */
	private static $update_checker;

	/**
	 * Registers Plugin Update Checker.
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::$update_checker ) {
			return;
		}

		$puc_file = WIDGETS_MANAGER_PATH . 'vendor/plugin-update-checker/plugin-update-checker.php';
		if ( ! is_readable( $puc_file ) ) {
			return;
		}

		require_once $puc_file;

		self::$update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
			self::GITHUB_REPOSITORY,
			WIDGETS_MANAGER_FILE,
			self::PLUGIN_SLUG
		);

		self::$update_checker->getVcsApi()->enableReleaseAssets(
			'/^' . preg_quote( self::RELEASE_ASSET, '/' ) . '$/',
			\YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::REQUIRE_RELEASE_ASSETS
		);

		add_filter(
			self::$update_checker->getUniqueName( 'vcs_update_detection_strategies' ),
			array( __CLASS__, 'release_only_strategies' )
		);
	}

	/**
	 * Prevents fallback to GitHub tag and branch source archives.
	 *
	 * @param array $strategies Available update detection strategies.
	 * @return array
	 */
	public static function release_only_strategies( $strategies ) {
		$release_strategy = \YahnisElsts\PluginUpdateChecker\v5p7\Vcs\Api::STRATEGY_LATEST_RELEASE;
		if ( ! isset( $strategies[ $release_strategy ] ) ) {
			return array();
		}

		return array( $release_strategy => $strategies[ $release_strategy ] );
	}
}
