<?php
/**
 * Metadata-only feature module catalog.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Module_Catalog {
	private $modules;

	public function __construct( array $modules = array() ) {
		$this->modules = $modules;
	}

	public function all() {
		return $this->modules;
	}

	public function ids() {
		return array_values( array_unique( array_column( $this->modules, 'id' ) ) );
	}

	public function find( $module_id ) {
		foreach ( $this->modules as $module ) {
			if ( isset( $module['id'] ) && $module_id === $module['id'] ) {
				return $module;
			}
		}

		return null;
	}

	/**
	 * Determines availability from lightweight metadata only. Module files are never loaded here.
	 *
	 * @param array<string,mixed> $module Module metadata.
	 * @return array<string,mixed>
	 */
	public function status( array $module ) {
		$reasons      = array();
		$has_conflict = ! empty( $module['conflict'] ) && $this->has_conflict( $module['conflict'] );

		if ( $has_conflict ) {
			$reasons[] = __( 'The standalone ACF Custom Gallery for Elementor plugin is active. Deactivate it before enabling this module.', 'wp-features-manager' );
		}

		if ( ! $this->has_readable_file( $module ) ) {
			$reasons[] = __( 'The module implementation file is missing.', 'wp-features-manager' );
		}

		if ( ! empty( $module['requires_acf'] ) && ! $this->has_acf() ) {
			$reasons[] = __( 'Advanced Custom Fields must be installed and active.', 'wp-features-manager' );
		}

		if ( ! empty( $module['requires_elementor'] ) && ! $this->has_elementor( $module ) ) {
			$reasons[] = sprintf(
				/* translators: %s: minimum Elementor version. */
				__( 'Elementor %s or later must be installed and active.', 'wp-features-manager' ),
				isset( $module['minimum_elementor_version'] ) ? $module['minimum_elementor_version'] : '3.20.0'
			);
		}

		return array(
			'available' => empty( $reasons ),
			'reason'    => implode( ' ', $reasons ),
			'conflict'  => $has_conflict,
		);
	}

	private function has_readable_file( array $module ) {
		if ( empty( $module['file'] ) || ! is_string( $module['file'] ) ) {
			return false;
		}
		return is_readable( WP_FEATURES_MANAGER_PATH . ltrim( $module['file'], "/\\" ) );
	}

	private function has_acf() {
		return class_exists( 'acf_field', false ) || function_exists( 'acf_register_field_type' ) || function_exists( 'acf' );
	}

	private function has_elementor( array $module ) {
		$minimum = isset( $module['minimum_elementor_version'] ) ? $module['minimum_elementor_version'] : '3.20.0';
		return did_action( 'elementor/loaded' ) && defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, $minimum, '>=' );
	}

	private function has_conflict( $conflict ) {
		if ( 'acf-elementor-gallery-standalone' !== $conflict ) {
			return false;
		}

		return defined( 'ACFGE_VERSION' ) || function_exists( 'acfge_register_field_type' ) || class_exists( 'ACFGE_Field_Gallery', false ) || class_exists( 'ACFGE_Dynamic_Tag_Gallery', false );
	}
}
