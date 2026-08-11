<?php
/**
 * Feature module allowlist persistence.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Enabled_Modules {
	const OPTION_NAME = 'wp_features_manager_enabled_modules';
	private $catalog;

	public function __construct( Module_Catalog $catalog ) {
		$this->catalog = $catalog;
	}

	public function ids() {
		$stored_ids = get_option( self::OPTION_NAME, array() );
		return is_array( $stored_ids ) ? $this->valid_ids( $stored_ids ) : array();
	}

	public function save( array $submitted_ids ) {
		$enabled_ids = $this->valid_ids( $submitted_ids );
		if ( $enabled_ids !== $this->ids() ) {
			update_option( self::OPTION_NAME, $enabled_ids, false );
		}
	}

	private function valid_ids( array $candidate_ids ) {
		$known_ids = $this->catalog->ids();
		$ids = array();
		foreach ( $candidate_ids as $candidate_id ) {
			if ( ! is_string( $candidate_id ) ) {
				continue;
			}
			$id = sanitize_text_field( $candidate_id );
			if ( in_array( $id, $known_ids, true ) ) {
				$ids[] = $id;
			}
		}
		return array_values( array_unique( $ids ) );
	}
}
