<?php
/**
 * Allowlist and conditional loading tests.
 *
 * @package WidgetsManager
 */

use WidgetsManager\Catalog;
use WidgetsManager\Elementor_Adapter;
use WidgetsManager\Enabled_Widgets;

/**
 * Records widgets passed to the Elementor registration boundary.
 */
final class Widgets_Manager_Test_Registry {

	/**
	 * Registered widget instances.
	 *
	 * @var array<int,object>
	 */
	public $widgets = array();

	/**
	 * Records a registered widget.
	 *
	 * @param object $widget Widget instance.
	 * @return void
	 */
	public function register( $widget ) {
		$this->widgets[] = $widget;
	}
}

/**
 * Tests the default widget loading policy.
 */
class Widgets_Manager_Enabled_Widgets_Test extends WP_UnitTestCase {

	/**
	 * Removes the option left by each test.
	 *
	 * @return void
	 */
	public function tearDown() {
		delete_option( Enabled_Widgets::OPTION_NAME );
		parent::tearDown();
	}

	/**
	 * A new installation has no enabled widgets.
	 *
	 * @return void
	 */
	public function test_new_installation_has_no_enabled_widgets() {
		$enabled_widgets = new Enabled_Widgets( $this->catalog() );

		$this->assertSame( array(), $enabled_widgets->ids() );
	}

	/**
	 * Only known IDs are persisted, including provider-qualified IDs.
	 *
	 * @return void
	 */
	public function test_save_keeps_known_provider_ids_and_rejects_unknown_ids() {
		$enabled_widgets = new Enabled_Widgets( $this->catalog() );

		$enabled_widgets->save(
			array(
				'elementor/enabled-test',
				'unknown/widget',
				'elementor/enabled-test',
			)
		);

		$this->assertSame( array( 'elementor/enabled-test' ), $enabled_widgets->ids() );
	}

	/**
	 * An empty allowlist loads and registers no widget classes.
	 *
	 * @return void
	 */
	public function test_elementor_adapter_loads_nothing_when_all_widgets_are_disabled() {
		$catalog         = $this->catalog();
		$enabled_widgets = new Enabled_Widgets( $catalog );
		$adapter          = new Elementor_Adapter( $catalog, $enabled_widgets );
		$registry         = new Widgets_Manager_Test_Registry();

		$this->assertFalse( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );

		$adapter->register_enabled_widgets( $registry );

		$this->assertFalse( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );
		$this->assertSame( array(), $registry->widgets );
	}

	/**
	 * The Elementor adapter loads only an explicitly enabled widget file.
	 *
	 * @return void
	 */
	public function test_elementor_adapter_loads_only_enabled_widget_classes() {
		$catalog         = $this->catalog();
		$enabled_widgets = new Enabled_Widgets( $catalog );
		$adapter          = new Elementor_Adapter( $catalog, $enabled_widgets );
		$registry         = new Widgets_Manager_Test_Registry();

		$enabled_widgets->save( array( 'elementor/enabled-test' ) );

		$this->assertFalse( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Enabled_Test_Widget', false ) );
		$this->assertFalse( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );

		$adapter->register_enabled_widgets( $registry );

		$this->assertTrue( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Enabled_Test_Widget', false ) );
		$this->assertFalse( class_exists( 'WidgetsManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );
		$this->assertCount( 1, $registry->widgets );
		$this->assertInstanceOf( 'WidgetsManager\\Tests\\Fixtures\\Enabled_Test_Widget', $registry->widgets[0] );
	}

	/**
	 * Returns controlled metadata without loading fixture classes.
	 *
	 * @return Catalog
	 */
	private function catalog() {
		return new Catalog(
			array(
				array(
					'id'          => 'elementor/enabled-test',
					'provider'    => 'elementor',
					'class'       => 'WidgetsManager\\Tests\\Fixtures\\Enabled_Test_Widget',
					'file'        => 'tests/fixtures/class-enabled-test-widget.php',
					'title'       => 'Enabled test widget',
					'description' => 'A controlled enabled fixture.',
				),
				array(
					'id'          => 'elementor/disabled-test',
					'provider'    => 'elementor',
					'class'       => 'WidgetsManager\\Tests\\Fixtures\\Disabled_Test_Widget',
					'file'        => 'tests/fixtures/class-disabled-test-widget.php',
					'title'       => 'Disabled test widget',
					'description' => 'A controlled disabled fixture.',
				),
			)
		);
	}
}
