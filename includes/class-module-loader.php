<?php
/**
 * Loads enabled, available modules immediately before they register hooks.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Module_Loader {
	private $catalog;
	private $enabled_modules;
	private $base_path;

	public function __construct( Module_Catalog $catalog, Enabled_Modules $enabled_modules, $base_path = WP_FEATURES_MANAGER_PATH ) {
		$this->catalog = $catalog;
		$this->enabled_modules = $enabled_modules;
		$this->base_path = trailingslashit( $base_path );
	}

	public function load_enabled_modules() {
		foreach ( $this->enabled_modules->ids() as $module_id ) {
			$module = $this->catalog->find( $module_id );
			if ( ! $module || ! $this->catalog->status( $module )['available'] ) {
				continue;
			}
			$this->load_module( $module );
		}
	}

	private function load_module( array $module ) {
		if ( empty( $module['file'] ) ) {
			return;
		}
		$file = $this->base_path . ltrim( $module['file'], "/\\" );
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}
}
