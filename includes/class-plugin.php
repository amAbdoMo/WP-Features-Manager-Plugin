<?php
/**
 * Plugin bootstrap.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Plugin {
	const MINIMUM_ELEMENTOR_VERSION = '3.20.0';

	private static $instance;
	private $widget_catalog;
	private $enabled_widgets;
	private $module_catalog;
	private $enabled_modules;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {}

	public function boot() {
		add_action( 'plugins_loaded', array( $this, 'initialize' ), 20 );
	}

	public function initialize() {
		$this->widget_catalog  = new Catalog( $this->widget_catalog() );
		$this->enabled_widgets = new Enabled_Widgets( $this->widget_catalog );
		$this->module_catalog  = new Module_Catalog( $this->module_catalog() );
		$this->enabled_modules = new Enabled_Modules( $this->module_catalog );

		$admin_page = new Admin_Page( $this->widget_catalog, $this->enabled_widgets, $this->module_catalog, $this->enabled_modules );
		$admin_page->register();

		$module_loader = new Module_Loader( $this->module_catalog, $this->enabled_modules );
		$module_loader->load_enabled_modules();

		if ( defined( 'WIDGETS_MANAGER_VERSION' ) ) {
			add_action( 'admin_notices', array( $this, 'render_legacy_plugin_notice' ) );
			return;
		}

		if ( ! $this->has_compatible_elementor() ) {
			add_action( 'admin_notices', array( $this, 'render_elementor_notice' ) );
			return;
		}

		$elementor_adapter = new Elementor_Adapter( $this->widget_catalog, $this->enabled_widgets );
		$elementor_adapter->register();
	}

	/**
	 * Returns widget metadata without loading widget implementation files.
	 *
	 * @return array<int,array<string,string>>
	 */
	public function widget_catalog() {
		return array(
			array(
				'id'          => 'elementor/before-after-image',
				'provider'    => 'elementor',
				'class'       => 'WPFeaturesManager\\Elementor\\Before_After_Image_Widget',
				'file'        => 'widgets/elementor/class-before-after-image-widget.php',
				'title'       => __( 'Before/After Image', 'wp-features-manager' ),
				'description' => __( 'Compare two images with an accessible, draggable reveal control.', 'wp-features-manager' ),
			),
			array(
				'id'          => 'elementor/custom-video',
				'provider'    => 'elementor',
				'class'       => 'WPFeaturesManager\\Elementor\\Custom_Video_Widget',
				'file'        => 'widgets/elementor/class-custom-video-widget.php',
				'title'       => __( 'Custom Video', 'wp-features-manager' ),
				'description' => __( 'Render self-hosted or direct HTML5 videos with plugin-owned controls and captions.', 'wp-features-manager' ),
			),
		);
	}

	/**
	 * Module metadata stays lightweight so disabled implementation files remain unloaded.
	 *
	 * @return array<int,array<string,mixed>>
	 */
	private function module_catalog() {
		return array(
			array(
				'id'                        => 'acf-elementor-gallery',
				'title'                     => __( 'ACF Elementor Gallery', 'wp-features-manager' ),
				'description'               => __( 'Adds the Gallery (Free) ACF field and a compatible Elementor gallery dynamic tag.', 'wp-features-manager' ),
				'file'                      => 'modules/acf-elementor-gallery/module.php',
				'requires_acf'              => true,
				'requires_elementor'        => true,
				'minimum_elementor_version' => self::MINIMUM_ELEMENTOR_VERSION,
				'conflict'                  => 'acf-elementor-gallery-standalone',
			),
			array(
				'id'          => 'duplicate-content',
				'title'       => __( 'Duplicate Content', 'wp-features-manager' ),
				'description' => __( 'Adds a Duplicate row action for Posts, Pages, and custom post types without leaving the list.', 'wp-features-manager' ),
				'file'        => 'modules/duplicate-content/module.php',
			),
		);
	}

	private function has_compatible_elementor() {
		return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' );
	}

	public function render_elementor_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$message = sprintf(
				/* translators: 1: minimum Elementor version, 2: installed Elementor version. */
				__( 'WP Features Manager requires Elementor %1$s or later for its widgets. You are running Elementor %2$s.', 'wp-features-manager' ),
				self::MINIMUM_ELEMENTOR_VERSION,
				ELEMENTOR_VERSION
			);
		} else {
			$message = __( 'WP Features Manager requires Elementor to be installed and active for its widgets.', 'wp-features-manager' );
		}
		?>
		<div class="notice notice-warning"><p><?php echo esc_html( $message ); ?></p></div>
		<?php
	}

	public function render_legacy_plugin_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		?>
		<div class="notice notice-warning"><p><?php esc_html_e( 'The old Widgets Manager plugin is still active. Deactivate it before using the migrated widgets in WP Features Manager.', 'wp-features-manager' ); ?></p></div>
		<?php
	}
}
