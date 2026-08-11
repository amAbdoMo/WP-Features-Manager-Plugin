<?php
/**
 * Before/after image Elementor widget.
 *
 * @package WPFeaturesManager
 */

namespace WPFeaturesManager\Elementor;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;
use WPFeaturesManager\Elementor_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders an accessible image comparison control.
 */
final class Before_After_Image_Widget extends Widget_Base {

	/**
	 * Returns the widget identifier.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'widgets-manager-before-after-image';
	}

	/**
	 * Returns the widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Before/After Image', 'wp-features-manager' );
	}

	/**
	 * Returns the widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-image-before-after';
	}

	/**
	 * Returns the widget category.
	 *
	 * @return array<int,string>
	 */
	public function get_categories() {
		return array( Elementor_Adapter::CATEGORY );
	}

	/**
	 * Returns keywords for the editor search.
	 *
	 * @return array<int,string>
	 */
	public function get_keywords() {
		return array( 'before', 'after', 'image', 'comparison', 'reveal' );
	}

	/**
	 * Returns frontend stylesheet dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_style_depends() {
		return array( Elementor_Adapter::BEFORE_AFTER_STYLE_HANDLE );
	}

	/**
	 * Returns frontend script dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_script_depends() {
		return array( Elementor_Adapter::BEFORE_AFTER_SCRIPT_HANDLE );
	}

	/**
	 * Registers Elementor controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_container_style_controls();
		$this->register_image_style_controls();
		$this->register_divider_style_controls();
		$this->register_handle_style_controls();
		$this->register_label_style_controls();
	}

	/**
	 * Registers image, label, and accessibility controls.
	 *
	 * @return void
	 */
	private function register_content_controls() {
		$this->start_controls_section(
			'comparison_content',
			array( 'label' => __( 'Images', 'wp-features-manager' ) )
		);
		$this->add_control(
			'before_image',
			array(
				'label' => __( 'Before image', 'wp-features-manager' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_control(
			'after_image',
			array(
				'label' => __( 'After image', 'wp-features-manager' ),
				'type'  => Controls_Manager::MEDIA,
			)
		);
		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'comparison_image',
				'default' => 'large',
			)
		);
		$this->add_control(
			'show_labels',
			array(
				'label'        => __( 'Show labels', 'wp-features-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Show', 'wp-features-manager' ),
				'label_off'    => __( 'Hide', 'wp-features-manager' ),
				'return_value' => 'yes',
				'default'      => '',
			)
		);
		$this->add_control(
			'before_label',
			array(
				'label'     => __( 'Before label', 'wp-features-manager' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'Before', 'wp-features-manager' ),
				'condition' => array( 'show_labels' => 'yes' ),
			)
		);
		$this->add_control(
			'after_label',
			array(
				'label'     => __( 'After label', 'wp-features-manager' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => __( 'After', 'wp-features-manager' ),
				'condition' => array( 'show_labels' => 'yes' ),
			)
		);
		$this->add_control(
			'comparison_label',
			array(
				'label'       => __( 'Accessible comparison label', 'wp-features-manager' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Before and after image comparison', 'wp-features-manager' ),
				'label_block' => true,
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Registers layout and interaction controls under the Content tab.
	 *
	 * @return void
	 */
	private function register_layout_controls() {
		$this->start_controls_section(
			'comparison_layout',
			array( 'label' => __( 'Layout', 'wp-features-manager' ) )
		);
		$this->add_control(
			'orientation',
			array(
				'label'   => __( 'Orientation', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'horizontal',
				'options' => array(
					'horizontal' => __( 'Horizontal', 'wp-features-manager' ),
					'vertical'   => __( 'Vertical', 'wp-features-manager' ),
				),
			)
		);
		$this->add_responsive_control(
			'start_position',
			array(
				'label'      => __( 'Starting position', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'unit' => '%', 'size' => 50 ),
				'range'      => array( '%' => array( 'min' => 0, 'max' => 100 ) ),
				'size_units' => array( '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .widgets-manager-before-after' => '--wm-start-position: {{SIZE}}{{UNIT}};',
				),
			)
		);
		$this->add_control(
			'reveal_direction',
			array(
				'label'   => __( 'Revealed image', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'before',
				'options' => array(
					'before' => __( 'Before image', 'wp-features-manager' ),
					'after'  => __( 'After image', 'wp-features-manager' ),
				),
			)
		);
		$this->add_control(
			'sizing_mode',
			array(
				'label'   => __( 'Container sizing', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'image',
				'options' => array(
					'image'  => __( 'Match image aspect ratio', 'wp-features-manager' ),
					'height' => __( 'Custom responsive height', 'wp-features-manager' ),
				),
			)
		);
		$this->add_responsive_control(
			'container_height',
			array(
				'label'      => __( 'Container height', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 420, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 1200 ) ),
				'size_units' => array( 'px', 'vh' ),
				'condition'  => array( 'sizing_mode' => 'height' ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => 'height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'object_fit',
			array(
				'label'   => __( 'Image fit', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'cover',
				'options' => array(
					'cover'   => __( 'Cover', 'wp-features-manager' ),
					'contain' => __( 'Contain', 'wp-features-manager' ),
					'fill'    => __( 'Fill', 'wp-features-manager' ),
					'none'    => __( 'None', 'wp-features-manager' ),
				),
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__image' => 'object-fit: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'before_focal_position',
			array(
				'label'   => __( 'Before focal position', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => $this->focal_position_options(),
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__layer--before .widgets-manager-before-after__image' => 'object-position: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'after_focal_position',
			array(
				'label'   => __( 'After focal position', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'center center',
				'options' => $this->focal_position_options(),
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__layer--after .widgets-manager-before-after__image' => 'object-position: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'click_to_position',
			array(
				'label'        => __( 'Click or tap to position', 'wp-features-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'wp-features-manager' ),
				'label_off'    => __( 'No', 'wp-features-manager' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'drag_to_position',
			array(
				'label'        => __( 'Drag to position', 'wp-features-manager' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'Yes', 'wp-features-manager' ),
				'label_off'    => __( 'No', 'wp-features-manager' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);
		$this->add_control(
			'keyboard_increment',
			array(
				'label'   => __( 'Keyboard increment', 'wp-features-manager' ),
				'type'    => Controls_Manager::NUMBER,
				'default' => 5,
				'min'     => 1,
				'max'     => 25,
				'step'    => 1,
			)
		);
		$this->add_control(
			'handle_icon_source',
			array(
				'label'   => __( 'Handle icon', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'default',
				'options' => array(
					'default' => __( 'Default double arrow', 'wp-features-manager' ),
					'custom'  => __( 'Custom Elementor icon', 'wp-features-manager' ),
				),
			)
		);
		$this->add_control(
			'handle_icon',
			array(
				'label'     => __( 'Custom handle icon', 'wp-features-manager' ),
				'type'      => Controls_Manager::ICONS,
				'condition' => array( 'handle_icon_source' => 'custom' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Registers container style controls.
	 *
	 * @return void
	 */
	private function register_container_style_controls() {
		$this->start_controls_section(
			'comparison_container_style',
			array( 'label' => __( 'Container', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE )
		);
		$this->add_group_control(
			Group_Control_Background::get_type(),
			array( 'name' => 'container_background', 'selector' => '{{WRAPPER}} .widgets-manager-before-after' )
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'container_border', 'selector' => '{{WRAPPER}} .widgets-manager-before-after' )
		);
		$this->add_responsive_control(
			'container_radius',
			array(
				'label'      => __( 'Border radius', 'wp-features-manager' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'container_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-before-after' )
		);
		$this->end_controls_section();
	}

	/**
	 * Registers individual image styles.
	 *
	 * @return void
	 */
	private function register_image_style_controls() {
		$this->start_controls_section(
			'comparison_images_style',
			array( 'label' => __( 'Images', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE )
		);
		$this->add_control(
			'before_heading',
			array( 'label' => __( 'Before image', 'wp-features-manager' ), 'type' => Controls_Manager::HEADING )
		);
		$this->add_responsive_control(
			'before_opacity',
			array(
				'label'      => __( 'Opacity', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1 ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__layer--before .widgets-manager-before-after__image' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array( 'name' => 'before_filter', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__layer--before .widgets-manager-before-after__image' )
		);
		$this->register_tint_controls( 'before', __( 'Before tint', 'wp-features-manager' ) );
		$this->add_control(
			'after_heading',
			array( 'label' => __( 'After image', 'wp-features-manager' ), 'type' => Controls_Manager::HEADING, 'separator' => 'before' )
		);
		$this->add_responsive_control(
			'after_opacity',
			array(
				'label'      => __( 'Opacity', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 1 ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__layer--after .widgets-manager-before-after__image' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Css_Filter::get_type(),
			array( 'name' => 'after_filter', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__layer--after .widgets-manager-before-after__image' )
		);
		$this->register_tint_controls( 'after', __( 'After tint', 'wp-features-manager' ) );
		$this->end_controls_section();
	}

	/**
	 * Registers color and opacity controls for an image tint.
	 *
	 * @param string $image_name Image state name.
	 * @param string $label Control label.
	 * @return void
	 */
	private function register_tint_controls( $image_name, $label ) {
		$this->add_control(
			$image_name . '_tint_color',
			array(
				'label'     => $label,
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__layer--' . $image_name . ' .widgets-manager-before-after__tint' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			$image_name . '_tint_opacity',
			array(
				'label'      => __( 'Tint opacity', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.01 ) ),
				'default'    => array( 'size' => 0 ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__layer--' . $image_name . ' .widgets-manager-before-after__tint' => 'opacity: {{SIZE}};' ),
			)
		);
	}

	/**
	 * Registers divider style controls.
	 *
	 * @return void
	 */
	private function register_divider_style_controls() {
		$this->start_controls_section(
			'comparison_divider_style',
			array( 'label' => __( 'Divider', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE )
		);
		$this->add_control(
			'divider_color',
			array(
				'label'     => __( 'Color', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-divider-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'divider_thickness',
			array(
				'label'      => __( 'Thickness', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 2, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 1, 'max' => 12 ) ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-divider-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'divider_style',
			array(
				'label'   => __( 'Line style', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'dashed',
				'options' => array(
					'solid'  => __( 'Solid', 'wp-features-manager' ),
					'dashed' => __( 'Dashed', 'wp-features-manager' ),
				),
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-divider-style: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'divider_opacity',
			array(
				'label'      => __( 'Opacity', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 0.9 ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.01 ) ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__divider' => 'opacity: {{SIZE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'divider_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__divider' )
		);
		$this->end_controls_section();
	}

	/**
	 * Registers handle style controls.
	 *
	 * @return void
	 */
	private function register_handle_style_controls() {
		$this->start_controls_section(
			'comparison_handle_style',
			array( 'label' => __( 'Handle', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE )
		);
		$this->add_responsive_control(
			'handle_size',
			array(
				'label'      => __( 'Size', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 52, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 24, 'max' => 160 ) ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-handle-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'handle_background',
			array(
				'label'     => __( 'Background', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-handle-background: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'handle_hover_background',
			array(
				'label'     => __( 'Hover background', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#f5f7fa',
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-handle-hover-background: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'handle_icon_color',
			array(
				'label'     => __( 'Icon color', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#172033',
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-handle-icon-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'handle_icon_size',
			array(
				'label'      => __( 'Icon size', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 20, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 80 ) ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-handle-icon-size: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'handle_border', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__handle' )
		);
		$this->add_responsive_control(
			'handle_radius',
			array(
				'label'      => __( 'Border radius', 'wp-features-manager' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'default'    => array( 'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%' ),
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__handle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'handle_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__handle' )
		);
		$this->end_controls_section();
	}

	/**
	 * Registers label style controls.
	 *
	 * @return void
	 */
	private function register_label_style_controls() {
		$this->start_controls_section(
			'comparison_labels_style',
			array( 'label' => __( 'Labels', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE )
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array( 'name' => 'label_typography', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__label' )
		);
		$this->add_control(
			'label_color',
			array(
				'label'     => __( 'Text color', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__label' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'label_background',
			array(
				'label'     => __( 'Background', 'wp-features-manager' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .widgets-manager-before-after__label' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'label_padding',
			array(
				'label'      => __( 'Padding', 'wp-features-manager' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__label' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'label_spacing',
			array(
				'label'      => __( 'Edge spacing', 'wp-features-manager' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array( 'size' => 16, 'unit' => 'px' ),
				'range'      => array( 'px' => array( 'min' => 0, 'max' => 100 ) ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after' => '--wm-label-spacing: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array( 'name' => 'label_border', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__label' )
		);
		$this->add_responsive_control(
			'label_radius',
			array(
				'label'      => __( 'Border radius', 'wp-features-manager' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array( '{{WRAPPER}} .widgets-manager-before-after__label' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array( 'name' => 'label_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-before-after__label' )
		);
		$this->add_control(
			'label_placement',
			array(
				'label'   => __( 'Placement', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'top',
				'options' => array(
					'top'    => __( 'Top', 'wp-features-manager' ),
					'bottom' => __( 'Bottom', 'wp-features-manager' ),
				),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Returns named image focal positions.
	 *
	 * @return array<string,string>
	 */
	private function focal_position_options() {
		return array(
			'left top'      => __( 'Top left', 'wp-features-manager' ),
			'center top'    => __( 'Top center', 'wp-features-manager' ),
			'right top'     => __( 'Top right', 'wp-features-manager' ),
			'left center'   => __( 'Center left', 'wp-features-manager' ),
			'center center' => __( 'Center', 'wp-features-manager' ),
			'right center'  => __( 'Center right', 'wp-features-manager' ),
			'left bottom'   => __( 'Bottom left', 'wp-features-manager' ),
			'center bottom' => __( 'Bottom center', 'wp-features-manager' ),
			'right bottom'  => __( 'Bottom right', 'wp-features-manager' ),
		);
	}

	/**
	 * Renders the comparison markup.
	 *
	 * @return void
	 */
	protected function render() {
		$settings          = $this->get_settings_for_display();
		$orientation       = $this->allowed_value( $settings, 'orientation', array( 'horizontal', 'vertical' ), 'horizontal' );
		$reveal_direction  = $this->allowed_value( $settings, 'reveal_direction', array( 'before', 'after' ), 'before' );
		$sizing_mode      = $this->allowed_value( $settings, 'sizing_mode', array( 'image', 'height' ), 'image' );
		$label_placement   = $this->allowed_value( $settings, 'label_placement', array( 'top', 'bottom' ), 'top' );
		$position          = $this->comparison_position( $settings );
		$keyboard_step     = $this->keyboard_step( $settings );
		$before_image_html = $this->image_html( $settings, 'before_image' );
		$after_image_html  = $this->image_html( $settings, 'after_image' );
		if ( '' === $before_image_html && '' !== $after_image_html ) {
			$before_image_html = $after_image_html;
		} elseif ( '' === $after_image_html && '' !== $before_image_html ) {
			$after_image_html = $before_image_html;
		} elseif ( '' === $before_image_html && 'image' === $sizing_mode ) {
			$sizing_mode = 'height';
		}
		$comparison_label  = $this->comparison_label( $settings );
		$show_labels       = isset( $settings['show_labels'] ) && 'yes' === $settings['show_labels'];
		$custom_icon       = isset( $settings['handle_icon_source'] ) && 'custom' === $settings['handle_icon_source'];
		$base_state        = 'before' === $reveal_direction ? 'after' : 'before';
		$reveal_state      = 'before' === $reveal_direction ? 'before' : 'after';
		$base_image_html   = 'before' === $base_state ? $before_image_html : $after_image_html;
		$reveal_image_html = 'before' === $reveal_state ? $before_image_html : $after_image_html;
		$component_classes = 'widgets-manager-before-after widgets-manager-before-after--' . $orientation . ' widgets-manager-before-after--sizing-' . $sizing_mode . ' widgets-manager-before-after--reveal-' . $reveal_direction . ' widgets-manager-before-after--labels-' . $label_placement;
		?>
		<div class="<?php echo esc_attr( $component_classes ); ?>" data-widgets-manager-before-after data-click-to-position="<?php echo esc_attr( $this->interaction_enabled( $settings, 'click_to_position' ) ); ?>" data-drag-to-position="<?php echo esc_attr( $this->interaction_enabled( $settings, 'drag_to_position' ) ); ?>" data-keyboard-step="<?php echo esc_attr( $keyboard_step ); ?>" data-start-position="<?php echo esc_attr( $position ); ?>">
			<div class="widgets-manager-before-after__layer widgets-manager-before-after__layer--<?php echo esc_attr( $base_state ); ?>">
				<?php $this->render_image( $base_image_html ); ?>
				<span class="widgets-manager-before-after__tint" aria-hidden="true"></span>
				<div class="widgets-manager-before-after__base-label-mask">
					<?php $this->render_label( $show_labels, $settings, $base_state . '_label', $base_state ); ?>
				</div>
			</div>
			<div class="widgets-manager-before-after__reveal">
				<div class="widgets-manager-before-after__layer widgets-manager-before-after__layer--<?php echo esc_attr( $reveal_state ); ?>">
					<?php $this->render_image( $reveal_image_html ); ?>
					<span class="widgets-manager-before-after__tint" aria-hidden="true"></span>
					<?php $this->render_label( $show_labels, $settings, $reveal_state . '_label', $reveal_state ); ?>
				</div>
			</div>
			<div class="widgets-manager-before-after__divider" aria-hidden="true"></div>
			<div class="widgets-manager-before-after__handle" role="slider" tabindex="0" aria-label="<?php echo esc_attr( $comparison_label ); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo esc_attr( $position ); ?>" aria-orientation="<?php echo esc_attr( $orientation ); ?>">
				<span class="widgets-manager-before-after__handle-icon<?php echo esc_attr( $custom_icon ? '' : ' widgets-manager-before-after__handle-icon--default' ); ?>" aria-hidden="true">
					<?php $this->render_handle_icon( $settings, $custom_icon ); ?>
				</span>
			</div>
		</div>
		<?php
	}

	/**
	 * Returns a safely bounded position value.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return string
	 */
	private function comparison_position( array $settings ) {
		$raw_position = isset( $settings['start_position']['size'] ) ? (float) $settings['start_position']['size'] : 50;
		$position     = max( 0, min( 100, $raw_position ) );

		return number_format( $position, 2, '.', '' );
	}

	/**
	 * Returns a bounded keyboard increment.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return int
	 */
	private function keyboard_step( array $settings ) {
		$raw_step = isset( $settings['keyboard_increment'] ) ? absint( $settings['keyboard_increment'] ) : 5;

		return max( 1, min( 25, $raw_step ) );
	}

	/**
	 * Returns an allowed select value.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $setting Setting name.
	 * @param array<int,string>   $allowed Allowed values.
	 * @param string              $fallback Fallback value.
	 * @return string
	 */
	private function allowed_value( array $settings, $setting, array $allowed, $fallback ) {
		$value = isset( $settings[ $setting ] ) ? sanitize_key( $settings[ $setting ] ) : '';

		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * Returns the enabled state for an interaction setting.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $setting Setting name.
	 * @return string
	 */
	private function interaction_enabled( array $settings, $setting ) {
		return isset( $settings[ $setting ] ) && 'yes' === $settings[ $setting ] ? 'true' : 'false';
	}

	/**
	 * Returns the accessible slider label.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return string
	 */
	private function comparison_label( array $settings ) {
		$label = isset( $settings['comparison_label'] ) ? sanitize_text_field( $settings['comparison_label'] ) : '';

		return '' === $label ? __( 'Before and after image comparison', 'wp-features-manager' ) : $label;
	}

	/**
	 * Builds image markup through Elementor's supported image-size API.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $image_key Image setting key.
	 * @return string
	 */
	private function image_html( array $settings, $image_key ) {
		if ( empty( $settings[ $image_key ]['id'] ) && empty( $settings[ $image_key ]['url'] ) ) {
			return '';
		}

		return Group_Control_Image_Size::get_attachment_image_html( $settings, 'comparison_image', $image_key );
	}

	/**
	 * Outputs a filtered image element when an image has been selected.
	 *
	 * @param string $image_html Image markup from Elementor.
	 * @return void
	 */
	private function render_image( $image_html ) {
		if ( '' === $image_html ) {
			return;
		}

		$allowed_image_html = array(
			'img' => array(
				'alt'      => true,
				'class'    => true,
				'decoding'  => true,
				'draggable' => true,
				'height'    => true,
				'loading'  => true,
				'sizes'    => true,
				'src'      => true,
				'srcset'   => true,
				'title'    => true,
				'width'    => true,
			),
		);
		$image_html = preg_replace( '/class="([^"]*)"/', 'class="$1 widgets-manager-before-after__image" draggable="false"', $image_html, 1 );
		if ( false === strpos( $image_html, 'widgets-manager-before-after__image' ) ) {
			$image_html = preg_replace( '/<img\\b/', '<img class="widgets-manager-before-after__image" draggable="false"', $image_html, 1 );
		}
		echo wp_kses( $image_html, $allowed_image_html );
	}

	/**
	 * Outputs a visible label when labels are enabled and nonempty.
	 *
	 * @param bool                $show_labels Whether labels are enabled.
	 * @param array<string,mixed> $settings Widget settings.
	 * @param string              $setting Label setting name.
	 * @param string              $state Label state class.
	 * @return void
	 */
	private function render_label( $show_labels, array $settings, $setting, $state ) {
		if ( ! $show_labels || empty( $settings[ $setting ] ) ) {
			return;
		}
		?>
		<span class="widgets-manager-before-after__label widgets-manager-before-after__label--<?php echo esc_attr( $state ); ?>" aria-hidden="true"><?php echo esc_html( sanitize_text_field( $settings[ $setting ] ) ); ?></span>
		<?php
	}

	/**
	 * Outputs the default SVG or a supported Elementor icon.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @param bool                $custom_icon Whether to render a custom icon.
	 * @return void
	 */
	private function render_handle_icon( array $settings, $custom_icon ) {
		if ( $custom_icon && ! empty( $settings['handle_icon']['value'] ) ) {
			Icons_Manager::render_icon( $settings['handle_icon'], array( 'aria-hidden' => 'true' ) );
			return;
		}
		?>
		<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M8.7 5.3 2 12l6.7 6.7 1.4-1.4L5.8 13H18v-2H5.8l4.3-4.3-1.4-1.4Zm6.6 0-1.4 1.4 4.3 4.3H6v2h12.2l-4.3 4.3 1.4 1.4L22 12l-6.7-6.7Z" /></svg>
		<?php
	}
}
