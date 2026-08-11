<?php
/**
 * ACF Gallery (Free) field type.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Modules\ACFElementorGallery;

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'acf_field' ) ) {
	return;
}

final class Field_Gallery extends \acf_field {
	public function initialize() {
		$this->name     = 'acfge_gallery';
		$this->label    = __( 'Gallery (Free)', 'wp-features-manager' );
		$this->category = 'content';
		$this->defaults = array(
			'preview_size' => 'medium',
			'library'      => 'all',
		);
	}

	public function __construct() {
		$this->initialize();
		parent::__construct();
	}

	public function render_field_settings( $field ) {
		acf_render_field_setting(
			$field,
			array(
				'label'   => __( 'Preview size', 'wp-features-manager' ),
				'type'    => 'select',
				'name'    => 'preview_size',
				'choices' => acf_get_image_sizes(),
			)
		);
		acf_render_field_setting(
			$field,
			array(
				'label'   => __( 'Library', 'wp-features-manager' ),
				'type'    => 'radio',
				'name'    => 'library',
				'layout'  => 'horizontal',
				'choices' => array(
					'all'        => __( 'All', 'wp-features-manager' ),
					'uploadedTo' => __( 'Uploaded to post', 'wp-features-manager' ),
				),
			)
		);
	}

	public function render_field( $field ) {
		$field_id  = ! empty( $field['id'] ) ? $field['id'] : $field['key'];
		$items     = $this->attachment_ids( $field['value'] );
		$preview   = ! empty( $field['preview_size'] ) ? $field['preview_size'] : 'medium';
		$library   = ! empty( $field['library'] ) ? $field['library'] : 'all';
		$input_name = $field['name'];
		?>
		<div class="acfge-gallery-field" id="acfge-<?php echo esc_attr( $field_id ); ?>">
			<div class="acfge-inputs">
				<?php foreach ( $items as $attachment_id ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>[]" value="<?php echo esc_attr( $attachment_id ); ?>" />
				<?php endforeach; ?>
				<?php if ( empty( $items ) ) : ?>
					<input type="hidden" name="<?php echo esc_attr( $input_name ); ?>[]" value="" class="acfge-empty-placeholder" />
				<?php endif; ?>
			</div>
			<ul class="acfge-thumbs">
				<?php foreach ( $items as $attachment_id ) : ?>
					<?php $this->render_thumbnail( $attachment_id, $preview ); ?>
				<?php endforeach; ?>
			</ul>
			<button
				type="button"
				class="button acfge-add-images"
				data-field-id="<?php echo esc_attr( $field_id ); ?>"
				data-preview-size="<?php echo esc_attr( $preview ); ?>"
				data-library="<?php echo esc_attr( $library ); ?>"
			><?php esc_html_e( '+ Add Images', 'wp-features-manager' ); ?></button>
		</div>
		<?php
	}

	private function render_thumbnail( $attachment_id, $preview_size ) {
		$image_url = wp_get_attachment_image_url( $attachment_id, $preview_size );
		$alt_text  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
		?>
		<li class="acfge-thumb" data-id="<?php echo esc_attr( $attachment_id ); ?>">
			<img src="<?php echo esc_url( $image_url ? $image_url : '' ); ?>" alt="<?php echo esc_attr( $alt_text ); ?>" />
			<button type="button" class="acfge-remove" aria-label="<?php esc_attr_e( 'Remove image', 'wp-features-manager' ); ?>">&#x2715;</button>
			<span class="acfge-drag-handle" title="<?php esc_attr_e( 'Drag to reorder', 'wp-features-manager' ); ?>">&#8801;</span>
		</li>
		<?php
	}

	public function input_admin_enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_style( 'wp-features-manager-acfge-field', WP_FEATURES_MANAGER_URL . 'modules/acf-elementor-gallery/assets/field-admin.css', array(), WP_FEATURES_MANAGER_VERSION );
		wp_enqueue_script( 'wp-features-manager-acfge-field', WP_FEATURES_MANAGER_URL . 'modules/acf-elementor-gallery/assets/field-admin.js', array( 'jquery', 'jquery-ui-sortable', 'media-editor' ), WP_FEATURES_MANAGER_VERSION, true );
		wp_localize_script(
			'wp-features-manager-acfge-field',
			'WPFeaturesManagerGallery',
			array(
				'select_images'   => __( 'Select Gallery Images', 'wp-features-manager' ),
				'add_to_gallery'  => __( 'Add to Gallery', 'wp-features-manager' ),
				'remove_image'    => __( 'Remove image', 'wp-features-manager' ),
				'drag_to_reorder' => __( 'Drag to reorder', 'wp-features-manager' ),
			)
		);
	}

	public function load_value( $value, $post_id, $field ) {
		if ( empty( $value ) ) {
			return array();
		}
		if ( is_string( $value ) ) {
			$value = maybe_unserialize( $value );
		}
		if ( ! is_array( $value ) ) {
			$value = explode( ',', (string) $value );
		}
		return $this->attachment_ids( $value );
	}

	public function update_value( $value, $post_id, $field ) {
		return is_array( $value ) ? $this->attachment_ids( $value ) : array();
	}

	public function format_value( $value, $post_id, $field ) {
		$formatted_attachments = array();
		foreach ( $this->attachment_ids( $value ) as $attachment_id ) {
			$attachment = $this->format_attachment( $attachment_id );
			if ( $attachment ) {
				$formatted_attachments[] = $attachment;
			}
		}
		return $formatted_attachments;
	}

	private function attachment_ids( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}
		return array_values( array_filter( array_map( 'absint', $value ) ) );
	}

	private function format_attachment( $attachment_id ) {
		$attachment = wp_prepare_attachment_for_js( $attachment_id );
		if ( ! $attachment ) {
			return null;
		}

		return array(
			'ID'          => $attachment_id,
			'id'          => $attachment_id,
			'title'       => isset( $attachment['title'] ) ? $attachment['title'] : '',
			'filename'    => isset( $attachment['filename'] ) ? $attachment['filename'] : '',
			'url'         => isset( $attachment['url'] ) ? $attachment['url'] : '',
			'link'        => isset( $attachment['link'] ) ? $attachment['link'] : '',
			'alt'         => isset( $attachment['alt'] ) ? $attachment['alt'] : '',
			'caption'     => isset( $attachment['caption'] ) ? $attachment['caption'] : '',
			'description' => isset( $attachment['description'] ) ? $attachment['description'] : '',
			'mime_type'   => isset( $attachment['mime'] ) ? $attachment['mime'] : '',
			'width'       => isset( $attachment['width'] ) ? $attachment['width'] : 0,
			'height'      => isset( $attachment['height'] ) ? $attachment['height'] : 0,
			'sizes'       => $this->attachment_sizes( $attachment ),
		);
	}

	private function attachment_sizes( array $attachment ) {
		$sizes = array();
		if ( empty( $attachment['sizes'] ) ) {
			return $sizes;
		}
		foreach ( $attachment['sizes'] as $name => $size ) {
			$sizes[ $name ] = array(
				'url'    => $size['url'],
				'width'  => $size['width'],
				'height' => $size['height'],
			);
		}
		return $sizes;
	}
}
