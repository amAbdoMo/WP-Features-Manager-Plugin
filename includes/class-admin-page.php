<?php
/**
 * WP Features Manager admin page.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Admin_Page {
	const SLUG = 'wp-features-manager';

	private $widget_catalog;
	private $enabled_widgets;
	private $module_catalog;
	private $enabled_modules;
	private $page_hook = '';

	public function __construct( Catalog $widget_catalog, Enabled_Widgets $enabled_widgets, Module_Catalog $module_catalog, Enabled_Modules $enabled_modules ) {
		$this->widget_catalog  = $widget_catalog;
		$this->enabled_widgets = $enabled_widgets;
		$this->module_catalog  = $module_catalog;
		$this->enabled_modules = $enabled_modules;
	}

	public function register() {
		add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'admin_post_wp_features_manager_save_settings', array( $this, 'save' ) );
	}

	public function add_menu_page() {
		$this->page_hook = add_menu_page(
			__( 'WP Features Manager', 'wp-features-manager' ),
			__( 'Features Manager', 'wp-features-manager' ),
			'manage_options',
			self::SLUG,
			array( $this, 'render' ),
			'dashicons-screenoptions',
			58
		);
	}

	public function enqueue_assets( $hook_suffix ) {
		if ( $this->page_hook !== $hook_suffix ) {
			return;
		}

		wp_enqueue_style( 'wp-features-manager-admin', WP_FEATURES_MANAGER_URL . 'assets/css/admin.css', array(), WP_FEATURES_MANAGER_VERSION );
		wp_enqueue_script( 'wp-features-manager-admin', WP_FEATURES_MANAGER_URL . 'assets/js/admin.js', array(), WP_FEATURES_MANAGER_VERSION, true );
		wp_add_inline_script(
			'wp-features-manager-admin',
			'window.WPFeaturesManagerAdmin = ' . wp_json_encode(
				array(
					'unsaved'     => __( 'Unsaved changes', 'wp-features-manager' ),
					'saved'       => __( 'All changes saved', 'wp-features-manager' ),
					'enabled'     => __( 'Enabled', 'wp-features-manager' ),
					'disabled'    => __( 'Disabled', 'wp-features-manager' ),
					'unavailable' => __( 'Unavailable', 'wp-features-manager' ),
					/* translators: 1: enabled feature count, 2: total feature count. */
					'summary'     => __( '%1$d of %2$d enabled', 'wp-features-manager' ),
				)
			) . ';',
			'before'
		);
	}

	public function save() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to manage features.', 'wp-features-manager' ) );
		}

		check_admin_referer( 'wp_features_manager_save_settings' );
		$this->enabled_widgets->save( $this->submitted_ids( 'enabled_widgets' ) );
		$this->enabled_modules->save( $this->submitted_ids( 'enabled_modules' ) );

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'                        => self::SLUG,
					'wp-features-manager-updated' => '1',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	public function render() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$module_catalog     = $this->module_catalog->all();
		$widget_catalog     = $this->widget_catalog->all();
		$enabled_module_ids = $this->enabled_modules->ids();
		$enabled_widget_ids = $this->enabled_widgets->ids();
		$feature_count      = count( $module_catalog ) + count( $widget_catalog );
		$enabled_count      = count( $enabled_module_ids ) + count( $enabled_widget_ids );
		$is_empty           = 0 === $feature_count;

		require WP_FEATURES_MANAGER_PATH . 'templates/admin-page.php';
	}

	private function render_module_card( array $module_metadata, array $enabled_ids ) {
		$module_id     = $module_metadata['id'];
		$status        = $this->module_catalog->status( $module_metadata );
		$is_enabled    = in_array( $module_id, $enabled_ids, true );
		$input_id      = $this->input_id( $module_id );
		$search_text   = $this->lowercase_search_text( $module_metadata['title'] . ' ' . $module_metadata['description'] . ' ' . $status['reason'] );
		$card_status   = $status['available'] ? ( $is_enabled ? 'enabled' : 'disabled' ) : 'unavailable';
		$toggle_locked = ! $status['available'] && ! $is_enabled;

		require WP_FEATURES_MANAGER_PATH . 'templates/feature-card.php';
	}

	private function render_widget_card( array $widget_metadata, array $enabled_ids ) {
		$widget_id          = $widget_metadata['id'];
		$widget_title       = $widget_metadata['title'];
		$widget_description = $widget_metadata['description'];
		$is_enabled         = in_array( $widget_id, $enabled_ids, true );
		$input_id           = $this->input_id( $widget_id );
		$search_text        = $this->lowercase_search_text( $widget_title . ' ' . $widget_description );

		require WP_FEATURES_MANAGER_PATH . 'templates/widget-card.php';
	}

	private function input_id( $feature_id ) {
		return 'wp-features-manager-toggle-' . sanitize_html_class( str_replace( '/', '-', $feature_id ) ) . '-' . md5( $feature_id );
	}

	private function lowercase_search_text( $search_text ) {
		if ( function_exists( 'mb_strtolower' ) ) {
			return mb_strtolower( $search_text, 'UTF-8' );
		}
		return strtolower( $search_text );
	}

	private function render_updated_notice() {
		$is_updated = isset( $_GET['wp-features-manager-updated'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['wp-features-manager-updated'] ) );
		if ( ! $is_updated ) {
			return;
		}
		?>
		<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Feature settings saved.', 'wp-features-manager' ); ?></p></div>
		<?php
	}

	private function submitted_ids( $key ) {
		if ( ! isset( $_POST[ $key ] ) || ! is_array( $_POST[ $key ] ) ) {
			return array();
		}

		$submitted_ids = array();
		foreach ( wp_unslash( $_POST[ $key ] ) as $submitted_id ) {
			if ( is_string( $submitted_id ) ) {
				$submitted_ids[] = sanitize_text_field( $submitted_id );
			}
		}
		return $submitted_ids;
	}
}
