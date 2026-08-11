<?php
/**
 * Minimal Elementor base widget stub for dependency declarations.
 *
 * @package WPFeaturesManager
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Elementor\\Widget_Base' ) ) {
	abstract class Widget_Base {

		/**
		 * Controlled settings used by isolated render tests.
		 *
		 * @var array<string,mixed>
		 */
		public static $test_settings = array();

		/**
		 * Returns controlled settings without loading Elementor.
		 *
		 * @return array<string,mixed>
		 */
		public function get_settings_for_display() {
			return self::$test_settings;
		}

		/**
		 * Exposes the child render method to isolated tests.
		 *
		 * @return void
		 */
		public function render_for_test() {
			$this->render();
		}
	}
}
