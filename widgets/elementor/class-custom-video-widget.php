<?php
/**
 * Custom HTML5 video Elementor widget.
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
use Elementor\Modules\DynamicTags\Module as Tags_Module;
use Elementor\Repeater;
use Elementor\Widget_Base;
use WPFeaturesManager\Elementor_Adapter;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders a plugin-owned, progressively enhanced HTML5 video player.
 */
final class Custom_Video_Widget extends Widget_Base {

	/**
	 * Returns the widget identifier.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'widgets-manager-custom-video';
	}

	/**
	 * Returns the widget title.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'Custom Video', 'wp-features-manager' );
	}

	/**
	 * Returns the widget icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-play-o';
	}

	/**
	 * Returns widget categories.
	 *
	 * @return array<int,string>
	 */
	public function get_categories() {
		return array( Elementor_Adapter::CATEGORY );
	}

	/**
	 * Returns editor search keywords.
	 *
	 * @return array<int,string>
	 */
	public function get_keywords() {
		return array( 'video', 'html5', 'player', 'media', 'captions' );
	}

	/**
	 * Returns conditional stylesheet dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_style_depends() {
		return array( Elementor_Adapter::CUSTOM_VIDEO_STYLE_HANDLE );
	}

	/**
	 * Returns conditional script dependencies.
	 *
	 * @return array<int,string>
	 */
	public function get_script_depends() {
		return array( Elementor_Adapter::CUSTOM_VIDEO_SCRIPT_HANDLE );
	}

	/**
	 * Registers editor controls.
	 *
	 * @return void
	 */
	protected function register_controls() {
		$this->register_source_controls();
		$this->register_playback_controls();
		$this->register_overlay_controls();
		$this->register_dimension_controls();
		$this->register_control_visibility_controls();
		$this->register_controls_layout_controls();
		$this->register_player_style_controls();
		$this->register_overlay_style_controls();
		$this->register_play_button_style_controls();
		$this->register_control_bar_style_controls();
	}

	/**
	 * Registers video, alternate-source, poster, and captions settings.
	 *
	 * @return void
	 */
	private function register_source_controls() {
		$this->start_controls_section( 'custom_video_source', array( 'label' => __( 'Video Source', 'wp-features-manager' ) ) );
		$this->add_control(
			'video_source_type',
			array(
				'label'   => __( 'Source type', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'media',
				'options' => array(
					'media'    => __( 'WordPress Media Library', 'wp-features-manager' ),
					'external' => __( 'Direct video file URL', 'wp-features-manager' ),
				),
			)
		);
		$this->add_control(
			'video_media',
			array(
				'label'       => __( 'Video file', 'wp-features-manager' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array(
					'active'     => true,
					'categories' => array( Tags_Module::MEDIA_CATEGORY ),
				),
				'media_types' => array( 'video' ),
				'description' => __( 'For broad browser support, use an H.264 video with AAC audio in an MP4 container. HEVC videos may play audio without a picture in some browsers.', 'wp-features-manager' ),
				'condition'   => array( 'video_source_type' => 'media' ),
			)
		);
		$this->add_control(
			'video_url',
			array(
				'label'         => __( 'Direct video file URL', 'wp-features-manager' ),
				'type'          => Controls_Manager::URL,
				'dynamic'       => array(
					'active'     => true,
					'categories' => array(
						Tags_Module::POST_META_CATEGORY,
						Tags_Module::URL_CATEGORY,
					),
				),
				'placeholder'   => 'https://example.com/video.mp4',
				'show_external' => false,
				'description'   => __( 'Enter a direct MP4, WebM, OGV, or native HLS file URL. A YouTube or Vimeo page URL is not a video file URL.', 'wp-features-manager' ),
				'condition'     => array( 'video_source_type' => 'external' ),
			)
		);
		$source_repeater = new Repeater();
		$source_repeater->add_control( 'url', array( 'label' => __( 'File URL', 'wp-features-manager' ), 'type' => Controls_Manager::URL, 'show_external' => false ) );
		$source_repeater->add_control(
			'mime',
			array(
				'label'       => __( 'MIME type', 'wp-features-manager' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $this->mime_options(),
				'description' => __( 'Use a browser-playable alternate file such as WebM.', 'wp-features-manager' ),
			)
		);
		$this->add_control(
			'alternate_sources',
			array(
				'label'         => __( 'Fallback video files', 'wp-features-manager' ),
				'type'          => Controls_Manager::REPEATER,
				'fields'        => $source_repeater->get_controls(),
				'title_field'   => '{{{ url.url }}}',
				'prevent_empty' => false,
				'description'   => __( 'Optional alternate encodings of the same video, such as WebM after MP4. The browser uses the first format it can play.', 'wp-features-manager' ),
			)
		);
		$this->add_control( 'poster', array( 'label' => __( 'Poster image', 'wp-features-manager' ), 'type' => Controls_Manager::MEDIA ) );
		$this->add_group_control( Group_Control_Image_Size::get_type(), array( 'name' => 'poster_image', 'default' => 'large', 'condition' => array( 'poster[url]!' => '' ) ) );
		$this->add_control(
			'preload',
			array(
				'label'   => __( 'Preload', 'wp-features-manager' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'metadata',
				'options' => array( 'none' => __( 'None', 'wp-features-manager' ), 'metadata' => __( 'Metadata', 'wp-features-manager' ), 'auto' => __( 'Auto', 'wp-features-manager' ) ),
			)
		);
		$this->add_control( 'start_time', array( 'label' => __( 'Start time (seconds)', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'min' => 0, 'step' => 0.1 ) );
		$this->add_control( 'end_time', array( 'label' => __( 'End time (seconds)', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'min' => 0, 'step' => 0.1 ) );
		$this->add_control(
			'restart_at_end_time',
			array( 'label' => __( 'Restart when end time is reached', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' )
		);
		$this->add_control(
			'fallback_text',
			array( 'label' => __( 'Fallback text', 'wp-features-manager' ), 'type' => Controls_Manager::TEXTAREA, 'default' => __( 'Your browser does not support this video.', 'wp-features-manager' ) )
		);
		$track_repeater = new Repeater();
		$track_repeater->add_control( 'url', array( 'label' => __( 'WebVTT file URL', 'wp-features-manager' ), 'type' => Controls_Manager::URL, 'show_external' => false ) );
		$track_repeater->add_control( 'language', array( 'label' => __( 'Language code', 'wp-features-manager' ), 'type' => Controls_Manager::TEXT, 'placeholder' => 'en' ) );
		$track_repeater->add_control( 'label', array( 'label' => __( 'Language label', 'wp-features-manager' ), 'type' => Controls_Manager::TEXT, 'placeholder' => __( 'English', 'wp-features-manager' ) ) );
		$track_repeater->add_control( 'kind', array( 'label' => __( 'Kind', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'subtitles', 'options' => array( 'subtitles' => __( 'Subtitles', 'wp-features-manager' ), 'captions' => __( 'Captions', 'wp-features-manager' ) ) ) );
		$track_repeater->add_control( 'default', array( 'label' => __( 'Default track', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'caption_tracks', array( 'label' => __( 'Captions and subtitles', 'wp-features-manager' ), 'type' => Controls_Manager::REPEATER, 'fields' => $track_repeater->get_controls(), 'title_field' => '{{{ label }}}', 'prevent_empty' => false ) );
		$this->end_controls_section();
	}

	/**
	 * Registers playback behavior settings.
	 *
	 * @return void
	 */
	private function register_playback_controls() {
		$this->start_controls_section( 'custom_video_playback', array( 'label' => __( 'Playback', 'wp-features-manager' ) ) );
		$this->add_control( 'autoplay', array( 'label' => __( 'Autoplay', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '', 'description' => __( 'Browsers usually allow autoplay only when muted and played inline.', 'wp-features-manager' ) ) );
		$this->add_control( 'muted', array( 'label' => __( 'Muted', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'loop', array( 'label' => __( 'Loop', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'plays_inline', array( 'label' => __( 'Plays inline', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'pause_offscreen', array( 'label' => __( 'Pause outside viewport', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'pause_others', array( 'label' => __( 'Pause other players on start', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'remember_volume', array( 'label' => __( 'Remember volume for this page session', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'click_to_toggle', array( 'label' => __( 'Click video to play/pause', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'double_click_fullscreen', array( 'label' => __( 'Double-click video for fullscreen', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => '' ) );
		$this->add_control( 'disable_editor_autoplay', array( 'label' => __( 'Disable autoplay in the Elementor editor', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->end_controls_section();
	}

	/**
	 * Registers poster overlay and play-button settings.
	 *
	 * @return void
	 */
	private function register_overlay_controls() {
		$this->start_controls_section( 'custom_video_overlay', array( 'label' => __( 'Overlay', 'wp-features-manager' ) ) );
		$this->add_control( 'show_overlay', array( 'label' => __( 'Show poster overlay', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'show_play_button', array( 'label' => __( 'Show play button', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'show_overlay' => 'yes' ) ) );
		$this->add_control( 'play_label', array( 'label' => __( 'Play button label', 'wp-features-manager' ), 'type' => Controls_Manager::TEXT, 'default' => __( 'Play video', 'wp-features-manager' ), 'condition' => array( 'show_play_button' => 'yes' ) ) );
		$this->add_control( 'overlay_title', array( 'label' => __( 'Title', 'wp-features-manager' ), 'type' => Controls_Manager::TEXT ) );
		$this->add_control( 'overlay_subtitle', array( 'label' => __( 'Subtitle', 'wp-features-manager' ), 'type' => Controls_Manager::TEXTAREA ) );
		$this->add_control( 'title_tag', array( 'label' => __( 'Title HTML tag', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'h3', 'options' => array( 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'h5' => 'H5', 'h6' => 'H6', 'div' => 'div' ), 'condition' => array( 'overlay_title!' => '' ) ) );
		$this->add_control( 'overlay_horizontal_alignment', array( 'label' => __( 'Horizontal alignment', 'wp-features-manager' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'center', 'options' => array( 'left' => array( 'title' => __( 'Left', 'wp-features-manager' ), 'icon' => 'eicon-text-align-left' ), 'center' => array( 'title' => __( 'Center', 'wp-features-manager' ), 'icon' => 'eicon-text-align-center' ), 'right' => array( 'title' => __( 'Right', 'wp-features-manager' ), 'icon' => 'eicon-text-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__overlay-content' => 'align-items: {{VALUE}}; text-align: {{VALUE}};' ) ) );
		$this->add_control( 'overlay_vertical_alignment', array( 'label' => __( 'Vertical alignment', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'center', 'options' => array( 'flex-start' => __( 'Top', 'wp-features-manager' ), 'center' => __( 'Center', 'wp-features-manager' ), 'flex-end' => __( 'Bottom', 'wp-features-manager' ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__overlay' => 'justify-content: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'overlay_gap', array( 'label' => __( 'Content spacing', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 14, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 80 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-overlay-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'hide_overlay_text_on_play', array( 'label' => __( 'Hide overlay text after playback begins', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'restore_overlay_on_end', array( 'label' => __( 'Restore poster overlay when playback ends', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes' ) );
		$this->add_control( 'play_icon_source', array( 'label' => __( 'Play icon', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'default', 'options' => array( 'default' => __( 'Default play triangle', 'wp-features-manager' ), 'custom' => __( 'Elementor icon', 'wp-features-manager' ) ), 'condition' => array( 'show_play_button' => 'yes' ) ) );
		$this->add_control( 'play_icon', array( 'label' => __( 'Custom play icon', 'wp-features-manager' ), 'type' => Controls_Manager::ICONS, 'condition' => array( 'play_icon_source' => 'custom' ) ) );
		$this->add_control( 'play_icon_alignment', array( 'label' => __( 'Icon alignment', 'wp-features-manager' ), 'type' => Controls_Manager::CHOOSE, 'default' => 'center', 'options' => array( 'left' => array( 'title' => __( 'Left', 'wp-features-manager' ), 'icon' => 'eicon-h-align-left' ), 'center' => array( 'title' => __( 'Center', 'wp-features-manager' ), 'icon' => 'eicon-h-align-center' ), 'right' => array( 'title' => __( 'Right', 'wp-features-manager' ), 'icon' => 'eicon-h-align-right' ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__play-icon' => 'justify-content: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'play_icon_offset', array( 'label' => __( 'Icon horizontal offset', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => -30, 'max' => 30 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__play-icon' => 'transform: translateX({{SIZE}}{{UNIT}});' ) ) );
		$this->add_control( 'play_animation', array( 'label' => __( 'Play-button animation', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'rings', 'options' => $this->animation_options() ) );
		$this->add_control( 'animation_duration', array( 'label' => __( 'Animation duration (seconds)', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'default' => 2.4, 'min' => 0.5, 'max' => 12, 'step' => 0.1, 'condition' => array( 'play_animation!' => 'none' ) ) );
		$this->add_control( 'animation_delay', array( 'label' => __( 'Animation delay (seconds)', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'default' => 0, 'min' => 0, 'max' => 8, 'step' => 0.1, 'condition' => array( 'play_animation!' => 'none' ) ) );
		$this->add_control( 'ring_count', array( 'label' => __( 'Ring count', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'default' => 2, 'min' => 1, 'max' => 3, 'step' => 1, 'condition' => array( 'play_animation' => array( 'rings', 'ripple' ) ) ) );
		$this->add_control( 'ring_opacity', array( 'label' => __( 'Ring opacity', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 0.55 ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.05 ) ), 'condition' => array( 'play_animation' => array( 'rings', 'ripple' ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-ring-opacity: {{SIZE}};' ) ) );
		$this->add_control( 'animation_easing', array( 'label' => __( 'Animation easing', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'ease-in-out', 'options' => array( 'linear' => __( 'Linear', 'wp-features-manager' ), 'ease' => __( 'Ease', 'wp-features-manager' ), 'ease-in-out' => __( 'Ease in out', 'wp-features-manager' ) ), 'condition' => array( 'play_animation!' => 'none' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-animation-easing: {{VALUE}};' ) ) );
		$this->add_control( 'pause_animation_on_play', array( 'label' => __( 'Pause animation during playback', 'wp-features-manager' ), 'type' => Controls_Manager::SWITCHER, 'return_value' => 'yes', 'default' => 'yes', 'condition' => array( 'play_animation!' => 'none' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers responsive player dimensions.
	 *
	 * @return void
	 */
	private function register_dimension_controls() {
		$this->start_controls_section( 'custom_video_dimensions', array( 'label' => __( 'Responsive Dimensions', 'wp-features-manager' ) ) );
		$this->add_responsive_control( 'player_width', array( 'label' => __( 'Width', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( '%', 'px', 'vw' ), 'range' => array( '%' => array( 'min' => 1, 'max' => 100 ), 'px' => array( 'min' => 100, 'max' => 2000 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => 'width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'player_max_width', array( 'label' => __( 'Maximum width', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( '%', 'px', 'vw' ), 'range' => array( '%' => array( 'min' => 1, 'max' => 100 ), 'px' => array( 'min' => 100, 'max' => 2400 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => 'max-width: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'sizing_mode', array( 'label' => __( 'Sizing mode', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'ratio', 'options' => array( 'ratio' => __( 'Aspect ratio', 'wp-features-manager' ), 'height' => __( 'Custom height', 'wp-features-manager' ) ) ) );
		$this->add_control( 'aspect_ratio', array( 'label' => __( 'Aspect ratio', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => '16 / 9', 'options' => array( '16 / 9' => '16:9', '4 / 3' => '4:3', '1 / 1' => '1:1', '9 / 16' => '9:16', '21 / 9' => '21:9', 'custom' => __( 'Custom', 'wp-features-manager' ) ), 'condition' => array( 'sizing_mode' => 'ratio' ) ) );
		$this->add_control( 'custom_aspect_ratio', array( 'label' => __( 'Custom aspect ratio', 'wp-features-manager' ), 'type' => Controls_Manager::TEXT, 'placeholder' => '3 / 2', 'description' => __( 'Use two positive numbers separated by a slash, for example 3 / 2.', 'wp-features-manager' ), 'condition' => array( 'sizing_mode' => 'ratio', 'aspect_ratio' => 'custom' ) ) );
		$this->add_responsive_control( 'custom_height', array( 'label' => __( 'Custom height', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh', 'vw' ), 'range' => array( 'px' => array( 'min' => 100, 'max' => 1600 ) ), 'condition' => array( 'sizing_mode' => 'height' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__stage' => 'height: {{SIZE}}{{UNIT}}; aspect-ratio: auto;' ) ) );
		$this->add_responsive_control( 'minimum_height', array( 'label' => __( 'Minimum height', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'size_units' => array( 'px', 'vh' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1200 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__stage' => 'min-height: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'video_object_fit', array( 'label' => __( 'Video object fit', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'cover', 'options' => array( 'cover' => __( 'Cover', 'wp-features-manager' ), 'contain' => __( 'Contain', 'wp-features-manager' ), 'fill' => __( 'Fill', 'wp-features-manager' ), 'scale-down' => __( 'Scale down', 'wp-features-manager' ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__media' => 'object-fit: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'video_object_position', array( 'label' => __( 'Video focal position', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'center center', 'options' => $this->focal_position_options(), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__media' => 'object-position: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers independent responsive visibility controls for every feature.
	 *
	 * @return void
	 */
	private function register_control_visibility_controls() {
		$this->start_controls_section( 'custom_video_controls', array( 'label' => __( 'Custom Controls', 'wp-features-manager' ) ) );
		$this->add_control( 'control_help', array( 'type' => Controls_Manager::RAW_HTML, 'raw' => esc_html__( 'Each control can be shown or hidden at every active Elementor breakpoint.', 'wp-features-manager' ) ) );
		foreach ( $this->control_visibility_definitions() as $control_key => $control ) {
			if ( 'show_buffered' === $control_key ) {
				$this->add_responsive_control(
					$control_key,
					array(
						'label'     => $control['label'],
						'type'      => Controls_Manager::SELECT,
						'default'   => 'var(--wm-video-buffer-color)',
						'options'   => array( 'var(--wm-video-buffer-color)' => __( 'Show', 'wp-features-manager' ), 'transparent' => __( 'Hide', 'wp-features-manager' ) ),
						'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-buffer-display-color: {{VALUE}};' ),
					)
				);
				continue;
			}
			$this->add_responsive_control(
				$control_key,
				array(
					'label'     => $control['label'],
					'type'      => Controls_Manager::SELECT,
					'default'   => $control['default'],
					'options'   => array( 'inline-flex' => __( 'Show', 'wp-features-manager' ), 'none' => __( 'Hide', 'wp-features-manager' ) ),
					'selectors' => array( '{{WRAPPER}} ' . $control['selector'] => 'display: {{VALUE}};' ),
				)
			);
		}
		$this->add_control( 'seek_interval', array( 'label' => __( 'Rewind and fast-forward interval', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => '10', 'options' => array( '5' => '5', '10' => '10', '15' => '15', '30' => '30' ) ) );
		$this->add_control( 'download_url', array( 'label' => __( 'Download URL', 'wp-features-manager' ), 'type' => Controls_Manager::URL, 'show_external' => false, 'condition' => array( 'show_download!' => 'none' ), 'description' => __( 'Hiding this link does not protect the delivered video file.', 'wp-features-manager' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers controls bar layout settings.
	 *
	 * @return void
	 */
	private function register_controls_layout_controls() {
		$this->start_controls_section( 'custom_video_controls_layout', array( 'label' => __( 'Controls Layout', 'wp-features-manager' ) ) );
		$this->add_control( 'controls_position', array( 'label' => __( 'Controls position', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'overlay', 'options' => array( 'overlay' => __( 'Overlay bottom', 'wp-features-manager' ), 'below' => __( 'Below video', 'wp-features-manager' ) ) ) );
		$this->add_control( 'controls_visibility', array( 'label' => __( 'Control bar visibility', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'hover', 'options' => array( 'always' => __( 'Always visible', 'wp-features-manager' ), 'hover' => __( 'On hover or focus', 'wp-features-manager' ), 'paused' => __( 'While paused', 'wp-features-manager' ) ) ) );
		$this->add_control( 'controls_hide_delay', array( 'label' => __( 'Auto-hide delay (milliseconds)', 'wp-features-manager' ), 'type' => Controls_Manager::NUMBER, 'default' => 2000, 'min' => 0, 'max' => 15000, 'step' => 100, 'condition' => array( 'controls_visibility' => 'hover' ) ) );
		$this->add_control( 'timeline_placement', array( 'label' => __( 'Timeline placement', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'row', 'options' => array( 'inline' => __( 'Inline', 'wp-features-manager' ), 'row' => __( 'Own row', 'wp-features-manager' ) ) ) );
		$this->add_control( 'controls_grouping', array( 'label' => __( 'Control grouping', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'grouped', 'options' => array( 'grouped' => __( 'Playback left, actions right', 'wp-features-manager' ), 'single' => __( 'Single group', 'wp-features-manager' ) ) ) );
		$this->add_responsive_control( 'compact_mobile', array( 'label' => __( 'Compact controls', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => '0px', 'options' => array( '0px' => __( 'Standard', 'wp-features-manager' ), '-4px' => __( 'Compact', 'wp-features-manager' ) ), 'description' => __( 'Choose Compact at any active Elementor breakpoint to reduce control spacing without shrinking touch targets.', 'wp-features-manager' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-compact-gap-adjustment: {{VALUE}};' ) ) );
		$this->add_control( 'time_format', array( 'label' => __( 'Time format', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'duration', 'options' => array( 'elapsed' => __( 'Elapsed only', 'wp-features-manager' ), 'duration' => __( 'Elapsed / duration', 'wp-features-manager' ) ) ) );
		$this->add_control( 'volume_direction', array( 'label' => __( 'Volume slider direction', 'wp-features-manager' ), 'type' => Controls_Manager::SELECT, 'default' => 'horizontal', 'options' => array( 'horizontal' => __( 'Horizontal', 'wp-features-manager' ), 'vertical' => __( 'Vertical', 'wp-features-manager' ) ) ) );
		$this->add_responsive_control( 'volume_width', array( 'label' => __( 'Volume slider length', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 90, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 44, 'max' => 240 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-volume-length: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'control_gap', array( 'label' => __( 'Control gap', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 6, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 40 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-controls-gap: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'controls_padding', array( 'label' => __( 'Control bar padding', 'wp-features-manager' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em', 'rem', '%' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__controls' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers player, poster, and media style controls.
	 *
	 * @return void
	 */
	private function register_player_style_controls() {
		$this->start_controls_section( 'custom_video_player_style', array( 'label' => __( 'Player Frame', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Background::get_type(), array( 'name' => 'player_background', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video' ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'player_border', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video' ) );
		$this->add_responsive_control( 'player_radius', array( 'label' => __( 'Border radius', 'wp-features-manager' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%', 'em', 'rem' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'player_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video' ) );
		$this->add_responsive_control( 'poster_opacity', array( 'label' => __( 'Poster opacity', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 1 ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 1, 'step' => 0.01 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__media' => 'opacity: {{SIZE}};' ) ) );
		$this->add_group_control( Group_Control_Css_Filter::get_type(), array( 'name' => 'video_filters', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__media' ) );
		$this->add_control( 'media_transition', array( 'label' => __( 'Media transition duration', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 200, 'unit' => 'ms' ), 'size_units' => array( 'ms', 's' ), 'range' => array( 'ms' => array( 'min' => 0, 'max' => 2000 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__media' => 'transition-duration: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers overlay text and tint styles.
	 *
	 * @return void
	 */
	private function register_overlay_style_controls() {
		$this->start_controls_section( 'custom_video_overlay_style', array( 'label' => __( 'Overlay and Text', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Background::get_type(), array( 'name' => 'overlay_background', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__overlay' ) );
		$this->add_responsive_control( 'overlay_padding', array( 'label' => __( 'Overlay padding', 'wp-features-manager' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', 'em', 'rem', '%' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__overlay' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_control( 'title_heading', array( 'label' => __( 'Title', 'wp-features-manager' ), 'type' => Controls_Manager::HEADING ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'overlay_title_typography', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__title' ) );
		$this->add_control( 'overlay_title_color', array( 'label' => __( 'Title color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__title' => 'color: {{VALUE}};' ) ) );
		$this->add_control( 'subtitle_heading', array( 'label' => __( 'Subtitle', 'wp-features-manager' ), 'type' => Controls_Manager::HEADING ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'overlay_subtitle_typography', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__subtitle' ) );
		$this->add_control( 'overlay_subtitle_color', array( 'label' => __( 'Subtitle color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__subtitle' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers play-button styles and animation properties.
	 *
	 * @return void
	 */
	private function register_play_button_style_controls() {
		$this->start_controls_section( 'custom_video_play_style', array( 'label' => __( 'Play Button', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_responsive_control( 'play_button_size', array( 'label' => __( 'Button size', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 82, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 44, 'max' => 220 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-size: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'play_icon_size', array( 'label' => __( 'Icon size', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 28, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 12, 'max' => 120 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-icon-size: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'play_button_background', array( 'label' => __( 'Background', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'default' => '#87e8df', 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-background: {{VALUE}};' ) ) );
		$this->add_control( 'play_button_color', array( 'label' => __( 'Icon color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'default' => '#11243b', 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-color: {{VALUE}};' ) ) );
		$this->add_control( 'play_button_hover_background', array( 'label' => __( 'Hover background', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-hover-background: {{VALUE}};' ) ) );
		$this->add_control( 'play_button_hover_color', array( 'label' => __( 'Hover icon color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-play-hover-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'play_button_border', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__play' ) );
		$this->add_responsive_control( 'play_button_radius', array( 'label' => __( 'Border radius', 'wp-features-manager' ), 'type' => Controls_Manager::DIMENSIONS, 'default' => array( 'top' => 50, 'right' => 50, 'bottom' => 50, 'left' => 50, 'unit' => '%' ), 'size_units' => array( 'px', '%', 'em', 'rem' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__play' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'play_button_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__play' ) );
		$this->add_control( 'animation_ring_color', array( 'label' => __( 'Animation ring/glow color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'default' => '#87e8df', 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-ring-color: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'animation_max_scale', array( 'label' => __( 'Ring maximum scale', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 1.8 ), 'range' => array( 'px' => array( 'min' => 1, 'max' => 4, 'step' => 0.1 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-ring-scale: {{SIZE}};' ) ) );
		$this->add_responsive_control( 'glow_blur', array( 'label' => __( 'Glow blur', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 28, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 0, 'max' => 100 ) ), 'condition' => array( 'play_animation' => 'glow' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-glow-blur: {{SIZE}}{{UNIT}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Registers control-bar, button, timeline, and menu styles.
	 *
	 * @return void
	 */
	private function register_control_bar_style_controls() {
		$this->start_controls_section( 'custom_video_controls_style', array( 'label' => __( 'Control Bar', 'wp-features-manager' ), 'tab' => Controls_Manager::TAB_STYLE ) );
		$this->add_group_control( Group_Control_Background::get_type(), array( 'name' => 'controls_background', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__controls' ) );
		$this->add_group_control( Group_Control_Border::get_type(), array( 'name' => 'controls_border', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__controls' ) );
		$this->add_responsive_control( 'controls_radius', array( 'label' => __( 'Control bar radius', 'wp-features-manager' ), 'type' => Controls_Manager::DIMENSIONS, 'size_units' => array( 'px', '%', 'em', 'rem' ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__controls' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ) ) );
		$this->add_group_control( Group_Control_Box_Shadow::get_type(), array( 'name' => 'controls_shadow', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__controls' ) );
		$this->add_control( 'controls_backdrop_blur', array( 'label' => __( 'Backdrop blur', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'range' => array( 'px' => array( 'min' => 0, 'max' => 30 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__controls' => 'backdrop-filter: blur({{SIZE}}{{UNIT}}); -webkit-backdrop-filter: blur({{SIZE}}{{UNIT}});' ) ) );
		$this->add_control( 'control_button_color', array( 'label' => __( 'Button color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-control-color: {{VALUE}};' ) ) );
		$this->add_control( 'control_button_hover_color', array( 'label' => __( 'Button hover color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'default' => '#ffffff', 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-control-hover-color: {{VALUE}};' ) ) );
		$this->add_control( 'control_button_background', array( 'label' => __( 'Button background', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-control-background: {{VALUE}};' ) ) );
		$this->add_responsive_control( 'control_button_size', array( 'label' => __( 'Button size', 'wp-features-manager' ), 'type' => Controls_Manager::SLIDER, 'default' => array( 'size' => 44, 'unit' => 'px' ), 'range' => array( 'px' => array( 'min' => 32, 'max' => 72 ) ), 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-control-size: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_control( 'timeline_track_color', array( 'label' => __( 'Timeline track color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-track-color: {{VALUE}};' ) ) );
		$this->add_control( 'timeline_buffer_color', array( 'label' => __( 'Timeline buffered color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-buffer-color: {{VALUE}};' ) ) );
		$this->add_control( 'timeline_progress_color', array( 'label' => __( 'Timeline played color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-progress-color: {{VALUE}};' ) ) );
		$this->add_control( 'timeline_thumb_color', array( 'label' => __( 'Timeline thumb color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video' => '--wm-video-thumb-color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'time_typography', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__time' ) );
		$this->add_control( 'time_color', array( 'label' => __( 'Time text color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__time' => 'color: {{VALUE}};' ) ) );
		$this->add_group_control( Group_Control_Typography::get_type(), array( 'name' => 'menu_typography', 'selector' => '{{WRAPPER}} .widgets-manager-custom-video__menu' ) );
		$this->add_control( 'menu_background', array( 'label' => __( 'Menu background', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__menu' => 'background-color: {{VALUE}};' ) ) );
		$this->add_control( 'menu_color', array( 'label' => __( 'Menu text color', 'wp-features-manager' ), 'type' => Controls_Manager::COLOR, 'selectors' => array( '{{WRAPPER}} .widgets-manager-custom-video__menu' => 'color: {{VALUE}};' ) ) );
		$this->end_controls_section();
	}

	/**
	 * Renders a configured player or a concise editor placeholder.
	 *
	 * @return void
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();
		$sources  = self::normalized_sources( $settings );
		if ( empty( $sources ) ) {
			$this->render_empty_state();
			return;
		}

		$tracks       = self::normalized_tracks( $settings );
		$poster       = $this->poster_url( $settings );
		$title_tag    = $this->allowed_value( $settings, 'title_tag', array( 'h2', 'h3', 'h4', 'h5', 'h6', 'div' ), 'h3' );
		$ratio        = $this->aspect_ratio_value( $settings );
		$animation     = $this->allowed_value( $settings, 'play_animation', array_keys( $this->animation_options() ), 'rings' );
		$labels        = $this->frontend_labels();
		$root_classes  = 'widgets-manager-custom-video widgets-manager-custom-video--' . $this->allowed_value( $settings, 'controls_position', array( 'overlay', 'below' ), 'overlay' ) . ' widgets-manager-custom-video--timeline-' . $this->allowed_value( $settings, 'timeline_placement', array( 'inline', 'row' ), 'row' ) . ' widgets-manager-custom-video--controls-' . $this->allowed_value( $settings, 'controls_grouping', array( 'grouped', 'single' ), 'grouped' ) . ' widgets-manager-custom-video--volume-' . $this->allowed_value( $settings, 'volume_direction', array( 'horizontal', 'vertical' ), 'horizontal' ) . ' widgets-manager-custom-video--animation-' . $animation;
		if ( 'ratio' === $this->allowed_value( $settings, 'sizing_mode', array( 'ratio', 'height' ), 'ratio' ) ) {
			$root_classes .= ' widgets-manager-custom-video--ratio';
		}
		?>
		<div class="<?php echo esc_attr( $root_classes ); ?>" data-widgets-manager-custom-video tabindex="0" role="group" aria-label="<?php echo esc_attr__( 'Custom video player', 'wp-features-manager' ); ?>" data-autoplay="<?php echo esc_attr( $this->boolean_attribute( $settings, 'autoplay' ) ); ?>" data-pause-offscreen="<?php echo esc_attr( $this->boolean_attribute( $settings, 'pause_offscreen' ) ); ?>" data-pause-others="<?php echo esc_attr( $this->boolean_attribute( $settings, 'pause_others' ) ); ?>" data-remember-volume="<?php echo esc_attr( $this->boolean_attribute( $settings, 'remember_volume' ) ); ?>" data-click-to-toggle="<?php echo esc_attr( $this->boolean_attribute( $settings, 'click_to_toggle' ) ); ?>" data-double-click-fullscreen="<?php echo esc_attr( $this->boolean_attribute( $settings, 'double_click_fullscreen' ) ); ?>" data-disable-editor-autoplay="<?php echo esc_attr( $this->boolean_attribute( $settings, 'disable_editor_autoplay' ) ); ?>" data-start-time="<?php echo esc_attr( $this->time_value( $settings, 'start_time' ) ); ?>" data-end-time="<?php echo esc_attr( $this->time_value( $settings, 'end_time' ) ); ?>" data-restart-at-end="<?php echo esc_attr( $this->boolean_attribute( $settings, 'restart_at_end_time' ) ); ?>" data-hide-overlay-text="<?php echo esc_attr( $this->boolean_attribute( $settings, 'hide_overlay_text_on_play' ) ); ?>" data-restore-overlay="<?php echo esc_attr( $this->boolean_attribute( $settings, 'restore_overlay_on_end' ) ); ?>" data-pause-animation="<?php echo esc_attr( $this->boolean_attribute( $settings, 'pause_animation_on_play' ) ); ?>" data-controls-visibility="<?php echo esc_attr( $this->allowed_value( $settings, 'controls_visibility', array( 'always', 'hover', 'paused' ), 'hover' ) ); ?>" data-controls-delay="<?php echo esc_attr( $this->bounded_integer( $settings, 'controls_hide_delay', 0, 15000, 2000 ) ); ?>" data-seek-interval="<?php echo esc_attr( $this->bounded_integer( $settings, 'seek_interval', 5, 30, 10 ) ); ?>" data-time-format="<?php echo esc_attr( $this->allowed_value( $settings, 'time_format', array( 'elapsed', 'duration' ), 'duration' ) ); ?>" data-labels="<?php echo esc_attr( wp_json_encode( $labels ) ); ?>" style="--wm-video-aspect-ratio: <?php echo esc_attr( $ratio ); ?>; --wm-video-animation-duration: <?php echo esc_attr( $this->bounded_decimal( $settings, 'animation_duration', 0.5, 12, 2.4 ) ); ?>s; --wm-video-animation-delay: <?php echo esc_attr( $this->bounded_decimal( $settings, 'animation_delay', 0, 8, 0 ) ); ?>s;">
			<div class="widgets-manager-custom-video__stage">
				<video class="widgets-manager-custom-video__media" controls preload="<?php echo esc_attr( $this->allowed_value( $settings, 'preload', array( 'none', 'metadata', 'auto' ), 'metadata' ) ); ?>"<?php echo $poster ? ' poster="' . esc_url( $poster ) . '"' : ''; ?><?php echo $this->is_enabled( $settings, 'muted' ) ? ' muted' : ''; ?><?php echo $this->is_enabled( $settings, 'loop' ) ? ' loop' : ''; ?><?php echo $this->is_enabled( $settings, 'plays_inline' ) ? ' playsinline' : ''; ?>>
					<?php $this->render_sources( $sources ); ?>
					<?php $this->render_tracks( $tracks ); ?>
					<?php echo esc_html( $this->text_value( $settings, 'fallback_text', __( 'Your browser does not support this video.', 'wp-features-manager' ) ) ); ?>
				</video>
				<?php $this->render_overlay( $settings, $title_tag, $animation ); ?>
				<p class="widgets-manager-custom-video__status screen-reader-text" aria-live="polite"></p>
			</div>
			<?php $this->render_controls( $settings, $tracks, $sources[0]['url'] ); ?>
		</div>
		<?php
	}

	/**
	 * Returns valid source entries for a setting array.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return array<int,array<string,string>>
	 */
	public static function normalized_sources( array $settings ) {
		$sources = array();
		$primary = 'external' === ( isset( $settings['video_source_type'] ) ? $settings['video_source_type'] : '' ) ? self::nested_url( $settings, 'video_url' ) : self::nested_url( $settings, 'video_media' );
		if ( '' !== $primary ) {
			$sources[] = array( 'url' => $primary, 'mime' => self::mime_from_url( $primary ) );
		}
		if ( ! empty( $settings['alternate_sources'] ) && is_array( $settings['alternate_sources'] ) ) {
			foreach ( $settings['alternate_sources'] as $alternate_source ) {
				if ( ! is_array( $alternate_source ) ) {
					continue;
				}
				$url = self::nested_url( $alternate_source, 'url' );
				if ( '' === $url || self::source_exists( $sources, $url ) ) {
					continue;
				}
				$mime = isset( $alternate_source['mime'] ) ? sanitize_text_field( $alternate_source['mime'] ) : '';
				if ( '' !== $mime && ! in_array( $mime, array( 'video/mp4', 'video/webm', 'video/ogg', 'application/x-mpegURL' ), true ) ) {
					continue;
				}
				$sources[] = array( 'url' => $url, 'mime' => $mime ? $mime : self::mime_from_url( $url ) );
			}
		}
		return $sources;
	}

	/**
	 * Returns valid, browser-native text track entries.
	 *
	 * @param array<string,mixed> $settings Widget settings.
	 * @return array<int,array<string,string>>
	 */
	public static function normalized_tracks( array $settings ) {
		$tracks      = array();
		$has_default = false;
		if ( empty( $settings['caption_tracks'] ) || ! is_array( $settings['caption_tracks'] ) ) {
			return $tracks;
		}
		foreach ( $settings['caption_tracks'] as $track ) {
			if ( ! is_array( $track ) ) {
				continue;
			}
			$url      = self::nested_url( $track, 'url' );
			$language = isset( $track['language'] ) ? sanitize_key( $track['language'] ) : '';
			$label    = isset( $track['label'] ) ? sanitize_text_field( $track['label'] ) : '';
			$kind     = isset( $track['kind'] ) ? sanitize_key( $track['kind'] ) : 'subtitles';
			if ( '' === $url || '' === $language || '' === $label || ! in_array( $kind, array( 'subtitles', 'captions' ), true ) ) {
				continue;
			}
			$default  = ! $has_default && isset( $track['default'] ) && 'yes' === $track['default'];
			$tracks[] = array( 'url' => $url, 'language' => $language, 'label' => $label, 'kind' => $kind, 'default' => $default ? 'yes' : '' );
			$has_default = $has_default || $default;
		}
		return $tracks;
	}

	/**
	 * Renders an editor-only empty-state placeholder.
	 *
	 * @return void
	 */
	private function render_empty_state() {
		if ( $this->is_elementor_editor() ) {
			echo '<div class="widgets-manager-custom-video__empty">' . esc_html__( 'Choose a Media Library video or direct video-file URL to configure Custom Video.', 'wp-features-manager' ) . '</div>';
		}
	}

	/**
	 * Renders source tags.
	 *
	 * @param array<int,array<string,string>> $sources Sources.
	 * @return void
	 */
	private function render_sources( array $sources ) {
		foreach ( $sources as $source ) {
			?>
			<source src="<?php echo esc_url( $source['url'] ); ?>"<?php echo '' !== $source['mime'] ? ' type="' . esc_attr( $source['mime'] ) . '"' : ''; ?> />
			<?php
		}
	}

	/**
	 * Renders valid VTT tracks with at most one default track.
	 *
	 * @param array<int,array<string,string>> $tracks Tracks.
	 * @return void
	 */
	private function render_tracks( array $tracks ) {
		foreach ( $tracks as $track ) {
			?>
			<track kind="<?php echo esc_attr( $track['kind'] ); ?>" src="<?php echo esc_url( $track['url'] ); ?>" srclang="<?php echo esc_attr( $track['language'] ); ?>" label="<?php echo esc_attr( $track['label'] ); ?>"<?php echo 'yes' === $track['default'] ? ' default' : ''; ?> />
			<?php
		}
	}

	/**
	 * Renders the poster overlay and plugin-owned safe default SVG.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $title_tag Safe title tag.
	 * @param string              $animation Animation identifier.
	 * @return void
	 */
	private function render_overlay( array $settings, $title_tag, $animation ) {
		if ( ! $this->is_enabled( $settings, 'show_overlay' ) ) {
			return;
		}
		$title    = $this->text_value( $settings, 'overlay_title', '' );
		$subtitle = $this->text_value( $settings, 'overlay_subtitle', '' );
		?>
		<div class="widgets-manager-custom-video__overlay">
			<div class="widgets-manager-custom-video__overlay-content">
				<?php if ( $this->is_enabled( $settings, 'show_play_button' ) ) : ?>
					<button class="widgets-manager-custom-video__play" type="button" aria-label="<?php echo esc_attr( $this->text_value( $settings, 'play_label', __( 'Play video', 'wp-features-manager' ) ) ); ?>" data-wm-action="play">
						<?php $this->render_animation_rings( $settings, $animation ); ?>
						<span class="widgets-manager-custom-video__play-icon" aria-hidden="true"><?php $this->render_play_icon( $settings ); ?></span>
					</button>
				<?php endif; ?>
				<?php if ( '' !== $title || '' !== $subtitle ) : ?>
					<div class="widgets-manager-custom-video__overlay-text">
						<?php if ( '' !== $title ) : ?>
							<<?php echo esc_attr( $title_tag ); ?> class="widgets-manager-custom-video__title"><?php echo esc_html( $title ); ?></<?php echo esc_attr( $title_tag ); ?>>
						<?php endif; ?>
						<?php if ( '' !== $subtitle ) : ?><p class="widgets-manager-custom-video__subtitle"><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders custom controls which remain hidden until JavaScript initializes.
	 *
	 * @param array<string,mixed>              $settings Settings.
	 * @param array<int,array<string,string>>  $tracks Tracks.
	 * @param string                           $primary_url Primary source URL.
	 * @return void
	 */
	private function render_controls( array $settings, array $tracks, $primary_url ) {
		$download_url       = self::nested_url( $settings, 'download_url' );
		$timeline_placement = $this->allowed_value( $settings, 'timeline_placement', array( 'inline', 'row' ), 'row' );
		if ( '' === $download_url ) {
			$download_url = $primary_url;
		}
		?>
		<div class="widgets-manager-custom-video__controls" role="group" aria-label="<?php echo esc_attr__( 'Video controls', 'wp-features-manager' ); ?>" aria-hidden="true">
			<?php if ( 'row' === $timeline_placement ) : ?>
				<div class="widgets-manager-custom-video__controls-row widgets-manager-custom-video__controls-row--timeline">
					<?php $this->render_seek_control(); ?>
				</div>
			<?php endif; ?>
			<div class="widgets-manager-custom-video__controls-row widgets-manager-custom-video__controls-row--main">
				<div class="widgets-manager-custom-video__controls-group widgets-manager-custom-video__controls-group--playback">
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--play" type="button" data-wm-action="play" aria-label="<?php echo esc_attr__( 'Play video', 'wp-features-manager' ); ?>" aria-pressed="false"><span class="widgets-manager-custom-video__control-icon widgets-manager-custom-video__control-icon--play" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M8 5.7v12.6L18.5 12 8 5.7Z" /></svg></span><span class="widgets-manager-custom-video__control-icon widgets-manager-custom-video__control-icon--pause" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><path d="M7 5h4v14H7V5Zm6 0h4v14h-4V5Z" /></svg></span></button>
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--rewind" type="button" data-wm-action="rewind" aria-label="<?php echo esc_attr__( 'Rewind', 'wp-features-manager' ); ?>"><span aria-hidden="true">↺</span></button>
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--forward" type="button" data-wm-action="forward" aria-label="<?php echo esc_attr__( 'Fast forward', 'wp-features-manager' ); ?>"><span aria-hidden="true">↻</span></button>
					<span class="widgets-manager-custom-video__time widgets-manager-custom-video__time--current" aria-label="<?php echo esc_attr__( 'Current time', 'wp-features-manager' ); ?>">0:00</span>
					<?php if ( 'inline' === $timeline_placement ) : ?>
						<?php $this->render_seek_control(); ?>
					<?php endif; ?>
					<span class="widgets-manager-custom-video__time widgets-manager-custom-video__time--duration" aria-label="<?php echo esc_attr__( 'Duration', 'wp-features-manager' ); ?>">0:00</span>
				</div>
				<div class="widgets-manager-custom-video__controls-group widgets-manager-custom-video__controls-group--actions">
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--mute" type="button" data-wm-action="mute" aria-label="<?php echo esc_attr__( 'Mute video', 'wp-features-manager' ); ?>" aria-pressed="false"><span aria-hidden="true">🔊</span></button>
					<label class="widgets-manager-custom-video__volume-label"><span class="screen-reader-text"><?php esc_html_e( 'Volume', 'wp-features-manager' ); ?></span><input class="widgets-manager-custom-video__volume" type="range" min="0" max="1" value="1" step="0.05" aria-label="<?php echo esc_attr__( 'Volume', 'wp-features-manager' ); ?>" /></label>
					<div class="widgets-manager-custom-video__menu-wrap widgets-manager-custom-video__menu-wrap--speed"><button class="widgets-manager-custom-video__control" type="button" data-wm-menu="speed" aria-expanded="false" aria-haspopup="menu" aria-label="<?php echo esc_attr__( 'Playback speed', 'wp-features-manager' ); ?>">1×</button><div class="widgets-manager-custom-video__menu" role="menu" hidden><?php $this->render_speed_options(); ?></div></div>
					<?php $this->render_caption_menu( $tracks ); ?>
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--pip" type="button" data-wm-action="pip" aria-label="<?php echo esc_attr__( 'Picture in Picture', 'wp-features-manager' ); ?>">▣</button>
					<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--fullscreen" type="button" data-wm-action="fullscreen" aria-label="<?php echo esc_attr__( 'Fullscreen', 'wp-features-manager' ); ?>" aria-pressed="false">⛶</button>
					<a class="widgets-manager-custom-video__control widgets-manager-custom-video__control--download" href="<?php echo esc_url( $download_url ); ?>" download aria-label="<?php echo esc_attr__( 'Download video', 'wp-features-manager' ); ?>">⇩</a>
					<?php $this->render_mobile_overflow_menu( $settings, $tracks, $download_url ); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders the seek control in its selected layout row.
	 *
	 * @return void
	 */
	private function render_seek_control() {
		?>
		<label class="widgets-manager-custom-video__seek-label"><span class="screen-reader-text"><?php esc_html_e( 'Video progress', 'wp-features-manager' ); ?></span><input class="widgets-manager-custom-video__seek" type="range" min="0" max="100" value="0" step="0.1" aria-label="<?php echo esc_attr__( 'Video progress', 'wp-features-manager' ); ?>" /></label>
		<?php
	}

	/**
	 * Renders the captions menu only when valid tracks exist.
	 *
	 * @param array<int,array<string,string>> $tracks Tracks.
	 * @return void
	 */
	private function render_caption_menu( array $tracks ) {
		if ( empty( $tracks ) ) {
			return;
		}
		?>
		<div class="widgets-manager-custom-video__menu-wrap widgets-manager-custom-video__menu-wrap--captions">
			<button class="widgets-manager-custom-video__control widgets-manager-custom-video__control--captions" type="button" data-wm-menu="captions" aria-expanded="false" aria-haspopup="menu" aria-label="<?php echo esc_attr__( 'Captions', 'wp-features-manager' ); ?>">CC</button>
			<div class="widgets-manager-custom-video__menu" role="menu" hidden><?php $this->render_caption_options( $tracks ); ?></div>
		</div>
		<?php
	}

	/**
	 * Renders the compact overflow menu used at narrow player widths.
	 *
	 * @param array<string,mixed>             $settings Settings.
	 * @param array<int,array<string,string>> $tracks Tracks.
	 * @param string                          $download_url Download URL.
	 * @return void
	 */
	private function render_mobile_overflow_menu( array $settings, array $tracks, $download_url ) {
		?>
		<div class="widgets-manager-custom-video__menu-wrap widgets-manager-custom-video__menu-wrap--more">
			<button class="widgets-manager-custom-video__control" type="button" data-wm-menu="more" aria-expanded="false" aria-haspopup="menu" aria-label="<?php echo esc_attr__( 'More video controls', 'wp-features-manager' ); ?>"><span class="widgets-manager-custom-video__control-icon" aria-hidden="true"><svg viewBox="0 0 24 24" focusable="false"><circle cx="5" cy="12" r="2"/><circle cx="12" cy="12" r="2"/><circle cx="19" cy="12" r="2"/></svg></span></button>
			<div class="widgets-manager-custom-video__menu widgets-manager-custom-video__menu--more" role="menu" hidden>
				<?php $this->render_mobile_seek_options( $settings ); ?>
				<div class="widgets-manager-custom-video__overflow-section widgets-manager-custom-video__menu-wrap--speed" role="group" aria-label="<?php echo esc_attr__( 'Playback speed', 'wp-features-manager' ); ?>"><span class="widgets-manager-custom-video__menu-heading"><?php esc_html_e( 'Playback speed', 'wp-features-manager' ); ?></span><?php $this->render_speed_options(); ?></div>
				<?php $this->render_mobile_caption_options( $tracks ); ?>
				<button class="widgets-manager-custom-video__menu-action widgets-manager-custom-video__control--pip" type="button" role="menuitem" data-wm-action="pip"><span aria-hidden="true">▣</span><?php esc_html_e( 'Picture in Picture', 'wp-features-manager' ); ?></button>
				<a class="widgets-manager-custom-video__menu-action widgets-manager-custom-video__control--download" role="menuitem" href="<?php echo esc_url( $download_url ); ?>" download><span aria-hidden="true">⇩</span><?php esc_html_e( 'Download video', 'wp-features-manager' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Renders rewind and fast-forward overflow actions.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private function render_mobile_seek_options( array $settings ) {
		$seconds = $this->bounded_integer( $settings, 'seek_interval', 5, 30, 10 );
		/* translators: %d: seek interval in seconds. */
		$rewind_label = sprintf( __( 'Rewind %d seconds', 'wp-features-manager' ), $seconds );
		/* translators: %d: seek interval in seconds. */
		$forward_label = sprintf( __( 'Forward %d seconds', 'wp-features-manager' ), $seconds );
		?>
		<button class="widgets-manager-custom-video__menu-action widgets-manager-custom-video__control--rewind" type="button" role="menuitem" data-wm-action="rewind"><span aria-hidden="true">↺</span><?php echo esc_html( $rewind_label ); ?></button>
		<button class="widgets-manager-custom-video__menu-action widgets-manager-custom-video__control--forward" type="button" role="menuitem" data-wm-action="forward"><span aria-hidden="true">↻</span><?php echo esc_html( $forward_label ); ?></button>
		<?php
	}

	/**
	 * Renders shared playback-speed options.
	 *
	 * @return void
	 */
	private function render_speed_options() {
		foreach ( array( 1, 1.25, 1.5, 2 ) as $speed ) {
			echo '<button type="button" role="menuitemradio" aria-checked="' . ( 1 === $speed ? 'true' : 'false' ) . '" data-wm-speed="' . esc_attr( $speed ) . '">' . esc_html( $speed . '×' ) . '</button>';
		}
	}

	/**
	 * Renders shared caption options.
	 *
	 * @param array<int,array<string,string>> $tracks Tracks.
	 * @return void
	 */
	private function render_caption_options( array $tracks ) {
		$has_default_track = in_array( 'yes', wp_list_pluck( $tracks, 'default' ), true );
		?>
		<button type="button" role="menuitemradio" aria-checked="<?php echo $has_default_track ? 'false' : 'true'; ?>" data-wm-track="off"><?php esc_html_e( 'Captions off', 'wp-features-manager' ); ?></button>
		<?php foreach ( $tracks as $index => $track ) : ?>
			<button type="button" role="menuitemradio" aria-checked="<?php echo 'yes' === $track['default'] ? 'true' : 'false'; ?>" data-wm-track="<?php echo esc_attr( $index ); ?>"><?php echo esc_html( $track['label'] ); ?></button>
		<?php endforeach; ?>
		<?php
	}

	/**
	 * Renders caption options in the narrow overflow menu.
	 *
	 * @param array<int,array<string,string>> $tracks Tracks.
	 * @return void
	 */
	private function render_mobile_caption_options( array $tracks ) {
		if ( empty( $tracks ) ) {
			return;
		}
		?>
		<div class="widgets-manager-custom-video__overflow-section widgets-manager-custom-video__menu-wrap--captions" role="group" aria-label="<?php echo esc_attr__( 'Captions', 'wp-features-manager' ); ?>"><span class="widgets-manager-custom-video__menu-heading"><?php esc_html_e( 'Captions', 'wp-features-manager' ); ?></span><?php $this->render_caption_options( $tracks ); ?></div>
		<?php
	}

	/**
	 * Outputs decorative play-animation rings.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $animation Animation identifier.
	 * @return void
	 */
	private function render_animation_rings( array $settings, $animation ) {
		if ( ! in_array( $animation, array( 'rings', 'ripple' ), true ) ) {
			return;
		}
		$ring_count = $this->bounded_integer( $settings, 'ring_count', 1, 3, 2 );
		for ( $ring = 0; $ring < $ring_count; $ring++ ) {
			echo '<span class="widgets-manager-custom-video__ring" aria-hidden="true" style="--wm-video-ring-index: ' . esc_attr( $ring ) . ';"></span>';
		}
	}

	/**
	 * Renders a safe default SVG or Elementor's sanitized icon output.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return void
	 */
	private function render_play_icon( array $settings ) {
		if ( isset( $settings['play_icon_source'], $settings['play_icon']['value'] ) && 'custom' === $settings['play_icon_source'] && ! empty( $settings['play_icon']['value'] ) ) {
			if ( Icons_Manager::render_icon( $settings['play_icon'], array( 'aria-hidden' => 'true' ) ) ) {
				return;
			}
		}
		?>
		<svg viewBox="0 0 24 24" focusable="false" aria-hidden="true"><path d="M8.2 5.1a1.5 1.5 0 0 0-2.2 1.3v11.2a1.5 1.5 0 0 0 2.2 1.3l10-5.6a1.5 1.5 0 0 0 0-2.6l-10-5.6Z" /></svg>
		<?php
	}

	/**
	 * Returns a poster URL generated through Elementor's image-size API.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return string
	 */
	private function poster_url( array $settings ) {
		if ( empty( $settings['poster']['id'] ) && empty( $settings['poster']['url'] ) ) {
			return '';
		}
		if ( ! empty( $settings['poster']['id'] ) ) {
			$image_url = Group_Control_Image_Size::get_attachment_image_src( $settings['poster']['id'], 'poster_image', $settings );
			if ( $image_url ) {
				return esc_url_raw( $image_url );
			}
		}
		return self::nested_url( $settings, 'poster' );
	}

	/**
	 * Returns a safe built-in aspect ratio selection.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @return string
	 */
	private function aspect_ratio_value( array $settings ) {
		$value   = isset( $settings['aspect_ratio'] ) ? sanitize_text_field( $settings['aspect_ratio'] ) : '';
		$allowed = array( '16 / 9', '4 / 3', '1 / 1', '9 / 16', '21 / 9' );
		if ( 'custom' === $value && ! empty( $settings['custom_aspect_ratio'] ) ) {
			$custom_ratio = sanitize_text_field( $settings['custom_aspect_ratio'] );
			if ( preg_match( '/^([1-9][0-9]{0,3}(?:\\.[0-9]{1,2})?)\\s*\\/\\s*([1-9][0-9]{0,3}(?:\\.[0-9]{1,2})?)$/', $custom_ratio, $parts ) ) {
				return $parts[1] . ' / ' . $parts[2];
			}
		}
		return in_array( $value, $allowed, true ) ? $value : '16 / 9';
	}

	/**
	 * Returns allowed control options.
	 *
	 * @return array<string,string>
	 */
	private function mime_options() {
		return array( '' => __( 'Infer from URL', 'wp-features-manager' ), 'video/mp4' => 'video/mp4', 'video/webm' => 'video/webm', 'video/ogg' => 'video/ogg', 'application/x-mpegURL' => 'application/x-mpegURL' );
	}

	/**
	 * Returns animation options.
	 *
	 * @return array<string,string>
	 */
	private function animation_options() {
		return array( 'none' => __( 'None', 'wp-features-manager' ), 'pulse' => __( 'Soft pulse', 'wp-features-manager' ), 'rings' => __( 'Expanding light circles', 'wp-features-manager' ), 'ripple' => __( 'Ripple', 'wp-features-manager' ), 'glow' => __( 'Glow halo', 'wp-features-manager' ), 'float' => __( 'Gentle float', 'wp-features-manager' ), 'hover' => __( 'Hover scale only', 'wp-features-manager' ) );
	}

	/**
	 * Returns independently responsive custom-control definitions.
	 *
	 * @return array<string,array<string,string>>
	 */
	private function control_visibility_definitions() {
		return array(
			'show_play_pause' => array( 'label' => __( 'Play/pause button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--play', 'default' => 'inline-flex' ),
			'show_rewind' => array( 'label' => __( 'Rewind button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--rewind', 'default' => 'inline-flex' ),
			'show_forward' => array( 'label' => __( 'Fast-forward button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--forward', 'default' => 'inline-flex' ),
			'show_progress' => array( 'label' => __( 'Progress timeline', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__seek-label', 'default' => 'inline-flex' ),
			'show_buffered' => array( 'label' => __( 'Buffered progress', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__seek--buffered', 'default' => 'inline-flex' ),
			'show_current_time' => array( 'label' => __( 'Current time', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__time--current', 'default' => 'inline-flex' ),
			'show_duration' => array( 'label' => __( 'Duration', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__time--duration', 'default' => 'inline-flex' ),
			'show_mute' => array( 'label' => __( 'Mute/unmute button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--mute', 'default' => 'inline-flex' ),
			'show_volume' => array( 'label' => __( 'Volume slider', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__volume-label', 'default' => 'inline-flex' ),
			'show_speed' => array( 'label' => __( 'Playback-speed menu', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__menu-wrap--speed', 'default' => 'inline-flex' ),
			'show_captions' => array( 'label' => __( 'Captions/subtitles menu', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__menu-wrap--captions', 'default' => 'inline-flex' ),
			'show_pip' => array( 'label' => __( 'Picture-in-Picture button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--pip', 'default' => 'inline-flex' ),
			'show_fullscreen' => array( 'label' => __( 'Fullscreen button', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--fullscreen', 'default' => 'inline-flex' ),
			'show_download' => array( 'label' => __( 'Download link', 'wp-features-manager' ), 'selector' => '.widgets-manager-custom-video__control--download', 'default' => 'none' ),
		);
	}

	/**
	 * Returns named object focal positions.
	 *
	 * @return array<string,string>
	 */
	private function focal_position_options() {
		return array( 'left top' => __( 'Top left', 'wp-features-manager' ), 'center top' => __( 'Top center', 'wp-features-manager' ), 'right top' => __( 'Top right', 'wp-features-manager' ), 'left center' => __( 'Center left', 'wp-features-manager' ), 'center center' => __( 'Center', 'wp-features-manager' ), 'right center' => __( 'Center right', 'wp-features-manager' ), 'left bottom' => __( 'Bottom left', 'wp-features-manager' ), 'center bottom' => __( 'Bottom center', 'wp-features-manager' ), 'right bottom' => __( 'Bottom right', 'wp-features-manager' ) );
	}

	/**
	 * Returns translated labels consumed by the dependency-free player script.
	 *
	 * @return array<string,string>
	 */
	private function frontend_labels() {
		return array(
			'buffering'              => __( 'Video is buffering.', 'wp-features-manager' ),
			'ready'                  => __( 'Video is ready to play.', 'wp-features-manager' ),
			'error'                  => __( 'This video could not be played.', 'wp-features-manager' ),
			'pip_started'            => __( 'Picture in Picture started.', 'wp-features-manager' ),
			'pip_ended'              => __( 'Picture in Picture ended.', 'wp-features-manager' ),
			'playback_failed'        => __( 'Playback could not start. Select play to try again.', 'wp-features-manager' ),
			'playing'                => __( 'Video playing.', 'wp-features-manager' ),
			'paused'                 => __( 'Video paused.', 'wp-features-manager' ),
			'ended'                  => __( 'Video ended.', 'wp-features-manager' ),
			'unmute'                 => __( 'Unmute video', 'wp-features-manager' ),
			'mute'                   => __( 'Mute video', 'wp-features-manager' ),
			'pause'                  => __( 'Pause video', 'wp-features-manager' ),
			'play'                   => __( 'Play video', 'wp-features-manager' ),
			/* translators: %s: playback speed multiplier. */
			'speed_changed'          => __( 'Playback speed set to %s times.', 'wp-features-manager' ),
			'captions_off'           => __( 'Captions off.', 'wp-features-manager' ),
			'captions_changed'       => __( 'Captions changed.', 'wp-features-manager' ),
			'pip_unavailable'        => __( 'Picture in Picture is unavailable.', 'wp-features-manager' ),
			'fullscreen_unavailable' => __( 'Fullscreen is unavailable.', 'wp-features-manager' ),
			'exit_fullscreen'        => __( 'Exit fullscreen', 'wp-features-manager' ),
			'fullscreen'             => __( 'Fullscreen', 'wp-features-manager' ),
		);
	}

	/**
	 * Returns a sanitized nested Elementor URL setting.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @return string
	 */
	private static function nested_url( array $settings, $key ) {
		$value = isset( $settings[ $key ] ) ? $settings[ $key ] : array();
		$url   = is_array( $value ) && isset( $value['url'] ) ? $value['url'] : ( is_string( $value ) ? $value : '' );
		return esc_url_raw( $url, array( 'http', 'https' ) );
	}

	/**
	 * Determines a trusted MIME type from an extension.
	 *
	 * @param string $url File URL.
	 * @return string
	 */
	private static function mime_from_url( $url ) {
		$extension = strtolower( pathinfo( wp_parse_url( $url, PHP_URL_PATH ), PATHINFO_EXTENSION ) );
		$types     = array( 'mp4' => 'video/mp4', 'm4v' => 'video/mp4', 'webm' => 'video/webm', 'ogv' => 'video/ogg', 'ogg' => 'video/ogg', 'm3u8' => 'application/x-mpegURL' );
		return isset( $types[ $extension ] ) ? $types[ $extension ] : '';
	}

	/**
	 * Checks whether a source URL was already accepted.
	 *
	 * @param array<int,array<string,string>> $sources Sources.
	 * @param string                          $url Candidate URL.
	 * @return bool
	 */
	private static function source_exists( array $sources, $url ) {
		foreach ( $sources as $source ) {
			if ( $url === $source['url'] ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks a switcher setting.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @return bool
	 */
	private function is_enabled( array $settings, $key ) {
		return isset( $settings[ $key ] ) && 'yes' === $settings[ $key ];
	}

	/**
	 * Returns a data attribute boolean.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @return string
	 */
	private function boolean_attribute( array $settings, $key ) {
		return $this->is_enabled( $settings, $key ) ? 'true' : 'false';
	}

	/**
	 * Returns an allowed select value.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @param array<int,string>   $allowed Allowed values.
	 * @param string              $fallback Fallback value.
	 * @return string
	 */
	private function allowed_value( array $settings, $key, array $allowed, $fallback ) {
		$value = isset( $settings[ $key ] ) ? sanitize_key( $settings[ $key ] ) : '';
		return in_array( $value, $allowed, true ) ? $value : $fallback;
	}

	/**
	 * Returns a cleaned text value.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @param string              $fallback Fallback value.
	 * @return string
	 */
	private function text_value( array $settings, $key, $fallback ) {
		$value = isset( $settings[ $key ] ) ? sanitize_text_field( $settings[ $key ] ) : '';
		return '' === $value ? $fallback : $value;
	}

	/**
	 * Returns a bounded integer.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @param int                 $minimum Minimum.
	 * @param int                 $maximum Maximum.
	 * @param int                 $fallback Fallback.
	 * @return int
	 */
	private function bounded_integer( array $settings, $key, $minimum, $maximum, $fallback ) {
		$value = isset( $settings[ $key ] ) ? absint( $settings[ $key ] ) : $fallback;
		return max( $minimum, min( $maximum, $value ) );
	}

	/**
	 * Returns a bounded decimal value.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @param float               $minimum Minimum.
	 * @param float               $maximum Maximum.
	 * @param float               $fallback Fallback.
	 * @return string
	 */
	private function bounded_decimal( array $settings, $key, $minimum, $maximum, $fallback ) {
		$value = isset( $settings[ $key ] ) && is_numeric( $settings[ $key ] ) ? (float) $settings[ $key ] : $fallback;
		return number_format( max( $minimum, min( $maximum, $value ) ), 2, '.', '' );
	}

	/**
	 * Returns a nonnegative time or zero when invalid.
	 *
	 * @param array<string,mixed> $settings Settings.
	 * @param string              $key Setting key.
	 * @return string
	 */
	private function time_value( array $settings, $key ) {
		$value = isset( $settings[ $key ] ) && is_numeric( $settings[ $key ] ) ? max( 0, (float) $settings[ $key ] ) : 0;
		return number_format( $value, 2, '.', '' );
	}

	/**
	 * Determines whether Elementor is rendering its editor.
	 *
	 * @return bool
	 */
	private function is_elementor_editor() {
		return class_exists( '\\Elementor\\Plugin' ) && isset( \Elementor\Plugin::$instance->editor ) && \Elementor\Plugin::$instance->editor->is_edit_mode();
	}
}
