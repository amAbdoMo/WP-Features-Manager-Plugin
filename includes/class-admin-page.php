<?php
/**
 * Widgets Manager admin page.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders and saves the widget allowlist screen.
 */
final class Admin_Page {

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	const SLUG = 'widgets-manager';

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
	 * Admin page hook suffix.
	 *
	 * @var string
	 */
	private $page_hook = '';

	/**
	 * Creates the admin page.
	 *
	 * @param Catalog         $catalog Widget catalog.
	 * @param Enabled_Widgets $enabled_widgets Allowlist settings.
	 */
	public function __construct( Catalog $catalog, Enabled_Widgets $enabled_widgets ) {
		$this->catalog         = $catalog;
		$this->enabled_widgets = $enabled_widgets;
	}

	/**
	 * Registers admin hooks.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_widgets_manager_save_widgets', array( $this, 'save' ) );
	}

	/**
	 * Adds the top-level Widgets Manager screen.
	 *
	 * @return void
	 */
	public function add_menu_page() {
		$this->page_hook = add_menu_page(
			__( 'Widgets Manager', 'widgets-manager' ),
			__( 'Widgets Manager', 'widgets-manager' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-screenoptions',
			58
		);
	}

	/**
	 * Enqueues assets only on this plugin screen.
	 *
	 * @param string $hook_suffix Current screen hook.
	 * @return void
	 */
	public function enqueue_assets( $hook_suffix ) {
		if ( $this->page_hook !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style(
			'widgets-manager-admin',
			WIDGETS_MANAGER_URL . 'assets/css/admin.css',
			array(),
			WIDGETS_MANAGER_VERSION
		);
		wp_enqueue_script(
			'widgets-manager-admin',
			WIDGETS_MANAGER_URL . 'assets/js/admin.js',
			array(),
			WIDGETS_MANAGER_VERSION,
			true
		);
		wp_add_inline_script(
			'widgets-manager-admin',
			'window.WidgetsManagerAdmin = ' . wp_json_encode(
				array(
					'unsaved'  => __( 'Unsaved changes', 'widgets-manager' ),
					'saved'    => __( 'All changes saved', 'widgets-manager' ),
					'enabled'  => __( 'Enabled', 'widgets-manager' ),
					'disabled' => __( 'Disabled', 'widgets-manager' ),
					/* translators: 1: enabled widget count, 2: total widget count. */
					'summary'  => __( '%1$d of %2$d enabled', 'widgets-manager' ),
				)
			) . ';',
			'before'
		);
	}

	/**
	 * Saves the submitted allowlist and redirects back to the screen.
	 *
	 * @return void
	 */
	public function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage widgets.', 'widgets-manager' ) );
		}

		check_admin_referer( 'widgets_manager_save_widgets' );
		$this->enabled_widgets->save( $this->submitted_widget_ids() );

		$redirect_url = add_query_arg(
			array(
				'page'                    => self::SLUG,
				'widgets-manager-updated' => '1',
			),
			admin_url( 'admin.php' )
		);

		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * Renders the Widgets Manager screen.
	 *
	 * @return void
	 */
	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$widget_catalog = $this->catalog->all();
		$enabled_ids    = $this->enabled_widgets->ids();
		$enabled_count  = count( $enabled_ids );
		$widget_count   = count( $widget_catalog );
		$is_empty       = empty( $widget_catalog );

		require WIDGETS_MANAGER_PATH . 'templates/admin-page.php';
	}

	/**
	 * Renders one catalog toggle.
	 *
	 * @param array<string,string> $widget_metadata Widget metadata.
	 * @param array<int,string>    $enabled_ids Enabled widget identifiers.
	 * @return void
	 */
	private function render_widget_card( array $widget_metadata, array $enabled_ids ) {
		$widget_id          = $widget_metadata['id'];
		$widget_title       = $widget_metadata['title'];
		$widget_description = $widget_metadata['description'];
		$is_enabled         = in_array( $widget_id, $enabled_ids, true );
		$input_id           = 'widgets-manager-toggle-' . sanitize_html_class( str_replace( '/', '-', $widget_id ) ) . '-' . md5( $widget_id );
		$search_text        = wp_strtolower( $widget_title . ' ' . $widget_description );

		require WIDGETS_MANAGER_PATH . 'templates/widget-card.php';
	}

	/**
	 * Renders the post-redirect success message.
	 *
	 * @return void
	 */
	private function render_updated_notice() {
		$is_updated = isset( $_GET['widgets-manager-updated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['widgets-manager-updated'] ) );

		if ( ! $is_updated ) {
			return;
		}
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Widget settings saved.', 'widgets-manager' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Extracts checkbox values from the submitted form.
	 *
	 * @return array<int,string>
	 */
	private function submitted_widget_ids() {
		if ( ! isset( $_POST['enabled_widgets'] ) || ! is_array( $_POST['enabled_widgets'] ) ) {
			return array();
		}

		$submitted_ids = array();
		$raw_ids       = wp_unslash( $_POST['enabled_widgets'] );

		foreach ( $raw_ids as $raw_id ) {
			if ( is_string( $raw_id ) ) {
				$submitted_ids[] = sanitize_text_field( $raw_id );
			}
		}

		return $submitted_ids;
	}
}
