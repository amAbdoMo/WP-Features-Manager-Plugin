<?php
/**
 * Elementor gallery dynamic tag for acfge_gallery fields.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Modules\ACFElementorGallery;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Elementor\Core\DynamicTags\Data_Tag;
use Elementor\Modules\DynamicTags\Module as Tags_Module;

final class Dynamic_Tag_Gallery extends Data_Tag {
	public function get_name() {
		return 'acfge-gallery';
	}

	public function get_title() {
		return __( 'ACF Gallery', 'wp-features-manager' );
	}

	public function get_group() {
		return 'acfge';
	}

	public function get_categories() {
		return array( Tags_Module::GALLERY_CATEGORY );
	}

	protected function register_controls() {
		$field_choices = $this->gallery_field_choices();
		if ( empty( $field_choices ) ) {
			$this->add_field_name_control();
		} else {
			$this->add_field_select_control( $field_choices );
		}

		$this->add_control(
			'post_id_override',
			array(
				'label'       => __( 'Post ID (leave blank for current post)', 'wp-features-manager' ),
				'type'        => \Elementor\Controls_Manager::NUMBER,
				'placeholder' => get_the_ID(),
			)
		);
	}

	private function add_field_select_control( array $field_choices ) {
		$this->add_control(
			'acf_field_key',
			array(
				'label'       => __( 'Gallery Field', 'wp-features-manager' ),
				'type'        => \Elementor\Controls_Manager::SELECT,
				'options'     => $field_choices,
				'default'     => $this->first_choice_key( $field_choices ),
				'description' => __( 'Select the ACF gallery field to use.', 'wp-features-manager' ),
			)
		);
	}

	private function add_field_name_control() {
		$this->add_control(
			'acf_field_key',
			array(
				'label'       => __( 'Field Name', 'wp-features-manager' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'default'     => '',
				'placeholder' => 'project_images',
				'description' => __( 'Enter the ACF gallery field name exactly as set in your field group.', 'wp-features-manager' ),
			)
		);
	}

	private function first_choice_key( array $field_choices ) {
		foreach ( $field_choices as $field_name => $label ) {
			return $field_name;
		}
		return '';
	}

	private function gallery_field_choices() {
		$field_choices = array();
		if ( ! function_exists( 'acf_get_field_groups' ) || ! function_exists( 'acf_get_fields' ) ) {
			return $field_choices;
		}

		foreach ( acf_get_field_groups() as $field_group ) {
			if ( empty( $field_group['key'] ) ) {
				continue;
			}
			$field_choices = array_merge( $field_choices, $this->group_field_choices( acf_get_fields( $field_group['key'] ) ) );
		}
		return $field_choices;
	}

	private function group_field_choices( $fields ) {
		$field_choices = array();
		if ( ! is_array( $fields ) ) {
			return $field_choices;
		}
		foreach ( $fields as $field ) {
			if ( ! isset( $field['type'], $field['name'], $field['label'] ) || 'acfge_gallery' !== $field['type'] ) {
				continue;
			}
			$field_choices[ $field['name'] ] = $field['label'] . ' (' . $field['name'] . ')';
		}
		return $field_choices;
	}

	public function get_value( array $options = array() ) {
		if ( ! function_exists( 'get_field' ) ) {
			return array();
		}

		$field_name = trim( (string) $this->get_settings( 'acf_field_key' ) );
		$post_id    = $this->selected_post_id();
		if ( '' === $field_name || $post_id <= 0 ) {
			return array();
		}

		$formatted_value = get_field( $field_name, $post_id );
		if ( is_array( $formatted_value ) && ! empty( $formatted_value ) ) {
			return $this->formatted_value_gallery( $formatted_value );
		}
		return $this->raw_value_gallery( $field_name, $post_id );
	}

	private function selected_post_id() {
		$override = absint( $this->get_settings( 'post_id_override' ) );
		return $override ? $override : get_the_ID();
	}

	private function formatted_value_gallery( array $formatted_value ) {
		$attachment_ids = array();
		foreach ( $formatted_value as $attachment ) {
			if ( is_array( $attachment ) && isset( $attachment['id'] ) ) {
				$attachment_ids[] = $attachment['id'];
			} elseif ( is_numeric( $attachment ) ) {
				$attachment_ids[] = $attachment;
			}
		}
		return $this->attachment_ids_to_gallery( $attachment_ids );
	}

	private function raw_value_gallery( $field_name, $post_id ) {
		$raw_value = get_post_meta( $post_id, $field_name, true );
		if ( empty( $raw_value ) ) {
			return array();
		}
		$raw_value = is_array( $raw_value ) ? $raw_value : maybe_unserialize( $raw_value );
		return is_array( $raw_value ) ? $this->attachment_ids_to_gallery( $raw_value ) : array();
	}

	private function attachment_ids_to_gallery( array $attachment_ids ) {
		$gallery = array();
		foreach ( $attachment_ids as $attachment_id ) {
			$attachment_id = absint( $attachment_id );
			if ( $attachment_id ) {
				$gallery[] = array( 'id' => $attachment_id );
			}
		}
		return $gallery;
	}
}
