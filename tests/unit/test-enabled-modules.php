<?php
/**
 * Module allowlist and conditional loading tests.
 *
 * @package WPFeaturesManager
 */

use WPFeaturesManager\Enabled_Modules;
use WPFeaturesManager\Module_Catalog;
use WPFeaturesManager\Module_Loader;

class WP_Features_Manager_Enabled_Modules_Test extends WP_UnitTestCase {
	public function tearDown() {
		delete_option( Enabled_Modules::OPTION_NAME );
		unset(
			$GLOBALS['wp_features_manager_disabled_fixture_loaded'],
			$GLOBALS['wp_features_manager_enabled_fixture_loaded'],
			$GLOBALS['wp_features_manager_unavailable_fixture_loaded']
		);
		parent::tearDown();
	}

	public function test_save_accepts_only_known_unique_module_ids() {
		$enabled_modules = new Enabled_Modules( $this->catalog( 'tests/fixtures/module-loader-enabled-fixture.php' ) );
		$enabled_modules->save( array( 'test-module', 'unknown-module', 'test-module', 42 ) );

		$this->assertSame( array( 'test-module' ), $enabled_modules->ids() );
	}

	public function test_disabled_module_implementation_is_not_required() {
		$catalog         = $this->catalog( 'tests/fixtures/module-loader-disabled-fixture.php' );
		$enabled_modules = new Enabled_Modules( $catalog );

		( new Module_Loader( $catalog, $enabled_modules ) )->load_enabled_modules();

		$this->assertArrayNotHasKey( 'wp_features_manager_disabled_fixture_loaded', $GLOBALS );
	}

	public function test_enabled_available_module_implementation_is_required() {
		$catalog         = $this->catalog( 'tests/fixtures/module-loader-enabled-fixture.php' );
		$enabled_modules = new Enabled_Modules( $catalog );
		$enabled_modules->save( array( 'test-module' ) );

		( new Module_Loader( $catalog, $enabled_modules ) )->load_enabled_modules();

		$this->assertTrue( $GLOBALS['wp_features_manager_enabled_fixture_loaded'] );
	}

	public function test_enabled_unavailable_module_stays_saved_without_loading_implementation() {
		$catalog         = $this->catalog(
			'tests/fixtures/module-loader-unavailable-fixture.php',
			array( 'requires_acf' => true )
		);
		$enabled_modules = new Enabled_Modules( $catalog );
		$enabled_modules->save( array( 'test-module' ) );

		( new Module_Loader( $catalog, $enabled_modules ) )->load_enabled_modules();

		$this->assertSame( array( 'test-module' ), $enabled_modules->ids() );
		$this->assertArrayNotHasKey( 'wp_features_manager_unavailable_fixture_loaded', $GLOBALS );
	}

	private function catalog( $file, array $extra_metadata = array() ) {
		return new Module_Catalog(
			array(
				array_merge(
					array(
						'id'          => 'test-module',
						'title'       => 'Test module',
						'description' => 'A controlled module fixture.',
						'file'        => $file,
					),
					$extra_metadata
				),
			)
		);
	}
}
