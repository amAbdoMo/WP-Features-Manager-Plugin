<?php
/**
 * Plugin bootstrap.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordinates the Widgets Manager services.
 */
final class Plugin {

	/**
	 * Minimum compatible Elementor version.
	 *
	 * @var string
	 */
	const MINIMUM_ELEMENTOR_VERSION = '3.20.0';

	/**
	 * Plugin instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance;

	/**
	 * Widget catalog.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Enabled widget settings.
	 *
	 * @var Enabled_Widgets
	 */
	private $enabled_widgets;

	/**
	 * Returns the plugin singleton.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Prevents direct instantiation.
	 */
	private function __construct() {
		$this->catalog         = new Catalog( $this->widget_catalog() );
		$this->enabled_widgets = new Enabled_Widgets( $this->catalog );
	}

	/**
	 * Registers WordPress hooks.
	 *
	 * @return void
	 */
	public function boot() {
		add_action( 'plugins_loaded', array( $this, 'initialize' ), 20 );
	}

	/**
	 * Starts the admin screen and compatible provider integrations.
	 *
	 * @return void
	 */
	public function initialize() {
		$admin_page = new Admin_Page( $this->catalog, $this->enabled_widgets );
		$admin_page->register();

		if ( ! $this->has_compatible_elementor() ) {
			add_action( 'admin_notices', array( $this, 'render_elementor_notice' ) );
			return;
		}

		$elementor_adapter = new Elementor_Adapter( $this->catalog, $this->enabled_widgets );
		$elementor_adapter->register();
	}

	/**
	 * Returns the production widget metadata.
	 *
	 * Add each production widget here without loading its PHP class.
	 *
	 * @return array<int,array<string,string>>
	 */
	private function widget_catalog() {
		return array();
	}

	/**
	 * Determines whether Elementor can register widgets.
	 *
	 * @return bool
	 */
	private function has_compatible_elementor() {
		return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' );
	}

	/**
	 * Shows an actionable Elementor compatibility notice.
	 *
	 * @return void
	 */
	public function render_elementor_notice() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) ) {
			$message = sprintf(
				/* translators: 1: minimum Elementor version, 2: installed Elementor version. */
				__( 'Widgets Manager requires Elementor %1$s or later. You are running Elementor %2$s.', 'widgets-manager' ),
				self::MINIMUM_ELEMENTOR_VERSION,
				ELEMENTOR_VERSION
			);
		} else {
			$message = __( 'Widgets Manager requires Elementor to be installed and active.', 'widgets-manager' );
		}
		?>
		<div class="notice notice-warning">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}
}
