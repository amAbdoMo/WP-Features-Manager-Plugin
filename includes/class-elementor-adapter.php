<?php
/**
 * Elementor integration.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers only allowlisted widget classes with Elementor.
 */
final class Elementor_Adapter {

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
		add_action( 'elementor/widgets/register', array( $this, 'register_enabled_widgets' ) );
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
		$widget_file  = WIDGETS_MANAGER_PATH . ltrim( $widget_metadata['file'], '/\\' );
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
