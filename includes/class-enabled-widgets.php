<?php
/**
 * Enabled widget settings.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Persists the positive allowlist of enabled widgets.
 */
final class Enabled_Widgets {

	/**
	 * Option name.
	 *
	 * @var string
	 */
	const OPTION_NAME = 'widgets_manager_enabled_widgets';

	/**
	 * Catalog instance.
	 *
	 * @var Catalog
	 */
	private $catalog;

	/**
	 * Creates the settings service.
	 *
	 * @param Catalog $catalog Widget catalog.
	 */
	public function __construct( Catalog $catalog ) {
		$this->catalog = $catalog;
	}

	/**
	 * Gets enabled widget identifiers from the allowlist.
	 *
	 * @return array<int,string>
	 */
	public function ids() {
		$stored_ids = get_option( self::OPTION_NAME, array() );

		if ( ! is_array( $stored_ids ) ) {
			return array();
		}

		return $this->valid_ids( $stored_ids );
	}

	/**
	 * Saves a sanitized catalog allowlist.
	 *
	 * @param array<int,string> $submitted_ids Submitted widget identifiers.
	 * @return void
	 */
	public function save( array $submitted_ids ) {
		$enabled_ids = $this->valid_ids( $submitted_ids );

		if ( $enabled_ids !== $this->ids() ) {
			update_option( self::OPTION_NAME, $enabled_ids, false );
		}
	}

	/**
	 * Restricts a list to known, unique widget IDs.
	 *
	 * @param array<int,mixed> $candidate_ids Candidate identifiers.
	 * @return array<int,string>
	 */
	private function valid_ids( array $candidate_ids ) {
		$known_ids   = $this->catalog->ids();
		$enabled_ids = array();

		foreach ( $candidate_ids as $candidate_id ) {
			if ( ! is_string( $candidate_id ) ) {
				continue;
			}

			$widget_id = sanitize_text_field( $candidate_id );

			if ( in_array( $widget_id, $known_ids, true ) ) {
				$enabled_ids[] = $widget_id;
			}
		}

		return array_values( array_unique( $enabled_ids ) );
	}
}
