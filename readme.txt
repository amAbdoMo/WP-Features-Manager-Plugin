=== WP Features Manager ===
Contributors: amAbdoMo
Tags: elementor, acf, gallery, widgets, video, duplicate post, custom post types
Requires at least: 6.5
Requires PHP: 7.4
Stable tag: 2.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enable optional WordPress modules and Elementor widgets from one custom management screen.

== Description ==

WP Features Manager provides one custom admin screen with separate Modules and Elementor Widgets tabs. Search, status filters, and bulk actions apply to the active tab, while positive allowlists keep disabled implementation files unloaded.

The disabled-by-default ACF Elementor Gallery module adds the `acfge_gallery` field type and `acfge-gallery` Elementor dynamic tag when Advanced Custom Fields and Elementor 3.20 or later are active. The module preserves the standalone gallery identifiers and stored attachment-ID format.

The disabled-by-default Duplicate Content module adds a small Duplicate row action to Posts, Pages, and standard custom post type list tables. It copies content, taxonomy terms, and custom fields into a new draft without leaving the list. Copy titles use `Title (Copy)`, then `Title (Copy 2)`, `Title (Copy 3)`, and so on.

Version 1.2.1 includes the disabled-by-default Custom Video widget. It is a plugin-owned, dependency-free HTML5 video player for WordPress Media Library videos and direct video-file URLs, with optional alternate sources, poster images, custom overlay/play button, captions, keyboard controls, and individually configurable custom controls. It does not require Elementor Pro, jQuery, or a third-party player library.

The Custom Video widget supports direct HTML5 media only. YouTube and Vimeo are intentionally excluded because iframe rendering cannot offer fully plugin-owned controls. Browsers may reject autoplay unless video is muted and played inline; the widget attempts autoplay once and restores the poster/play state when it is rejected. Browser support determines Picture-in-Picture, fullscreen, native HLS, and delivered media format support. Hiding the optional download link is not download protection.

Each custom control can be independently shown or hidden through Elementor responsive controls, including active custom breakpoints. Widget assets are registered as Elementor dependencies and are not globally enqueued.

== Installation ==

1. Upload the `wp-features-manager` directory to `/wp-content/plugins/`.
2. Deactivate the old Widgets Manager plugin, then activate WP Features Manager.
3. Open Features Manager in the WordPress admin menu.
4. Enable the required module or widget and save changes. Bundled widgets appear in Elementor's WP Features Manager category.

== Frequently Asked Questions ==

= Which video providers are supported? =

WordPress Media Library files and direct browser-playable video-file URLs are supported. YouTube and Vimeo are not supported by Custom Video.

= Does the widget load assets on every page? =

No. Its assets are widget dependencies and are not globally enqueued by WP Features Manager.

= Does hiding Download prevent downloads? =

No. It only hides the optional link; it does not protect a video file that has been delivered to a browser.

= What is removed on uninstall? =

The `wp_features_manager_enabled_modules`, `wp_features_manager_enabled_widgets`, and migrated legacy `widgets_manager_enabled_widgets` options are removed. On multisite, they are removed from every site.

== Changelog ==

= 2.0.0 =
* Added fast client-side Modules and Elementor Widgets tabs with tab-scoped search, filters, and bulk actions.
* Renamed the host to WP Features Manager and added a generic opt-in module system.
* Integrated ACF Elementor Gallery as the first dependency-aware module.
* Added an opt-in Duplicate Content module for Posts, Pages, and custom post types.
* Migrated the complete Before/After Image and Custom Video widgets, assets, controls, custom admin design, and regression tests from Widgets Manager.
* Copies the previous widget allowlist on first use and warns while the old plugin remains active.

= 1.2.13 =
* Added the same scoped hover, keyboard-focus, pressed, and active background treatment to the bottom play/pause button as the other video controls.

= 1.2.12 =
* Fixed aspect-ratio players overflowing their Elementor widget wrapper by synchronizing the wrapper minimum height with the rendered video stage and controls.
* Recalculates the reserved wrapper height after responsive resizing and fullscreen changes.

= 1.2.11 =
* Hardened Custom Video button and menu states against theme or Elementor focus, active, and mobile tap-highlight backgrounds.
* Reduced mobile control-bar padding and established an explicit stage-plus-controls column layout to prevent overlap with following content.

= 1.2.10 =
* Docked the Custom Video control bar below the video stage at narrow player widths so it no longer overlays mobile video content.
* Kept mobile docked controls visible while preserving the overlay layout in fullscreen mode.

= 1.2.9 =
* Restyled the Custom Video bottom overlay with a compact gradient, a thin timeline directly above one control row, and a larger play icon.

= 1.2.8 =
* Reduced Custom Video control-bar padding and the dedicated timeline-row height for a more compact bottom overlay.

= 1.2.7 =
* Replaced the Custom Video widget camera icon with Elementor's clearer circular play icon.

= 1.2.6 =
* Added 3px spacing between Custom Video overflow-menu options and option groups.

= 1.2.5 =
* Fixed Before/After Image labels so the divider progressively clips both labels symmetrically with their visible image regions.

= 1.2.4 =
* Fixed the default Custom Video hover color so icons remain visible against the dark control bar.
* Reduced control-bar padding and added a compact mobile layout with a three-dot overflow menu for secondary actions.

= 1.2.3 =
* Fixed Custom Video control alignment at narrow player widths with centered stacked groups and compact responsive sizing.

= 1.2.2 =
* Fixed Before/After Image so the revealed image is clipped at full size instead of resizing with the divider.
* Added image-aspect-ratio sizing by default and retained an optional responsive custom-height mode.
* Removed the hard-coded mobile minimum height and replaced outline-style handle focus with subtle visual feedback.

= 1.2.1 =
* Fixed Custom Video control alignment and playback-speed menu visibility.
* Replaced the control-bar play glyph with a background-free SVG icon and removed outline-style focus rings.
* Allowed the final fallback-source or captions repeater item to be removed.
* Clarified direct-file, fallback-encoding, and browser codec requirements.

= 1.2.0 =
* Added the disabled-by-default Custom Video Elementor widget with plugin-owned HTML5 controls, captions, responsive dimensions, and conditional assets.

= 1.1.2 =
* Replaced the status select with a fully custom keyboard-accessible menu.
* Removed visible control outlines, simplified Save changes, and made widget rows full width.

= 1.1.1 =
* Fixed the Widgets Manager screen fatal error on installations without a WordPress lowercase helper.
* Replaced native-looking manager controls with custom-styled search, status, and action controls.

= 1.1.0 =
* Added the disabled-by-default Before/After Image Elementor widget with accessible pointer and keyboard comparison controls.
* Added conditional widget asset registration and a Widgets Manager Elementor category.

= 1.0.0 =
* Initial release with an empty widget catalog and Elementor allowlist controls.
