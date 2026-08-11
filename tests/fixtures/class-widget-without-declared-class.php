<?php
/**
 * Fixture file that intentionally omits the catalog-declared class.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Tests\Fixtures;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * An unrelated class proves that loading a file is not enough to register a widget.
 */
final class Unrelated_Test_Widget {
}
