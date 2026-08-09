<?php
/**
 * Widget catalog.
 *
 * @package WidgetsManager
 */

namespace WidgetsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides metadata for Widgets Manager widgets without loading widget code.
 */
final class Catalog {

	/**
	 * Widget metadata.
	 *
	 * @var array<int,array<string,string>>
	 */
	private $widgets;

	/**
	 * Creates a metadata-only widget catalog.
	 *
	 * @param array<int,array<string,string>> $widgets Widget metadata.
	 */
	public function __construct( array $widgets = array() ) {
		$this->widgets = $widgets;
	}

	/**
	 * Returns all registered widget metadata.
	 *
	 * @return array<int,array<string,string>>
	 */
	public function all() {
		return $this->widgets;
	}

	/**
	 * Returns metadata for one provider.
	 *
	 * @param string $provider Provider identifier.
	 * @return array<int,array<string,string>>
	 */
	public function for_provider( $provider ) {
		$catalog = array();

		foreach ( $this->all() as $widget_metadata ) {
			if ( $provider === $widget_metadata['provider'] ) {
				$catalog[] = $widget_metadata;
			}
		}

		return $catalog;
	}

	/**
	 * Returns catalog widget identifiers.
	 *
	 * @return array<int,string>
	 */
	public function ids() {
		$ids = array();

		foreach ( $this->all() as $widget_metadata ) {
			$ids[] = $widget_metadata['id'];
		}

		return $ids;
	}

	/**
	 * Finds metadata by its stable identifier.
	 *
	 * @param string $widget_id Widget identifier.
	 * @return array<string,string>|null
	 */
	public function find( $widget_id ) {
		foreach ( $this->all() as $widget_metadata ) {
			if ( $widget_id === $widget_metadata['id'] ) {
				return $widget_metadata;
			}
		}

		return null;
	}
}
