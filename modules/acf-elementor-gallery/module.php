<?php
/**
 * ACF Elementor Gallery module entry point.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Modules\ACFElementorGallery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Module {
	public static function register() {
		add_action( 'acf/include_field_types', array( __CLASS__, 'register_field_type' ) );
		add_action( 'acf/register_fields', array( __CLASS__, 'register_field_type' ) );
		add_action( 'init', array( __CLASS__, 'register_field_type' ), 20 );
		add_action( 'elementor/dynamic_tags/register', array( __CLASS__, 'register_dynamic_tag' ) );
	}

	public static function register_field_type() {
		static $registered = false;

		if ( $registered || ! class_exists( 'acf_field' ) ) {
			return;
		}

		require_once WP_FEATURES_MANAGER_PATH . 'modules/acf-elementor-gallery/includes/class-field-gallery.php';
		if ( ! class_exists( Field_Gallery::class, false ) ) {
			return;
		}

		if ( function_exists( 'acf_register_field_type' ) ) {
			acf_register_field_type( Field_Gallery::class );
		} else {
			new Field_Gallery();
		}
		$registered = true;
	}

	public static function register_dynamic_tag( $dynamic_tags_manager ) {
		if ( ! is_object( $dynamic_tags_manager ) || ! method_exists( $dynamic_tags_manager, 'register' ) || ! class_exists( '\Elementor\Core\DynamicTags\Data_Tag' ) ) {
			return;
		}
		require_once WP_FEATURES_MANAGER_PATH . 'modules/acf-elementor-gallery/includes/class-dynamic-tag-gallery.php';
		if ( method_exists( $dynamic_tags_manager, 'register_group' ) ) {
			$dynamic_tags_manager->register_group( 'acfge', array( 'title' => __( 'ACF Gallery', 'wp-features-manager' ) ) );
		}
		$dynamic_tags_manager->register( new Dynamic_Tag_Gallery() );
	}
}

Module::register();
