<?php
/**
 * Allowlist and conditional loading tests.
 *
 * @package WPFeaturesManager
 */

use WPFeaturesManager\Catalog;
use WPFeaturesManager\Elementor_Adapter;
use WPFeaturesManager\Enabled_Widgets;
use WPFeaturesManager\Plugin;

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
		delete_option( Enabled_Widgets::LEGACY_OPTION_NAME );
		parent::tearDown();
	}

	/**
	 * The production catalog describes the widget without loading its class.
	 *
	 * @return void
	 */
	public function test_production_catalog_describes_widgets_without_loading_their_classes() {
		$catalog      = new Catalog( Plugin::instance()->widget_catalog() );
		$image_widget = $catalog->find( 'elementor/before-after-image' );
		$video_widget = $catalog->find( 'elementor/custom-video' );

		$this->assertIsArray( $image_widget );
		$this->assertSame( 'elementor', $image_widget['provider'] );
		$this->assertSame( 'WPFeaturesManager\\Elementor\\Before_After_Image_Widget', $image_widget['class'] );
		$this->assertSame( 'widgets/elementor/class-before-after-image-widget.php', $image_widget['file'] );
		$this->assertIsArray( $video_widget );
		$this->assertSame( 'elementor', $video_widget['provider'] );
		$this->assertSame( 'WPFeaturesManager\\Elementor\\Custom_Video_Widget', $video_widget['class'] );
		$this->assertSame( 'widgets/elementor/class-custom-video-widget.php', $video_widget['file'] );
	}

	/**
	 * The disabled production widget is not loaded or registered.
	 *
	 * @return void
	 */
	public function test_production_widget_remains_unloaded_when_disabled() {
		$widget_class = 'WPFeaturesManager\\Elementor\\Before_After_Image_Widget';
		$catalog      = new Catalog( Plugin::instance()->widget_catalog() );
		$adapter      = new Elementor_Adapter( $catalog, new Enabled_Widgets( $catalog ) );
		$registry     = new Widgets_Manager_Test_Registry();
		$was_loaded  = class_exists( $widget_class, false );

		$adapter->register_enabled_widgets( $registry );

		$this->assertSame( $was_loaded, class_exists( $widget_class, false ) );
		$this->assertSame( array(), $registry->widgets );
	}

	/**
	 * Widget asset handles are registered without global enqueueing.
	 *
	 * @return void
	 */
	public function test_widget_assets_are_registered_without_global_enqueueing() {
		$catalog = $this->catalog();
		$adapter = new Elementor_Adapter( $catalog, new Enabled_Widgets( $catalog ) );

		$adapter->register_widget_styles();
		$adapter->register_widget_scripts();

		$this->assertTrue( wp_style_is( Elementor_Adapter::BEFORE_AFTER_STYLE_HANDLE, 'registered' ) );
		$this->assertTrue( wp_script_is( Elementor_Adapter::BEFORE_AFTER_SCRIPT_HANDLE, 'registered' ) );
		$this->assertTrue( wp_style_is( Elementor_Adapter::CUSTOM_VIDEO_STYLE_HANDLE, 'registered' ) );
		$this->assertTrue( wp_script_is( Elementor_Adapter::CUSTOM_VIDEO_SCRIPT_HANDLE, 'registered' ) );
		$this->assertFalse( wp_style_is( Elementor_Adapter::BEFORE_AFTER_STYLE_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Elementor_Adapter::BEFORE_AFTER_SCRIPT_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_style_is( Elementor_Adapter::CUSTOM_VIDEO_STYLE_HANDLE, 'enqueued' ) );
		$this->assertFalse( wp_script_is( Elementor_Adapter::CUSTOM_VIDEO_SCRIPT_HANDLE, 'enqueued' ) );
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
	 * The previous plugin allowlist is copied once into the renamed option.
	 *
	 * @return void
	 */
	public function test_legacy_allowlist_is_migrated_without_deleting_its_source() {
		update_option( Enabled_Widgets::LEGACY_OPTION_NAME, array( 'elementor/enabled-test', 'unknown/widget' ), false );
		$enabled_widgets = new Enabled_Widgets( $this->catalog() );

		$this->assertSame( array( 'elementor/enabled-test' ), $enabled_widgets->ids() );
		$this->assertSame( array( 'elementor/enabled-test' ), get_option( Enabled_Widgets::OPTION_NAME ) );
		$this->assertSame( array( 'elementor/enabled-test', 'unknown/widget' ), get_option( Enabled_Widgets::LEGACY_OPTION_NAME ) );
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

		$this->assertFalse( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );

		$adapter->register_enabled_widgets( $registry );

		$this->assertFalse( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );
		$this->assertSame( array(), $registry->widgets );
	}

	/**
	 * Enabled catalog entries fail safely when their file or class is unavailable.
	 *
	 * @return void
	 */
	public function test_elementor_adapter_skips_missing_widget_files_and_classes() {
		$catalog = new Catalog(
			array(
				array(
					'id'          => 'elementor/missing-file',
					'provider'    => 'elementor',
					'class'       => 'WPFeaturesManager\\Tests\\Fixtures\\Missing_File_Widget',
					'file'        => 'tests/fixtures/class-missing-widget.php',
					'title'       => 'Missing file',
					'description' => 'Missing file fixture.',
				),
				array(
					'id'          => 'elementor/missing-class',
					'provider'    => 'elementor',
					'class'       => 'WPFeaturesManager\\Tests\\Fixtures\\Missing_Declared_Widget',
					'file'        => 'tests/fixtures/class-widget-without-declared-class.php',
					'title'       => 'Missing class',
					'description' => 'Missing declared class fixture.',
				),
			)
		);
		$enabled_widgets = new Enabled_Widgets( $catalog );
		$adapter          = new Elementor_Adapter( $catalog, $enabled_widgets );
		$registry         = new Widgets_Manager_Test_Registry();

		$enabled_widgets->save( array( 'elementor/missing-file', 'elementor/missing-class' ) );
		$adapter->register_enabled_widgets( $registry );

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

		$this->assertFalse( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Enabled_Test_Widget', false ) );
		$this->assertFalse( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );

		$adapter->register_enabled_widgets( $registry );

		$this->assertTrue( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Enabled_Test_Widget', false ) );
		$this->assertFalse( class_exists( 'WPFeaturesManager\\Tests\\Fixtures\\Disabled_Test_Widget', false ) );
		$this->assertCount( 1, $registry->widgets );
		$this->assertInstanceOf( 'WPFeaturesManager\\Tests\\Fixtures\\Enabled_Test_Widget', $registry->widgets[0] );
	}

	/**
	 * The production widget loads through the adapter only after its full ID is enabled.
	 *
	 * The base-widget stub isolates dependency declarations; it does not test Elementor rendering.
	 *
	 * @return void
	 */
	public function test_production_widget_registers_only_when_provider_qualified_id_is_enabled() {
		$widget_class = 'WPFeaturesManager\\Elementor\\Before_After_Image_Widget';
		$catalog      = new Catalog( Plugin::instance()->widget_catalog() );
		$enabled      = new Enabled_Widgets( $catalog );
		$adapter      = new Elementor_Adapter( $catalog, $enabled );
		$registry     = new Widgets_Manager_Test_Registry();

		$this->assertFalse( class_exists( $widget_class, false ) );
		require_once WP_FEATURES_MANAGER_PATH . 'tests/fixtures/class-elementor-widget-base-stub.php';
		$enabled->save( array( 'elementor/before-after-image' ) );
		$adapter->register_enabled_widgets( $registry );

		$this->assertTrue( class_exists( $widget_class, false ) );
		$this->assertCount( 1, $registry->widgets );
		$this->assertInstanceOf( $widget_class, $registry->widgets[0] );
		$this->assertSame( array( Elementor_Adapter::BEFORE_AFTER_STYLE_HANDLE ), $registry->widgets[0]->get_style_depends() );
		$this->assertSame( array( Elementor_Adapter::BEFORE_AFTER_SCRIPT_HANDLE ), $registry->widgets[0]->get_script_depends() );
	}

	/**
	 * The production video widget registers only after its full ID is enabled.
	 *
	 * The base-widget stub isolates dependency declarations from Elementor rendering.
	 *
	 * @return void
	 */
	public function test_custom_video_registers_only_when_enabled() {
		$widget_class = 'WPFeaturesManager\\Elementor\\Custom_Video_Widget';
		$catalog      = new Catalog( Plugin::instance()->widget_catalog() );
		$enabled      = new Enabled_Widgets( $catalog );
		$adapter      = new Elementor_Adapter( $catalog, $enabled );
		$registry     = new Widgets_Manager_Test_Registry();

		$this->assertFalse( class_exists( $widget_class, false ) );
		require_once WP_FEATURES_MANAGER_PATH . 'tests/fixtures/class-elementor-widget-base-stub.php';
		$adapter->register_enabled_widgets( $registry );
		$this->assertFalse( class_exists( $widget_class, false ) );
		$this->assertSame( array(), $registry->widgets );

		$enabled->save( array( 'elementor/custom-video' ) );
		$adapter->register_enabled_widgets( $registry );
		$this->assertTrue( class_exists( $widget_class, false ) );
		$this->assertCount( 1, $registry->widgets );
		$this->assertSame( array( Elementor_Adapter::CUSTOM_VIDEO_STYLE_HANDLE ), $registry->widgets[0]->get_style_depends() );
		$this->assertSame( array( Elementor_Adapter::CUSTOM_VIDEO_SCRIPT_HANDLE ), $registry->widgets[0]->get_script_depends() );
	}

	/**
	 * Invalid alternate source entries are not passed to rendered source markup.
	 *
	 * @return void
	 */
	public function test_custom_video_skips_malformed_and_duplicate_alternate_sources() {
		$widget_class = 'WPFeaturesManager\\Elementor\\Custom_Video_Widget';

		require_once WP_FEATURES_MANAGER_PATH . 'tests/fixtures/class-elementor-widget-base-stub.php';
		require_once WP_FEATURES_MANAGER_PATH . 'widgets/elementor/class-custom-video-widget.php';

		$this->assertSame(
			array(
				array( 'url' => 'https://example.test/movie.mp4', 'mime' => 'video/mp4' ),
				array( 'url' => 'https://example.test/movie.webm', 'mime' => 'video/webm' ),
			),
			$widget_class::normalized_sources(
				array(
					'video_source_type' => 'external',
					'video_url'          => array( 'url' => 'https://example.test/movie.mp4' ),
					'alternate_sources'  => array(
						array( 'url' => array( 'url' => 'javascript:alert(1)' ), 'mime' => 'video/mp4' ),
						array( 'url' => array( 'url' => 'https://example.test/movie.webm' ), 'mime' => 'video/webm' ),
						array( 'url' => array( 'url' => 'https://example.test/movie.mp4' ), 'mime' => 'video/mp4' ),
					),
				)
			)
		);
	}

	/**
	 * Rendered settings are escaped and expose one selected captions option.
	 *
	 * @return void
	 */
	public function test_custom_video_render_escapes_text_and_caption_state() {
		$widget_class = 'WPFeaturesManager\\Elementor\\Custom_Video_Widget';

		require_once WP_FEATURES_MANAGER_PATH . 'tests/fixtures/class-elementor-widget-base-stub.php';
		require_once WP_FEATURES_MANAGER_PATH . 'widgets/elementor/class-custom-video-widget.php';

		\Elementor\Widget_Base::$test_settings = array(
			'video_source_type' => 'external',
			'video_url'         => array( 'url' => 'https://example.test/movie.mp4' ),
			'show_overlay'      => 'yes',
			'show_play_button'  => 'yes',
			'play_label'        => 'Play & watch',
			'overlay_title'     => '<script>alert(1)</script>',
			'title_tag'         => 'script',
			'caption_tracks'    => array(
				array(
					'url'      => array( 'url' => 'https://example.test/en.vtt' ),
					'language' => 'en',
					'label'    => '<b>English</b>',
					'kind'     => 'captions',
					'default'  => 'yes',
				),
			),
		);

		ob_start();
		$widget = new $widget_class();
		$widget->render_for_test();
		$markup = ob_get_clean();

		$this->assertStringNotContainsString( '<script>', $markup );
		$this->assertStringContainsString( '<h3 class="widgets-manager-custom-video__title">alert(1)</h3>', $markup );
		$this->assertStringContainsString( 'data-wm-track="off">', $markup );
		$this->assertStringContainsString( 'aria-checked="false" data-wm-track="off"', $markup );
		$this->assertStringContainsString( 'aria-checked="true" data-wm-track="0"', $markup );
		$this->assertStringNotContainsString( '<b>English</b>', $markup );
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
					'class'       => 'WPFeaturesManager\\Tests\\Fixtures\\Enabled_Test_Widget',
					'file'        => 'tests/fixtures/class-enabled-test-widget.php',
					'title'       => 'Enabled test widget',
					'description' => 'A controlled enabled fixture.',
				),
				array(
					'id'          => 'elementor/disabled-test',
					'provider'    => 'elementor',
					'class'       => 'WPFeaturesManager\\Tests\\Fixtures\\Disabled_Test_Widget',
					'file'        => 'tests/fixtures/class-disabled-test-widget.php',
					'title'       => 'Disabled test widget',
					'description' => 'A controlled disabled fixture.',
				),
			)
		);
	}
}
