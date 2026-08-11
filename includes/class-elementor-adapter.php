<?php
/**
 * Elementor integration.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers only allowlisted widget classes with Elementor.
 */
final class Elementor_Adapter {

	/**
	 * Elementor widget category slug.
	 *
	 * @var string
	 */
	const CATEGORY = 'wp-features-manager';

	/**
	 * Widget stylesheet handle.
	 *
	 * @var string
	 */
	const BEFORE_AFTER_STYLE_HANDLE = 'widgets-manager-before-after-image';

	/**
	 * Widget script handle.
	 *
	 * @var string
	 */
	const BEFORE_AFTER_SCRIPT_HANDLE = 'widgets-manager-before-after-image';

	/**
	 * Custom Video widget stylesheet handle.
	 *
	 * @var string
	 */
	const CUSTOM_VIDEO_STYLE_HANDLE = 'widgets-manager-custom-video';

	/**
	 * Custom Video widget script handle.
	 *
	 * @var string
	 */
	const CUSTOM_VIDEO_SCRIPT_HANDLE = 'widgets-manager-custom-video';

	/**
	 * Catalog instance.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Allowlist settings.
	 *
	 * @var Enabled_Widgets
	 */
	private $enabled_widgets;

	/**
	 * Creates the Elementor adapter.
	 *
	 * @param Catalog         $catalog Widget catalog.
	 * @param Enabled_Widgets $enabled_widgets Allowlist settings.
	 */
	public function __construct( Catalog $catalog, Enabled_Widgets $enabled_widgets ) {
		$this->catalog         = $catalog;
		$this->enabled_widgets = $enabled_widgets;
	}

	/**
	 * Hooks into Elementor after compatibility is confirmed.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'register_widget_styles' ) );
		add_action( 'elementor/frontend/after_register_scripts', array( $this, 'register_widget_scripts' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_enabled_widgets' ) );
	}

	/**
	 * Registers the plugin-owned Elementor category.
	 *
	 * @param object $elements_manager Elementor elements manager.
	 * @return void
	 */
	public function register_category( $elements_manager ) {
		if ( ! is_object( $elements_manager ) || ! method_exists( $elements_manager, 'add_category' ) ) {
			return;
		}

		$elements_manager->add_category(
			self::CATEGORY,
			array(
				'title' => __( 'WP Features Manager', 'wp-features-manager' ),
				'icon'  => 'eicon-image-before-after',
			)
		);
	}

	/**
	 * Registers the widget stylesheet without enqueuing it globally.
	 *
	 * @return void
	 */
	public function register_widget_styles() {
		wp_register_style(
			self::BEFORE_AFTER_STYLE_HANDLE,
			WP_FEATURES_MANAGER_URL . 'assets/css/widgets/before-after-image.css',
			array(),
			WP_FEATURES_MANAGER_VERSION
		);
		wp_register_style(
			self::CUSTOM_VIDEO_STYLE_HANDLE,
			WP_FEATURES_MANAGER_URL . 'assets/css/widgets/custom-video.css',
			array(),
			WP_FEATURES_MANAGER_VERSION
		);
	}

	/**
	 * Registers the widget script without enqueuing it globally.
	 *
	 * @return void
	 */
	public function register_widget_scripts() {
		wp_register_script(
			self::BEFORE_AFTER_SCRIPT_HANDLE,
			WP_FEATURES_MANAGER_URL . 'assets/js/widgets/before-after-image.js',
			array(),
			WP_FEATURES_MANAGER_VERSION,
			true
		);
		wp_register_script(
			self::CUSTOM_VIDEO_SCRIPT_HANDLE,
			WP_FEATURES_MANAGER_URL . 'assets/js/widgets/custom-video.js',
			array(),
			WP_FEATURES_MANAGER_VERSION,
			true
		);
	}

	/**
	 * Loads and registers enabled Elementor widgets.
	 *
	 * @param object $elementor_widgets_manager Elementor widget registry.
	 * @return void
	 */
	public function register_enabled_widgets( $elementor_widgets_manager ) {
		if ( ! is_object( $elementor_widgets_manager ) || ! method_exists( $elementor_widgets_manager, 'register' ) ) {
			return;
		}

		$enabled_ids = $this->enabled_widgets->ids();

		if ( empty( $enabled_ids ) ) {
			return;
		}

		foreach ( $this->catalog->for_provider( 'elementor' ) as $widget_metadata ) {
			if ( in_array( $widget_metadata['id'], $enabled_ids, true ) ) {
				$this->register_widget( $elementor_widgets_manager, $widget_metadata );
			}
		}
	}

	/**
	 * Loads an enabled widget class immediately before registration.
	 *
	 * @param object               $elementor_widgets_manager Elementor widget registry.
	 * @param array<string,string> $widget_metadata Widget metadata.
	 * @return void
	 */
	private function register_widget( $elementor_widgets_manager, array $widget_metadata ) {
		$widget_file  = WP_FEATURES_MANAGER_PATH . ltrim( $widget_metadata['file'], '/\\' );
		$widget_class = $widget_metadata['class'];

		if ( ! is_readable( $widget_file ) ) {
			return;
		}

		require_once $widget_file;

		if ( ! class_exists( $widget_class ) ) {
			return;
		}

		$elementor_widgets_manager->register( new $widget_class() );
	}
}
